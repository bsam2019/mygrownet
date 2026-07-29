<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\GrowFinance\Entities;

use App\Domain\GrowFinance\Entities\Customer;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class CustomerTest extends TestCase
{
    #[Test]
    public function constructor_sets_properties()
    {
        $customer = new Customer(id: 1, businessId: 5, name: 'Acme Corp', email: 'acme@example.com');

        $this->assertSame(1, $customer->id);
        $this->assertSame(5, $customer->businessId);
        $this->assertSame('Acme Corp', $customer->name);
        $this->assertSame('acme@example.com', $customer->email);
    }

    #[Test]
    public function defaults_are_applied()
    {
        $customer = new Customer(id: null, businessId: 5, name: 'Test');
        $this->assertTrue($customer->isActive);
        $this->assertSame(0.0, $customer->outstandingBalance);
    }

    #[Test]
    public function reconstitute_restores_from_array()
    {
        $customer = Customer::reconstitute([
            'id' => 1, 'business_id' => 5, 'name' => 'Acme',
            'tax_number' => 'TAX123', 'credit_limit' => 10000.0,
        ]);

        $this->assertSame('Acme', $customer->name);
        $this->assertSame('TAX123', $customer->taxNumber);
        $this->assertSame(10000.0, $customer->creditLimit);
    }

    #[Test]
    public function to_array_returns_all_fields()
    {
        $customer = new Customer(id: 1, businessId: 5, name: 'Acme', email: 'a@b.com');
        $array = $customer->toArray();

        $this->assertSame('Acme', $array['name']);
        $this->assertSame('a@b.com', $array['email']);
    }
}
