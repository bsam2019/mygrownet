<?php

namespace App\Http\Controllers\GrowNet\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\CloudflareStreamService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class EducationAdminController extends Controller
{
    /**
     * Admin Education & Workshops Program Control Center
     */
    public function index(Request $request)
    {
        $curricula = DB::table('education_curricula')
            ->orderBy('level')
            ->orderBy('sort_order')
            ->get();

        $workshops = DB::table('workshops')
            ->orderBy('id', 'desc')
            ->get();

        $skillsDemand = DB::table('skills')
            ->orderBy('demand_count', 'desc')
            ->get();

        $pendingSubmissions = DB::table('practical_task_submissions')
            ->join('users', 'practical_task_submissions.user_id', '=', 'users.id')
            ->select('practical_task_submissions.*', 'users.name as user_name', 'users.email as user_email')
            ->where('practical_task_submissions.status', 'pending')
            ->get();

        $levelCounts = DB::table('users')
            ->select('current_professional_level', DB::raw('count(*) as total'))
            ->groupBy('current_professional_level')
            ->get();

        return Inertia::render('GrowNet/Admin/Education', [
            'curricula' => $curricula,
            'workshops' => $workshops,
            'skillsDemand' => $skillsDemand,
            'pendingSubmissions' => $pendingSubmissions,
            'levelCounts' => $levelCounts,
        ]);
    }

    /**
     * Initialize Cloudflare Stream TUS upload session
     */
    public function tusInit(Request $request)
    {
        $request->validate([
            'file_size' => 'required|integer|min:1',
            'title' => 'required|string|max:255',
        ]);

        $cfService = app(CloudflareStreamService::class);
        $result = $cfService->createTusUpload((int) $request->file_size, $request->title);

        if ($result) {
            return response()->json($result);
        }

        return response()->json(['error' => 'Unable to initialize TUS upload session'], 500);
    }

    /**
     * Helper to upload video using decoupled CloudflareStreamService or Storage fallback
     */
    protected function handleVideoUpload(Request $request, ?string $defaultUrl = null): ?string
    {
        $videoUrl = $defaultUrl;

        if ($request->hasFile('video_file')) {
            @set_time_limit(300);
            @ini_set('max_execution_time', '300');

            $file = $request->file('video_file');
            $title = $request->lesson_title ?? $file->getClientOriginalName();

            $cfService = app(CloudflareStreamService::class);
            $streamUid = $cfService->upload($file, $title);

            if (!empty($streamUid)) {
                return $streamUid;
            }

            // Storage fallback if Cloudflare Stream API credentials are not set or API call fails
            $path = $file->store('education/videos', 'public');
            $videoUrl = Storage::url($path);
        }

        return $videoUrl;
    }

    /**
     * Store new curriculum lesson with GrowStream Cloudflare Stream Upload & multi-format support
     */
    public function storeCurriculum(Request $request)
    {
        $request->validate([
            'level' => 'required|integer|min:1|max:7',
            'module_title' => 'required|string|max:255',
            'lesson_title' => 'required|string|max:255',
            'content_type' => 'required|in:video,audio,text,pdf,workshop,practical',
            'video_url' => 'nullable|string',
            'video_file' => 'nullable|file|mimes:mp4,mov,avi,mkv,webm|max:204800',
            'audio_url' => 'nullable|string',
            'pdf_url' => 'nullable|string',
            'duration_minutes' => 'nullable|integer',
            'practical_activity_prompt' => 'nullable|string',
        ]);

        $videoUrl = $this->handleVideoUpload($request, $request->video_url);

        DB::table('education_curricula')->insert([
            'level' => (int) $request->level,
            'module_title' => $request->module_title,
            'lesson_title' => $request->lesson_title,
            'description' => $request->description ?? '',
            'content_type' => $request->content_type,
            'video_url' => $videoUrl,
            'audio_url' => $request->audio_url,
            'pdf_url' => $request->pdf_url,
            'transcript' => $request->transcript,
            'simplified_notes' => $request->simplified_notes,
            'practical_activity_prompt' => $request->practical_activity_prompt,
            'duration_minutes' => (int) ($request->duration_minutes ?? 15),
            'is_required' => $request->boolean('is_required', true),
            'is_low_literacy_friendly' => $request->boolean('is_low_literacy_friendly', true),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return back()->with('success', 'Curriculum lesson successfully added.');
    }

    /**
     * Update curriculum lesson
     */
    public function updateCurriculum(Request $request, int $id)
    {
        $request->validate([
            'lesson_title' => 'required|string|max:255',
            'level' => 'required|integer|min:1|max:7',
            'video_file' => 'nullable|file|mimes:mp4,mov,avi,mkv,webm|max:204800',
        ]);

        $videoUrl = $this->handleVideoUpload($request, $request->video_url);

        DB::table('education_curricula')->where('id', $id)->update([
            'level' => (int) $request->level,
            'module_title' => $request->module_title,
            'lesson_title' => $request->lesson_title,
            'description' => $request->description ?? '',
            'content_type' => $request->content_type ?? 'video',
            'video_url' => $videoUrl,
            'audio_url' => $request->audio_url,
            'pdf_url' => $request->pdf_url,
            'transcript' => $request->transcript,
            'simplified_notes' => $request->simplified_notes,
            'duration_minutes' => (int) ($request->duration_minutes ?? 15),
            'practical_activity_prompt' => $request->practical_activity_prompt,
            'is_low_literacy_friendly' => $request->boolean('is_low_literacy_friendly', true),
            'updated_at' => now(),
        ]);

        return back()->with('success', 'Curriculum lesson updated.');
    }

    /**
     * Delete curriculum lesson
     */
    public function deleteCurriculum(int $id)
    {
        DB::table('education_curricula')->where('id', $id)->delete();
        return back()->with('success', 'Lesson deleted.');
    }

    /**
     * Store new regional/live workshop
     */
    public function storeWorkshop(Request $request)
    {
        $request->validate([
            'topic' => 'required|string|max:255',
            'level' => 'required|integer|min:1|max:7',
            'description' => 'required|string',
            'location' => 'nullable|string',
            'instructor_name' => 'nullable|string',
            'institution_name' => 'nullable|string',
        ]);

        DB::table('workshops')->insert([
            'level' => (int) $request->level,
            'topic' => $request->topic,
            'description' => $request->description,
            'location' => $request->location ?? 'Lusaka / Online',
            'instructor_name' => $request->instructor_name ?? 'GrowNet Expert',
            'status' => 'published',
            'delivery_mode' => $request->delivery_mode ?? 'hybrid',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return back()->with('success', 'Workshop created and published.');
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

        return back()->with('success', 'Practical submission evaluated.');
    }
}
