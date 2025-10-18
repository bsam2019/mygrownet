<?php

namespace App\Services;

use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\User;
use App\Models\InvestmentTier;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class EducationalContentService
{
    /**
     * Get courses available for a specific tier
     */
    public function getCoursesForTier(string $tierName): Collection
    {
        return Cache::remember("courses_tier_{$tierName}", 3600, function () use ($tierName) {
            return Course::published()
                ->forTier($tierName)
                ->orderBy('order')
                ->orderBy('created_at')
                ->get();
        });
    }

    /**
     * Get courses available for a user based on their current tier
     */
    public function getCoursesForUser(User $user): Collection
    {
        $tierName = $user->currentTier?->name ?? 'Bronze';
        return $this->getCoursesForTier($tierName);
    }

    /**
     * Enroll user in a course if they have access
     */
    public function enrollUserInCourse(User $user, Course $course): CourseEnrollment
    {
        if (!$course->isAccessibleByUser($user)) {
            throw new \Exception('User does not have access to this course based on their tier.');
        }

        // Check if already enrolled
        $existingEnrollment = CourseEnrollment::where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->first();

        if ($existingEnrollment) {
            return $existingEnrollment;
        }

        return CourseEnrollment::create([
            'user_id' => $user->id,
            'course_id' => $course->id,
            'enrolled_at' => now(),
            'tier_at_enrollment' => $user->currentTier?->name,
            'status' => 'enrolled'
        ]);
    }

    /**
     * Get tier-specific content recommendations
     */
    public function getTierContentRecommendations(string $tierName): array
    {
        $tierStructure = Course::getTierBasedContentStructure();
        $tierConfig = $tierStructure[$tierName] ?? $tierStructure['Bronze'];

        return [
            'recommended_categories' => $tierConfig['categories'],
            'max_difficulty' => $tierConfig['max_difficulty'],
            'content_types' => $tierConfig['content_types'],
            'monthly_limit' => $tierConfig['monthly_content_limit'],
            'courses' => $this->getCoursesForTier($tierName)->take($tierConfig['monthly_content_limit'])
        ];
    }

    /**
     * Update monthly content for all courses that need updates
     */
    public function updateMonthlyContent(): array
    {
        $updated = [];
        $courses = Course::published()
            ->whereNotNull('content_update_frequency')
            ->get();

        foreach ($courses as $course) {
            if ($course->needsContentUpdate()) {
                $this->updateCourseContent($course);
                $updated[] = $course->id;
            }
        }

        Log::info('Monthly content update completed', ['updated_courses' => count($updated)]);
        return $updated;
    }

    /**
     * Update content for a specific course
     */
    public function updateCourseContent(Course $course): void
    {
        // Mark content as updated
        $course->markContentUpdated();

        // Clear cache for all tiers that can access this course
        $tiers = $course->required_membership_tiers ?: ['Bronze', 'Silver', 'Gold', 'Diamond', 'Elite'];
        foreach ($tiers as $tier) {
            Cache::forget("courses_tier_{$tier}");
        }

        // Notify enrolled users about content update
        $this->notifyUsersOfContentUpdate($course);
    }

    /**
     * Get user's learning progress across all courses
     */
    public function getUserLearningProgress(User $user): array
    {
        $enrollments = CourseEnrollment::where('user_id', $user->id)
            ->with('course')
            ->get();

        $totalCourses = $enrollments->count();
        $completedCourses = $enrollments->where('status', 'completed')->count();
        $inProgressCourses = $enrollments->where('status', 'in_progress')->count();

        return [
            'total_enrolled' => $totalCourses,
            'completed' => $completedCourses,
            'in_progress' => $inProgressCourses,
            'completion_rate' => $totalCourses > 0 ? ($completedCourses / $totalCourses) * 100 : 0,
            'enrollments' => $enrollments,
            'certificates_earned' => $enrollments->whereNotNull('certificate_issued_at')->count()
        ];
    }

    /**
     * Get courses that need content updates this month
     */
    public function getCoursesNeedingUpdate(): Collection
    {
        return Course::published()
            ->whereNotNull('content_update_frequency')
            ->get()
            ->filter(function ($course) {
                return $course->needsContentUpdate();
            });
    }

    /**
     * Schedule content updates for a course
     */
    public function scheduleContentUpdate(Course $course, array $schedule): void
    {
        $course->update([
            'monthly_content_schedule' => array_merge(
                $course->monthly_content_schedule ?? [],
                $schedule
            )
        ]);
    }

    /**
     * Get content statistics by tier
     */
    public function getContentStatisticsByTier(): array
    {
        $stats = [];
        $tiers = ['Bronze', 'Silver', 'Gold', 'Diamond', 'Elite'];

        foreach ($tiers as $tier) {
            $courses = $this->getCoursesForTier($tier);
            $stats[$tier] = [
                'total_courses' => $courses->count(),
                'by_category' => $courses->groupBy('category')->map->count(),
                'by_difficulty' => $courses->groupBy('difficulty_level')->map->count(),
                'premium_courses' => $courses->where('is_premium', true)->count(),
                'certificate_eligible' => $courses->where('certificate_eligible', true)->count()
            ];
        }

        return $stats;
    }

    /**
     * Notify users about content updates
     */
    private function notifyUsersOfContentUpdate(Course $course): void
    {
        $enrolledUsers = User::whereHas('courseEnrollments', function ($query) use ($course) {
            $query->where('course_id', $course->id)
                  ->whereIn('status', ['enrolled', 'in_progress']);
        })->get();

        foreach ($enrolledUsers as $user) {
            // Here you would typically send a notification
            // For now, we'll just log it
            Log::info('Content update notification', [
                'user_id' => $user->id,
                'course_id' => $course->id,
                'course_title' => $course->title
            ]);
        }
    }

    /**
     * Get tier-specific educational benefits
     */
    public function getTierEducationalBenefits(string $tierName): array
    {
        return match ($tierName) {
            'Bronze' => [
                'content_types' => ['E-books', 'Templates', 'Basic Financial Tips'],
                'monthly_content' => 5,
                'categories' => ['Financial Literacy', 'Life Skills'],
                'features' => ['Basic educational content', 'Peer circle access']
            ],
            'Silver' => [
                'content_types' => ['Videos', 'Webinars', 'Advanced Courses'],
                'monthly_content' => 10,
                'categories' => ['Financial Literacy', 'Business Skills', 'Life Skills'],
                'features' => ['Advanced educational content', 'Mentorship access']
            ],
            'Gold' => [
                'content_types' => ['Business Toolkits', 'Investment Courses', 'Webinars'],
                'monthly_content' => 15,
                'categories' => ['Business Skills', 'Investment Strategies', 'MLM Training'],
                'features' => ['Business planning toolkits', 'Expert office hours']
            ],
            'Diamond' => [
                'content_types' => ['Advanced Courses', 'Mentorship Content', 'Toolkits'],
                'monthly_content' => 20,
                'categories' => ['Investment Strategies', 'Leadership Development', 'MLM Training'],
                'features' => ['Investment courses', 'Community project voting']
            ],
            'Elite' => [
                'content_types' => ['VIP Mentorship', 'Innovation Lab', 'Exclusive Content'],
                'monthly_content' => 25,
                'categories' => ['Leadership Development', 'Business Skills', 'Investment Strategies'],
                'features' => ['VIP mentorship content', 'Innovation lab access', 'Business facilitation']
            ],
            default => $this->getTierEducationalBenefits('Bronze')
        };
    }
}