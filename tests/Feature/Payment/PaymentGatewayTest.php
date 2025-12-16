<?php

declare(strict_types=1);

use App\Domain\Payment\DTOs\CollectionRequest;
use App\Domain\Payment\DTOs\DisbursementRequest;
use App\Domain\Payment\Enums\TransactionStatus;
use App\Domain\Payment\Gateways\MoneyUnifyGateway;
use App\Domain\Payment\Gateways\PawapayGateway;
use App\Domain\Payment\Services\PaymentService;
use Illuminate\Support\Facades\Http;

describe('PaymentService', function () {
    it('can get available gateways', function () {
        $service = new PaymentService();
        $gateways = $service->getAvailableGateways();

        expect($gateways)->toBeArray()
            ->and($gateways)->toHaveKey('moneyunify')
            ->and($gateways)->toHaveKey('pawapay');
    });

    it('can get default gateway', function () {
        $service = new PaymentService();
        $default = $service->getDefaultGateway();

        expect($default)->toBe('moneyunify');
    });

    it('throws exception for invalid gateway', function () {
        $service = new PaymentService();
        $service->getGateway('invalid_gateway');
    })->throws(InvalidArgumentException::class);
});

describe('MoneyUnifyGateway', function () {
    beforeEach(function () {
        $this->gateway = new MoneyUnifyGateway();
    });

    it('has correct identifier', function () {
        expect($this->gateway->getIdentifier())->toBe('moneyunify');
    });

    it('has correct name', function () {
        expect($this->gateway->getName())->toBe('MoneyUnify');
    });

    it('supports Zambia', function () {
        expect($this->gateway->getSupportedCountries())->toContain('ZM');
    });

    it('supports ZMW currency', function () {
        expect($this->gateway->getSupportedCurrencies())->toContain('ZMW');
    });

    it('supports collections', function () {
        expect($this->gateway->supportsCollections())->toBeTrue();
    });

    it('does not support disbursements', function () {
        // MoneyUnify package only supports collections, not disbursements
        expect($this->gateway->supportsDisbursements())->toBeFalse();
    });

    it('returns failure for disbursement attempts', function () {
        $request = new DisbursementRequest(
            phoneNumber: '0971234567',
            amount: 50.00,
            currency: 'ZMW',
            provider: 'airtel',
            description: 'Test payout'
        );

        $response = $this->gateway->disburse($request);

        expect($response->success)->toBeFalse()
            ->and($response->status)->toBe(TransactionStatus::FAILED)
            ->and($response->message)->toContain('does not support');
    });
});


describe('PawapayGateway', function () {
    beforeEach(function () {
        $this->gateway = new PawapayGateway();
    });

    it('has correct identifier', function () {
        expect($this->gateway->getIdentifier())->toBe('pawapay');
    });

    it('has correct name', function () {
        expect($this->gateway->getName())->toBe('PawaPay');
    });

    it('supports multiple African countries', function () {
        $countries = $this->gateway->getSupportedCountries();
        
        expect($countries)->toContain('ZM')
            ->and($countries)->toContain('KE')
            ->and($countries)->toContain('TZ')
            ->and($countries)->toContain('UG')
            ->and($countries)->toContain('GH');
    });

    it('supports multiple currencies', function () {
        $currencies = $this->gateway->getSupportedCurrencies();
        
        expect($currencies)->toContain('ZMW')
            ->and($currencies)->toContain('KES')
            ->and($currencies)->toContain('USD');
    });

    it('supports collections', function () {
        expect($this->gateway->supportsCollections())->toBeTrue();
    });

    it('supports disbursements', function () {
        expect($this->gateway->supportsDisbursements())->toBeTrue();
    });

    it('can initiate collection with mocked response', function () {
        Http::fake([
            '*/deposits' => Http::response([
                'depositId' => 'PP123456',
                'status' => 'SUBMITTED',
            ], 200),
        ]);

        $request = new CollectionRequest(
            phoneNumber: '0971234567',
            amount: 100.00,
            currency: 'ZMW',
            provider: 'mtn',
            description: 'Test payment'
        );

        $response = $this->gateway->collect($request);

        expect($response->success)->toBeTrue()
            ->and($response->transactionId)->not->toBeEmpty()
            ->and($response->status)->toBe(TransactionStatus::PENDING);
    });

    it('handles collection failure', function () {
        Http::fake([
            '*/deposits' => Http::response([
                'message' => 'Invalid phone number',
            ], 400),
        ]);

        $request = new CollectionRequest(
            phoneNumber: 'invalid',
            amount: 100.00,
            currency: 'ZMW',
            provider: 'mtn'
        );

        $response = $this->gateway->collect($request);

        expect($response->success)->toBeFalse()
            ->and($response->status)->toBe(TransactionStatus::FAILED);
    });

    it('can initiate disbursement with mocked response', function () {
        Http::fake([
            '*/payouts' => Http::response([
                'payoutId' => 'PP789012',
                'status' => 'SUBMITTED',
            ], 200),
        ]);

        $request = new DisbursementRequest(
            phoneNumber: '0971234567',
            amount: 50.00,
            currency: 'ZMW',
            provider: 'airtel',
            description: 'Test payout'
        );

        $response = $this->gateway->disburse($request);

        expect($response->success)->toBeTrue()
            ->and($response->transactionId)->not->toBeEmpty()
            ->and($response->status)->toBe(TransactionStatus::PENDING);
    });

    it('can check collection status', function () {
        Http::fake([
            '*/deposits/*' => Http::response([
                'status' => 'COMPLETED',
            ], 200),
        ]);

        $status = $this->gateway->checkCollectionStatus('test-txn-123');

        expect($status)->toBe(TransactionStatus::COMPLETED);
    });

    it('can check disbursement status', function () {
        Http::fake([
            '*/payouts/*' => Http::response([
                'status' => 'ACCEPTED',
            ], 200),
        ]);

        $status = $this->gateway->checkDisbursementStatus('test-txn-456');

        expect($status)->toBe(TransactionStatus::COMPLETED);
    });

    it('maps provider to correspondent correctly', function () {
        // Test via reflection or by checking the request payload
        Http::fake([
            '*/deposits' => Http::response(['status' => 'SUBMITTED'], 200),
        ]);

        $request = new CollectionRequest(
            phoneNumber: '0971234567',
            amount: 100.00,
            currency: 'ZMW',
            provider: 'mtn'
        );

        $this->gateway->collect($request);

        Http::assertSent(function ($request) {
            $body = json_decode($request->body(), true);
            return $body['correspondent'] === 'MTN_MOMO_ZMB';
        });
    });
});

describe('TransactionStatus Enum', function () {
    it('has pending status', function () {
        expect(TransactionStatus::PENDING->value)->toBe('pending');
    });

    it('has completed status', function () {
        expect(TransactionStatus::COMPLETED->value)->toBe('completed');
    });

    it('has failed status', function () {
        expect(TransactionStatus::FAILED->value)->toBe('failed');
    });

    it('can check if pending', function () {
        expect(TransactionStatus::PENDING->isPending())->toBeTrue()
            ->and(TransactionStatus::PROCESSING->isPending())->toBeTrue()
            ->and(TransactionStatus::COMPLETED->isPending())->toBeFalse();
    });

    it('can check if completed', function () {
        expect(TransactionStatus::COMPLETED->isCompleted())->toBeTrue()
            ->and(TransactionStatus::PENDING->isCompleted())->toBeFalse();
    });

    it('can check if failed', function () {
        expect(TransactionStatus::FAILED->isFailed())->toBeTrue()
            ->and(TransactionStatus::CANCELLED->isFailed())->toBeTrue()
            ->and(TransactionStatus::COMPLETED->isFailed())->toBeFalse();
    });
});
