<?php

use App\Infrastructure\Persistence\Eloquent\VentureBuilder\VentureModel;
use App\Infrastructure\Persistence\Eloquent\VentureBuilder\VentureDividendModel;
use App\Infrastructure\Persistence\Eloquent\VentureBuilder\VentureShareholderModel;
use App\Services\VentureBuilder\VentureDividendService;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->service = new VentureDividendService();
});

describe('declareDividend', function () {
    it('declares dividends pro-rata to active shareholders', function () {
        $venture = VentureModel::factory()->active()->create();
        $shareholderA = VentureShareholderModel::factory()->create([
            'venture_id' => $venture->id,
            'equity_percentage' => 30.0,
            'status' => 'active',
        ]);
        $shareholderB = VentureShareholderModel::factory()->create([
            'venture_id' => $venture->id,
            'equity_percentage' => 70.0,
            'status' => 'active',
        ]);

        $dividends = $this->service->declareDividend($venture, 'Q1-2026', 10000);

        expect($dividends)->toHaveCount(2);
        expect($dividends[0]->amount)->toBe(3000.0);
        expect($dividends[0]->status)->toBe('declared');
        expect($dividends[1]->amount)->toBe(7000.0);
    });

    it('rejects dividend for non-active ventures', function () {
        $venture = VentureModel::factory()->create(['status' => 'funding']);

        expect(fn () => $this->service->declareDividend($venture, 'Q1-2026', 10000))
            ->toThrow(\InvalidArgumentException::class, 'Only active ventures');
    });

    it('rejects dividend when no active shareholders', function () {
        $venture = VentureModel::factory()->active()->create();

        expect(fn () => $this->service->declareDividend($venture, 'Q1-2026', 10000))
            ->toThrow(\InvalidArgumentException::class, 'No active shareholders');
    });

    it('logs audit event on declaration', function () {
        $venture = VentureModel::factory()->active()->create();
        VentureShareholderModel::factory()->create([
            'venture_id' => $venture->id,
            'status' => 'active',
        ]);

        $this->service->declareDividend($venture, 'Q1-2026', 10000);

        expect(AuditLog::where('event_type', 'venture_dividend_declared')->count())->toBe(1);
    });
});

describe('processDividend', function () {
    it('processes a declared dividend and credits wallet', function () {
        $user = User::factory()->create(['wallet_balance' => 0]);
        $shareholder = VentureShareholderModel::factory()->create([
            'user_id' => $user->id,
            'total_dividends_received' => 0,
        ]);
        $dividend = VentureDividendModel::factory()->create([
            'shareholder_id' => $shareholder->id,
            'status' => 'declared',
            'amount' => 5000,
        ]);

        $result = $this->service->processDividend($dividend);

        expect($result->status)->toBe('paid');
        expect($result->payment_method)->toBe('wallet');
        expect($user->fresh()->wallet_balance)->toBe(5000.0);
        expect($shareholder->fresh()->total_dividends_received)->toBe(5000.0);
    });

    it('rejects processing a non-declared dividend', function () {
        $dividend = VentureDividendModel::factory()->paid()->create();

        expect(fn () => $this->service->processDividend($dividend))
            ->toThrow(\InvalidArgumentException::class);
    });

    it('logs audit event on payment', function () {
        $user = User::factory()->create();
        $shareholder = VentureShareholderModel::factory()->create(['user_id' => $user->id]);
        $dividend = VentureDividendModel::factory()->create([
            'shareholder_id' => $shareholder->id,
            'status' => 'declared',
        ]);

        $this->service->processDividend($dividend);

        expect(AuditLog::where('event_type', 'venture_dividend_paid')->count())->toBe(1);
    });
});
