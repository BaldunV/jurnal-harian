<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        RateLimiter::for('mobile-login', function (Request $request): array {
            $nis = Str::lower(trim((string) $request->input('nis')));

            return [
                Limit::perMinute(10)->by('mobile-login-ip|'.$request->ip()),
                Limit::perMinute(5)->by('mobile-login-account|'.$request->ip().'|'.$nis),
            ];
        });

        RateLimiter::for('mobile-otp', function (Request $request): array {
            $challenge = (string) $request->input('challenge_id');

            return [
                Limit::perMinute(10)->by('mobile-otp-ip|'.$request->ip()),
                Limit::perMinute(5)->by('mobile-otp-challenge|'.$request->ip().'|'.$challenge),
            ];
        });

        RateLimiter::for('mobile-otp-resend', function (Request $request): Limit {
            return Limit::perMinute(3)->by(
                'mobile-otp-resend|'.$request->ip().'|'.(string) $request->input('challenge_id'),
            );
        });

        RateLimiter::for('mobile-api', function (Request $request): Limit {
            return Limit::perMinute(120)->by(
                'mobile-api|'.($request->user()?->getAuthIdentifier() ?? $request->ip()),
            );
        });
    }
}
