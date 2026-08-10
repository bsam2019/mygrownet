<?php

namespace App\Domain\GrowMusic\Presentation\Http\Controllers\Web;

use App\Domain\GrowMusic\Services\MusicCreatorService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class GrowMusicController extends Controller
{
    public function __construct(
        private MusicCreatorService $musicCreatorService
    ) {}

    public function index(Request $request)
    {
        $tracks = DB::table('music_tracks')
            ->latest()
            ->take(12)
            ->get();

        return Inertia::render('GrowMusic/Index', [
            'tracks' => $tracks,
        ]);
    }

    public function showPlayer(Request $request, int $id)
    {
        $track = DB::table('music_tracks')->where('id', $id)->first();
        if (!$track) {
            abort(404, 'Track not found');
        }

        return response()->json([
            'track' => $track,
        ]);
    }

    public function logStream(Request $request)
    {
        $request->validate([
            'track_id' => 'required|integer|exists:music_tracks,id',
            'duration' => 'nullable|integer',
        ]);

        $result = $this->musicCreatorService->logStream(
            trackId: (int) $request->track_id,
            listener: $request->user(),
            durationSeconds: (int) ($request->duration ?? 30)
        );

        return response()->json($result);
    }
}
