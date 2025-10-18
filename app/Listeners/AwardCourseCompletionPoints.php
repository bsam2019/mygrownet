<?php

namespace App\Listeners;

use App\Services\PointService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;

class AwardCourseCompletionPoints implements ShouldQueue
{
    public function __construct(
        protected PointService $pointService
    ) {}

    /**
     * Handle the event when a user completes a course
     */
    public function handle($event): void
    {
        try {
            $course = $event->enrollment->course;
            $user = $event->enrollment->user;

            // Determine points based on course difficulty
            [$lpAmount, $mapAmount] = $this->getPointsForCourse($course);

            $this->pointService->awardPoints(
                user: $user,
                source: 'course_completion',
                lpAmount: $lpAmount,
                mapAmount: $mapAmount,
                description: "Completed course: {$course->title}",
                reference: $event->enrollment
            );

            Log::info('Course completion points awarded', [
                'user_id' => $user->id,
                'course_id' => $course->id,
                'lp' => $lpAmount,
                'map' => $mapAmount,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to award course completion points', [
                'error' => $e->getMessage(),
                'user_id' => $event->enrollment->user_id ?? null,
            ]);
        }
    }

    /**
     * Get points based on course difficulty
     */
    protected function getPointsForCourse($course): array
    {
        return match ($course->difficulty_level ?? 'basic') {
            'basic' => [30, 30],
            'intermediate' => [45, 45],
            'advanced' => [60, 60],
            default => [30, 30],
        };
    }
}
