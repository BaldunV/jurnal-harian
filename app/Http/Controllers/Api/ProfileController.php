<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\ChangePasswordRequest;
use App\Http\Requests\Api\UpdateProfileRequest;
use App\Http\Requests\Api\UploadPhotoRequest;
use App\Http\Resources\StudentResource;
use App\Services\MediaService;
use App\Services\OtpService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Response;

class ProfileController extends ApiController
{
    public function __construct(
        private readonly MediaService $media,
        private readonly OtpService $otp,
    ) {}

    public function show(Request $request): JsonResponse
    {
        return $this->success(new StudentResource($request->user()));
    }

    public function update(UpdateProfileRequest $request): JsonResponse
    {
        $request->user()->update($request->validated());

        return $this->success(
            new StudentResource($request->user()->refresh()),
            'Profil berhasil diperbarui.',
        );
    }

    public function uploadPhoto(UploadPhotoRequest $request): JsonResponse
    {
        $user = $this->media->replaceProfilePhoto($request->user(), $request->file('photo'));

        return $this->success(new StudentResource($user), 'Foto profil berhasil disimpan.');
    }

    public function photo(Request $request): Response
    {
        return $this->media->profilePhotoResponse($request->user()->fresh());
    }

    public function changePassword(ChangePasswordRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $user = $request->user();

        if (! Hash::check($validated['current_password'], $user->password)) {
            return $this->error(
                'Password saat ini salah.',
                422,
                ['current_password' => ['Password saat ini salah.']],
                'current_password_invalid',
            );
        }

        DB::transaction(function () use ($user, $validated): void {
            $user->update(['password' => $validated['password']]);
            $this->otp->revokeTrustedDevices($user);
            $user->tokens()->delete();
        });
        Auth::guard('sanctum')->forgetUser();

        return $this->success(
            ['reauthentication_required' => true],
            'Password berhasil diubah. Silakan login kembali.',
        );
    }
}
