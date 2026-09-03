<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

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
                Limit::perMinute(5)->by('mobile-login-account|'.$nis),
            ];
        });

        RateLimiter::for('mobile-api', function (Request $request): Limit {
            return Limit::perMinute(120)->by(
                'mobile-api|'.($request->user()?->getAuthIdentifier() ?? $request->ip()),
            );
        });
    }
}
