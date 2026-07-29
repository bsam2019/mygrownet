<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\GrowFinance\Entities;

use App\Domain\GrowFinance\Entities\BankAccount;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class BankAccountTest extends TestCase
{
    #[Test]
    public function constructor_sets_properties()
    {
        $account = new BankAccount(id: 1, businessId: 5, accountName: 'Main Account', accountNumber: '1234567890');

        $this->assertSame(1, $account->id);
        $this->assertSame('Main Account', $account->accountName);
        $this->assertSame('1234567890', $account->accountNumber);
    }

    #[Test]
    public function defaults_are_applied()
    {
        $account = new BankAccount(id: null, businessId: 5, accountName: 'Test', accountNumber: '0000');

        $this->assertTrue($account->isActive);
        $this->assertFalse($account->isDefault);
        $this->assertSame(0.0, $account->openingBalance);
        $this->assertSame(0.0, $account->currentBalance);
    }

    #[Test]
    public function reconstitute_restores_from_array()
    {
        $account = BankAccount::reconstitute([
            'id' => 1,
            'business_id' => 5,
            'account_name' => 'Savings',
            'account_number' => '987654',
            'bank_name' => 'Test Bank',
            'is_default' => true,
        ]);

        $this->assertSame('Savings', $account->accountName);
        $this->assertSame('Test Bank', $account->bankName);
        $this->assertTrue($account->isDefault);
    }

    #[Test]
    public function to_array_returns_all_fields()
    {
        $account = new BankAccount(id: 1, businessId: 5, accountName: 'Main', accountNumber: '0000', bankName: 'Bank');
        $array = $account->toArray();

        $this->assertSame('Main', $array['account_name']);
        $this->assertSame('Bank', $array['bank_name']);
    }
}
