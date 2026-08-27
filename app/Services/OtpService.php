<?php

namespace App\Services;

use App\Models\OtpDevice;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Cookie;

class OtpService
{
    public const PENDING_SESSION_KEY = 'auth.otp.pending';

    public const TRUSTED_DEVICE_COOKIE = 'otp_trusted_device';

    public function __construct(private readonly OtpDeliveryService $delivery)
    {
    }

    public function createChallenge(
        User $user,
        string $channel,
        string $purpose = 'web-login',
        array $context = [],
    ): array {
        $channel = $this->normalizeChannel($channel);
        $purpose = $this->normalizePurpose($purpose);

        return Cache::lock($this->creationLockKey($user, $purpose), 10)->block(3, function () use (
            $user,
            $channel,
            $purpose,
            $context,
        ): array {
            $challengeId = Str::random(48);
            $now = now();
            $expiresAt = $now->copy()->addMinutes((int) config('otp.expires_minutes', 5));
            $code = $this->generateCode();
            $payload = $this->buildPayload(
                $user,
                $channel,
                $code,
                $now,
                $expiresAt,
                $purpose,
                $context,
            );

            $this->delivery->send($user, $code, $channel);

            $activeKey = $this->activeChallengeKey($user->id, $purpose);
            $previousChallengeId = Cache::get($activeKey);
            if (is_string($previousChallengeId)) {
                Cache::forget($this->cacheKey($previousChallengeId));
            }

            Cache::put($this->cacheKey($challengeId), $payload, $expiresAt);
            Cache::put($activeKey, $challengeId, $expiresAt);

            return [
                'id' => $challengeId,
                'channel' => $channel,
                'sent_at' => $payload['sent_at'],
                'resend_at' => $payload['resend_at'],
                'expires_at' => $payload['expires_at'],
            ];
        });
    }

    public function getChallenge(string $challengeId): ?array
    {
        $payload = $this->challengePayload($challengeId);
        if (! $payload) {
            return null;
        }

        return [
            'channel' => $payload['channel'],
            'sent_at' => $payload['sent_at'],
            'resend_at' => $payload['resend_at'],
            'expires_at' => $payload['expires_at'],
        ];
    }

    /**
     * @return array{user: User, context: array<string, mixed>}|null
     */
    public function resolveChallenge(string $challengeId, string $purpose): ?array
    {
        $payload = $this->challengePayload($challengeId);
        if (! $payload || ($payload['purpose'] ?? null) !== $this->normalizePurpose($purpose)) {
            return null;
        }

        $user = User::find($payload['user_id'] ?? null);
        if (! $user) {
            $this->forgetChallenge($challengeId, $payload);

            return null;
        }

        return [
            'user' => $user,
            'context' => is_array($payload['context'] ?? null) ? $payload['context'] : [],
        ];
    }

    public function resendChallenge(
        string $challengeId,
        User $user,
        ?string $purpose = null,
    ): array {
        return Cache::lock($this->challengeLockKey($challengeId), 10)->block(3, function () use (
            $challengeId,
            $user,
            $purpose,
        ): array {
            $key = $this->cacheKey($challengeId);
            $payload = Cache::get($key);

            if (! $this->payloadMatches($payload, $user, $purpose)) {
                return ['status' => 'expired'];
            }

            $now = now();
            $retryAfter = (int) ($payload['resend_at'] ?? 0) - $now->timestamp;
            if ($retryAfter > 0) {
                return ['status' => 'throttled', 'retry_after' => $retryAfter];
            }

            $expiresAt = now()->setTimestamp((int) $payload['expires_at']);
            if ($expiresAt->isPast()) {
                $this->forgetChallenge($challengeId, $payload);

                return ['status' => 'expired'];
            }

            $channel = $this->normalizeChannel((string) ($payload['channel'] ?? $user->otp_channel));
            $code = $this->generateCode();

            $this->delivery->send($user, $code, $channel);

            $payload['channel'] = $channel;
            $payload['code_hash'] = Hash::make($code);
            $payload['sent_at'] = $now->timestamp;
            $payload['resend_at'] = $now->copy()
                ->addSeconds((int) config('otp.resend_seconds', 60))
                ->timestamp;
            $payload['sends'] = (int) ($payload['sends'] ?? 1) + 1;
            Cache::put($key, $payload, $expiresAt);

            return [
                'status' => 'ok',
                'channel' => $channel,
                'sent_at' => $payload['sent_at'],
                'resend_at' => $payload['resend_at'],
                'expires_at' => $payload['expires_at'],
            ];
        });
    }

    public function verifyChallenge(
        string $challengeId,
        User $user,
        string $code,
        ?string $purpose = null,
    ): array {
        return Cache::lock($this->challengeLockKey($challengeId), 10)->block(3, function () use (
            $challengeId,
            $user,
            $code,
            $purpose,
        ): array {
            $key = $this->cacheKey($challengeId);
            $payload = Cache::get($key);

            if (! $this->payloadMatches($payload, $user, $purpose)) {
                return ['status' => 'expired'];
            }

            if ((int) ($payload['expires_at'] ?? 0) <= now()->timestamp) {
                $this->forgetChallenge($challengeId, $payload);

                return ['status' => 'expired'];
            }

            $attempts = (int) ($payload['attempts'] ?? 0);
            $maxAttempts = (int) config('otp.max_attempts', 5);
            if ($attempts >= $maxAttempts) {
                $this->forgetChallenge($challengeId, $payload);

                return ['status' => 'locked'];
            }

            if (! Hash::check($code, (string) ($payload['code_hash'] ?? ''))) {
                $attempts++;
                $payload['attempts'] = $attempts;

                if ($attempts >= $maxAttempts) {
                    $this->forgetChallenge($challengeId, $payload);

                    return ['status' => 'locked'];
                }

                $ttl = max(1, (int) $payload['expires_at'] - now()->timestamp);
                Cache::put($key, $payload, now()->addSeconds($ttl));

                return ['status' => 'invalid', 'remaining' => $maxAttempts - $attempts];
            }

            $this->forgetChallenge($challengeId, $payload);

            return ['status' => 'ok'];
        });
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

    private function buildPayload(
        User $user,
        string $channel,
        string $code,
        $createdAt,
        $expiresAt,
        string $purpose,
        array $context,
    ): array
    {
        return [
            'user_id' => $user->id,
            'channel' => $channel,
            'code_hash' => Hash::make($code),
            'attempts' => 0,
            'sends' => 1,
            'purpose' => $purpose,
            'context' => $context,
            'sent_at' => $createdAt->timestamp,
            'resend_at' => $createdAt->copy()->addSeconds((int) config('otp.resend_seconds', 60))->timestamp,
            'expires_at' => $expiresAt->timestamp,
        ];
    }

    private function challengePayload(string $challengeId): ?array
    {
        $payload = Cache::get($this->cacheKey($challengeId));
        if (! is_array($payload)) {
            return null;
        }

        if ((int) ($payload['expires_at'] ?? 0) <= now()->timestamp) {
            $this->forgetChallenge($challengeId, $payload);

            return null;
        }

        return $payload;
    }

    private function payloadMatches(mixed $payload, User $user, ?string $purpose): bool
    {
        if (! is_array($payload) || (int) ($payload['user_id'] ?? 0) !== $user->id) {
            return false;
        }

        return $purpose === null
            || ($payload['purpose'] ?? null) === $this->normalizePurpose($purpose);
    }

    private function forgetChallenge(string $challengeId, array $payload): void
    {
        Cache::forget($this->cacheKey($challengeId));

        $purpose = $this->normalizePurpose((string) ($payload['purpose'] ?? 'web-login'));
        $activeKey = $this->activeChallengeKey((int) ($payload['user_id'] ?? 0), $purpose);
        if (Cache::get($activeKey) === $challengeId) {
            Cache::forget($activeKey);
        }
    }

    private function generateCode(): string
    {
        return (string) random_int(100000, 999999);
    }

    private function cacheKey(string $challengeId): string
    {
        return 'login-otp:'.$challengeId;
    }

    private function challengeLockKey(string $challengeId): string
    {
        return 'login-otp-lock:'.$challengeId;
    }

    private function creationLockKey(User $user, string $purpose): string
    {
        return 'login-otp-create:'.$purpose.':'.$user->id;
    }

    private function activeChallengeKey(int $userId, string $purpose): string
    {
        return 'login-otp-active:'.$purpose.':'.$userId;
    }

    private function normalizeChannel(string $channel): string
    {
        return in_array($channel, ['whatsapp', 'sms'], true) ? $channel : 'whatsapp';
    }

    private function normalizePurpose(string $purpose): string
    {
        $purpose = preg_replace('/[^a-z0-9-]/', '', Str::lower($purpose)) ?? '';

        return $purpose !== '' ? $purpose : 'web-login';
    }
}
