<?php

namespace Tests\Unit\Services;

use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\User;
use App\Models\InvestmentTier;
use App\Services\EducationalContentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class EducationalContentServiceTest extends TestCase
{
    use RefreshDatabase;

    private EducationalContentService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new EducationalContentService();
    }

    public function test_get_courses_for_tier_returns_accessible_courses(): void
    {
        // Create courses for different tiers
        $bronzeCourse = Course::factory()->published()->forTier('Bronze')->create();
        $silverCourse = Course::factory()->published()->forTier('Silver')->create();
        $goldCourse = Course::factory()->published()->forTier('Gold')->create();
        $allTiersCourse = Course::factory()->published()->create(['required_membership_tiers' => []]);

        // Bronze tier should see Bronze and all-tiers courses
        $bronzeCourses = $this->service->getCoursesForTier('Bronze');
        $this->assertCount(2, $bronzeCourses);
        $this->assertTrue($bronzeCourses->contains($bronzeCourse));
        $this->assertTrue($bronzeCourses->contains($allTiersCourse));
        $this->assertFalse($bronzeCourses->contains($silverCourse));
        $this->assertFalse($bronzeCourses->contains($goldCourse));
    }

    public function test_get_courses_for_user_based_on_tier(): void
    {
        // Create investment tier
        $silverTier = InvestmentTier::factory()->create(['name' => 'Silver']);
        
        // Create user with Silver tier
        $user = User::factory()->create(['current_investment_tier_id' => $silverTier->id]);
        
        // Create courses
        $bronzeCourse = Course::factory()->published()->forTier('Bronze')->create();
        $silverCourse = Course::factory()->published()->forTier('Silver')->create();
        $goldCourse = Course::factory()->published()->forTier('Gold')->create();

        $userCourses = $this->service->getCoursesForUser($user);
        
        // Should see Silver courses but not Gold
        $this->assertTrue($userCourses->contains($silverCourse));
        $this->assertFalse($userCourses->contains($goldCourse));
    }

    public function test_enroll_user_in_accessible_course(): void
    {
        $silverTier = InvestmentTier::factory()->create(['name' => 'Silver']);
        $user = User::factory()->create(['current_investment_tier_id' => $silverTier->id]);
        $course = Course::factory()->published()->forTier('Silver')->create();

        $enrollment = $this->service->enrollUserInCourse($user, $course);

        $this->assertInstanceOf(CourseEnrollment::class, $enrollment);
        $this->assertEquals($user->id, $enrollment->user_id);
        $this->assertEquals($course->id, $enrollment->course_id);
        $this->assertEquals('Silver', $enrollment->tier_at_enrollment);
        $this->assertEquals('enrolled', $enrollment->status);
    }

    public function test_cannot_enroll_user_in_inaccessible_course(): void
    {
        $bronzeTier = InvestmentTier::factory()->create(['name' => 'Bronze']);
        $user = User::factory()->create(['current_investment_tier_id' => $bronzeTier->id]);
        $course = Course::factory()->published()->forTier('Gold')->create();

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('User does not have access to this course based on their tier.');

        $this->service->enrollUserInCourse($user, $course);
    }

    public function test_get_tier_content_recommendations(): void
    {
        $recommendations = $this->service->getTierContentRecommendations('Gold');

        $this->assertArrayHasKey('recommended_categories', $recommendations);
        $this->assertArrayHasKey('max_difficulty', $recommendations);
        $this->assertArrayHasKey('content_types', $recommendations);
        $this->assertArrayHasKey('monthly_limit', $recommendations);
        $this->assertArrayHasKey('courses', $recommendations);

        // Gold tier should have specific categories
        $this->assertContains('business_skills', $recommendations['recommended_categories']);
        $this->assertContains('investment_strategies', $recommendations['recommended_categories']);
        $this->assertEquals('advanced', $recommendations['max_difficulty']);
        $this->assertEquals(15, $recommendations['monthly_limit']);
    }

    public function test_update_monthly_content_updates_courses_needing_update(): void
    {
        // Create course that needs update (last updated over a month ago)
        $course = Course::factory()->published()->create([
            'content_update_frequency' => 'monthly',
            'last_content_update' => now()->subMonths(2)
        ]);

        // Create course that doesn't need update
        $recentCourse = Course::factory()->published()->create([
            'content_update_frequency' => 'monthly',
            'last_content_update' => now()->subDays(15)
        ]);

        $updatedCourses = $this->service->updateMonthlyContent();

        $this->assertContains($course->id, $updatedCourses);
        $this->assertNotContains($recentCourse->id, $updatedCourses);

        // Verify the course was marked as updated
        $course->refresh();
        $this->assertTrue($course->last_content_update->isToday());
    }

    public function test_get_user_learning_progress(): void
    {
        $user = User::factory()->create();
        
        // Create enrollments with different statuses
        CourseEnrollment::factory()->create([
            'user_id' => $user->id,
            'status' => 'completed',
            'certificate_issued_at' => now()
        ]);
        
        CourseEnrollment::factory()->create([
            'user_id' => $user->id,
            'status' => 'in_progress'
        ]);
        
        CourseEnrollment::factory()->create([
            'user_id' => $user->id,
            'status' => 'enrolled'
        ]);

        $progress = $this->service->getUserLearningProgress($user);

        $this->assertEquals(3, $progress['total_enrolled']);
        $this->assertEquals(1, $progress['completed']);
        $this->assertEquals(1, $progress['in_progress']);
        $this->assertEquals(1, $progress['certificates_earned']);
        $this->assertEquals(33.33, round($progress['completion_rate'], 2));
    }

    public function test_get_tier_educational_benefits(): void
    {
        $bronzeBenefits = $this->service->getTierEducationalBenefits('Bronze');
        $eliteBenefits = $this->service->getTierEducationalBenefits('Elite');

        // Bronze should have basic benefits
        $this->assertEquals(5, $bronzeBenefits['monthly_content']);
        $this->assertContains('E-books', $bronzeBenefits['content_types']);
        $this->assertContains('Financial Literacy', $bronzeBenefits['categories']);

        // Elite should have premium benefits
        $this->assertEquals(25, $eliteBenefits['monthly_content']);
        $this->assertContains('VIP Mentorship', $eliteBenefits['content_types']);
        $this->assertContains('Innovation lab access', $eliteBenefits['features']);
    }

    public function test_courses_are_cached_by_tier(): void
    {
        Cache::flush();
        
        Course::factory()->published()->forTier('Bronze')->create();
        
        // First call should hit database
        $courses1 = $this->service->getCoursesForTier('Bronze');
        
        // Second call should hit cache
        $courses2 = $this->service->getCoursesForTier('Bronze');
        
        $this->assertEquals($courses1->count(), $courses2->count());
        $this->assertTrue(Cache::has('courses_tier_Bronze'));
    }

    public function test_content_statistics_by_tier(): void
    {
        // Create courses for different tiers
        Course::factory()->published()->forTier('Bronze')->withCategory('financial_literacy')->create();
        Course::factory()->published()->forTier('Silver')->withCategory('business_skills')->premium()->create();
        Course::factory()->published()->forTier('Gold')->withCategory('investment_strategies')->create();

        $stats = $this->service->getContentStatisticsByTier();

        $this->assertArrayHasKey('Bronze', $stats);
        $this->assertArrayHasKey('Silver', $stats);
        $this->assertArrayHasKey('Gold', $stats);

        // Check Bronze stats
        $this->assertEquals(1, $stats['Bronze']['total_courses']);
        $this->assertEquals(1, $stats['Bronze']['by_category']['financial_literacy']);

        // Check Silver stats (should include Bronze courses too)
        $this->assertGreaterThanOrEqual(1, $stats['Silver']['total_courses']);
        $this->assertEquals(1, $stats['Silver']['premium_courses']);
    }
}