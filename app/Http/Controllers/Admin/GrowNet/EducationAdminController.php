<?php

namespace App\Http\Controllers\Admin\GrowNet;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class EducationAdminController extends Controller
{
    /**
     * Admin Education Program Control Center
     */
    public function index(Request $request)
    {
        $curricula = DB::table('education_curricula')
            ->orderBy('level')
            ->orderBy('sort_order')
            ->get();

        $pendingSubmissions = DB::table('practical_task_submissions')
            ->join('users', 'practical_task_submissions.user_id', '=', 'users.id')
            ->select('practical_task_submissions.*', 'users.name as user_name', 'users.email as user_email')
            ->where('practical_task_submissions.status', 'pending')
            ->get();

        $pendingOralExams = DB::table('assessment_attempts')
            ->join('users', 'assessment_attempts.user_id', '=', 'users.id')
            ->select('assessment_attempts.*', 'users.name as user_name', 'users.email as user_email')
            ->where('assessment_attempts.assessment_method', 'voice_note')
            ->whereNull('evaluated_at')
            ->get();

        $levelCounts = DB::table('users')
            ->select('current_professional_level', DB::raw('count(*) as total'))
            ->groupBy('current_professional_level')
            ->get();

        return Inertia::render('Admin/GrowNet/Education', [
            'curricula' => $curricula,
            'pendingSubmissions' => $pendingSubmissions,
            'pendingOralExams' => $pendingOralExams,
            'levelCounts' => $levelCounts,
        ]);
    }

    /**
     * Store new lesson/module curriculum
     */
    public function storeCurriculum(Request $request)
    {
        $request->validate([
            'level' => 'required|integer|min:1|max:7',
            'module_title' => 'required|string|max:255',
            'lesson_title' => 'required|string|max:255',
            'content_type' => 'required|in:video,audio,text,pdf,workshop,practical',
            'video_url' => 'nullable|string',
            'audio_url' => 'nullable|string',
            'pdf_url' => 'nullable|string',
            'duration_minutes' => 'nullable|integer',
        ]);

        DB::table('education_curricula')->insert([
            'level' => (int) $request->level,
            'module_title' => $request->module_title,
            'lesson_title' => $request->lesson_title,
            'description' => $request->description ?? '',
            'content_type' => $request->content_type,
            'video_url' => $request->video_url,
            'audio_url' => $request->audio_url,
            'pdf_url' => $request->pdf_url,
            'duration_minutes' => (int) ($request->duration_minutes ?? 15),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return back()->with('success', 'Curriculum lesson successfully added.');
    }

    /**
     * Grade practical business plan submission
     */
    public function gradeSubmission(Request $request, int $id)
    {
        $request->validate([
            'status' => 'required|in:approved,rejected',
            'feedback' => 'nullable|string',
        ]);

        DB::table('practical_task_submissions')->where('id', $id)->update([
            'status' => $request->status,
            'evaluation_feedback' => $request->feedback,
            'evaluated_by' => $request->user()->id,
            'evaluated_at' => now(),
            'updated_at' => now(),
        ]);

        return back()->with('success', 'Practical submission successfully evaluated.');
    }
}
