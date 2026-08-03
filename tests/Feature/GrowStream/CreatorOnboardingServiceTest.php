<?php

use App\Domain\GrowStream\Infrastructure\Persistence\Eloquent\CreatorProfile;
use App\Domain\GrowStream\Services\CreatorProfileService;
use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->service = app(CreatorProfileService::class);
});

test('apply for creator creates a pending profile', function () {
    $profile = $this->service->applyForCreator($this->user->id, [
        'display_name' => 'Test Creator',
        'channel_name' => 'Test Channel',
        'bio' => 'Creating content',
    ]);

    expect($profile['user_id'])->toBe($this->user->id);
    expect($profile['status'])->toBe('pending');
    expect($profile['is_active'])->toBeFalse();
    expect($profile['can_upload'])->toBeFalse();

    $this->assertDatabaseHas('growstream_creator_profiles', [
        'user_id' => $this->user->id,
        'status' => 'pending',
        'display_name' => 'Test Creator',
    ]);
});

test('apply for creator throws when profile already exists', function () {
    $this->service->applyForCreator($this->user->id, ['display_name' => 'One']);

    expect(fn () => $this->service->applyForCreator($this->user->id, ['display_name' => 'Two']))
        ->toThrow(RuntimeException::class, 'Creator profile already exists for user '.$this->user->id);
});

test('approve creator activates the profile', function () {
    $profile = $this->service->applyForCreator($this->user->id, ['display_name' => 'Test Creator']);

    $approved = $this->service->approveCreator($profile['id']);

    expect($approved['status'])->toBe('approved');
    expect($approved['is_active'])->toBeTrue();
    expect($approved['can_upload'])->toBeTrue();

    $this->assertDatabaseHas('growstream_creator_profiles', [
        'id' => $profile['id'],
        'status' => 'approved',
    ]);
});

test('reject creator sets status and reason', function () {
    $profile = $this->service->applyForCreator($this->user->id, ['display_name' => 'Test Creator']);

    $rejected = $this->service->rejectCreator($profile['id'], 'Incomplete information');

    expect($rejected['status'])->toBe('rejected');
    expect($rejected['rejected_reason'])->toBe('Incomplete information');
    expect($rejected['can_upload'])->toBeFalse();
});

test('approve and reject throw for missing profile', function () {
    expect(fn () => $this->service->approveCreator(99999))->toThrow(RuntimeException::class);
    expect(fn () => $this->service->rejectCreator(99999, 'reason'))->toThrow(RuntimeException::class);
});

test('accept agreement records acceptance', function () {
    $profile = $this->service->applyForCreator($this->user->id, ['display_name' => 'Test Creator']);

    $this->service->acceptAgreement($profile['id'], '1.0', '127.0.0.1', 'test-agent');

    $this->assertDatabaseHas('growstream_creator_agreements', [
        'creator_profile_id' => $profile['id'],
        'version' => '1.0',
        'accepted' => true,
        'ip_address' => '127.0.0.1',
    ]);

    expect($this->service->hasAcceptedAgreement($profile['id'], '1.0'))->toBeTrue();
    expect($this->service->hasAcceptedAgreement($profile['id'], '2.0'))->toBeFalse();
});

test('pending creators returns only pending profiles', function () {
    $pending = $this->service->applyForCreator($this->user->id, ['display_name' => 'Pending Creator']);
    $otherUser = User::factory()->create();
    CreatorProfile::create([
        'user_id' => $otherUser->id,
        'display_name' => 'Approved Creator',
        'status' => 'approved',
        'is_active' => true,
    ]);

    $pendingProfiles = $this->service->pendingCreators();

    expect($pendingProfiles)->toHaveCount(1);
    expect($pendingProfiles->first()->id)->toBe($pending['id']);
    expect($this->service->pendingCreatorCount())->toBe(1);
});
