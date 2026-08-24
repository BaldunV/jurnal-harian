<?php

return [
    'expires_minutes' => (int) env('OTP_EXPIRES_MINUTES', 5),
    'resend_seconds' => (int) env('OTP_RESEND_SECONDS', 60),
    'max_attempts' => (int) env('OTP_MAX_ATTEMPTS', 5),
    'trusted_device_days' => (int) env('OTP_TRUSTED_DEVICE_DAYS', 30),

    'channels' => [
        'whatsapp' => [
            'driver' => env('OTP_WHATSAPP_DRIVER', 'log'),
        ],
        'sms' => [
            'driver' => env('OTP_SMS_DRIVER', 'log'),
        ],
    ],

    'fonnte' => [
        'url' => env('FONNTE_URL', 'https://api.fonnte.com/send'),
        'token' => env('FONNTE_TOKEN'),
    ],

    'twilio' => [
        'account_sid' => env('TWILIO_ACCOUNT_SID'),
        'auth_token' => env('TWILIO_AUTH_TOKEN'),
        'from' => env('TWILIO_FROM'),
        'whatsapp_from' => env('TWILIO_WHATSAPP_FROM'),
        'url' => env('TWILIO_URL', 'https://api.twilio.com/2010-04-01'),
    ],
];
