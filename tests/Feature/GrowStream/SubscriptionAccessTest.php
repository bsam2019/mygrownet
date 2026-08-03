<?php

use App\Domain\GrowStream\Infrastructure\Persistence\Eloquent\CreatorProfile;
use App\Domain\GrowStream\Infrastructure\Persistence\Eloquent\Video;
use App\Domain\GrowStream\Services\AccessControlService;
use App\Infrastructure\Persistence\Eloquent\ModuleModel;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

beforeEach(function () {
    ModuleModel::create([
        'id' => 'growstream',
        'name' => 'GrowStream',
        'slug' => 'growstream',
        'category' => 'sme',
        'description' => 'Video streaming platform',
        'account_types' => ['member', 'business'],
        'routes' => [],
        'requires_subscription' => true,
        'status' => 'active',
        'version' => '1.0.0',
    ]);

    $this->user = User::factory()->create(['account_types' => ['member']]);
    $this->access = app(AccessControlService::class);
});

function seedGrowStreamSubscription(User $user, string $tier): void
{
    DB::table('module_subscriptions')->insert([
        'user_id' => $user->id,
        'module_id' => 'growstream',
        'subscription_tier' => $tier,
        'status' => 'active',
        'started_at' => now(),
        'billing_cycle' => 'monthly',
        'amount' => 129,
        'currency' => 'ZMW',
        'auto_renew' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    Cache::forget("module_sub_{$user->id}_growstream");
}

test('member without subscription cannot access premium content', function () {
    expect($this->access->hasPaidSubscription($this->user))->toBeFalse();
    expect($this->access->userCanAccess($this->user, 'free'))->toBeTrue();
    expect($this->access->userCanAccess($this->user, 'premium'))->toBeFalse();
});

test('member with starter subscription unlocks premium content', function () {
    seedGrowStreamSubscription($this->user, 'starter');

    expect($this->access->currentTier($this->user))->toBe('starter');
    expect($this->access->hasPaidSubscription($this->user))->toBeTrue();
    expect($this->access->userCanAccess($this->user, 'premium'))->toBeTrue();
    expect($this->access->userCanAccess($this->user, 'basic'))->toBeTrue();
});

test('business tier unlocks institutional content', function () {
    seedGrowStreamSubscription($this->user, 'business');

    expect($this->access->currentTier($this->user))->toBe('business');
    expect($this->access->userCanAccess($this->user, 'institutional'))->toBeTrue();
});

test('guest has no access to non-free content', function () {
    expect($this->access->userCanAccess(null, 'free'))->toBeTrue();
    expect($this->access->userCanAccess(null, 'premium'))->toBeFalse();
    expect($this->access->currentTier(null))->toBe('none');
});

test('ensure growstream subscription middleware blocks non-subscribers', function () {
    $creator = CreatorProfile::create([
        'user_id' => User::factory()->create()->id,
        'display_name' => 'Creator',
        'status' => 'approved',
        'is_active' => true,
    ]);

    $video = Video::create([
        'uuid' => (string) Str::uuid(),
        'title' => 'Premium',
        'slug' => Str::slug('premium-'.uniqid()),
        'description' => 'desc',
        'creator_id' => $creator->id,
        'upload_status' => 'ready',
        'moderation_status' => 'approved',
        'content_type' => 'movie',
        'access_level' => 'premium',
        'is_published' => true,
        'published_at' => now(),
    ]);

    $this->actingAs($this->user, 'sanctum')
        ->post(route('api.growstream.watch.authorize'), ['video_id' => $video->id])
        ->assertForbidden();
});
