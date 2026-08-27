<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;

abstract class ApiController extends Controller
{
    protected function success(
        mixed $data = null,
        string $message = 'Permintaan berhasil.',
        int $status = 200,
        array $meta = [],
    ): JsonResponse {
        return ApiResponse::success($data, $message, $status, $meta);
    }

    protected function error(
        string $message,
        int $status,
        array $errors = [],
        ?string $code = null,
    ): JsonResponse {
        return ApiResponse::error($message, $status, $errors, $code);
    }
}
