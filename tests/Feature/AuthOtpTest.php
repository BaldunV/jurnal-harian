<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\OtpDeliveryService;
use App\Services\OtpService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Hash;
use Mockery;
use Tests\TestCase;

class AuthOtpTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_with_phone_requires_otp_and_trusts_verified_device(): void
    {
        $user = $this->student(['phone' => '081234567890']);
        $code = '';
        $this->fakeDelivery($code);

        $login = $this->post('/login', [
            'nis' => $user->nis,
            'password' => 'secret123',
            'login_as' => 'siswa',
            'remember' => '1',
        ]);

        $login->assertRedirect(route('login.otp'));
        $login->assertSessionHas(OtpService::PENDING_SESSION_KEY);
        $this->assertGuest();
        $this->assertMatchesRegularExpression('/^[0-9]{6}$/', $code);

        $this->get('/login/otp')
            ->assertOk()
            ->assertSee('Masukkan kode OTP')
            ->assertSee('WhatsApp');

        $verify = $this->post('/login/otp', ['code' => $code]);

        $verify->assertRedirect(route('dashboard'))
            ->assertCookie(OtpService::TRUSTED_DEVICE_COOKIE);
        $this->assertAuthenticatedAs($user);
        $this->assertDatabaseHas('otp_devices', ['user_id' => $user->id]);
    }

    public function test_invalid_otp_keeps_user_out_and_reports_remaining_attempts(): void
    {
        $user = $this->student(['phone' => '081234567890']);
        $code = '';
        $this->fakeDelivery($code);

        $this->post('/login', [
            'nis' => $user->nis,
            'password' => 'secret123',
            'login_as' => 'siswa',
        ]);

        $response = $this->from(route('login.otp'))->post('/login/otp', ['code' => '000000']);

        $response->assertRedirect(route('login.otp'))
            ->assertSessionHasErrors('code');
        $this->assertGuest();
        $this->assertDatabaseCount('otp_devices', 0);
    }

    public function test_sms_channel_can_be_used_and_otp_resend_updates_the_challenge(): void
    {
        Config::set('otp.resend_seconds', 0);

        $user = $this->student([
            'nis' => 'SIS004',
            'phone' => '081234567890',
            'otp_channel' => 'sms',
        ]);
        $code = '';
        $this->fakeDelivery($code, 'sms', 2);

        $this->post('/login', [
            'nis' => $user->nis,
            'password' => 'secret123',
            'login_as' => 'siswa',
        ])->assertRedirect(route('login.otp'));

        $firstCode = $code;
        $resend = $this->from(route('login.otp'))->post('/login/otp/resend');

        $resend->assertRedirect(route('login.otp'))
            ->assertSessionHas('otp_status');
        $this->assertNotSame($firstCode, $code);

        $this->post('/login/otp', ['code' => $code])
            ->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_trusted_device_skips_otp_on_next_login(): void
    {
        $user = $this->student(['phone' => '081234567890']);
        $code = '';
        $this->fakeDelivery($code);

        $this->post('/login', [
            'nis' => $user->nis,
            'password' => 'secret123',
            'login_as' => 'siswa',
        ]);
        $verify = $this->post('/login/otp', ['code' => $code]);
        $trustedToken = $verify->getCookie(OtpService::TRUSTED_DEVICE_COOKIE)->getValue();

        $this->post('/logout');
        $secondLogin = $this->withCookie(OtpService::TRUSTED_DEVICE_COOKIE, $trustedToken)
            ->post('/login', [
                'nis' => $user->nis,
                'password' => 'secret123',
                'login_as' => 'siswa',
            ]);

        $secondLogin->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_login_without_phone_keeps_existing_account_access(): void
    {
        $user = $this->student();

        $response = $this->post('/login', [
            'nis' => $user->nis,
            'password' => 'secret123',
            'login_as' => 'siswa',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);
    }

    private function student(array $overrides = []): User
    {
        return User::create(array_merge([
            'nis' => 'SIS001',
            'name' => 'Siswa OTP',
            'password' => Hash::make('secret123'),
            'role' => 'siswa',
            'kelas' => 'X PPLG',
        ], $overrides));
    }

    private function fakeDelivery(string &$code, string $channel = 'whatsapp', int $calls = 1): void
    {
        $delivery = Mockery::mock(OtpDeliveryService::class);
        $delivery->shouldReceive('send')
            ->times($calls)
            ->withArgs(function (User $user, string $sentCode, string $sentChannel) use (&$code, $channel): bool {
                $code = $sentCode;

                return $user->role === 'siswa' && $sentChannel === $channel;
            });

        $this->app->instance(OtpDeliveryService::class, $delivery);
    }
}
