<?php

use App\Domain\GrowStream\Infrastructure\Persistence\Eloquent\CreatorProfile;
use App\Domain\GrowStream\Services\CreatorSubscriptionService;
use App\Domain\GrowStream\Services\TipService;
use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->creator = CreatorProfile::create([
        'user_id' => User::factory()->create()->id,
        'display_name' => 'Creator',
        'status' => 'approved',
        'is_active' => true,
    ]);
});

test('subscribe creates an active fan subscription', function () {
    $service = app(CreatorSubscriptionService::class);
    $subscription = $service->subscribe($this->user->id, $this->creator->id, 15.0, 'REF-SUB-1');

    expect($subscription['user_id'])->toBe($this->user->id);
    expect($subscription['creator_id'])->toBe($this->creator->id);
    expect((float) $subscription['price_monthly'])->toBe(15.0);
    expect($subscription['status'])->toBe('active');
    expect($service->isSubscribed($this->user->id, $this->creator->id))->toBeTrue();
});

test('subscribing twice is idempotent', function () {
    $service = app(CreatorSubscriptionService::class);
    $service->subscribe($this->user->id, $this->creator->id, 15.0, 'REF-SUB-1');
    $second = $service->subscribe($this->user->id, $this->creator->id, 15.0, 'REF-SUB-2');

    expect($second['provider_reference'])->toBe('REF-SUB-1');
    $this->assertDatabaseCount('growstream_creator_subscriptions', 1);
});

test('cancel marks the subscription inactive', function () {
    $service = app(CreatorSubscriptionService::class);
    $service->subscribe($this->user->id, $this->creator->id, 15.0);

    $service->cancel($this->user->id, $this->creator->id);

    expect($service->isSubscribed($this->user->id, $this->creator->id))->toBeFalse();
    $this->assertDatabaseHas('growstream_creator_subscriptions', [
        'user_id' => $this->user->id,
        'creator_id' => $this->creator->id,
        'status' => 'cancelled',
    ]);
});

test('subscriber count reflects active subscriptions', function () {
    $service = app(CreatorSubscriptionService::class);
    $fanA = User::factory()->create();
    $fanB = User::factory()->create();

    $service->subscribe($fanA->id, $this->creator->id, 15.0);
    $service->subscribe($fanB->id, $this->creator->id, 15.0);
    $service->cancel($fanB->id, $this->creator->id);

    expect($service->subscriberCount($this->creator->id))->toBe(1);
});

test('tip records a completed donation', function () {
    $service = app(TipService::class);
    $tip = $service->send($this->user->id, $this->creator->id, 20.0, 'Great content!', true, 'REF-TIP-1');

    expect((float) $tip['amount'])->toBe(20.0);
    expect($tip['message'])->toBe('Great content!');
    expect($tip['is_anonymous'])->toBeTrue();
    expect($tip['status'])->toBe('completed');
});

test('tip rejects non-positive amounts', function () {
    $service = app(TipService::class);

    expect(fn () => $service->send($this->user->id, $this->creator->id, 0))
        ->toThrow(InvalidArgumentException::class);
});

test('tip totals aggregate completed tips', function () {
    $service = app(TipService::class);
    $otherUser = User::factory()->create();

    $service->send($this->user->id, $this->creator->id, 10.0);
    $service->send($this->user->id, $this->creator->id, 25.0);
    $service->send($otherUser->id, $this->creator->id, 15.0);

    expect($service->totalForCreator($this->creator->id))->toBe(50.0);
    expect($service->countForCreator($this->creator->id))->toBe(3);
});
