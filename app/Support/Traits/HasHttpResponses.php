<?php

namespace App\Support\Traits;

use Illuminate\Http\JsonResponse;

trait HasHttpResponses
{
    public function success(mixed $data = null, string $message = 'Success', int $status = 200): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'message' => $message,
            'data' => $data,
        ], $status);
    }

    public function noContent(): JsonResponse
    {
        return response()->json(null, 204);
    }

    public function error(string $message = 'Error', int $status = 500, mixed $data = null): JsonResponse
    {
        return response()->json([
            'status' => 'error',
            'message' => $message,
            'data' => $data,
        ], $status);
    }

    public function notFound(string $message = 'Not Found', mixed $data = null): JsonResponse
    {
        return $this->error($message, 404, $data);
    }
}
