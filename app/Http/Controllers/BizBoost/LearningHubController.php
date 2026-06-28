<?php

namespace App\Http\Controllers\BizBoost;

use App\Domain\Module\Services\SubscriptionService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class LearningHubController extends Controller
{
    private const MODULE_ID = 'bizboost';

    public function __construct(
        private SubscriptionService $subscriptionService
    ) {}

    public function index(Request $request)
    {
        $user = $request->user();
        $tier = $this->subscriptionService->getUserTier($user, self::MODULE_ID);
        $tierOrder = ['free' => 0, 'basic' => 1, 'professional' => 2, 'business' => 3];
        // Admins get highest tier level
        $userTierLevel = $this->subscriptionService->isAdmin($user) ? 3 : ($tierOrder[$tier] ?? 0);

        $courses = DB::table('bizboost_courses')
            ->where('is_published', true)
            ->orderBy('sort_order')
            ->orderBy('title')
            ->get()
            ->map(function ($course) use ($userTierLevel, $tierOrder, $request) {
                $requiredLevel = $tierOrder[$course->tier_required] ?? 0;
                $course->is_locked = $userTierLevel < $requiredLevel;
                
                // Get user progress
                $progress = DB::table('bizboost_course_progress')
                    ->where('user_id', $request->user()->id)
                    ->where('course_id', $course->id)
                    ->first();
                
                $course->progress_percent = $progress->progress_percent ?? 0;
                $course->is_completed = $progress && $progress->completed_at !== null;
                
                return $course;
            });

        $categories = $courses->pluck('category')->unique()->values();

        // User's certificates
        $certificates = DB::table('bizboost_certificates')
            ->join('bizboost_courses', 'bizboost_certificates.course_id', '=', 'bizboost_courses.id')
            ->where('bizboost_certificates.user_id', $request->user()->id)
            ->select('bizboost_certificates.*', 'bizboost_courses.title as course_title')
            ->orderByDesc('issued_at')
            ->get();

        return Inertia::render('BizBoost/Learning/Index', [
            'courses' => $courses,
            'categories' => $categories,
            'certificates' => $certificates,
            'stats' => [
                'total_courses' => $courses->count(),
                'completed' => $courses->where('is_completed', true)->count(),
                'in_progress' => $courses->where('progress_percent', '>', 0)->where('is_completed', false)->count(),
                'certificates' => $certificates->count(),
            ],
        ]);
    }

    public function show(Request $request, $slug)
    {
        $course = DB::table('bizboost_courses')
            ->where('slug', $slug)
            ->where('is_published', true)
            ->first();

        if (!$course) {
            abort(404);
        }

        // Check tier access
        $user = $request->user();
        $tier = $this->subscriptionService->getUserTier($user, self::MODULE_ID);
        $tierOrder = ['free' => 0, 'basic' => 1, 'professional' => 2, 'business' => 3];
        // Admins get highest tier level
        $userTierLevel = $this->subscriptionService->isAdmin($user) ? 3 : ($tierOrder[$tier] ?? 0);
        $requiredLevel = $tierOrder[$course->tier_required] ?? 0;

        if ($userTierLevel < $requiredLevel) {
            return redirect()->route('bizboost.upgrade')
                ->with('message', 'Upgrade to access this course.');
        }

        $lessons = DB::table('bizboost_lessons')
            ->where('course_id', $course->id)
            ->where('is_published', true)
            ->orderBy('sort_order')
            ->get();

        $progress = DB::table('bizboost_course_progress')
            ->where('user_id', $request->user()->id)
            ->where('course_id', $course->id)
            ->first();

        $completedLessons = $progress ? json_decode($progress->completed_lessons ?? '[]', true) : [];

        return Inertia::render('BizBoost/Learning/Course', [
            'course' => $course,
            'lessons' => $lessons->map(function ($lesson) use ($completedLessons) {
                $lesson->is_completed = in_array($lesson->id, $completedLessons);
                return $lesson;
            }),
            'progress' => $progress,
            'completedLessons' => $completedLessons,
        ]);
    }

    public function lesson(Request $request, $slug, $lessonSlug)
    {
        $course = DB::table('bizboost_courses')
            ->where('slug', $slug)
            ->where('is_published', true)
            ->first();

        if (!$course) {
            abort(404);
        }

        $lesson = DB::table('bizboost_lessons')
            ->where('course_id', $course->id)
            ->where('slug', $lessonSlug)
            ->where('is_published', true)
            ->first();

        if (!$lesson) {
            abort(404);
        }

        $lessons = DB::table('bizboost_lessons')
            ->where('course_id', $course->id)
            ->where('is_published', true)
            ->orderBy('sort_order')
            ->get();

        $progress = DB::table('bizboost_course_progress')
            ->where('user_id', $request->user()->id)
            ->where('course_id', $course->id)
            ->first();

        $completedLessons = $progress ? json_decode($progress->completed_lessons ?? '[]', true) : [];

        // Start progress if not exists
        if (!$progress) {
            DB::table('bizboost_course_progress')->insert([
                'user_id' => $request->user()->id,
                'course_id' => $course->id,
                'current_lesson_id' => $lesson->id,
                'completed_lessons' => '[]',
                'progress_percent' => 0,
                'started_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return Inertia::render('BizBoost/Learning/Lesson', [
            'course' => $course,
            'lesson' => $lesson,
            'lessons' => $lessons,
            'completedLessons' => $completedLessons,
            'nextLesson' => $lessons->where('sort_order', '>', $lesson->sort_order)->first(),
            'prevLesson' => $lessons->where('sort_order', '<', $lesson->sort_order)->last(),
        ]);
    }

    public function completeLesson(Request $request, $slug, $lessonSlug)
    {
        $course = DB::table('bizboost_courses')->where('slug', $slug)->first();
        $lesson = DB::table('bizboost_lessons')
            ->where('course_id', $course->id)
            ->where('slug', $lessonSlug)
            ->first();

        if (!$course || !$lesson) {
            abort(404);
        }

        $progress = DB::table('bizboost_course_progress')
            ->where('user_id', $request->user()->id)
            ->where('course_id', $course->id)
            ->first();

        $completedLessons = $progress ? json_decode($progress->completed_lessons ?? '[]', true) : [];
        
        if (!in_array($lesson->id, $completedLessons)) {
            $completedLessons[] = $lesson->id;
        }

        $totalLessons = DB::table('bizboost_lessons')
            ->where('course_id', $course->id)
            ->where('is_published', true)
            ->count();

        $progressPercent = $totalLessons > 0 
            ? round((count($completedLessons) / $totalLessons) * 100) 
            : 0;

        $isCompleted = $progressPercent >= 100;

        if ($progress) {
            DB::table('bizboost_course_progress')
                ->where('id', $progress->id)
                ->update([
                    'completed_lessons' => json_encode($completedLessons),
                    'progress_percent' => $progressPercent,
                    'completed_at' => $isCompleted ? now() : null,
                    'updated_at' => now(),
                ]);
        }

        // Generate certificate if completed and course has certificate
        if ($isCompleted && $course->has_certificate) {
            $this->generateCertificate($request->user(), $course);
        }

        return back()->with('success', 'Lesson completed!');
    }

    public function certificates(Request $request)
    {
        $certificates = DB::table('bizboost_certificates')
            ->join('bizboost_courses', 'bizboost_certificates.course_id', '=', 'bizboost_courses.id')
            ->where('bizboost_certificates.user_id', $request->user()->id)
            ->select('bizboost_certificates.*', 'bizboost_courses.title as course_title', 'bizboost_courses.thumbnail')
            ->orderByDesc('issued_at')
            ->get();

        return Inertia::render('BizBoost/Learning/Certificates', [
            'certificates' => $certificates,
        ]);
    }

    public function downloadCertificate(Request $request, $id)
    {
        $certificate = DB::table('bizboost_certificates')
            ->where('id', $id)
            ->where('user_id', $request->user()->id)
            ->first();

        if (!$certificate || !$certificate->pdf_path) {
            abort(404);
        }

        return response()->download(storage_path('app/' . $certificate->pdf_path));
    }

    private function generateCertificate($user, $course)
    {
        $existing = DB::table('bizboost_certificates')
            ->where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->first();

        if ($existing) {
            return;
        }

        $certificateNumber = 'BB-' . strtoupper(substr(md5($user->id . $course->id . time()), 0, 8));

        DB::table('bizboost_certificates')->insert([
            'user_id' => $user->id,
            'course_id' => $course->id,
            'certificate_number' => $certificateNumber,
            'recipient_name' => $user->name,
            'issued_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // TODO: Generate PDF in background job
    }
}
