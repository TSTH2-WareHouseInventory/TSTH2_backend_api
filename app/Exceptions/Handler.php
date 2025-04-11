<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class Handler extends ExceptionHandler
{
    /**
     * Daftar jenis exception yang tidak akan dilaporkan.
     *
     * @var array<int, class-string<\Throwable>>
     */
    protected $dontReport = [];

    /**
     * Daftar jenis input yang tidak boleh dimasukkan ke dalam log.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Laporkan atau log exception.
     */
    public function report(Throwable $exception): void
    {
        parent::report($exception);
    }

    /**
     * Render exception menjadi response HTTP.
     */
    public function render($request, Throwable $exception): JsonResponse
    {
        // Menangani validasi yang gagal agar tidak return 500, tetapi 422
        if ($exception instanceof ValidationException) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $exception->errors(),
            ], 422);
        }

        return parent::render($request, $exception);
    }
}
