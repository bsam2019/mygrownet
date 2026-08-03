<?php

use App\Domain\GrowStream\Infrastructure\Persistence\Eloquent\CreatorEarning;
use App\Domain\GrowStream\Infrastructure\Persistence\Eloquent\CreatorProfile;
use App\Domain\GrowStream\Services\PayoutService;
use App\Models\User;

beforeEach(function () {
    $this->service = app(PayoutService::class);
    $this->creator = CreatorProfile::create([
        'user_id' => User::factory()->create()->id,
        'display_name' => 'Creator A',
        'status' => 'approved',
        'is_active' => true,
    ]);
});

function addEarnings(int $creatorId, float $amount, string $periodStart, string $periodEnd): CreatorEarning
{
    return CreatorEarning::create([
        'creator_id' => $creatorId,
        'period_start' => $periodStart,
        'period_end' => $periodEnd,
        'premium_watch_seconds' => 100,
        'pool_amount' => $amount,
        'share_percentage' => 50,
        'earned_amount' => $amount,
        'status' => 'pending',
    ]);
}

test('creates payout when earnings meet the minimum threshold', function () {
    config()->set('growstream.creator.minimum_payout', 100);
    addEarnings($this->creator->id, 250.0, '2026-07-01', '2026-07-31');

    $payouts = $this->service->processEligible();

    expect($payouts)->toHaveCount(1);
    expect($payouts[0]['creator_id'])->toBe($this->creator->id);
    expect((float) $payouts[0]['amount'])->toBe(250.0);
    expect($payouts[0]['status'])->toBe('pending');
    expect($payouts[0]['reference'])->not->toBeNull();

    // earnings marked paid
    $this->assertDatabaseHas('growstream_creator_earnings', [
        'creator_id' => $this->creator->id,
        'status' => 'paid',
    ]);
});

test('skips creators below the minimum threshold', function () {
    config()->set('growstream.creator.minimum_payout', 100);
    addEarnings($this->creator->id, 50.0, '2026-07-01', '2026-07-31');

    $payouts = $this->service->processEligible();

    expect($payouts)->toBeEmpty();
    $this->assertDatabaseHas('growstream_creator_earnings', [
        'creator_id' => $this->creator->id,
        'status' => 'pending',
    ]);
    $this->assertDatabaseCount('growstream_creator_payouts', 0);
});

test('aggregates multiple earning periods into one payout', function () {
    config()->set('growstream.creator.minimum_payout', 100);
    addEarnings($this->creator->id, 80.0, '2026-06-01', '2026-06-30');
    addEarnings($this->creator->id, 120.0, '2026-07-01', '2026-07-31');

    $payouts = $this->service->processEligible();

    expect($payouts)->toHaveCount(1);
    expect((float) $payouts[0]['amount'])->toBe(200.0);
});

test('does not create duplicate payouts for already-paid earnings', function () {
    config()->set('growstream.creator.minimum_payout', 100);
    addEarnings($this->creator->id, 250.0, '2026-07-01', '2026-07-31');

    $this->service->processEligible();
    $payouts = $this->service->processEligible();

    expect($payouts)->toBeEmpty();
    $this->assertDatabaseCount('growstream_creator_payouts', 1);
});

test('mark completed updates payout status', function () {
    config()->set('growstream.creator.minimum_payout', 100);
    addEarnings($this->creator->id, 250.0, '2026-07-01', '2026-07-31');

    $payouts = $this->service->processEligible();
    $id = $payouts[0]['id'];

    $result = $this->service->markCompleted($id, 'MTN-1234');

    expect($result['status'])->toBe('completed');
    expect($result['reference'])->toBe('MTN-1234');
    expect($result['paid_at'])->not->toBeNull();
});

test('mark failed records the reason', function () {
    config()->set('growstream.creator.minimum_payout', 100);
    addEarnings($this->creator->id, 250.0, '2026-07-01', '2026-07-31');

    $payouts = $this->service->processEligible();
    $id = $payouts[0]['id'];

    $result = $this->service->markFailed($id, 'Insufficient mobile money balance');

    expect($result['status'])->toBe('failed');
    expect($result['notes'])->toBe('Insufficient mobile money balance');
});
