<?php

namespace App\Http\Controllers\Platform;

use App\Domain\Core\Contracts\HealthService;
use App\Domain\Core\Enums\HealthStatus;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class HealthController extends Controller
{
    public function __construct(
        private HealthService $health,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $applicationId = $request->query('app');
        $status = $this->health->check($applicationId);
        $details = $this->health->details($applicationId);

        return response()->json([
            'status' => $status->value,
            'label' => $status->label(),
            'operational' => $status->isOperational(),
            'timestamp' => now()->toIso8601String(),
            'details' => $details,
        ], $status->isOperational() ? 200 : 503);
    }

    public function all(): JsonResponse
    {
        $apps = $this->health->all();

        return response()->json([
            'timestamp' => now()->toIso8601String(),
            'applications' => $apps,
        ]);
    }
}
