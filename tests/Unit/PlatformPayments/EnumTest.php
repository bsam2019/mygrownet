<?php

namespace Tests\Unit\PlatformPayments;

use App\Domain\PlatformPayments\Entities\PaymentMethod;
use App\Domain\PlatformPayments\Entities\TransactionStatus;
use PHPUnit\Framework\TestCase;

class EnumTest extends TestCase
{
    public function test_transaction_status_values(): void
    {
        $this->assertEquals('initiated', TransactionStatus::Initiated->value);
        $this->assertEquals('pending', TransactionStatus::Pending->value);
        $this->assertEquals('completed', TransactionStatus::Completed->value);
        $this->assertEquals('failed', TransactionStatus::Failed->value);
        $this->assertEquals('refunded', TransactionStatus::Refunded->value);
    }

    public function test_payment_method_values(): void
    {
        $this->assertEquals('mtn_momo', PaymentMethod::MTNMoMo->value);
        $this->assertEquals('airtel_money', PaymentMethod::AirtelMoney->value);
        $this->assertEquals('card', PaymentMethod::Card->value);
        $this->assertEquals('bank_transfer', PaymentMethod::BankTransfer->value);
        $this->assertEquals('wallet', PaymentMethod::Wallet->value);
    }
}
