<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\LoginRequest;
use App\Http\Resources\StudentResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends ApiController
{
    public function login(LoginRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $user = User::query()
            ->where('nis', $validated['nis'])
            ->first();

        if (! $user || ! Hash::check($validated['password'], $user->password)) {
            return $this->error(
                'NIS atau password yang dimasukkan salah.',
                401,
                code: 'invalid_credentials',
            );
        }

        if ($user->role !== 'siswa') {
            return $this->error(
                'Aplikasi mobile hanya tersedia untuk siswa.',
                403,
                code: 'student_only',
            );
        }

        $expiresAt = now()->addDays(
            (int) config(
                'sanctum.mobile_token_expiration_days',
                30
            )
        );

        $deviceName = (string) (
            $validated['device_name'] ?? 'Flutter'
        );

        $token = $user->createToken(
            $deviceName,
            ['student'],
            $expiresAt
        );

        return $this->success([
            'token' => $token->plainTextToken,
            'token_type' => 'Bearer',
            'expires_at' => $expiresAt->toIso8601String(),
            'user' => new StudentResource($user),
        ], 'Login berhasil.');
    }

    public function me(Request $request): JsonResponse
    {
        return $this->success(
            new StudentResource($request->user())
        );
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()
            ->currentAccessToken()
            ?->delete();

        Auth::guard('sanctum')->forgetUser();

        return $this->success(
            message: 'Logout berhasil.'
        );
    }
}
