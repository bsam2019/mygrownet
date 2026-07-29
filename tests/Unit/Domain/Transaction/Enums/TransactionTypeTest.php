<?php

namespace Tests\Unit\Domain\Transaction\Enums;

use App\Domain\Transaction\Enums\TransactionType;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class TransactionTypeTest extends TestCase
{
    #[Test]
    public function cases_have_expected_values(): void
    {
        $this->assertEquals('deposit', TransactionType::DEPOSIT->value);
        $this->assertEquals('withdrawal', TransactionType::WITHDRAWAL->value);
        $this->assertEquals('commission_earned', TransactionType::COMMISSION_EARNED->value);
        $this->assertEquals('profit_share', TransactionType::PROFIT_SHARE->value);
        $this->assertEquals('loan_disbursement', TransactionType::LOAN_DISBURSEMENT->value);
        $this->assertEquals('loan_repayment', TransactionType::LOAN_REPAYMENT->value);
    }

    #[Test]
    public function from_string(): void
    {
        $this->assertSame(TransactionType::DEPOSIT, TransactionType::from('deposit'));
        $this->assertSame(TransactionType::WITHDRAWAL, TransactionType::from('withdrawal'));
    }

    #[Test]
    public function try_from_string(): void
    {
        $this->assertSame(TransactionType::COMMISSION_EARNED, TransactionType::tryFrom('commission_earned'));
        $this->assertNull(TransactionType::tryFrom('nonexistent_type'));
    }

    #[Test]
    public function isCredit_returns_true_for_deposit(): void
    {
        $this->assertTrue(TransactionType::DEPOSIT->isCredit());
    }

    #[Test]
    public function isCredit_returns_true_for_wallet_topup(): void
    {
        $this->assertTrue(TransactionType::WALLET_TOPUP->isCredit());
    }

    #[Test]
    public function isCredit_returns_true_for_credit_types(): void
    {
        $creditTypes = [
            TransactionType::DEPOSIT,
            TransactionType::WALLET_TOPUP,
            TransactionType::SHOP_CREDIT_ALLOCATION,
            TransactionType::COMMISSION_EARNED,
            TransactionType::PROFIT_SHARE,
            TransactionType::LGR_EARNED,
            TransactionType::LGR_MANUAL_AWARD,
            TransactionType::LGR_TRANSFER_IN,
            TransactionType::LOAN_DISBURSEMENT,
        ];
        foreach ($creditTypes as $type) {
            $this->assertTrue($type->isCredit(), "{$type->value} should be a credit");
        }
    }

    #[Test]
    public function isCredit_returns_false_for_debit_types(): void
    {
        $debitTypes = [
            TransactionType::WITHDRAWAL,
            TransactionType::STARTER_KIT_PURCHASE,
            TransactionType::STARTER_KIT_UPGRADE,
            TransactionType::STARTER_KIT_GIFT,
            TransactionType::SHOP_PURCHASE,
            TransactionType::SHOP_CREDIT_USAGE,
            TransactionType::LGR_TRANSFER_OUT,
            TransactionType::LOAN_REPAYMENT,
            TransactionType::SUBSCRIPTION_PAYMENT,
            TransactionType::WORKSHOP_PAYMENT,
            TransactionType::MARKETING_EXPENSE,
        ];
        foreach ($debitTypes as $type) {
            $this->assertFalse($type->isCredit(), "{$type->value} should not be a credit");
        }
    }

    #[Test]
    public function isDebit_is_opposite_of_isCredit(): void
    {
        foreach (TransactionType::cases() as $type) {
            $this->assertSame(!$type->isCredit(), $type->isDebit(), "Mismatch for {$type->value}");
        }
    }

    #[Test]
    public function label_returns_human_readable_strings(): void
    {
        $this->assertEquals('Deposit', TransactionType::DEPOSIT->label());
        $this->assertEquals('Withdrawal', TransactionType::WITHDRAWAL->label());
        $this->assertEquals('Commission Earned', TransactionType::COMMISSION_EARNED->label());
        $this->assertEquals('Profit Share', TransactionType::PROFIT_SHARE->label());
        $this->assertEquals('Starter Kit Purchase', TransactionType::STARTER_KIT_PURCHASE->label());
    }

    #[Test]
    public function label_is_non_empty_for_all_cases(): void
    {
        foreach (TransactionType::cases() as $type) {
            $this->assertNotEmpty($type->label(), "Label should not be empty for {$type->value}");
        }
    }

    #[Test]
    public function icon_returns_expected_value_for_deposit(): void
    {
        $this->assertEquals('arrow-down-circle', TransactionType::DEPOSIT->icon());
        $this->assertEquals('arrow-down-circle', TransactionType::WALLET_TOPUP->icon());
        $this->assertEquals('arrow-up-circle', TransactionType::WITHDRAWAL->icon());
        $this->assertEquals('currency-dollar', TransactionType::COMMISSION_EARNED->icon());
    }

    #[Test]
    public function icon_is_always_a_non_empty_string(): void
    {
        foreach (TransactionType::cases() as $type) {
            $this->assertNotEmpty($type->icon(), "Icon should not be empty for {$type->value}");
        }
    }

    #[Test]
    public function color_returns_green_for_credit_types(): void
    {
        $greenTypes = [
            TransactionType::DEPOSIT,
            TransactionType::WALLET_TOPUP,
            TransactionType::COMMISSION_EARNED,
            TransactionType::PROFIT_SHARE,
            TransactionType::LGR_EARNED,
            TransactionType::LGR_MANUAL_AWARD,
            TransactionType::LGR_TRANSFER_IN,
            TransactionType::LOAN_DISBURSEMENT,
        ];
        foreach ($greenTypes as $type) {
            $this->assertEquals('green', $type->color(), "{$type->value} should be green");
        }
    }

    #[Test]
    public function color_returns_red_for_debit_types(): void
    {
        $redTypes = [
            TransactionType::WITHDRAWAL,
            TransactionType::STARTER_KIT_PURCHASE,
            TransactionType::SHOP_PURCHASE,
            TransactionType::SHOP_CREDIT_USAGE,
            TransactionType::LGR_TRANSFER_OUT,
            TransactionType::LOAN_REPAYMENT,
            TransactionType::SUBSCRIPTION_PAYMENT,
            TransactionType::MARKETING_EXPENSE,
        ];
        foreach ($redTypes as $type) {
            $this->assertEquals('red', $type->color(), "{$type->value} should be red");
        }
    }

    #[Test]
    public function color_returns_blue_for_gift_and_credit_allocation(): void
    {
        $this->assertEquals('blue', TransactionType::STARTER_KIT_GIFT->color());
        $this->assertEquals('blue', TransactionType::SHOP_CREDIT_ALLOCATION->color());
    }

    #[Test]
    public function credits_returns_only_credit_types(): void
    {
        $credits = TransactionType::credits();
        foreach ($credits as $type) {
            $this->assertTrue($type->isCredit());
        }
        $this->assertCount(9, $credits);
    }

    #[Test]
    public function debits_returns_only_debit_types(): void
    {
        $debits = TransactionType::debits();
        foreach ($debits as $type) {
            $this->assertTrue($type->isDebit());
        }
    }

    #[Test]
    public function credits_and_debits_cover_all_cases(): void
    {
        $all = array_merge(TransactionType::credits(), TransactionType::debits());
        $this->assertCount(count(TransactionType::cases()), $all);
    }
}
