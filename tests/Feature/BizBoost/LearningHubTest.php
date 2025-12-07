<?php

namespace Tests\Feature\BizBoost;

use Illuminate\Foundation\Testing\RefreshDatabase;

class LearningHubTest extends BizBoostTestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seedCourses();
    }

    protected function seedCourses(): void
    {
        \DB::table('bizboost_courses')->insert([
            [
                'id' => 1,
                'title' => 'Marketing Basics',
                'slug' => 'marketing-basics',
                'description' => 'Learn the fundamentals of marketing',
                'category' => 'Marketing',
                'difficulty' => 'beginner',
                'duration_minutes' => 60,
                'lessons_count' => 5,
                'tier_required' => 'free',
                'has_certificate' => true,
                'is_published' => true,
                'sort_order' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'title' => 'Advanced Social Media',
                'slug' => 'advanced-social-media',
                'description' => 'Master social media marketing',
                'category' => 'Social Media',
                'difficulty' => 'advanced',
                'duration_minutes' => 120,
                'lessons_count' => 10,
                'tier_required' => 'professional',
                'has_certificate' => true,
                'is_published' => true,
                'sort_order' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        \DB::table('bizboost_lessons')->insert([
            [
                'id' => 1,
                'course_id' => 1,
                'title' => 'Introduction to Marketing',
                'slug' => 'introduction',
                'content' => '<p>Welcome to marketing basics!</p>',
                'duration_minutes' => 10,
                'sort_order' => 1,
                'is_published' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'course_id' => 1,
                'title' => 'Understanding Your Audience',
                'slug' => 'understanding-audience',
                'content' => '<p>Learn about your target audience.</p>',
                'duration_minutes' => 15,
                'sort_order' => 2,
                'is_published' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }

    public function test_can_view_learning_hub_index(): void
    {
        $this->actingAs($this->user)
            ->get('/bizboost/learning')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('BizBoost/Learning/Index')
                ->has('courses')
                ->has('categories')
                ->has('certificates')
                ->has('stats')
            );
    }

    public function test_can_view_course_detail(): void
    {
        $this->actingAs($this->user)
            ->get('/bizboost/learning/marketing-basics')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('BizBoost/Learning/Course')
                ->has('course')
                ->has('lessons')
            );
    }

    public function test_can_view_lesson(): void
    {
        $this->actingAs($this->user)
            ->get('/bizboost/learning/marketing-basics/introduction')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('BizBoost/Learning/Lesson')
                ->has('course')
                ->has('lesson')
                ->has('lessons')
            );
    }

    public function test_can_complete_lesson(): void
    {
        $this->actingAs($this->user)
            ->post('/bizboost/learning/marketing-basics/introduction/complete')
            ->assertRedirect();

        $this->assertDatabaseHas('bizboost_course_progress', [
            'user_id' => $this->user->id,
            'course_id' => 1,
        ]);
    }

    public function test_progress_updates_on_lesson_completion(): void
    {
        // Complete first lesson
        $this->actingAs($this->user)
            ->post('/bizboost/learning/marketing-basics/introduction/complete');

        $progress = \DB::table('bizboost_course_progress')
            ->where('user_id', $this->user->id)
            ->where('course_id', 1)
            ->first();

        $this->assertEquals(50, $progress->progress_percent); // 1 of 2 lessons = 50%
    }

    public function test_certificate_generated_on_course_completion(): void
    {
        // Complete all lessons
        $this->actingAs($this->user)
            ->post('/bizboost/learning/marketing-basics/introduction/complete');
        
        $this->actingAs($this->user)
            ->post('/bizboost/learning/marketing-basics/understanding-audience/complete');

        $this->assertDatabaseHas('bizboost_certificates', [
            'user_id' => $this->user->id,
            'course_id' => 1,
        ]);
    }

    public function test_locked_course_redirects_to_upgrade(): void
    {
        // User is on free tier, trying to access professional course
        $this->actingAs($this->user)
            ->get('/bizboost/learning/advanced-social-media')
            ->assertRedirect('/bizboost/upgrade');
    }

    public function test_can_view_certificates_page(): void
    {
        $this->actingAs($this->user)
            ->get('/bizboost/learning/certificates')
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('BizBoost/Learning/Certificates')
                ->has('certificates')
            );
    }
}
