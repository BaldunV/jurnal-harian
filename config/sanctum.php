<?php

use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Laravel\Sanctum\Http\Middleware\AuthenticateSession;

return [
    'stateful' => [],
    'guard' => [],
    'expiration' => null,
    'last_used_at' => true,
    'token_prefix' => env('SANCTUM_TOKEN_PREFIX', ''),
    'mobile_token_expiration_days' => (int) env('SANCTUM_MOBILE_TOKEN_DAYS', 30),
    'routes' => false,
    'middleware' => [
        'authenticate_session' => AuthenticateSession::class,
        'encrypt_cookies' => EncryptCookies::class,
        'validate_csrf_token' => ValidateCsrfToken::class,
    ],
];
