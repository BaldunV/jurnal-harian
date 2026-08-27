<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\Api\StatisticsRequest;
use App\Services\JournalStatisticsService;
use Illuminate\Http\JsonResponse;

class StatisticsController extends ApiController
{
    public function __construct(private readonly JournalStatisticsService $statistics) {}

    public function show(StatisticsRequest $request): JsonResponse
    {
        return $this->success($this->statistics->forPeriod(
            $request->user(),
            $request->validated('period'),
        ));
    }
}
