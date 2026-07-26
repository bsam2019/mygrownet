<?php

namespace App\Http\Controllers\Platform;

use App\Domain\Core\Services\EventReplayService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class EventReplayController extends Controller
{
    public function __construct(
        private EventReplayService $replay,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $events = $this->replay->eventsInRange(
            $request->query('from'),
            $request->query('to'),
            $request->query('event_name'),
            (int) $request->query('per_page', 50)
        );

        return response()->json($events);
    }

    public function events(): JsonResponse
    {
        return response()->json([
            'event_names' => $this->replay->availableEventNames(),
        ]);
    }

    public function replay(Request $request): JsonResponse
    {
        $results = $this->replay->replay(
            $request->input('event_name'),
            $request->input('from'),
            $request->input('to'),
        );

        return response()->json([
            'message' => "Replayed {$results['published']} events, {$results['failed']} failed",
            'results' => $results,
        ]);
    }
}
