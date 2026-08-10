<?php

namespace App\Domain\GrowMusic\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;

class MusicCreatorService
{
    /**
     * Log a music stream (>30s) and award ZAMCO royalties & GrowNet LP/BP points.
     */
    public function logStream(int $trackId, ?User $listener, int $durationSeconds = 30): array
    {
        $track = DB::table('music_tracks')->where('id', $trackId)->first();
        if (!$track) {
            return ['success' => false, 'message' => 'Track not found'];
        }

        // Increment track stream count
        DB::table('music_tracks')->where('id', $trackId)->increment('stream_count');

        // Calculate royalty (K0.10 per stream)
        $royaltyEarned = 0.10;

        // Log stream
        $logId = DB::table('music_stream_logs')->insertGetId([
            'track_id' => $trackId,
            'listener_user_id' => $listener?->id,
            'duration_listened_seconds' => $durationSeconds,
            'royalty_earned' => $royaltyEarned,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Every 1,000 streams on artist catalog awards +50 LP and +25 BP via central PointService (prevents double-counting)
        $totalStreams = DB::table('music_tracks')->where('artist_user_id', $track->artist_user_id)->sum('stream_count');
        if ($totalStreams > 0 && $totalStreams % 1000 === 0) {
            $artist = User::find($track->artist_user_id);
            if ($artist) {
                app(\App\Domain\GrowNet\Services\PointService::class)->awardPoints(
                    user: $artist,
                    source: 'growmusic_stream_milestone',
                    lpAmount: 50,
                    mapAmount: 25,
                    description: 'Milestone Award: 1,000 Verified Music Streams on GrowMusic'
                );
            }
        }

        return [
            'success' => true,
            'log_id' => $logId,
            'track_title' => $track->title,
            'total_streams' => $totalStreams,
            'royalty_earned' => $royaltyEarned,
        ];
    }
}
