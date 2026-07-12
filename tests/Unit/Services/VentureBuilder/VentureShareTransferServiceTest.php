<?php

use App\Infrastructure\Persistence\Eloquent\VentureBuilder\VentureModel;
use App\Infrastructure\Persistence\Eloquent\VentureBuilder\VentureShareholderModel;
use App\Infrastructure\Persistence\Eloquent\VentureBuilder\VentureShareTransferModel;
use App\Services\VentureBuilder\VentureShareTransferService;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->service = new VentureShareTransferService();
    $this->user = User::factory()->create();
    $this->otherUser = User::factory()->create();
    Auth::login($this->user);
});

describe('requestTransfer', function () {
    it('creates a pending transfer request', function () {
        $venture = VentureModel::factory()->funded()->create();
        VentureShareholderModel::factory()->create([
            'venture_id' => $venture->id,
            'user_id' => $this->user->id,
            'shares_owned' => 500,
            'status' => 'active',
        ]);

        $transfer = $this->service->requestTransfer($this->user, $venture, $this->otherUser, 100, 150);

        expect($transfer->status)->toBe('pending');
        expect($transfer->shares)->toBe(100.0);
        expect($transfer->total_value)->toBe(15000.0);
        expect($transfer->from_user_id)->toBe($this->user->id);
        expect($transfer->to_user_id)->toBe($this->otherUser->id);
    });

    it('rejects transfer when not an active shareholder', function () {
        $venture = VentureModel::factory()->funded()->create();

        expect(fn () => $this->service->requestTransfer($this->user, $venture, $this->otherUser, 100))
            ->toThrow(\InvalidArgumentException::class, 'not an active shareholder');
    });

    it('rejects transfer when shares exceed owned amount', function () {
        $venture = VentureModel::factory()->funded()->create();
        VentureShareholderModel::factory()->create([
            'venture_id' => $venture->id,
            'user_id' => $this->user->id,
            'shares_owned' => 50,
            'status' => 'active',
        ]);

        expect(fn () => $this->service->requestTransfer($this->user, $venture, $this->otherUser, 100))
            ->toThrow(\InvalidArgumentException::class, 'Insufficient shares');
    });

    it('logs audit event on request', function () {
        $venture = VentureModel::factory()->funded()->create();
        VentureShareholderModel::factory()->create([
            'venture_id' => $venture->id,
            'user_id' => $this->user->id,
            'shares_owned' => 500,
            'status' => 'active',
        ]);

        $this->service->requestTransfer($this->user, $venture, $this->otherUser, 100);

        expect(AuditLog::where('event_type', 'venture_share_transfer_requested')->count())->toBe(1);
    });
});

describe('approveTransfer', function () {
    it('approves a transfer and updates cap table', function () {
        $venture = VentureModel::factory()->funded()->create();
        $shareholder = VentureShareholderModel::factory()->create([
            'venture_id' => $venture->id,
            'user_id' => $this->user->id,
            'shares_owned' => 500,
            'equity_percentage' => 100.0,
            'status' => 'active',
        ]);
        $transfer = VentureShareTransferModel::factory()->create([
            'venture_id' => $venture->id,
            'from_user_id' => $this->user->id,
            'to_user_id' => $this->otherUser->id,
            'shares' => 200,
            'status' => 'pending',
        ]);

        $result = $this->service->approveTransfer($transfer);

        expect($result->status)->toBe('approved');
        expect($shareholder->fresh()->shares_owned)->toBe(300.0);
    });

    it('rejects approving a non-pending transfer', function () {
        $transfer = VentureShareTransferModel::factory()->completed()->create();

        expect(fn () => $this->service->approveTransfer($transfer))
            ->toThrow(\InvalidArgumentException::class);
    });

    it('logs audit event on approval', function () {
        $venture = VentureModel::factory()->funded()->create();
        VentureShareholderModel::factory()->create([
            'venture_id' => $venture->id,
            'user_id' => $this->user->id,
            'shares_owned' => 500,
            'status' => 'active',
        ]);
        $transfer = VentureShareTransferModel::factory()->create([
            'venture_id' => $venture->id,
            'from_user_id' => $this->user->id,
            'status' => 'pending',
        ]);

        $this->service->approveTransfer($transfer);

        expect(AuditLog::where('event_type', 'venture_share_transfer_completed')->count())->toBe(1);
    });
});

describe('rejectTransfer', function () {
    it('rejects a pending transfer', function () {
        $transfer = VentureShareTransferModel::factory()->create(['status' => 'pending']);

        $result = $this->service->rejectTransfer($transfer, 'Not enough shares');

        expect($result->status)->toBe('rejected');
        expect($result->admin_notes)->toBe('Not enough shares');
    });

    it('rejects rejecting a non-pending transfer', function () {
        $transfer = VentureShareTransferModel::factory()->completed()->create();

        expect(fn () => $this->service->rejectTransfer($transfer))
            ->toThrow(\InvalidArgumentException::class);
    });
});
