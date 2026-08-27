<?php

namespace Tests\Feature\Api;

use App\Models\User;
use App\Services\OtpDeliveryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Hash;
use Mockery;
use Tests\TestCase;

class AuthApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_requires_valid_credentials_and_uses_the_api_envelope(): void
    {
        $user = $this->student(['nis' => 'API001', 'phone' => '081234567890']);

        $response = $this->postJson('/api/auth/login', [
            'nis' => $user->nis,
            'password' => 'wrong-password',
            'device_name' => 'Android Test',
        ]);

        $response->assertUnauthorized()
            ->assertJsonPath('success', false)
            ->assertJsonPath('code', 'invalid_credentials')
            ->assertJsonMissingPath('data.token');
        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    public function test_mobile_login_requires_a_configured_otp_phone(): void
    {
        $user = $this->student(['nis' => 'API002']);

        $this->postJson('/api/auth/login', [
            'nis' => $user->nis,
            'password' => 'secret123',
            'device_name' => 'Android Test',
        ])->assertForbidden()
            ->assertJsonPath('code', 'otp_setup_required');

        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    public function test_staff_accounts_cannot_use_the_student_api(): void
    {
        $teacher = $this->student([
            'nis' => 'API003',
            'phone' => '081234567890',
            'role' => 'guru',
        ]);

        $this->postJson('/api/auth/login', [
            'nis' => $teacher->nis,
            'password' => 'secret123',
            'device_name' => 'Android Test',
        ])->assertForbidden()
            ->assertJsonPath('code', 'student_only');

        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    public function test_login_returns_only_public_otp_challenge_metadata(): void
    {
        $user = $this->student(['nis' => 'API004', 'phone' => '081234567890']);
        $code = '';
        $this->fakeDelivery($code);

        $response = $this->login($user);

        $response->assertStatus(202)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.otp_required', true)
            ->assertJsonPath('data.channel', 'whatsapp')
            ->assertJsonStructure([
                'data' => [
                    'challenge_id',
                    'masked_phone',
                    'sent_at',
                    'resend_at',
                    'expires_at',
                ],
            ])
            ->assertJsonMissingPath('data.token')
            ->assertJsonMissingPath('data.phone')
            ->assertJsonMissingPath('data.user_id')
            ->assertJsonMissingPath('data.code');

        $this->assertMatchesRegularExpression('/^[0-9]{6}$/', $code);
        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    public function test_valid_otp_issues_a_token_for_me_and_logout_revokes_only_that_token(): void
    {
        $user = $this->student(['nis' => 'API005', 'phone' => '081234567890']);
        $code = '';
        $this->fakeDelivery($code);
        $challengeId = $this->login($user)->json('data.challenge_id');

        $verify = $this->postJson('/api/auth/otp/verify', [
            'challenge_id' => $challengeId,
            'code' => $code,
        ]);

        $verify->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.token_type', 'Bearer')
            ->assertJsonPath('data.user.nis', $user->nis)
            ->assertJsonMissingPath('data.user.phone')
            ->assertJsonMissingPath('data.user.password');

        $token = $verify->json('data.token');
        $secondToken = $user->createToken('Other device', ['student'])->accessToken;
        $this->assertDatabaseCount('personal_access_tokens', 2);

        $this->withToken($token)->getJson('/api/auth/me')
            ->assertOk()
            ->assertJsonPath('data.nis', $user->nis);

        $this->withToken($token)->postJson('/api/auth/logout')
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseMissing('personal_access_tokens', ['id' => 1]);
        $this->assertDatabaseHas('personal_access_tokens', ['id' => $secondToken->id]);
        $this->withToken($token)->getJson('/api/auth/me')
            ->assertUnauthorized()
            ->assertJsonPath('code', 'unauthenticated');
    }

    public function test_invalid_expired_and_exhausted_otp_never_issue_a_token(): void
    {
        Config::set('otp.max_attempts', 2);
        $user = $this->student(['nis' => 'API006', 'phone' => '081234567890']);
        $code = '';
        $this->fakeDelivery($code);
        $challengeId = $this->login($user)->json('data.challenge_id');
        $invalidCode = $code === '000000' ? '999999' : '000000';

        $this->postJson('/api/auth/otp/verify', [
            'challenge_id' => $challengeId,
            'code' => $invalidCode,
        ])->assertUnprocessable()
            ->assertJsonPath('code', 'otp_invalid');

        $this->postJson('/api/auth/otp/verify', [
            'challenge_id' => $challengeId,
            'code' => $invalidCode,
        ])->assertTooManyRequests()
            ->assertJsonPath('code', 'otp_attempts_exhausted');

        $this->postJson('/api/auth/otp/verify', [
            'challenge_id' => $challengeId,
            'code' => $code,
        ])->assertStatus(410)
            ->assertJsonPath('code', 'otp_expired');

        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    public function test_resend_preserves_the_existing_attempt_count(): void
    {
        Config::set('otp.max_attempts', 2);
        Config::set('otp.resend_seconds', 0);
        $user = $this->student(['nis' => 'API007', 'phone' => '081234567890']);
        $code = '';
        $this->fakeDelivery($code, 2);
        $challengeId = $this->login($user)->json('data.challenge_id');
        $invalidCode = $code === '000000' ? '999999' : '000000';

        $this->postJson('/api/auth/otp/verify', [
            'challenge_id' => $challengeId,
            'code' => $invalidCode,
        ])->assertUnprocessable();

        $this->postJson('/api/auth/otp/resend', [
            'challenge_id' => $challengeId,
        ])->assertOk();

        $invalidResentCode = $code === '000000' ? '999999' : '000000';
        $this->postJson('/api/auth/otp/verify', [
            'challenge_id' => $challengeId,
            'code' => $invalidResentCode,
        ])->assertTooManyRequests()
            ->assertJsonPath('code', 'otp_attempts_exhausted');

        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    public function test_login_endpoint_is_rate_limited_per_ip_and_nis(): void
    {
        $user = $this->student(['nis' => 'API008', 'phone' => '081234567890']);
        $payload = [
            'nis' => $user->nis,
            'password' => 'wrong-password',
            'device_name' => 'Android Test',
        ];

        for ($attempt = 0; $attempt < 5; $attempt++) {
            $this->postJson('/api/auth/login', $payload)->assertUnauthorized();
        }

        $this->postJson('/api/auth/login', $payload)
            ->assertTooManyRequests()
            ->assertJsonPath('code', 'rate_limited');
    }

    public function test_expired_otp_is_rejected(): void
    {
        Config::set('otp.expires_minutes', 1);
        $user = $this->student(['nis' => 'API009', 'phone' => '081234567890']);
        $code = '';
        $this->fakeDelivery($code);
        $challengeId = $this->login($user)->json('data.challenge_id');

        $this->travel(2)->minutes();

        $this->postJson('/api/auth/otp/verify', [
            'challenge_id' => $challengeId,
            'code' => $code,
        ])->assertStatus(410)
            ->assertJsonPath('code', 'otp_expired');

        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    private function login(User $user)
    {
        return $this->postJson('/api/auth/login', [
            'nis' => $user->nis,
            'password' => 'secret123',
            'device_name' => 'Android Test',
        ]);
    }

    private function student(array $overrides = []): User
    {
        return User::create(array_merge([
            'nis' => 'API-STUDENT',
            'name' => 'Siswa API',
            'password' => Hash::make('secret123'),
            'role' => 'siswa',
            'kelas' => 'X PPLG',
        ], $overrides));
    }

    private function fakeDelivery(string &$code, int $calls = 1): void
    {
        $delivery = Mockery::mock(OtpDeliveryService::class);
        $delivery->shouldReceive('send')
            ->times($calls)
            ->withArgs(function (User $user, string $sentCode, string $channel) use (&$code): bool {
                $code = $sentCode;

                return $user->role === 'siswa' && in_array($channel, ['whatsapp', 'sms'], true);
            });

        $this->app->instance(OtpDeliveryService::class, $delivery);
    }
}
