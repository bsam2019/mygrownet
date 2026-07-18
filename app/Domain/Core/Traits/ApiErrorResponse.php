<?php

namespace App\Domain\Core\Traits;

use Illuminate\Http\JsonResponse;

trait ApiErrorResponse
{
    protected function error(string $code, string $message, int $status = 400, array $details = []): JsonResponse
    {
        $payload = ['error' => ['code' => $code, 'message' => $message]];

        if ($details) {
            $payload['error']['details'] = $details;
        }

        return response()->json($payload, $status);
    }

    protected function notFound(string $message = 'Resource not found.'): JsonResponse
    {
        return $this->error('NOT_FOUND', $message, 404);
    }

    protected function forbidden(string $message = 'Forbidden.'): JsonResponse
    {
        return $this->error('FORBIDDEN', $message, 403);
    }

    protected function unauthenticated(string $message = 'Unauthenticated.'): JsonResponse
    {
        return $this->error('UNAUTHENTICATED', $message, 401);
    }

    protected function validationError(array $details): JsonResponse
    {
        return $this->error('VALIDATION_ERROR', 'The given data was invalid.', 422, $details);
    }
}
