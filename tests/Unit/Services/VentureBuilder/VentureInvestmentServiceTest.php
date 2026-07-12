<?php

use App\Infrastructure\Persistence\Eloquent\VentureBuilder\VentureModel;
use App\Infrastructure\Persistence\Eloquent\VentureBuilder\VentureInvestmentModel;
use App\Infrastructure\Persistence\Eloquent\VentureBuilder\VentureShareholderModel;
use App\Services\VentureBuilder\VentureInvestmentService;
use App\Services\VentureBuilder\VentureService;
use App\Services\VentureBuilder\VentureLockInService;
use App\Services\ReceiptService;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->receiptService = Mockery::mock(ReceiptService::class);
    $this->receiptService->shouldReceive('createReceipt')->byDefault();

    $this->service = new VentureInvestmentService(
        new VentureService(),
        new VentureLockInService(),
        $this->receiptService,
    );

    $this->user = User::factory()->create(['wallet_balance' => 100000]);
});

describe('processWalletInvestment', function () {
    it('processes a wallet investment successfully', function () {
        $venture = VentureModel::factory()->funding()->create([
            'funding_target' => 100000,
            'total_raised' => 0,
            'minimum_investment' => 1000,
            'share_price' => 100,
        ]);

        $investment = $this->service->processWalletInvestment($this->user, $venture, 10000);

        expect($investment->status)->toBe('confirmed');
        expect($investment->amount)->toBe(10000.0);
        expect($investment->shares_allocated)->toBe(100.0);
        expect($investment->payment_method)->toBe('wallet');
        expect($this->user->fresh()->wallet_balance)->toBe(90000.0);
        expect($venture->fresh()->total_raised)->toBe(10000.0);
        expect($venture->fresh()->investor_count)->toBe(1);
    });

    it('rejects investment when wallet balance is insufficient', function () {
        $venture = VentureModel::factory()->funding()->create([
            'minimum_investment' => 1000,
        ]);
        $poorUser = User::factory()->create(['wallet_balance' => 100]);

        expect(fn () => $this->service->processWalletInvestment($poorUser, $venture, 5000))
            ->toThrow(\InvalidArgumentException::class, 'Insufficient');
    });

    it('rejects investment when venture is not funding', function () {
        $venture = VentureModel::factory()->create(['status' => 'draft']);

        expect(fn () => $this->service->processWalletInvestment($this->user, $venture, 10000))
            ->toThrow(\InvalidArgumentException::class, 'not currently accepting');
    });

    it('rejects investment exceeding remaining funding', function () {
        $venture = VentureModel::factory()->funding()->create([
            'funding_target' => 50000,
            'total_raised' => 45000,
            'minimum_investment' => 1000,
        ]);

        expect(fn () => $this->service->processWalletInvestment($this->user, $venture, 10000))
            ->toThrow(\InvalidArgumentException::class, 'exceeds remaining');
    });

    it('rejects investment exceeding maximum', function () {
        $venture = VentureModel::factory()->funding()->create([
            'maximum_investment' => 5000,
            'minimum_investment' => 1000,
        ]);

        expect(fn () => $this->service->processWalletInvestment($this->user, $venture, 10000))
            ->toThrow(\InvalidArgumentException::class, 'exceeds maximum');
    });

    it('rejects investment below minimum', function () {
        $venture = VentureModel::factory()->funding()->create([
            'minimum_investment' => 5000,
        ]);

        expect(fn () => $this->service->processWalletInvestment($this->user, $venture, 1000))
            ->toThrow(\InvalidArgumentException::class, 'below the minimum');
    });

    it('creates a receipt', function () {
        $venture = VentureModel::factory()->funding()->create([
            'minimum_investment' => 1000,
        ]);
        $this->receiptService->shouldReceive('createReceipt')->once();

        $this->service->processWalletInvestment($this->user, $venture, 5000);
    });

    it('logs audit event', function () {
        $venture = VentureModel::factory()->funding()->create([
            'minimum_investment' => 1000,
        ]);

        $this->service->processWalletInvestment($this->user, $venture, 5000);

        expect(AuditLog::where('event_type', 'venture_investment_confirmed')->count())->toBe(1);
    });

    it('auto-completes funding when target is reached', function () {
        $venture = VentureModel::factory()->funding()->create([
            'funding_target' => 50000,
            'total_raised' => 40000,
            'minimum_investment' => 1000,
        ]);

        $this->service->processWalletInvestment($this->user, $venture, 10000);

        expect($venture->fresh()->status)->toBe('funded');
    });
});

describe('initiateMobileMoneyInvestment', function () {
    it('creates a pending mobile money investment', function () {
        $venture = VentureModel::factory()->funding()->create([
            'minimum_investment' => 1000,
        ]);

        $investment = $this->service->initiateMobileMoneyInvestment($this->user, $venture, 5000);

        expect($investment->status)->toBe('pending');
        expect($investment->payment_method)->toBe('mobile_money');
        expect($investment->amount)->toBe(5000.0);
        expect($venture->fresh()->total_raised)->toBe(0.0);
    });

    it('rejects when venture is not funding', function () {
        $venture = VentureModel::factory()->create(['status' => 'draft']);

        expect(fn () => $this->service->initiateMobileMoneyInvestment($this->user, $venture, 5000))
            ->toThrow(\InvalidArgumentException::class);
    });
});

describe('confirmInvestment', function () {
    it('confirms a pending investment', function () {
        $venture = VentureModel::factory()->funding()->create([
            'total_raised' => 0,
            'investor_count' => 0,
        ]);
        $investment = VentureInvestmentModel::factory()->create([
            'venture_id' => $venture->id,
            'user_id' => $this->user->id,
            'amount' => 10000,
            'status' => 'pending',
        ]);

        $result = $this->service->confirmInvestment($investment);

        expect($result->status)->toBe('confirmed');
        expect($result->payment_confirmed_at)->not->toBeNull();
        expect($venture->fresh()->total_raised)->toBe(10000.0);
        expect($venture->fresh()->investor_count)->toBe(1);
    });

    it('rejects confirming a confirmed investment', function () {
        $investment = VentureInvestmentModel::factory()->confirmed()->create();

        expect(fn () => $this->service->confirmInvestment($investment))
            ->toThrow(\InvalidArgumentException::class);
    });
});

describe('refundInvestment', function () {
    it('refunds a pending investment', function () {
        $investment = VentureInvestmentModel::factory()->create([
            'user_id' => $this->user->id,
            'amount' => 10000,
            'status' => 'pending',
        ]);

        $result = $this->service->refundInvestment($investment);

        expect($result->status)->toBe('refunded');
    });

    it('refunds a confirmed investment and restores wallet', function () {
        $initialBalance = $this->user->wallet_balance;
        $venture = VentureModel::factory()->funding()->create([
            'total_raised' => 10000,
            'investor_count' => 1,
        ]);
        $investment = VentureInvestmentModel::factory()->confirmed()->create([
            'user_id' => $this->user->id,
            'venture_id' => $venture->id,
            'amount' => 10000,
            'payment_confirmed_at' => now()->subMonths(13),
        ]);

        $result = $this->service->refundInvestment($investment);

        expect($result->status)->toBe('refunded');
        expect($this->user->fresh()->wallet_balance)->toBe((float) $initialBalance + 10000);
        expect($venture->fresh()->total_raised)->toBe(0.0);
    });

    it('rejects refund if within lock-in period', function () {
        $investment = VentureInvestmentModel::factory()->confirmed()->create([
            'user_id' => $this->user->id,
            'payment_confirmed_at' => now()->subMonths(1),
        ]);

        expect(fn () => $this->service->refundInvestment($investment))
            ->toThrow(\RuntimeException::class, 'lock-in');
    });
});

describe('registerShareholder', function () {
    it('registers a shareholder for a confirmed investment', function () {
        $venture = VentureModel::factory()->funding()->create([
            'total_raised' => 50000,
        ]);
        $investment = VentureInvestmentModel::factory()->confirmed()->create([
            'venture_id' => $venture->id,
            'user_id' => $this->user->id,
            'amount' => 10000,
            'shares_allocated' => 100,
        ]);

        $shareholder = $this->service->registerShareholder($investment);

        expect($shareholder)->toBeInstanceOf(VentureShareholderModel::class);
        expect($shareholder->venture_id)->toBe($venture->id);
        expect($shareholder->user_id)->toBe($this->user->id);
        expect($shareholder->status)->toBe('active');
        expect($shareholder->equity_percentage)->toBe(20.0);
    });

    it('rejects shareholder registration for pending investment', function () {
        $investment = VentureInvestmentModel::factory()->create(['status' => 'pending']);

        expect(fn () => $this->service->registerShareholder($investment))
            ->toThrow(\InvalidArgumentException::class);
    });
});
