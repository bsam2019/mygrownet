<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\GrowFinance\Entities;

use App\Domain\GrowFinance\Entities\Payment;
use App\Domain\GrowFinance\ValueObjects\PaymentMethod;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class PaymentTest extends TestCase
{
    #[Test]
    public function constructor_sets_properties()
    {
        $payment = new Payment(
            id: 1, businessId: 5, payableType: 'invoice', payableId: 10,
            paymentDate: null, amount: 500.0, paymentMethod: PaymentMethod::CASH,
            reference: 'REF-001', notes: null, createdAt: null, updatedAt: null,
        );

        $this->assertSame(1, $payment->id);
        $this->assertSame(500.0, $payment->amount);
        $this->assertSame(PaymentMethod::CASH, $payment->paymentMethod);
    }

    #[Test]
    public function reconstitute_restores_from_array()
    {
        $payment = Payment::reconstitute([
            'id' => 1, 'business_id' => 5, 'payable_type' => 'invoice',
            'payable_id' => 10, 'amount' => 1000.0, 'payment_method' => 'bank',
        ]);

        $this->assertSame('invoice', $payment->payableType);
        $this->assertSame(PaymentMethod::BANK, $payment->paymentMethod);
    }

    #[Test]
    public function to_array_returns_all_fields()
    {
        $payment = new Payment(id: 1, businessId: 5, payableType: 'invoice', payableId: 10, paymentDate: null, amount: 500.0, paymentMethod: PaymentMethod::CASH, reference: null, notes: null, createdAt: null, updatedAt: null);
        $array = $payment->toArray();

        $this->assertSame('invoice', $array['payable_type']);
        $this->assertSame('cash', $array['payment_method']);
    }
}
