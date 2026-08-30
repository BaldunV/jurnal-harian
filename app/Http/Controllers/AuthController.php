<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\OtpService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Throwable;

class AuthController extends Controller
{
    public function __construct(private readonly OtpService $otp) {}

    public function showLogin()
    {
        if (Auth::check()) {
            return $this->redirectByRole(Auth::user());
        }

        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'nis' => ['required', 'string'],
            'password' => ['required', 'string'],
            'login_as' => ['required', 'in:siswa,staff'],
        ], [
            'nis.required' => 'NIS / Username wajib diisi.',
            'password.required' => 'Password wajib diisi.',
        ]);

        $nis = trim($credentials['nis']);
        $throttleKey = 'login|'.$request->ip().'|'.Str::lower($nis);

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            return back()->withErrors([
                'nis' => 'Terlalu banyak percobaan login. Coba lagi dalam '.RateLimiter::availableIn($throttleKey).' detik.',
            ])->onlyInput('nis', 'login_as');
        }

        $validPassword = Auth::validate([
            'nis' => $nis,
            'password' => $credentials['password'],
        ]);
        $user = User::where('nis', $nis)->first();

        if (! $validPassword || ! $user) {
            RateLimiter::hit($throttleKey, 60);

            return back()->withErrors([
                'nis' => 'NIS atau password yang dimasukkan salah.',
            ])->onlyInput('nis', 'login_as');
        }

        $validPortal = $credentials['login_as'] === 'siswa'
            ? $user->role === 'siswa'
            : in_array($user->role, ['admin', 'guru'], true);

        if (! $validPortal) {
            return back()->withErrors([
                'nis' => 'Silakan gunakan portal login yang sesuai dengan peran akun Anda.',
            ])->onlyInput('nis', 'login_as');
        }

        RateLimiter::clear($throttleKey);
        $remember = $request->boolean('remember');

        if (! $user->phone) {
            return back()->withErrors([
                'nis' => 'Nomor HP belum terdaftar. Hubungi administrator untuk mengaktifkan OTP.',
            ])->onlyInput('nis', 'login_as');
        }

        try {
            $challenge = $this->otp->createChallenge($user, $user->otp_channel);
        } catch (Throwable $exception) {
            report($exception);

            return back()->withErrors([
                'nis' => 'Kode OTP belum dapat dikirim. Periksa nomor HP akun atau hubungi administrator.',
            ])->onlyInput('nis', 'login_as');
        }

        $request->session()->put(OtpService::PENDING_SESSION_KEY, [
            'challenge_id' => $challenge['id'],
            'user_id' => $user->id,
            'remember' => $remember,
            'channel' => $challenge['channel'],
            'sent_at' => $challenge['sent_at'],
            'resend_at' => $challenge['resend_at'],
            'expires_at' => $challenge['expires_at'],
        ]);

        return redirect()->route('login.otp');
    }

    public function showOtp(Request $request)
    {
        $pending = $request->session()->get(OtpService::PENDING_SESSION_KEY);
        $user = $pending ? User::find($pending['user_id'] ?? null) : null;
        $challenge = $pending ? $this->otp->getChallenge($pending['challenge_id'] ?? '') : null;

        if (! $pending || ! $user || ! $challenge) {
            $request->session()->forget(OtpService::PENDING_SESSION_KEY);

            return redirect()->route('login')->withErrors([
                'nis' => 'Sesi OTP sudah kedaluwarsa. Silakan login kembali.',
            ]);
        }

        return view('auth.otp', [
            'maskedPhone' => $this->otp->maskPhone($user->phone),
            'channelLabel' => $this->otp->channelLabel($challenge['channel'] ?? $user->otp_channel),
            'expiresAt' => (int) $challenge['expires_at'],
            'resendAt' => (int) $challenge['resend_at'],
        ]);
    }

    public function verifyOtp(Request $request)
    {
        $validated = $request->validate([
            'code' => ['required', 'digits:6'],
        ], [
            'code.required' => 'Kode OTP wajib diisi.',
            'code.digits' => 'Kode OTP harus terdiri dari 6 angka.',
        ]);

        $pending = $request->session()->get(OtpService::PENDING_SESSION_KEY);
        $user = $pending ? User::find($pending['user_id'] ?? null) : null;

        if (! $pending || ! $user) {
            $request->session()->forget(OtpService::PENDING_SESSION_KEY);

            return redirect()->route('login')->withErrors([
                'nis' => 'Sesi OTP sudah kedaluwarsa. Silakan login kembali.',
            ]);
        }

        $result = $this->otp->verifyChallenge(
            (string) $pending['challenge_id'],
            $user,
            $validated['code'],
        );

        if ($result['status'] === 'invalid') {
            return back()->withErrors([
                'code' => 'Kode OTP salah. Sisa percobaan: '.$result['remaining'].'.',
            ])->withInput();
        }

        if ($result['status'] !== 'ok') {
            $request->session()->forget(OtpService::PENDING_SESSION_KEY);

            return redirect()->route('login')->withErrors([
                'nis' => $result['status'] === 'locked'
                    ? 'Percobaan OTP habis. Silakan login kembali untuk meminta kode baru.'
                    : 'Kode OTP sudah kedaluwarsa. Silakan login kembali.',
            ]);
        }

        $response = $this->finishLogin($request, $user, (bool) ($pending['remember'] ?? false));

        return $response
            ->withCookie($this->otp->issueTrustedDeviceCookie($user, $request))
            ->with('success', 'Verifikasi berhasil. Selamat datang kembali!');
    }

    public function resendOtp(Request $request)
    {
        $pending = $request->session()->get(OtpService::PENDING_SESSION_KEY);
        $user = $pending ? User::find($pending['user_id'] ?? null) : null;

        if (! $pending || ! $user) {
            $request->session()->forget(OtpService::PENDING_SESSION_KEY);

            return redirect()->route('login')->withErrors([
                'nis' => 'Sesi OTP sudah kedaluwarsa. Silakan login kembali.',
            ]);
        }

        try {
            $result = $this->otp->resendChallenge((string) $pending['challenge_id'], $user);
        } catch (Throwable $exception) {
            report($exception);

            return back()->withErrors([
                'code' => 'Kode OTP belum dapat dikirim ulang. Coba beberapa saat lagi.',
            ]);
        }

        if ($result['status'] === 'throttled') {
            return back()->withErrors([
                'code' => 'Tunggu '.$result['retry_after'].' detik sebelum meminta kode baru.',
            ]);
        }

        if ($result['status'] !== 'ok') {
            $request->session()->forget(OtpService::PENDING_SESSION_KEY);

            return redirect()->route('login')->withErrors([
                'nis' => 'Kode OTP sudah kedaluwarsa. Silakan login kembali.',
            ]);
        }

        $request->session()->put(OtpService::PENDING_SESSION_KEY, array_merge($pending, [
            'sent_at' => $result['sent_at'],
            'resend_at' => $result['resend_at'],
            'expires_at' => $result['expires_at'],
        ]));

        return back()->with('otp_status', 'Kode OTP baru telah dikirim melalui '.$this->otp->channelLabel($result['channel']).'.');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Anda telah keluar dari aplikasi.');
    }

    private function finishLogin(Request $request, User $user, bool $remember)
    {
        Auth::login($user, $remember);
        $request->session()->regenerate();
        $request->session()->forget(OtpService::PENDING_SESSION_KEY);

        return $this->redirectByRole($user);
    }

    private function redirectByRole(User $user)
    {
        return match ($user->role) {
            'admin' => redirect()->route('admin.dashboard'),
            'guru' => redirect()->route('teacher.index'),
            default => redirect()->route('dashboard'),
        };
    }
}
