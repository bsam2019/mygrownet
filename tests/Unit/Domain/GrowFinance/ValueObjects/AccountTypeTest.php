<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\GrowFinance\ValueObjects;

use App\Domain\GrowFinance\ValueObjects\AccountType;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class AccountTypeTest extends TestCase
{
    #[Test]
    public function all_cases_have_correct_values()
    {
        $this->assertSame('asset', AccountType::ASSET->value);
        $this->assertSame('liability', AccountType::LIABILITY->value);
        $this->assertSame('equity', AccountType::EQUITY->value);
        $this->assertSame('income', AccountType::INCOME->value);
        $this->assertSame('expense', AccountType::EXPENSE->value);
    }

    #[Test]
    public function from_returns_correct_case()
    {
        $this->assertSame(AccountType::ASSET, AccountType::from('asset'));
        $this->assertSame(AccountType::LIABILITY, AccountType::from('liability'));
        $this->assertSame(AccountType::EQUITY, AccountType::from('equity'));
        $this->assertSame(AccountType::INCOME, AccountType::from('income'));
        $this->assertSame(AccountType::EXPENSE, AccountType::from('expense'));
    }

    #[Test]
    public function invalid_value_throws_value_error()
    {
        $this->expectException(\ValueError::class);
        AccountType::from('invalid');
    }

    #[Test]
    public function label_returns_correct_string()
    {
        $this->assertSame('Asset', AccountType::ASSET->label());
        $this->assertSame('Liability', AccountType::LIABILITY->label());
        $this->assertSame('Equity', AccountType::EQUITY->label());
        $this->assertSame('Income', AccountType::INCOME->label());
        $this->assertSame('Expense', AccountType::EXPENSE->label());
    }

    #[Test]
    public function color_returns_correct_string()
    {
        $this->assertSame('blue', AccountType::ASSET->color());
        $this->assertSame('red', AccountType::LIABILITY->color());
        $this->assertSame('purple', AccountType::EQUITY->color());
        $this->assertSame('emerald', AccountType::INCOME->color());
        $this->assertSame('amber', AccountType::EXPENSE->color());
    }

    #[Test]
    public function is_debit_normal_returns_correctly()
    {
        $this->assertTrue(AccountType::ASSET->isDebitNormal());
        $this->assertFalse(AccountType::LIABILITY->isDebitNormal());
        $this->assertFalse(AccountType::EQUITY->isDebitNormal());
        $this->assertFalse(AccountType::INCOME->isDebitNormal());
        $this->assertTrue(AccountType::EXPENSE->isDebitNormal());
    }
}
