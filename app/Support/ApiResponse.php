<?php

namespace App\Support;

use Illuminate\Http\JsonResponse;

final class ApiResponse
{
    public static function success(
        mixed $data = null,
        string $message = 'Permintaan berhasil.',
        int $status = 200,
        array $meta = [],
    ): JsonResponse {
        $payload = [
            'success' => true,
            'message' => $message,
            'data' => $data ?? (object) [],
        ];

        if ($meta !== []) {
            $payload['meta'] = $meta;
        }

        return response()->json($payload, $status);
    }

    public static function error(
        string $message,
        int $status,
        array $errors = [],
        ?string $code = null,
        array $headers = [],
    ): JsonResponse {
        $payload = [
            'success' => false,
            'message' => $message,
            'errors' => $errors ?: (object) [],
        ];

        if ($code !== null) {
            $payload['code'] = $code;
        }

        return response()->json($payload, $status, $headers);
    }
}
