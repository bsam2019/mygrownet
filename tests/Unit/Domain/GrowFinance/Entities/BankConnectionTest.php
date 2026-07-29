<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\GrowFinance\Entities;

use App\Domain\GrowFinance\Entities\BankConnection;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class BankConnectionTest extends TestCase
{
    #[Test]
    public function constructor_sets_properties()
    {
        $conn = new BankConnection(
            id: 1, businessId: 5, bankName: 'Test Bank',
            accountName: 'Main', accountNumber: '1234',
            connectionType: 'api', status: 'active'
        );

        $this->assertSame('Test Bank', $conn->bankName);
        $this->assertSame('active', $conn->status);
    }

    #[Test]
    public function reconstitute_restores_from_array()
    {
        $conn = BankConnection::reconstitute([
            'id' => 1, 'business_id' => 5, 'bank_name' => 'Bank',
            'account_name' => 'Acc', 'account_number' => '123',
            'connection_type' => 'csv', 'status' => 'active',
        ]);

        $this->assertSame('Bank', $conn->bankName);
        $this->assertSame('csv', $conn->connectionType);
    }

    #[Test]
    public function to_array_returns_all_fields()
    {
        $conn = new BankConnection(
            id: 1, businessId: 5, bankName: 'B', accountName: 'A',
            accountNumber: '1', connectionType: 'api', status: 'active'
        );
        $array = $conn->toArray();

        $this->assertSame('B', $array['bank_name']);
        $this->assertSame('api', $array['connection_type']);
    }
}
