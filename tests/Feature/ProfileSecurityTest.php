<?php

namespace Tests\Feature;

use App\Models\OtpDevice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ProfileSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_can_change_to_a_stronger_password_and_trusted_devices_are_revoked(): void
    {
        $user = User::create([
            'nis' => 'SIS002',
            'name' => 'Siswa Password',
            'password' => Hash::make('secret123'),
            'role' => 'siswa',
        ]);
        OtpDevice::create([
            'user_id' => $user->id,
            'token_hash' => hash('sha256', 'old-device'),
            'expires_at' => now()->addDays(10),
        ]);

        $response = $this->actingAs($user)->post('/profile/password', [
            'current_password' => 'secret123',
            'password' => 'NewPassword1!',
            'password_confirmation' => 'NewPassword1!',
        ]);

        $response->assertRedirect();
        $this->assertTrue(Hash::check('NewPassword1!', $user->fresh()->password));
        $this->assertDatabaseCount('otp_devices', 0);
    }

    public function test_student_password_change_rejects_weak_password(): void
    {
        $user = User::create([
            'nis' => 'SIS003',
            'name' => 'Siswa Password Lemah',
            'password' => Hash::make('secret123'),
            'role' => 'siswa',
        ]);

        $response = $this->actingAs($user)->from('/profile')->post('/profile/password', [
            'current_password' => 'secret123',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect('/profile')->assertSessionHasErrors('password');
        $this->assertTrue(Hash::check('secret123', $user->fresh()->password));
    }
}
