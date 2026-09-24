<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthLoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_logs_in_directly_and_is_redirected_to_dashboard(): void
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

    public function test_admin_logs_in_directly_and_is_redirected_to_admin_dashboard(): void
    {
        $admin = User::create([
            'nis' => 'ADMIN001',
            'name' => 'Admin Utama',
            'password' => Hash::make('secret123'),
            'role' => 'admin',
        ]);

        $response = $this->post('/login', [
            'nis' => $admin->nis,
            'password' => 'secret123',
            'login_as' => 'staff',
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($admin);
    }

    public function test_invalid_credentials_are_rejected_without_authenticating(): void
    {
        $user = $this->student();

        $response = $this->from('/login')->post('/login', [
            'nis' => $user->nis,
            'password' => 'wrong-password',
            'login_as' => 'siswa',
        ]);

        $response->assertRedirect('/login')
            ->assertSessionHasErrors('nis');
        $this->assertGuest();
    }

    public function test_portal_mismatch_is_rejected(): void
    {
        $user = $this->student();

        $response = $this->from('/login')->post('/login', [
            'nis' => $user->nis,
            'password' => 'secret123',
            'login_as' => 'staff',
        ]);

        $response->assertRedirect('/login')
            ->assertSessionHasErrors('nis');
        $this->assertGuest();
    }

    public function test_logout_clears_the_session(): void
    {
        $user = $this->student();
        $this->actingAs($user);

        $this->post('/logout')->assertRedirect(route('login'));
        $this->assertGuest();
    }

    private function student(array $overrides = []): User
    {
        return User::create(array_merge([
            'nis' => 'SIS001',
            'name' => 'Siswa Login',
            'password' => Hash::make('secret123'),
            'role' => 'siswa',
            'kelas' => 'X PPLG',
        ], $overrides));
    }
}
