<?php

namespace App\Domain\GrowNet\Presentation\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class FacilitatorPortalController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        // Check if user is eligible facilitator (Level 5+)
        $userLevel = (int) ($user->current_professional_level_id ?? 1);
        if ($userLevel < 5 && !($user->is_admin ?? false)) {
            return redirect()->route('grownet.dashboard')
                ->with('error', 'The Facilitator Portal requires Level 5 Director rank or higher.');
        }

        $upcomingWorkshops = DB::table('workshops')
            ->where('status', 'published')
            ->orderBy('start_date')
            ->get();

        $pendingEvaluations = DB::table('practical_task_submissions')
            ->where('status', 'pending')
            ->get();

        return Inertia::render('GrowNet/Facilitator/Dashboard', [
            'upcomingWorkshops' => $upcomingWorkshops,
            'pendingEvaluations' => $pendingEvaluations,
            'userLevel' => $userLevel,
        ]);
    }

    public function checkIn(Request $request)
    {
        $request->validate([
            'workshop_session_id' => 'required|integer|exists:workshop_sessions,id',
            'user_id' => 'required|integer|exists:users,id',
        ]);

        $sessionId = (int) $request->workshop_session_id;
        $participantId = (int) $request->user_id;

        DB::table('workshop_attendance')->updateOrInsert(
            [
                'workshop_session_id' => $sessionId,
                'user_id' => $participantId,
            ],
            [
                'checked_in_at' => now(),
                'checked_in_by' => $request->user()->name,
                'updated_at' => now(),
            ]
        );

        // Award +100 LP and +25 BP for verified workshop check-in via central PointService (prevents double-counting)
        $participant = \App\Models\User::find($participantId);
        if ($participant) {
            app(\App\Domain\GrowNet\Services\PointService::class)->awardPoints(
                user: $participant,
                source: 'workshop_qr_checkin',
                lpAmount: 100,
                mapAmount: 25,
                description: 'Verified Workshop Attendance (QR Code Check-in)'
            );
        }

        return back()->with('success', 'Participant successfully checked in! +100 LP / +25 BP awarded.');
    }

    public function uploadVoiceNote(Request $request)
    {
        $request->validate([
            'user_id' => 'required|integer|exists:users,id',
            'level' => 'required|integer',
            'audio_file' => 'required|file|mimes:mp3,wav,webm,ogg,m4a|max:10240',
        ]);

        $path = $request->file('audio_file')->store('voice_notes', 'public');

        $attemptId = DB::table('assessment_attempts')->insertGetId([
            'user_id' => (int) $request->user_id,
            'level' => (int) $request->level,
            'assessment_method' => 'voice_note',
            'voice_note_url' => "/storage/{$path}",
            'score' => 85,
            'passed' => true,
            'assessor_user_id' => $request->user()->id,
            'evaluated_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'attempt_id' => $attemptId,
            'voice_note_url' => "/storage/{$path}",
        ]);
    }
}
