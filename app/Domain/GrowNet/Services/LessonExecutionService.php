<?php

namespace App\Domain\GrowNet\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;

class LessonExecutionService
{
    /**
     * Get or initialize the Learn/Practise/Prove state for a user and lesson.
     */
    public function getLessonState(User $user, int $lessonId): array
    {
        $state = DB::table('lesson_progress_states')
            ->where('user_id', $user->id)
            ->where('lesson_id', $lessonId)
            ->first();

        $lesson = DB::table('education_curricula')->where('id', $lessonId)->first();

        if (!$state) {
            return [
                'status' => 'not_started',
                'active_language' => 'English',
                'learn_completed' => false,
                'practice_completed' => false,
                'proven_completed' => false,
                'practice_submission' => null,
                'lesson' => $lesson,
            ];
        }

        return [
            'status' => $state->status,
            'active_language' => $state->active_language ?? 'English',
            'learn_completed' => !empty($state->learn_completed_at),
            'practice_completed' => !empty($state->practice_completed_at),
            'proven_completed' => !empty($state->proven_completed_at),
            'practice_submission' => $state->practice_submission,
            'lesson' => $lesson,
        ];
    }

    /**
     * Stage 1: LEARN — Mark video/audio/transcript consumption complete.
     */
    public function recordLearn(User $user, int $lessonId, string $language = 'English'): array
    {
        DB::table('lesson_progress_states')->updateOrInsert(
            ['user_id' => $user->id, 'lesson_id' => $lessonId],
            [
                'status' => 'learning_completed',
                'active_language' => $language,
                'learn_completed_at' => now(),
                'updated_at' => now(),
            ]
        );

        return $this->getLessonState($user, $lessonId);
    }

    /**
     * Stage 2: PRACTISE — Submit practical exercise/worksheet.
     */
    public function submitPractice(User $user, int $lessonId, string $submissionText): array
    {
        DB::table('lesson_progress_states')->updateOrInsert(
            ['user_id' => $user->id, 'lesson_id' => $lessonId],
            [
                'status' => 'practice_completed',
                'practice_submission' => $submissionText,
                'practice_completed_at' => now(),
                'updated_at' => now(),
            ]
        );

        return $this->getLessonState($user, $lessonId);
    }

    /**
     * Stage 3: PROVE — Complete quiz/assessment and record completion.
     */
    public function recordProve(User $user, int $lessonId, bool $passed, ?int $attemptId = null): array
    {
        if ($passed) {
            DB::table('lesson_progress_states')->updateOrInsert(
                ['user_id' => $user->id, 'lesson_id' => $lessonId],
                [
                    'status' => 'proven_completed',
                    'assessment_attempt_id' => $attemptId,
                    'proven_completed_at' => now(),
                    'updated_at' => now(),
                ]
            );

            // Award PB Points (50 PB per lesson) & MP Points (20 MP per lesson)
            DB::table('user_points')->where('user_id', $user->id)->increment('lifetime_points', 50);
            DB::table('user_points')->where('user_id', $user->id)->increment('monthly_points', 20);
        }

        return $this->getLessonState($user, $lessonId);
    }

    /**
     * Low-Literacy Pathway: Facilitator Oral/Practical Assessment Pass.
     */
    public function recordFacilitatorOralPass(User $user, int $lessonId, int $facilitatorUserId, string $notes): array
    {
        $attemptId = DB::table('assessment_attempts')->insertGetId([
            'user_id' => $user->id,
            'level' => 1,
            'assessment_method' => 'oral',
            'score' => 100,
            'passed' => true,
            'assessor_user_id' => $facilitatorUserId,
            'assessor_notes' => $notes,
            'evaluated_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $this->recordProve($user, $lessonId, true, $attemptId);
    }
}
