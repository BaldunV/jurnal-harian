<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\LoginRequest;
use App\Http\Requests\Api\ResendOtpRequest;
use App\Http\Requests\Api\VerifyOtpRequest;
use App\Http\Resources\StudentResource;
use App\Models\User;
use App\Services\OtpService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Throwable;

class AuthController extends ApiController
{
    private const CHALLENGE_PURPOSE = 'mobile-login';

    public function __construct(private readonly OtpService $otp) {}

    public function login(LoginRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $user = User::query()->where('nis', $validated['nis'])->first();

        if (! $user || ! Hash::check($validated['password'], $user->password)) {
            return $this->error(
                'NIS atau password yang dimasukkan salah.',
                401,
                code: 'invalid_credentials',
            );
        }

        if ($user->role !== 'siswa') {
            return $this->error(
                'Aplikasi mobile hanya tersedia untuk siswa.',
                403,
                code: 'student_only',
            );
        }

        if (! $user->phone) {
            return $this->error(
                'Nomor HP untuk OTP belum dikonfigurasi. Hubungi administrator.',
                403,
                code: 'otp_setup_required',
            );
        }

        try {
            $challenge = $this->otp->createChallenge(
                $user,
                $user->otp_channel,
                self::CHALLENGE_PURPOSE,
                ['device_name' => $validated['device_name']],
            );
        } catch (Throwable $exception) {
            report($exception);

            return $this->error(
                'Kode OTP belum dapat dikirim. Coba beberapa saat lagi.',
                503,
                code: 'otp_delivery_failed',
            );
        }

        return $this->success([
            'otp_required' => true,
            'challenge_id' => $challenge['id'],
            'channel' => $challenge['channel'],
            'channel_label' => $this->otp->channelLabel($challenge['channel']),
            'masked_phone' => $this->otp->maskPhone($user->phone),
            'sent_at' => $challenge['sent_at'],
            'resend_at' => $challenge['resend_at'],
            'expires_at' => $challenge['expires_at'],
        ], 'Kode OTP telah dikirim.', 202);
    }

    public function verifyOtp(VerifyOtpRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $subject = $this->otp->resolveChallenge(
            $validated['challenge_id'],
            self::CHALLENGE_PURPOSE,
        );

        if (! $subject) {
            return $this->error(
                'Kode OTP sudah kedaluwarsa. Silakan login kembali.',
                410,
                code: 'otp_expired',
            );
        }

        try {
            $result = $this->otp->verifyChallenge(
                $validated['challenge_id'],
                $subject['user'],
                $validated['code'],
                self::CHALLENGE_PURPOSE,
            );
        } catch (Throwable $exception) {
            report($exception);

            return $this->error(
                'Verifikasi OTP belum dapat diproses. Coba kembali.',
                503,
                code: 'otp_verification_failed',
            );
        }

        if ($result['status'] === 'invalid') {
            return $this->error(
                'Kode OTP salah. Sisa percobaan: '.$result['remaining'].'.',
                422,
                ['code' => ['Kode OTP tidak valid.']],
                'otp_invalid',
            );
        }

        if ($result['status'] === 'locked') {
            return $this->error(
                'Percobaan OTP habis. Silakan login kembali.',
                429,
                code: 'otp_attempts_exhausted',
            );
        }

        if ($result['status'] !== 'ok') {
            return $this->error(
                'Kode OTP sudah kedaluwarsa. Silakan login kembali.',
                410,
                code: 'otp_expired',
            );
        }

        $user = $subject['user']->fresh();
        if (! $user || $user->role !== 'siswa') {
            return $this->error(
                'Akun tidak diizinkan menggunakan aplikasi mobile.',
                403,
                code: 'student_only',
            );
        }

        $expiresAt = now()->addDays((int) config('sanctum.mobile_token_expiration_days', 30));
        $deviceName = (string) ($subject['context']['device_name'] ?? 'Flutter');
        $token = $user->createToken($deviceName, ['student'], $expiresAt);

        return $this->success([
            'token' => $token->plainTextToken,
            'token_type' => 'Bearer',
            'expires_at' => $expiresAt->toIso8601String(),
            'user' => new StudentResource($user),
        ], 'Login berhasil.');
    }

    public function resendOtp(ResendOtpRequest $request): JsonResponse
    {
        $challengeId = $request->validated('challenge_id');
        $subject = $this->otp->resolveChallenge($challengeId, self::CHALLENGE_PURPOSE);

        if (! $subject) {
            return $this->error(
                'Kode OTP sudah kedaluwarsa. Silakan login kembali.',
                410,
                code: 'otp_expired',
            );
        }

        try {
            $result = $this->otp->resendChallenge(
                $challengeId,
                $subject['user'],
                self::CHALLENGE_PURPOSE,
            );
        } catch (Throwable $exception) {
            report($exception);

            return $this->error(
                'Kode OTP belum dapat dikirim ulang. Coba beberapa saat lagi.',
                503,
                code: 'otp_delivery_failed',
            );
        }

        if ($result['status'] === 'throttled') {
            return $this->error(
                'Tunggu '.$result['retry_after'].' detik sebelum meminta kode baru.',
                429,
                code: 'otp_resend_throttled',
            );
        }

        if ($result['status'] !== 'ok') {
            return $this->error(
                'Kode OTP sudah kedaluwarsa. Silakan login kembali.',
                410,
                code: 'otp_expired',
            );
        }

        return $this->success([
            'channel' => $result['channel'],
            'sent_at' => $result['sent_at'],
            'resend_at' => $result['resend_at'],
            'expires_at' => $result['expires_at'],
        ], 'Kode OTP baru telah dikirim.');
    }

    public function me(Request $request): JsonResponse
    {
        return $this->success(new StudentResource($request->user()));
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()?->delete();
        Auth::guard('sanctum')->forgetUser();

        return $this->success(message: 'Logout berhasil.');
    }
}
