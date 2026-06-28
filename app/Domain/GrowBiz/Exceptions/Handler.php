<?php

declare(strict_types=1);

namespace App\Domain\GrowBiz\Exceptions;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

/**
 * GrowBiz Exception Handler
 * 
 * Provides centralized exception handling for GrowBiz domain exceptions.
 * Register this in bootstrap/app.php for automatic exception rendering.
 */
class Handler
{
    /**
     * Render a GrowBiz exception into an HTTP response.
     */
    public static function render(GrowBizException $exception, Request $request): Response
    {
        $statusCode = $exception->getCode() ?: 500;
        
        // Log the exception
        Log::channel('growbiz')->error($exception->getMessage(), [
            'error_code' => $exception->getErrorCode(),
            'context' => $exception->getContext(),
            'trace' => $exception->getTraceAsString(),
        ]);

        // For API/XHR requests, return JSON
        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'success' => false,
                'error' => [
                    'code' => $exception->getErrorCode(),
                    'message' => $exception->getMessage(),
                ],
            ], $statusCode);
        }

        // For Inertia requests, redirect back with error
        if ($request->header('X-Inertia')) {
            return back()->with('error', $exception->getMessage());
        }

        // Default: abort with status code
        abort($statusCode, $exception->getMessage());
    }

    /**
     * Determine if the exception should be reported.
     */
    public static function shouldReport(GrowBizException $exception): bool
    {
        // Don't report 4xx errors (client errors)
        $code = $exception->getCode();
        return $code < 400 || $code >= 500;
    }

    /**
     * Get user-friendly error messages for common error codes.
     */
    public static function getUserMessage(string $errorCode): string
    {
        return match ($errorCode) {
            'EMPLOYEE_NOT_FOUND' => 'The requested employee could not be found.',
            'TASK_NOT_FOUND' => 'The requested task could not be found.',
            'UNAUTHORIZED_ACCESS' => 'You do not have permission to access this resource.',
            'DUPLICATE_EMPLOYEE' => 'An employee with this email already exists.',
            'EMPLOYEE_HAS_ACTIVE_TASKS' => 'Cannot delete employee with active tasks.',
            'INVALID_ASSIGNMENT' => 'One or more assignees are invalid or inactive.',
            'INVALID_STATUS_TRANSITION' => 'This status change is not allowed.',
            'OPERATION_FAILED' => 'The operation could not be completed. Please try again.',
            default => 'An unexpected error occurred. Please try again.',
        };
    }
}
