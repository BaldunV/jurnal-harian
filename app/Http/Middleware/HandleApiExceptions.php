<?php

namespace App\Http\Middleware;

use App\Support\ApiResponse;
use Closure;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

class HandleApiExceptions
{
    public function handle(Request $request, Closure $next): Response
    {
        try {
            return $next($request);
        } catch (ValidationException $exception) {
            return ApiResponse::error(
                'Data tidak valid.',
                422,
                $exception->errors(),
                'validation_error',
            );
        } catch (AuthenticationException) {
            return ApiResponse::error(
                'Token tidak valid atau sudah kedaluwarsa.',
                401,
                code: 'unauthenticated',
            );
        } catch (AuthorizationException) {
            return ApiResponse::error(
                'Anda tidak memiliki izin untuk mengakses data ini.',
                403,
                code: 'forbidden',
            );
        } catch (HttpExceptionInterface $exception) {
            $status = $exception->getStatusCode();
            $message = match ($status) {
                403 => 'Anda tidak memiliki izin untuk mengakses data ini.',
                404 => 'Data tidak ditemukan.',
                429 => 'Terlalu banyak permintaan. Coba lagi beberapa saat.',
                default => $status >= 500 ? 'Terjadi kesalahan pada server.' : $exception->getMessage(),
            };

            return ApiResponse::error(
                $message,
                $status,
                code: match ($status) {
                    403 => 'forbidden',
                    404 => 'not_found',
                    429 => 'rate_limited',
                    default => 'http_error',
                },
                headers: $exception->getHeaders(),
            );
        } catch (Throwable $exception) {
            report($exception);

            return ApiResponse::error(
                'Terjadi kesalahan pada server.',
                500,
                code: 'server_error',
            );
        }
    }
}
