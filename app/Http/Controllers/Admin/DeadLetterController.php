<?php

namespace App\Http\Controllers\Admin;

use App\Domain\Core\Services\DeadLetterService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class DeadLetterController extends Controller
{
    public function __construct(
        private DeadLetterService $deadLetter,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['status', 'event_name', 'per_page']);
        return response()->json($this->deadLetter->all($filters));
    }

    public function show(int $id): JsonResponse
    {
        $event = \App\Domain\Core\Models\DeadLetterEvent::findOrFail($id);
        return response()->json($event);
    }

    public function replay(int $id): JsonResponse
    {
        $success = $this->deadLetter->replay($id);

        return response()->json([
            'success' => $success,
            'message' => $success ? 'Event replayed successfully' : 'Event could not be replayed',
        ], $success ? 200 : 500);
    }

    public function replayAll(Request $request): JsonResponse
    {
        $eventName = $request->query('event_name');
        $results = $this->deadLetter->replayAll($eventName);

        return response()->json([
            'message' => "Replayed {$results['succeeded']} events, {$results['failed']} failed",
            'results' => $results,
        ]);
    }

    public function stats(): JsonResponse
    {
        return response()->json($this->deadLetter->countByStatus());
    }

    public function pending(): JsonResponse
    {
        return response()->json($this->deadLetter->pending());
    }

    public function purge(Request $request): JsonResponse
    {
        $days = (int) $request->query('days', 30);
        $deleted = $this->deadLetter->purgeOlderThan($days);

        return response()->json([
            'message' => "Purged {$deleted} events older than {$days} days",
            'deleted' => $deleted,
        ]);
    }
}
