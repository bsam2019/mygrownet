<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\GrowFinance\Entities;

use App\Domain\GrowFinance\Entities\Account;
use App\Domain\GrowFinance\ValueObjects\AccountType;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class AccountTest extends TestCase
{
    #[Test]
    public function constructor_sets_all_properties()
    {
        $account = new Account(id: 1, businessId: 5, code: '1000', name: 'Cash', type: AccountType::ASSET);

        $this->assertSame(1, $account->id);
        $this->assertSame(5, $account->businessId);
        $this->assertSame('1000', $account->code);
        $this->assertSame('Cash', $account->name);
        $this->assertSame(AccountType::ASSET, $account->type);
    }

    #[Test]
    public function reconstitute_restores_from_array()
    {
        $account = Account::reconstitute([
            'id' => 1,
            'business_id' => 5,
            'code' => '1000',
            'name' => 'Cash',
            'type' => 'asset',
        ]);

        $this->assertSame(1, $account->id);
        $this->assertSame(5, $account->businessId);
        $this->assertSame('Cash', $account->name);
    }

    #[Test]
    public function to_array_returns_all_properties()
    {
        $account = new Account(id: 1, businessId: 5, code: '1000', name: 'Cash', type: AccountType::ASSET);
        $array = $account->toArray();

        $this->assertSame(1, $array['id']);
        $this->assertSame(5, $array['business_id']);
        $this->assertSame('1000', $array['code']);
        $this->assertSame('asset', $array['type']);
    }

    #[Test]
    public function is_contra_account_returns_false_for_normal_asset()
    {
        $account = new Account(id: 1, businessId: 5, code: '1000', name: 'Cash', type: AccountType::ASSET, normalBalance: 'debit');
        $this->assertFalse($account->isContraAccount());
    }

    #[Test]
    public function is_contra_account_returns_true_when_normal_balance_opposes_type()
    {
        $account = new Account(id: 1, businessId: 5, code: '2000', name: 'Acc Depr', type: AccountType::ASSET, normalBalance: 'credit');
        $this->assertTrue($account->isContraAccount());
    }

    #[Test]
    public function get_balance_returns_negative_for_contra_account()
    {
        $account = new Account(id: 1, businessId: 5, code: '2000', name: 'Acc Depr', type: AccountType::ASSET, normalBalance: 'credit', currentBalance: 500.0);
        $this->assertSame(-500.0, $account->getBalance());
    }

    #[Test]
    public function get_balance_returns_positive_for_normal_account()
    {
        $account = new Account(id: 1, businessId: 5, code: '1000', name: 'Cash', type: AccountType::ASSET, normalBalance: 'debit', currentBalance: 1000.0);
        $this->assertSame(1000.0, $account->getBalance());
    }

    #[Test]
    public function set_children_and_parent_work()
    {
        $parent = new Account(id: 1, businessId: 5, code: '1000', name: 'Current Assets', type: AccountType::ASSET);
        $child = new Account(id: 2, businessId: 5, code: '1010', name: 'Cash', type: AccountType::ASSET);

        $parent->setChildren([$child]);
        $child->setParentAccount($parent);

        $this->assertSame([$child], $parent->children());
        $this->assertSame($parent, $child->parent());
    }
}
