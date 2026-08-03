<?php

use App\Domain\GrowStream\Infrastructure\Persistence\Eloquent\CreatorProfile;
use App\Domain\GrowStream\Services\SponsorshipService;
use App\Models\User;

beforeEach(function () {
    $this->service = app(SponsorshipService::class);
    $this->creator = CreatorProfile::create([
        'user_id' => User::factory()->create()->id,
        'display_name' => 'Creator',
        'status' => 'approved',
        'is_active' => true,
    ]);
});

test('creator applies for a sponsorship grant', function () {
    $grant = $this->service->apply($this->creator->id, 'Comedy Special', 'A live comedy special', 5000.0, ['Script', 'Production']);

    expect($grant['creator_id'])->toBe($this->creator->id);
    expect($grant['title'])->toBe('Comedy Special');
    expect((float) $grant['amount'])->toBe(5000.0);
    expect($grant['status'])->toBe('submitted');

    $this->assertDatabaseHas('growstream_sponsorship_grants', [
        'creator_id' => $this->creator->id,
        'status' => 'submitted',
    ]);
});

test('grant application rejects non-positive amounts', function () {
    expect(fn () => $this->service->apply($this->creator->id, 'Title', 'Desc', 0))
        ->toThrow(InvalidArgumentException::class);
});

test('approve moves a grant to approved', function () {
    $grant = $this->service->apply($this->creator->id, 'Title', 'Desc', 1000.0);

    $approved = $this->service->approve($grant['id'], 1);

    expect($approved['status'])->toBe('approved');
    expect($approved['allocated_at'])->not->toBeNull();
});

test('reject records the reason', function () {
    $grant = $this->service->apply($this->creator->id, 'Title', 'Desc', 1000.0);

    $rejected = $this->service->reject($grant['id'], 'Not aligned with platform goals', 1);

    expect($rejected['status'])->toBe('rejected');
    expect($rejected['rejection_reason'])->toBe('Not aligned with platform goals');
});

test('disburse adds the grant amount to the creator pending payout', function () {
    $grant = $this->service->apply($this->creator->id, 'Title', 'Desc', 2500.0);
    $this->service->approve($grant['id'], 1);

    $disbursed = $this->service->disburse($grant['id']);

    expect($disbursed['status'])->toBe('disbursed');
    expect($disbursed['disbursed_at'])->not->toBeNull();

    $this->assertDatabaseHas('growstream_creator_profiles', [
        'id' => $this->creator->id,
        'pending_payout' => 2500.00,
    ]);
});

test('disburse only allows approved grants', function () {
    $grant = $this->service->apply($this->creator->id, 'Title', 'Desc', 1000.0);

    expect(fn () => $this->service->disburse($grant['id']))
        ->toThrow(RuntimeException::class, 'Only approved grants can be disbursed');
});

test('complete marks the grant finished', function () {
    $grant = $this->service->apply($this->creator->id, 'Title', 'Desc', 1000.0);
    $this->service->approve($grant['id'], 1);
    $this->service->disburse($grant['id']);

    $completed = $this->service->complete($grant['id']);

    expect($completed['status'])->toBe('completed');
});

test('for creator lists only that creators grants', function () {
    $otherCreator = CreatorProfile::create([
        'user_id' => User::factory()->create()->id,
        'display_name' => 'Other',
        'status' => 'approved',
        'is_active' => true,
    ]);

    $this->service->apply($this->creator->id, 'Mine', 'Desc', 1000.0);
    $this->service->apply($otherCreator->id, 'Other', 'Desc', 1000.0);

    $grants = $this->service->forCreator($this->creator->id);
    expect($grants)->toHaveCount(1);
    expect($grants->first()->title)->toBe('Mine');
});

test('totals aggregate approved and disbursed amounts', function () {
    $grant = $this->service->apply($this->creator->id, 'Title', 'Desc', 3000.0);
    $this->service->approve($grant['id'], 1);
    $this->service->disburse($grant['id']);

    expect($this->service->totalApproved())->toBe(3000.0);
    expect($this->service->totalDisbursed())->toBe(3000.0);
});
