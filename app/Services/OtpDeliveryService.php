<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class OtpDeliveryService
{
    public function send(User $user, string $code, string $channel): void
    {
        $channel = $this->normalizeChannel($channel);
        $driver = (string) config("otp.channels.{$channel}.driver", 'log');
        $message = sprintf(
            'Kode OTP Jurnal 7 Kebiasaan Anda adalah %s. Kode berlaku %d menit. Jangan bagikan kode ini kepada siapa pun.',
            $code,
            (int) config('otp.expires_minutes', 5),
        );

        match ($driver) {
            'fonnte' => $this->sendWithFonnte($user, $message),
            'twilio' => $this->sendWithTwilio($user, $message, $channel),
            'log' => Log::notice('OTP login generated in log mode.', [
                'user_id' => $user->id,
                'channel' => $channel,
                'phone' => $user->phone,
                'code' => $code,
            ]),
            default => throw new RuntimeException('Driver OTP tidak dikenali.'),
        };
    }

    private function sendWithFonnte(User $user, string $message): void
    {
        $token = config('otp.fonnte.token');

        if (! $token) {
            throw new RuntimeException('FONNTE_TOKEN belum dikonfigurasi.');
        }

        $response = Http::withHeaders([
            'Authorization' => $token,
        ])->asForm()->timeout(15)->post(config('otp.fonnte.url'), [
            'target' => ltrim((string) $user->phone, '+'),
            'message' => $message,
        ]);

        if (! $response->successful() || $response->json('status') === false) {
            throw new RuntimeException('OTP WhatsApp gagal dikirim melalui Fonnte.');
        }
    }

    private function sendWithTwilio(User $user, string $message, string $channel): void
    {
        $twilio = config('otp.twilio', []);
        $accountSid = $twilio['account_sid'] ?? null;
        $authToken = $twilio['auth_token'] ?? null;
        $from = $channel === 'whatsapp'
            ? ($twilio['whatsapp_from'] ?? $twilio['from'] ?? null)
            : ($twilio['from'] ?? null);

        if (! $accountSid || ! $authToken || ! $from) {
            throw new RuntimeException('Konfigurasi Twilio belum lengkap.');
        }

        $prefix = $channel === 'whatsapp' ? 'whatsapp:' : '';
        $from = str_starts_with($from, $prefix) ? $from : $prefix.$from;
        $to = $channel === 'whatsapp' ? 'whatsapp:'.$user->phone : $user->phone;
        $url = rtrim((string) ($twilio['url'] ?? ''), '/')
            .'/Accounts/'.rawurlencode($accountSid).'/Messages.json';

        $response = Http::withBasicAuth($accountSid, $authToken)
            ->asForm()
            ->timeout(15)
            ->post($url, [
                'From' => $from,
                'To' => $to,
                'Body' => $message,
            ]);

        if (! $response->successful()) {
            throw new RuntimeException('OTP gagal dikirim melalui Twilio.');
        }
    }

    private function normalizeChannel(string $channel): string
    {
        return in_array($channel, ['whatsapp', 'sms'], true) ? $channel : 'whatsapp';
    }
}
