<?php

namespace App\Http\Controllers\Admin\GrowMusic;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class GrowMusicAdminController extends Controller
{
    /**
     * Admin GrowMusic Control Center
     */
    public function dashboard(Request $request)
    {
        $totalTracks = DB::table('music_tracks')->count();
        $totalStreams = DB::table('music_tracks')->sum('stream_count');
        $totalRoyalties = DB::table('music_stream_logs')->sum('royalty_earned');
        $activeFanSubscriptions = DB::table('artist_fan_subscriptions')->where('status', 'active')->count();

        $recentTracks = DB::table('music_tracks')
            ->join('users', 'music_tracks.artist_user_id', '=', 'users.id')
            ->select('music_tracks.*', 'users.name as artist_name', 'users.email as artist_email')
            ->latest('music_tracks.created_at')
            ->take(15)
            ->get();

        $topArtists = DB::table('music_tracks')
            ->join('users', 'music_tracks.artist_user_id', '=', 'users.id')
            ->select('users.id', 'users.name', 'users.email', DB::raw('SUM(music_tracks.stream_count) as total_artist_streams'))
            ->groupBy('users.id', 'users.name', 'users.email')
            ->orderByDesc('total_artist_streams')
            ->take(10)
            ->get();

        return Inertia::render('Admin/GrowMusic/Dashboard', [
            'totalTracks' => $totalTracks,
            'totalStreams' => $totalStreams,
            'totalRoyalties' => (float) $totalRoyalties,
            'activeFanSubscriptions' => $activeFanSubscriptions,
            'recentTracks' => $recentTracks,
            'topArtists' => $topArtists,
        ]);
    }

    /**
     * Toggle VIP status or features for a track
     */
    public function toggleVip(Request $request, int $id)
    {
        $track = DB::table('music_tracks')->where('id', $id)->first();
        if (!$track) abort(404);

        DB::table('music_tracks')->where('id', $id)->update([
            'is_vip_only' => !$track->is_vip_only,
            'updated_at' => now(),
        ]);

        return back()->with('success', 'Track VIP access updated.');
    }
}
