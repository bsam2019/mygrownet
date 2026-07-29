<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\GrowFinance\Entities;

use App\Domain\GrowFinance\Entities\Vendor;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class VendorTest extends TestCase
{
    #[Test]
    public function constructor_sets_properties()
    {
        $vendor = new Vendor(id: 1, businessId: 5, name: 'Supply Co', email: 'supply@example.com', phone: '123456789', address: '123 Main St', taxNumber: 'TAX001', paymentTerms: 'Net 30', outstandingBalance: 5000.0, isActive: true, notes: null, createdAt: null, updatedAt: null);

        $this->assertSame(1, $vendor->id);
        $this->assertSame('Supply Co', $vendor->name);
        $this->assertSame(5000.0, $vendor->outstandingBalance);
    }

    #[Test]
    public function defaults_are_applied()
    {
        $vendor = Vendor::reconstitute(['id' => 1, 'business_id' => 5, 'name' => 'Test']);
        $this->assertTrue($vendor->isActive);
        $this->assertSame(0.0, $vendor->outstandingBalance);
    }

    #[Test]
    public function reconstitute_restores_from_array()
    {
        $vendor = Vendor::reconstitute([
            'id' => 1, 'business_id' => 5, 'name' => 'Vendor Inc',
            'email' => 'v@vendor.com', 'outstanding_balance' => 2000.0,
        ]);

        $this->assertSame('Vendor Inc', $vendor->name);
        $this->assertSame(2000.0, $vendor->outstandingBalance);
    }

    #[Test]
    public function to_array_returns_all_fields()
    {
        $vendor = new Vendor(id: 1, businessId: 5, name: 'Vendor', email: null, phone: null, address: null, taxNumber: null, paymentTerms: null, outstandingBalance: 1000.0, isActive: true, notes: null, createdAt: null, updatedAt: null);
        $array = $vendor->toArray();

        $this->assertSame('Vendor', $array['name']);
        $this->assertSame(1000.0, $array['outstanding_balance']);
    }
}
