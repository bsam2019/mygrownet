<?php

namespace Tests\Feature\BizBoost;

use App\Infrastructure\Persistence\Eloquent\BizBoostBusinessModel;
use App\Infrastructure\Persistence\Eloquent\BizBoostPostModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class PostsTest extends BizBoostTestCase
{
    use RefreshDatabase;

    public function test_user_can_view_posts_list(): void
    {
        $this->actingAs($this->user);
        
        // Create some posts
        BizBoostPostModel::factory()->count(3)->create([
            'business_id' => $this->business->id,
        ]);

        $response = $this->get(route('bizboost.posts.index'));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('BizBoost/Posts/Index')
            ->has('posts.data', 3)
            ->has('stats')
        );
    }

    public function test_user_can_create_draft_post(): void
    {
        $this->actingAs($this->user);

        $response = $this->post(route('bizboost.posts.store'), [
            'caption' => 'Test post caption for my business',
            'status' => 'draft',
            'post_type' => 'standard',
        ]);

        $response->assertRedirect(route('bizboost.posts.index'));
        $this->assertDatabaseHas('bizboost_posts', [
            'business_id' => $this->business->id,
            'caption' => 'Test post caption for my business',
            'status' => 'draft',
        ]);
    }

    public function test_user_can_schedule_post(): void
    {
        $this->actingAs($this->user);
        
        $scheduledAt = now()->addDay()->format('Y-m-d H:i:s');

        $response = $this->post(route('bizboost.posts.store'), [
            'caption' => 'Scheduled post caption',
            'status' => 'scheduled',
            'scheduled_at' => $scheduledAt,
            'platform_targets' => ['facebook'],
            'post_type' => 'standard',
        ]);

        $response->assertRedirect(route('bizboost.posts.index'));
        $this->assertDatabaseHas('bizboost_posts', [
            'business_id' => $this->business->id,
            'caption' => 'Scheduled post caption',
            'status' => 'scheduled',
        ]);
    }

    public function test_user_can_upload_media_with_post(): void
    {
        Storage::fake('public');
        $this->actingAs($this->user);

        $response = $this->post(route('bizboost.posts.store'), [
            'caption' => 'Post with media',
            'status' => 'draft',
            'post_type' => 'standard',
            'media' => [
                UploadedFile::fake()->image('photo.jpg', 1080, 1080),
            ],
        ]);

        $response->assertRedirect(route('bizboost.posts.index'));
        
        $post = BizBoostPostModel::where('caption', 'Post with media')->first();
        $this->assertNotNull($post);
        $this->assertEquals(1, $post->media()->count());
    }

    public function test_user_can_update_draft_post(): void
    {
        $this->actingAs($this->user);
        
        $post = BizBoostPostModel::factory()->create([
            'business_id' => $this->business->id,
            'status' => 'draft',
            'caption' => 'Original caption',
        ]);

        $response = $this->put(route('bizboost.posts.update', $post->id), [
            'caption' => 'Updated caption',
            'status' => 'draft',
            'post_type' => 'standard',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('bizboost_posts', [
            'id' => $post->id,
            'caption' => 'Updated caption',
        ]);
    }

    public function test_user_cannot_edit_published_post(): void
    {
        $this->actingAs($this->user);
        
        $post = BizBoostPostModel::factory()->create([
            'business_id' => $this->business->id,
            'status' => 'published',
            'published_at' => now(),
        ]);

        $response = $this->put(route('bizboost.posts.update', $post->id), [
            'caption' => 'Trying to update',
            'status' => 'draft',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');
    }

    public function test_user_can_delete_post(): void
    {
        $this->actingAs($this->user);
        
        $post = BizBoostPostModel::factory()->create([
            'business_id' => $this->business->id,
        ]);

        $response = $this->delete(route('bizboost.posts.destroy', $post->id));

        $response->assertRedirect(route('bizboost.posts.index'));
        $this->assertDatabaseMissing('bizboost_posts', ['id' => $post->id]);
    }

    public function test_user_can_duplicate_post(): void
    {
        $this->actingAs($this->user);
        
        $post = BizBoostPostModel::factory()->create([
            'business_id' => $this->business->id,
            'caption' => 'Original post to duplicate',
            'status' => 'published',
        ]);

        $response = $this->post(route('bizboost.posts.duplicate', $post->id));

        $response->assertRedirect();
        
        $duplicatedPost = BizBoostPostModel::where('caption', 'Original post to duplicate')
            ->where('status', 'draft')
            ->first();
        
        $this->assertNotNull($duplicatedPost);
        $this->assertNotEquals($post->id, $duplicatedPost->id);
    }

    public function test_user_can_get_share_links(): void
    {
        $this->actingAs($this->user);
        
        $post = BizBoostPostModel::factory()->create([
            'business_id' => $this->business->id,
            'caption' => 'Share this post!',
        ]);

        $response = $this->get(route('bizboost.posts.share-links', $post->id));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'caption',
            'media_url',
            'share_links' => [
                'facebook',
                'twitter',
                'whatsapp',
                'linkedin',
            ],
        ]);
    }

    public function test_posts_are_filtered_by_status(): void
    {
        $this->actingAs($this->user);
        
        BizBoostPostModel::factory()->create([
            'business_id' => $this->business->id,
            'status' => 'draft',
        ]);
        
        BizBoostPostModel::factory()->create([
            'business_id' => $this->business->id,
            'status' => 'scheduled',
        ]);
        
        BizBoostPostModel::factory()->create([
            'business_id' => $this->business->id,
            'status' => 'published',
        ]);

        $response = $this->get(route('bizboost.posts.index', ['status' => 'draft']));

        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('BizBoost/Posts/Index')
            ->has('posts.data', 1)
        );
    }
}
