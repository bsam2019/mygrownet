<?php

use App\Domain\GrowStream\Infrastructure\Persistence\Eloquent\CreatorProfile;
use App\Domain\GrowStream\Infrastructure\Persistence\Eloquent\Video;
use App\Models\User;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    $this->user = User::factory()->create();
});

function createApprovedCreator(User $user, bool $verified = false): CreatorProfile
{
    return CreatorProfile::create([
        'user_id' => $user->id,
        'display_name' => 'Test Creator',
        'channel_name' => 'Test Channel',
        'status' => 'approved',
        'is_active' => true,
        'can_upload' => true,
        'is_verified' => $verified,
        'upload_limit_per_month' => 50,
    ]);
}

function createAdminUser(): User
{
    $role = Role::where('name', 'admin')->first();
    if (! $role) {
        $role = Role::create(['name' => 'admin', 'slug' => 'admin', 'guard_name' => 'web']);
    }
    $admin = User::factory()->create();
    $admin->assignRole($role);

    return $admin;
}

test('unapproved creator cannot access creator videos', function () {
    CreatorProfile::create([
        'user_id' => $this->user->id,
        'display_name' => 'Pending Creator',
        'status' => 'pending',
        'is_active' => false,
    ]);

    $this->actingAs($this->user)
        ->get(route('growstream.creator.videos.index'))
        ->assertForbidden();
});

test('creator uploads a video via URL and it goes to moderation', function () {
    $creator = createApprovedCreator($this->user);

    $response = $this->actingAs($this->user)->post(route('growstream.creator.videos.store'), [
        'title' => 'My First Video',
        'description' => 'A great video',
        'content_type' => 'movie',
        'access_level' => 'free',
        'video_url' => 'https://example.com/video.mp4',
        'tags' => ['comedy', 'local'],
        'rights_declaration' => true,
    ]);

    $response->assertSessionHasNoErrors();
    $response->assertRedirect(route('growstream.creator.videos.index'));

    $this->assertDatabaseHas('growstream_videos', [
        'creator_id' => $creator->id,
        'title' => 'My First Video',
        'upload_status' => 'ready',
        'moderation_status' => 'pending_review',
        'is_published' => false,
    ]);

    $video = Video::where('title', 'My First Video')->first();
    expect($video->tags()->count())->toBe(2);
});

test('creator upload requires rights declaration', function () {
    $creator = createApprovedCreator($this->user);

    $response = $this->actingAs($this->user)->post(route('growstream.creator.videos.store'), [
        'title' => 'No Rights',
        'content_type' => 'movie',
        'access_level' => 'free',
        'video_url' => 'https://example.com/video.mp4',
        'rights_declaration' => false,
    ]);

    $response->assertSessionHasErrors('rights_declaration');
    $this->assertDatabaseMissing('growstream_videos', ['title' => 'No Rights']);
});

test('verified creator upload is auto-approved', function () {
    $creator = createApprovedCreator($this->user, verified: true);

    $this->actingAs($this->user)->post(route('growstream.creator.videos.store'), [
        'title' => 'Auto Approved',
        'content_type' => 'short',
        'access_level' => 'free',
        'video_url' => 'https://example.com/video.mp4',
        'rights_declaration' => true,
    ]);

    $this->assertDatabaseHas('growstream_videos', [
        'creator_id' => $creator->id,
        'title' => 'Auto Approved',
        'moderation_status' => 'approved',
    ]);
});

test('creator can list and edit own videos', function () {
    $creator = createApprovedCreator($this->user);
    $video = Video::create([
        'uuid' => (string) Str::uuid(),
        'title' => 'My Video',
        'slug' => Str::slug('My Video '.uniqid()),
        'description' => 'desc',
        'creator_id' => $creator->id,
        'upload_status' => 'ready',
        'moderation_status' => 'pending_review',
        'content_type' => 'movie',
        'access_level' => 'free',
        'is_published' => false,
    ]);

    $this->actingAs($this->user)->get(route('growstream.creator.videos.index'))->assertOk();

    $this->actingAs($this->user)->put(route('growstream.creator.videos.update', $video->id), [
        'title' => 'Renamed Video',
        'description' => 'updated',
        'content_type' => 'movie',
        'access_level' => 'free',
    ])->assertRedirect(route('growstream.creator.videos.index'));

    $this->assertDatabaseHas('growstream_videos', ['id' => $video->id, 'title' => 'Renamed Video']);
});

test('creator cannot edit another creators video', function () {
    $otherUser = User::factory()->create();
    $otherCreator = createApprovedCreator($otherUser);

    $creator = createApprovedCreator($this->user);
    $video = Video::create([
        'uuid' => (string) Str::uuid(),
        'title' => 'Other Video',
        'slug' => Str::slug('Other Video '.uniqid()),
        'description' => 'desc',
        'creator_id' => $otherCreator->id,
        'upload_status' => 'ready',
        'moderation_status' => 'approved',
        'content_type' => 'movie',
        'access_level' => 'free',
        'is_published' => true,
    ]);

    $this->actingAs($this->user)->get(route('growstream.creator.videos.edit', $video->id))->assertNotFound();
    $this->actingAs($this->user)->delete(route('growstream.creator.videos.destroy', $video->id))->assertNotFound();
});

test('admin moderation queue lists pending videos', function () {
    $creator = createApprovedCreator($this->user);
    Video::create([
        'uuid' => (string) Str::uuid(),
        'title' => 'Pending Video',
        'slug' => Str::slug('Pending Video '.uniqid()),
        'description' => 'desc',
        'creator_id' => $creator->id,
        'upload_status' => 'ready',
        'moderation_status' => 'pending_review',
        'content_type' => 'movie',
        'access_level' => 'free',
        'is_published' => false,
    ]);

    $admin = createAdminUser();

    $this->actingAs($admin)->get(route('growstream.admin.moderation'))->assertOk();
});

test('admin can approve a pending video', function () {
    $creator = createApprovedCreator($this->user);
    $video = Video::create([
        'uuid' => (string) Str::uuid(),
        'title' => 'Approve Me',
        'slug' => Str::slug('Approve Me '.uniqid()),
        'description' => 'desc',
        'creator_id' => $creator->id,
        'upload_status' => 'ready',
        'moderation_status' => 'pending_review',
        'content_type' => 'movie',
        'access_level' => 'free',
        'is_published' => false,
    ]);

    $admin = createAdminUser();

    $this->actingAs($admin)
        ->from(route('growstream.admin.moderation'))
        ->post(route('growstream.admin.moderation.approve', $video->id))
        ->assertRedirect(route('growstream.admin.moderation'));

    $this->assertDatabaseHas('growstream_videos', [
        'id' => $video->id,
        'moderation_status' => 'approved',
    ]);
});

test('admin can reject a pending video with reason', function () {
    $creator = createApprovedCreator($this->user);
    $video = Video::create([
        'uuid' => (string) Str::uuid(),
        'title' => 'Reject Me',
        'slug' => Str::slug('Reject Me '.uniqid()),
        'description' => 'desc',
        'creator_id' => $creator->id,
        'upload_status' => 'ready',
        'moderation_status' => 'pending_review',
        'content_type' => 'movie',
        'access_level' => 'free',
        'is_published' => false,
    ]);

    $admin = createAdminUser();

    $this->actingAs($admin)
        ->from(route('growstream.admin.moderation'))
        ->post(route('growstream.admin.moderation.reject', $video->id), ['reason' => 'Copyright violation'])
        ->assertRedirect(route('growstream.admin.moderation'));

    $this->assertDatabaseHas('growstream_videos', [
        'id' => $video->id,
        'moderation_status' => 'rejected',
        'moderation_reason' => 'Copyright violation',
    ]);
});

test('admin can approve and publish a pending video', function () {
    $creator = createApprovedCreator($this->user);
    $video = Video::create([
        'uuid' => (string) Str::uuid(),
        'title' => 'Publish Me',
        'slug' => Str::slug('Publish Me '.uniqid()),
        'description' => 'desc',
        'creator_id' => $creator->id,
        'upload_status' => 'ready',
        'moderation_status' => 'pending_review',
        'content_type' => 'movie',
        'access_level' => 'free',
        'is_published' => false,
    ]);

    $admin = createAdminUser();

    $this->actingAs($admin)
        ->from(route('growstream.admin.moderation'))
        ->post(route('growstream.admin.moderation.publish', $video->id))
        ->assertRedirect(route('growstream.admin.moderation'));

    $this->assertDatabaseHas('growstream_videos', [
        'id' => $video->id,
        'moderation_status' => 'approved',
        'is_published' => true,
    ]);
});
