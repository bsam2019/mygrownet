<?php

namespace App\Domain\GrowNet\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;

class GuidedLearningPathService
{
    /**
     * Generate "Your Next Steps" sequence based on user level and current progress.
     */
    public function calculateNextSteps(User $user): array
    {
        $currentLevelSlug = $user->current_professional_level ?? 'starter';
        $currentLevelObj = DB::table('professional_levels')->where('slug', $currentLevelSlug)->first();
        $currentLevelNum = (int) ($currentLevelObj->level ?? 1);

        $nextSteps = [];

        // 1. Check next uncompleted lesson
        $nextLesson = DB::table('education_curricula')
            ->where('level', '<=', $currentLevelNum)
            ->whereNotIn('id', function ($query) use ($user) {
                $query->select('lesson_id')
                    ->from('lesson_progress_states')
                    ->where('user_id', $user->id)
                    ->where('status', 'proven_completed');
            })
            ->orderBy('level', 'asc')
            ->orderBy('sort_order', 'asc')
            ->first();

        if ($nextLesson) {
            $nextSteps[] = [
                'step' => 1,
                'title' => 'Complete Lesson: ' . $nextLesson->lesson_title,
                'type' => 'lesson',
                'id' => $nextLesson->id,
                'status' => 'next',
                'url' => route('grownet.sub.learning.lesson', ['id' => $nextLesson->id]),
            ];
        }

        // 2. Check next upcoming workshop registration
        $nextWorkshop = DB::table('workshops')
            ->where('level', '<=', $currentLevelNum)
            ->whereNotIn('id', function ($query) use ($user) {
                $query->select('workshop_id')
                    ->from('workshop_registrations')
                    ->where('user_id', $user->id)
                    ->where('lifecycle_state', 'completed');
            })
            ->first();

        if ($nextWorkshop) {
            $nextSteps[] = [
                'step' => count($nextSteps) + 1,
                'title' => 'Attend Workshop: ' . ($nextWorkshop->topic ?? 'Practical Workshop'),
                'type' => 'workshop',
                'id' => $nextWorkshop->id,
                'status' => 'pending',
                'url' => route('grownet.sub.workshops.show', ['workshop' => $nextWorkshop->id]),
            ];
        }

        // 3. Practical activity
        $nextSteps[] = [
            'step' => count($nextSteps) + 1,
            'title' => 'Complete Practical Business Activity',
            'type' => 'practical',
            'status' => 'pending',
            'url' => route('grownet.network'),
        ];

        // 4. Level assessment
        $nextSteps[] = [
            'step' => count($nextSteps) + 1,
            'title' => "Pass Level {$currentLevelNum} Assessment",
            'type' => 'assessment',
            'status' => 'pending',
            'url' => route('grownet.network'),
        ];

        return $nextSteps;
    }
}
