<?php

namespace App\Services;

use App\Models\OtpDevice;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Cookie;
use Throwable;

class OtpService
{
    public const PENDING_SESSION_KEY = 'auth.otp.pending';

    public const TRUSTED_DEVICE_COOKIE = 'otp_trusted_device';

    public function __construct(private readonly OtpDeliveryService $delivery)
    {
    }

    public function createChallenge(User $user, string $channel): array
    {
        $channel = $this->normalizeChannel($channel);
        $challengeId = Str::random(48);
        $now = now();
        $expiresAt = $now->copy()->addMinutes((int) config('otp.expires_minutes', 5));
        $code = $this->generateCode();
        $payload = $this->buildPayload($user, $channel, $code, $now, $expiresAt);

        try {
            $this->delivery->send($user, $code, $channel);
        } catch (Throwable $exception) {
            throw $exception;
        }

        Cache::put($this->cacheKey($challengeId), $this->cachePayload($payload), $expiresAt);

        return [
            'id' => $challengeId,
            'channel' => $channel,
            'sent_at' => $payload['sent_at'],
            'resend_at' => $payload['resend_at'],
            'expires_at' => $payload['expires_at'],
        ];
    }

    public function getChallenge(string $challengeId): ?array
    {
        $payload = Cache::get($this->cacheKey($challengeId));

        if (! is_array($payload)) {
            return null;
        }

        unset($payload['code']);

        return $payload;
    }

    public function resendChallenge(string $challengeId, User $user): array
    {
        $key = $this->cacheKey($challengeId);
        $payload = Cache::get($key);

        if (! is_array($payload) || (int) ($payload['user_id'] ?? 0) !== $user->id) {
            return ['status' => 'expired'];
        }

        $now = now();
        $retryAfter = (int) ($payload['resend_at'] ?? 0) - $now->timestamp;
        if ($retryAfter > 0) {
            return ['status' => 'throttled', 'retry_after' => $retryAfter];
        }

        $expiresAt = now()->setTimestamp((int) $payload['expires_at']);
        if ($expiresAt->isPast()) {
            Cache::forget($key);

            return ['status' => 'expired'];
        }

        $channel = $this->normalizeChannel((string) ($payload['channel'] ?? $user->otp_channel));
        $code = $this->generateCode();
        $nextPayload = $this->buildPayload($user, $channel, $code, $now, $expiresAt);

        $this->delivery->send($user, $code, $channel);
        Cache::put($key, $this->cachePayload($nextPayload), $expiresAt);

        return [
            'status' => 'ok',
            'channel' => $channel,
            'sent_at' => $nextPayload['sent_at'],
            'resend_at' => $nextPayload['resend_at'],
            'expires_at' => $nextPayload['expires_at'],
        ];
    }

    public function verifyChallenge(string $challengeId, User $user, string $code): array
    {
        $key = $this->cacheKey($challengeId);
        $payload = Cache::get($key);

        if (! is_array($payload) || (int) ($payload['user_id'] ?? 0) !== $user->id) {
            return ['status' => 'expired'];
        }

        if ((int) ($payload['expires_at'] ?? 0) <= now()->timestamp) {
            Cache::forget($key);

            return ['status' => 'expired'];
        }

        $attempts = (int) ($payload['attempts'] ?? 0);
        $maxAttempts = (int) config('otp.max_attempts', 5);
        if ($attempts >= $maxAttempts) {
            Cache::forget($key);

            return ['status' => 'locked'];
        }

        if (! Hash::check($code, (string) ($payload['code_hash'] ?? ''))) {
            $attempts++;
            $payload['attempts'] = $attempts;
            $ttl = max(1, (int) $payload['expires_at'] - now()->timestamp);
            Cache::put($key, $payload, now()->addSeconds($ttl));

            return $attempts >= $maxAttempts
                ? ['status' => 'locked']
                : ['status' => 'invalid', 'remaining' => $maxAttempts - $attempts];
        }

        Cache::forget($key);

        return ['status' => 'ok'];
    }

    public function hasTrustedDevice(User $user, Request $request): bool
    {
        $token = $request->cookie(self::TRUSTED_DEVICE_COOKIE);
        if (! is_string($token) || $token === '') {
            return false;
        }

        $device = OtpDevice::query()
            ->where('user_id', $user->id)
            ->where('token_hash', hash('sha256', $token))
            ->where('expires_at', '>', now())
            ->first();

        if (! $device) {
            return false;
        }

        $device->forceFill(['last_used_at' => now()])->save();

        return true;
    }

    public function issueTrustedDeviceCookie(User $user, Request $request): Cookie
    {
        OtpDevice::where('user_id', $user->id)
            ->where('expires_at', '<=', now())
            ->delete();

        $token = Str::random(64);
        OtpDevice::create([
            'user_id' => $user->id,
            'token_hash' => hash('sha256', $token),
            'last_used_at' => now(),
            'expires_at' => now()->addDays((int) config('otp.trusted_device_days', 30)),
            'user_agent' => Str::limit((string) $request->userAgent(), 500, ''),
        ]);

        return cookie(
            self::TRUSTED_DEVICE_COOKIE,
            $token,
            (int) config('otp.trusted_device_days', 30) * 24 * 60,
            '/',
            null,
            $request->isSecure(),
            true,
            false,
            'lax',
        );
    }

    public function revokeTrustedDevices(User $user): void
    {
        $user->otpDevices()->delete();
    }

    public function maskPhone(?string $phone): string
    {
        $phone = (string) $phone;
        if ($phone === '') {
            return 'nomor HP terdaftar';
        }

        $visible = substr($phone, -4);

        return substr($phone, 0, 3).' **** '.$visible;
    }

    public function channelLabel(string $channel): string
    {
        return $this->normalizeChannel($channel) === 'sms' ? 'SMS' : 'WhatsApp';
    }

    private function buildPayload(User $user, string $channel, string $code, $createdAt, $expiresAt): array
    {
        return [
            'user_id' => $user->id,
            'channel' => $channel,
            'code' => $code,
            'code_hash' => Hash::make($code),
            'attempts' => 0,
            'sent_at' => $createdAt->timestamp,
            'resend_at' => $createdAt->copy()->addSeconds((int) config('otp.resend_seconds', 60))->timestamp,
            'expires_at' => $expiresAt->timestamp,
        ];
    }

    private function cachePayload(array $payload): array
    {
        unset($payload['code']);

        return $payload;
    }

    private function generateCode(): string
    {
        return (string) random_int(100000, 999999);
    }

    private function cacheKey(string $challengeId): string
    {
        return 'login-otp:'.$challengeId;
    }

    private function normalizeChannel(string $channel): string
    {
        return in_array($channel, ['whatsapp', 'sms'], true) ? $channel : 'whatsapp';
    }
}
