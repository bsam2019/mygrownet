<?php

namespace App\Http\Controllers\GrowNet;

use App\Domain\GrowNet\Services\GuidedLearningPathService;
use App\Domain\GrowNet\Services\LessonExecutionService;
use App\Domain\GrowNet\Services\WorkshopLifecycleService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class LearningHubController extends Controller
{
    public function __construct(
        protected LessonExecutionService $lessonService,
        protected WorkshopLifecycleService $workshopService,
        protected GuidedLearningPathService $learningPathService
    ) {}

    /**
     * Central "My Learning" Hub (`Curriculum | Workshops | Skills Training | Certificates`).
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $levelSlug = $user->current_professional_level ?? 'starter';
        $levelObj = DB::table('professional_levels')->where('slug', $levelSlug)->first();
        $levelNum = (int) ($levelObj->level ?? 1);

        $curriculum = DB::table('education_curricula')
            ->where('level', '<=', $levelNum + 1)
            ->orderBy('level', 'asc')
            ->orderBy('sort_order', 'asc')
            ->get();

        $userProgress = DB::table('lesson_progress_states')
            ->where('user_id', $user->id)
            ->get()
            ->keyBy('lesson_id');

        $nextSteps = $this->learningPathService->calculateNextSteps($user);

        $workshops = DB::table('workshops')
            ->where('status', 'published')
            ->orderBy('id', 'desc')
            ->limit(6)
            ->get();

        $certificates = DB::table('certificates')
            ->where('user_id', $user->id)
            ->orderBy('issued_at', 'desc')
            ->get();

        if ($curriculum->isEmpty()) {
            $curriculum = collect([
                (object)[
                    'id' => 1,
                    'level' => 1,
                    'module_number' => 1,
                    'sort_order' => 1,
                    'lesson_title' => 'Introduction to Entrepreneurship & Personal Finance',
                    'description' => 'Understand fundamental business concepts, personal budgeting, and financial discipline.',
                    'duration_minutes' => 15,
                ],
                (object)[
                    'id' => 2,
                    'level' => 1,
                    'module_number' => 1,
                    'sort_order' => 2,
                    'lesson_title' => 'Understanding Customers & Market Needs',
                    'description' => 'Identify customer pain points, value propositions, and effective communication.',
                    'duration_minutes' => 18,
                ],
                (object)[
                    'id' => 3,
                    'level' => 2,
                    'module_number' => 2,
                    'sort_order' => 1,
                    'lesson_title' => 'Pricing Your Products and Services',
                    'description' => 'Calculate production costs, break-even prices, and competitive profit margins.',
                    'duration_minutes' => 20,
                ],
                (object)[
                    'id' => 4,
                    'level' => 2,
                    'module_number' => 2,
                    'sort_order' => 2,
                    'lesson_title' => 'Basic Record Keeping & Cash Flow Management',
                    'description' => 'Track income, expenses, cash flow, and separate personal money from business funds.',
                    'duration_minutes' => 25,
                ],
            ]);
        }

        if ($workshops->isEmpty()) {
            $workshops = collect([
                (object)[
                    'id' => 1,
                    'level' => 1,
                    'topic' => 'Practical Business Financial Management',
                    'description' => 'Interactive workshop on business income, expenses, cash flow, and basic financial records.',
                    'start_date' => '2026-08-22 09:00:00',
                    'location' => 'Lusaka / Online',
                    'instructor_name' => 'John Banda (Business Accountant)',
                    'institution_name' => 'ABC College of Business Partner',
                ],
                (object)[
                    'id' => 2,
                    'level' => 2,
                    'topic' => 'Digital Marketing & Customer Acquisition for SMEs',
                    'description' => 'Practical masterclass on social media marketing, SMS campaigns, and customer messaging.',
                    'start_date' => '2026-08-28 14:00:00',
                    'location' => 'Kitwe / Online',
                    'instructor_name' => 'Sarah Tembo (Digital Strategist)',
                    'institution_name' => 'GrowNet Entrepreneurship Center',
                ],
                (object)[
                    'id' => 3,
                    'level' => 3,
                    'topic' => 'Practical Poultry Production & Agribusiness',
                    'description' => 'Specialist skills training on poultry management, feed optimization, and farm record keeping.',
                    'start_date' => '2026-09-05 09:00:00',
                    'location' => 'Ndola Practical Farm',
                    'instructor_name' => 'Dr. Michael Phiri (Agribusiness Expert)',
                    'institution_name' => 'Zambia Institute of Agriculture Partner',
                ],
            ]);
        }

        return Inertia::render('GrowNet/MyLearning/Index', [
            'level' => $levelObj,
            'curriculum' => $curriculum,
            'userProgress' => $userProgress,
            'nextSteps' => $nextSteps,
            'workshops' => $workshops,
            'certificates' => $certificates,
        ]);
    }

    /**
     * Single Lesson Show Page (Learn / Practise / Prove Experience).
     */
    public function showLesson(Request $request, int $id)
    {
        $user = $request->user();
        $state = $this->lessonService->getLessonState($user, $id);

        if (!$state['lesson']) {
            return redirect()->route('grownet.sub.learning.index')->with('error', 'Lesson not found.');
        }

        return Inertia::render('GrowNet/MyLearning/LessonShow', [
            'state' => $state,
        ]);
    }

    /**
     * Mark Stage 1: LEARN completed.
     */
    public function markLearnCompleted(Request $request, int $id)
    {
        $user = $request->user();
        $language = $request->input('language', 'English');

        $state = $this->lessonService->recordLearn($user, $id, $language);
        return back()->with('success', 'Learn stage completed!');
    }

    /**
     * Submit Stage 2: PRACTISE exercise.
     */
    public function submitPractice(Request $request, int $id)
    {
        $request->validate(['submission' => 'required|string|min:5']);

        $user = $request->user();
        $submission = $request->input('submission');

        $state = $this->lessonService->submitPractice($user, $id, $submission);
        return back()->with('success', 'Practice activity submitted successfully!');
    }

    /**
     * Complete Stage 3: PROVE.
     */
    public function submitProve(Request $request, int $id)
    {
        $user = $request->user();
        $passed = $request->boolean('passed', true);

        $state = $this->lessonService->recordProve($user, $id, $passed);
        return back()->with('success', 'Lesson completed and points awarded!');
    }
}
