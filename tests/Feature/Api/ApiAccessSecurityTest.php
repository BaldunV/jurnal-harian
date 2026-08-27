<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ApiAccessSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_private_api_rejects_guests_and_web_sessions(): void
    {
        $student = $this->user();

        $this->getJson('/api/me/profile')
            ->assertUnauthorized()
            ->assertJsonPath('code', 'unauthenticated');

        $this->actingAs($student, 'web')
            ->getJson('/api/me/profile')
            ->assertUnauthorized()
            ->assertJsonPath('code', 'unauthenticated');
    }

    public function test_private_api_requires_the_student_token_ability(): void
    {
        Sanctum::actingAs($this->user(), ['profile:read']);

        $this->getJson('/api/me/profile')
            ->assertForbidden()
            ->assertJsonPath('success', false)
            ->assertJsonPath('code', 'forbidden');
    }

    public function test_private_api_rechecks_the_authenticated_users_role(): void
    {
        Sanctum::actingAs($this->user(['role' => 'guru']), ['student']);

        $this->getJson('/api/me/profile')
            ->assertForbidden()
            ->assertJsonPath('code', 'forbidden');
    }

    public function test_expired_bearer_tokens_are_rejected(): void
    {
        $token = $this->user()->createToken(
            'Expired device',
            ['student'],
            now()->subMinute(),
        )->plainTextToken;

        $this->withToken($token)
            ->getJson('/api/me/profile')
            ->assertUnauthorized()
            ->assertJsonPath('code', 'unauthenticated');
    }

    public function test_unknown_api_routes_use_the_safe_json_envelope(): void
    {
        $this->getJson('/api/not-a-real-route')
            ->assertNotFound()
            ->assertJsonPath('success', false)
            ->assertJsonPath('code', 'not_found');
    }

    private function user(array $overrides = []): User
    {
        return User::create(array_merge([
            'nis' => 'SECURITY-001',
            'name' => 'Siswa Security',
            'password' => Hash::make('secret123'),
            'role' => 'siswa',
            'kelas' => 'X PPLG',
            'worship_type' => 'muslim',
        ], $overrides));
    }
}
