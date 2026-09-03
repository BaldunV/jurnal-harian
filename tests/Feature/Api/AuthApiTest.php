<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_requires_valid_credentials_and_uses_the_api_envelope(): void
    {
        $user = $this->student(['nis' => 'API001']);

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

    public function test_mobile_login_issues_a_token_with_student_metadata(): void
    {
        $user = $this->student(['nis' => 'API002']);

        $response = $this->postJson('/api/auth/login', [
            'nis' => $user->nis,
            'password' => 'secret123',
            'device_name' => 'Android Test',
        ]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.token_type', 'Bearer')
            ->assertJsonPath('data.user.nis', $user->nis)
            ->assertJsonPath('data.user.role', 'siswa')
            ->assertJsonMissingPath('data.user.password')
            ->assertJsonStructure([
                'data' => [
                    'token',
                    'token_type',
                    'expires_at',
                    'user',
                ],
            ]);

        $token = $response->json('data.token');
        $this->assertNotSame('', $token);
        $this->assertDatabaseCount('personal_access_tokens', 1);
    }

    public function test_staff_accounts_cannot_use_the_student_api(): void
    {
        $teacher = $this->student([
            'nis' => 'API003',
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

    public function test_issued_token_works_for_me_and_logout_revokes_only_that_token(): void
    {
        $user = $this->student(['nis' => 'API004']);

        $login = $this->postJson('/api/auth/login', [
            'nis' => $user->nis,
            'password' => 'secret123',
            'device_name' => 'Android Test',
        ]);
        $token = $login->json('data.token');
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

    public function test_login_endpoint_is_rate_limited_per_ip_and_nis(): void
    {
        $user = $this->student(['nis' => 'API005']);
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
}