<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\JournalIndexRequest;
use App\Http\Requests\Api\JournalRequest;
use App\Http\Requests\Api\UploadPhotoRequest;
use App\Http\Resources\JournalResource;
use App\Models\Journal;
use App\Services\JournalService;
use App\Services\MediaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class JournalController extends ApiController
{
    public function __construct(
        private readonly JournalService $journals,
        private readonly MediaService $media,
    ) {}

    public function today(Request $request): JsonResponse
    {
        $journal = $this->journals->findToday($request->user());

        return $this->success([
            'date' => $this->journals->todayDate(),
            'journal' => $journal ? new JournalResource($journal) : null,
        ]);
    }

    public function store(JournalRequest $request): JsonResponse
    {
        $journal = $this->journals->createToday($request->user(), $request->validated());

        return $this->success(new JournalResource($journal), 'Jurnal berhasil dibuat.', 201);
    }

    public function update(JournalRequest $request): JsonResponse
    {
        $journal = $this->journals->updateToday($request->user(), $request->validated());

        return $this->success(new JournalResource($journal), 'Jurnal berhasil diperbarui.');
    }

    public function submit(JournalRequest $request): JsonResponse
    {
        $journal = $this->journals->submitToday($request->user(), $request->validated());

        return $this->success(new JournalResource($journal), 'Jurnal berhasil dikirim.');
    }

    public function index(JournalIndexRequest $request): JsonResponse
    {
        $paginator = $request->user()->journals()
            ->orderByDesc('date')
            ->paginate((int) $request->validated('per_page', 15));
        $items = JournalResource::collection(collect($paginator->items()))->resolve($request);

        return $this->success($items, meta: [
            'current_page' => $paginator->currentPage(),
            'last_page' => $paginator->lastPage(),
            'per_page' => $paginator->perPage(),
            'total' => $paginator->total(),
        ]);
    }

    public function show(Request $request, int $journal): JsonResponse
    {
        return $this->success(new JournalResource($this->ownedJournal($request, $journal)));
    }

    public function uploadPhoto(
        UploadPhotoRequest $request,
        int $journal,
        string $type,
    ): JsonResponse {
        $journal = $this->media->replaceJournalPhoto(
            $request->user(),
            $this->ownedJournal($request, $journal),
            $type,
            $request->file('photo'),
        );

        return $this->success(new JournalResource($journal), 'Foto jurnal berhasil disimpan.');
    }

    public function photo(Request $request, int $journal, string $type): Response
    {
        return $this->media->journalPhotoResponse($this->ownedJournal($request, $journal), $type);
    }

    private function ownedJournal(Request $request, int $journal): Journal
    {
        return $request->user()->journals()->whereKey($journal)->firstOrFail();
    }
}
