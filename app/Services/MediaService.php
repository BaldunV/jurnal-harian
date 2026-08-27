<?php

namespace App\Services;

use App\Exceptions\ApiException;
use App\Models\Journal;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class MediaService
{
    private const JOURNAL_PREFIX = 'journal-media/';

    private const PROFILE_PREFIX = 'profile-media/';

    public function replaceJournalPhoto(
        User $user,
        Journal $journal,
        string $type,
        UploadedFile $photo,
    ): Journal {
        $column = $this->journalColumn($type);
        $newPath = self::JOURNAL_PREFIX.$user->id.'/'.$journal->id.'/'.$type.'/'.Str::uuid().'.'.$this->extension($photo);
        $this->store($photo, $newPath);
        $oldPath = null;

        try {
            $journal = DB::transaction(function () use ($user, $journal, $column, $newPath, &$oldPath): Journal {
                $locked = $user->journals()->whereKey($journal->id)->lockForUpdate()->first();
                if (! $locked) {
                    throw new ApiException('Jurnal tidak ditemukan.', 404, 'not_found');
                }
                if ($locked->is_submitted) {
                    throw new ApiException(
                        'Jurnal yang sudah dikirim tidak dapat diubah.',
                        409,
                        'journal_locked',
                    );
                }

                $oldPath = $locked->{$column};
                $locked->forceFill([$column => $newPath])->save();

                return $locked->refresh();
            });
        } catch (Throwable $exception) {
            Storage::disk('local')->delete($newPath);

            throw $exception;
        }

        $this->deleteJournalPath($oldPath);

        return $journal;
    }

    public function replaceProfilePhoto(User $user, UploadedFile $photo): User
    {
        $newPath = self::PROFILE_PREFIX.$user->id.'/'.Str::uuid().'.'.$this->extension($photo);
        $this->store($photo, $newPath);
        $oldPath = null;

        try {
            $user = DB::transaction(function () use ($user, $newPath, &$oldPath): User {
                $locked = User::query()->whereKey($user->id)->lockForUpdate()->firstOrFail();
                $oldPath = $locked->profile_photo;
                $locked->forceFill(['profile_photo' => $newPath])->save();

                return $locked->refresh();
            });
        } catch (Throwable $exception) {
            Storage::disk('local')->delete($newPath);

            throw $exception;
        }

        if ($oldPath) {
            Storage::disk('local')->delete($oldPath);
        }

        return $user;
    }

    public function journalPhotoResponse(Journal $journal, string $type): StreamedResponse
    {
        $path = $journal->{$this->journalColumn($type)};
        if (! $path) {
            throw new ApiException('Foto jurnal tidak ditemukan.', 404, 'not_found');
        }

        $disk = str_starts_with($path, self::JOURNAL_PREFIX) ? 'local' : 'public';

        return $this->response($disk, $path);
    }

    public function profilePhotoResponse(User $user): StreamedResponse
    {
        if (! $user->profile_photo) {
            throw new ApiException('Foto profil tidak ditemukan.', 404, 'not_found');
        }

        return $this->response('local', $user->profile_photo);
    }

    private function store(UploadedFile $photo, string $path): void
    {
        $stored = $photo->storeAs(dirname($path), basename($path), 'local');
        if (! $stored) {
            throw new ApiException(
                'Foto belum dapat disimpan. Coba kembali.',
                500,
                'media_storage_failed',
            );
        }
    }

    private function extension(UploadedFile $photo): string
    {
        return match ($photo->getMimeType()) {
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp',
            default => throw new ApiException(
                'Format foto harus JPEG, PNG, atau WebP.',
                422,
                'validation_error',
                ['photo' => ['Format foto harus JPEG, PNG, atau WebP.']],
            ),
        };
    }

    private function response(string $disk, string $path): StreamedResponse
    {
        if (! Storage::disk($disk)->exists($path)) {
            throw new ApiException('Foto tidak ditemukan.', 404, 'not_found');
        }

        return Storage::disk($disk)->response($path, null, [
            'Cache-Control' => 'private, no-store',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    private function deleteJournalPath(?string $path): void
    {
        if (! $path) {
            return;
        }

        $disk = str_starts_with($path, self::JOURNAL_PREFIX) ? 'local' : 'public';
        Storage::disk($disk)->delete($path);
    }

    private function journalColumn(string $type): string
    {
        return match ($type) {
            'olahraga' => 'olahraga_photo',
            'makan' => 'makan_photo',
            default => throw new ApiException('Jenis foto tidak ditemukan.', 404, 'not_found'),
        };
    }
}
