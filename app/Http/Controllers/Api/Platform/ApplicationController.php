<?php

namespace App\Http\Controllers\Api\Platform;

use App\Domain\Core\Models\Application;
use App\Domain\Core\Services\ApplicationRegistry;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;

class ApplicationController extends Controller
{
    public function __construct(
        private ApplicationRegistry $registry,
    ) {}

    public function index(): JsonResponse
    {
        $apps = $this->registry->all()->map(fn(Application $app) => [
            'id' => $app->id,
            'name' => $app->name,
            'slug' => $app->slug,
            'type' => $app->type,
        ]);

        return response()->json(['data' => $apps]);
    }

    public function show(string $slug): JsonResponse
    {
        $app = $this->registry->findBySlug($slug);

        if (!$app) {
            return response()->json([
                'error' => ['code' => 'NOT_FOUND', 'message' => 'Application not found.'],
            ], 404);
        }

        return response()->json(['data' => [
            'id' => $app->id,
            'name' => $app->name,
            'slug' => $app->slug,
            'type' => $app->type,
            'config' => $this->registry->getRoutingConfig($slug),
        ]]);
    }
}
