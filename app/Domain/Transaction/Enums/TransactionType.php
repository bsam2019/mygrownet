<?php

namespace App\Domain\Transaction\Enums;

/**
 * Transaction Type Enum
 * 
 * Defines all possible transaction types in the system.
 * Provides type safety and prevents invalid transaction types.
 */
enum TransactionType: string
{
    // Wallet Operations
    case DEPOSIT = 'deposit';
    case WALLET_TOPUP = 'wallet_topup'; // Legacy, same as DEPOSIT
    case WITHDRAWAL = 'withdrawal';

    // Starter Kit Operations
    case STARTER_KIT_PURCHASE = 'starter_kit_purchase';
    case STARTER_KIT_UPGRADE = 'starter_kit_upgrade';
    case STARTER_KIT_GIFT = 'starter_kit_gift';

    // Shop Operations
    case SHOP_PURCHASE = 'shop_purchase';
    case SHOP_CREDIT_ALLOCATION = 'shop_credit_allocation';
    case SHOP_CREDIT_USAGE = 'shop_credit_usage';

    // Earnings
    case COMMISSION_EARNED = 'commission_earned';
    case PROFIT_SHARE = 'profit_share';
    case LGR_EARNED = 'lgr_earned';
    case LGR_MANUAL_AWARD = 'lgr_manual_award';
    case LGR_TRANSFER_OUT = 'lgr_transfer_out';
    case LGR_TRANSFER_IN = 'lgr_transfer_in';

    // Loans
    case LOAN_DISBURSEMENT = 'loan_disbursement';
    case LOAN_REPAYMENT = 'loan_repayment';

    // Subscriptions & Services
    case SUBSCRIPTION_PAYMENT = 'subscription_payment';
    case WORKSHOP_PAYMENT = 'workshop_payment';
    case LEARNING_PACK_PURCHASE = 'learning_pack_purchase';
    case COACHING_PAYMENT = 'coaching_payment';
    case SERVICE_PAYMENT = 'service_payment';
    case GROWBUILDER_PAYMENT = 'growbuilder_payment';
    case MARKETPLACE_PURCHASE = 'marketplace_purchase';

    // Platform Expenses (from CMS)
    case MARKETING_EXPENSE = 'marketing_expense';
    case OFFICE_EXPENSE = 'office_expense';
    case TRAVEL_EXPENSE = 'travel_expense';
    case INFRASTRUCTURE_EXPENSE = 'infrastructure_expense';
    case LEGAL_EXPENSE = 'legal_expense';
    case PROFESSIONAL_FEES = 'professional_fees';
    case UTILITIES_EXPENSE = 'utilities_expense';
    case GENERAL_EXPENSE = 'general_expense';

    /**
     * Check if this is a credit transaction (adds to balance)
     */
    public function isCredit(): bool
    {
        return match($this) {
            self::DEPOSIT,
            self::WALLET_TOPUP,
            self::SHOP_CREDIT_ALLOCATION,
            self::COMMISSION_EARNED,
            self::PROFIT_SHARE,
            self::LGR_EARNED,
            self::LGR_MANUAL_AWARD,
            self::LGR_TRANSFER_IN,
            self::LOAN_DISBURSEMENT => true,
            default => false,
        };
    }

    /**
     * Check if this is a debit transaction (subtracts from balance)
     */
    public function isDebit(): bool
    {
        return !$this->isCredit();
    }

    /**
     * Get human-readable label
     */
    public function label(): string
    {
        return match($this) {
            self::DEPOSIT => 'Deposit',
            self::WALLET_TOPUP => 'Wallet Top-up',
            self::WITHDRAWAL => 'Withdrawal',
            self::STARTER_KIT_PURCHASE => 'Starter Kit Purchase',
            self::STARTER_KIT_UPGRADE => 'Starter Kit Upgrade',
            self::STARTER_KIT_GIFT => 'Starter Kit Gift',
            self::SHOP_PURCHASE => 'Shop Purchase',
            self::SHOP_CREDIT_ALLOCATION => 'Shop Credit Allocation',
            self::SHOP_CREDIT_USAGE => 'Shop Credit Usage',
            self::COMMISSION_EARNED => 'Commission Earned',
            self::PROFIT_SHARE => 'Profit Share',
            self::LGR_EARNED => 'LGR Earned',
            self::LGR_MANUAL_AWARD => 'LGR Manual Award',
            self::LGR_TRANSFER_OUT => 'LGR Transfer Out',
            self::LGR_TRANSFER_IN => 'LGR Transfer In',
            self::LOAN_DISBURSEMENT => 'Loan Disbursement',
            self::LOAN_REPAYMENT => 'Loan Repayment',
            self::SUBSCRIPTION_PAYMENT => 'Subscription Payment',
            self::WORKSHOP_PAYMENT => 'Workshop Payment',
            self::LEARNING_PACK_PURCHASE => 'Learning Pack Purchase',
            self::COACHING_PAYMENT => 'Coaching Payment',
            self::SERVICE_PAYMENT => 'Service Payment',
            self::GROWBUILDER_PAYMENT => 'GrowBuilder Payment',
            self::MARKETPLACE_PURCHASE => 'Marketplace Purchase',
            self::MARKETING_EXPENSE => 'Marketing Expense',
            self::OFFICE_EXPENSE => 'Office Expense',
            self::TRAVEL_EXPENSE => 'Travel Expense',
            self::INFRASTRUCTURE_EXPENSE => 'Infrastructure Expense',
            self::LEGAL_EXPENSE => 'Legal Expense',
            self::PROFESSIONAL_FEES => 'Professional Fees',
            self::UTILITIES_EXPENSE => 'Utilities Expense',
            self::GENERAL_EXPENSE => 'General Expense',
        };
    }

    /**
     * Get icon class for UI display
     */
    public function icon(): string
    {
        return match($this) {
            self::DEPOSIT, self::WALLET_TOPUP => 'arrow-down-circle',
            self::WITHDRAWAL => 'arrow-up-circle',
            self::STARTER_KIT_PURCHASE, self::STARTER_KIT_UPGRADE => 'shopping-bag',
            self::STARTER_KIT_GIFT => 'gift',
            self::SHOP_PURCHASE => 'shopping-cart',
            self::SHOP_CREDIT_ALLOCATION, self::SHOP_CREDIT_USAGE => 'credit-card',
            self::COMMISSION_EARNED, self::PROFIT_SHARE => 'currency-dollar',
            self::LGR_EARNED, self::LGR_MANUAL_AWARD, self::LGR_TRANSFER_IN => 'star',
            self::LGR_TRANSFER_OUT => 'arrow-right-circle',
            self::LOAN_DISBURSEMENT => 'banknotes',
            self::LOAN_REPAYMENT => 'arrow-path',
            self::SUBSCRIPTION_PAYMENT, self::WORKSHOP_PAYMENT, 
            self::LEARNING_PACK_PURCHASE, self::COACHING_PAYMENT,
            self::SERVICE_PAYMENT, self::GROWBUILDER_PAYMENT,
            self::MARKETPLACE_PURCHASE => 'academic-cap',
            self::MARKETING_EXPENSE, self::OFFICE_EXPENSE,
            self::TRAVEL_EXPENSE, self::INFRASTRUCTURE_EXPENSE,
            self::LEGAL_EXPENSE, self::PROFESSIONAL_FEES,
            self::UTILITIES_EXPENSE, self::GENERAL_EXPENSE => 'receipt-percent',
        };
    }

    /**
     * Get color class for UI display
     */
    public function color(): string
    {
        return match($this) {
            self::DEPOSIT, self::WALLET_TOPUP, 
            self::COMMISSION_EARNED, self::PROFIT_SHARE,
            self::LGR_EARNED, self::LGR_MANUAL_AWARD, self::LGR_TRANSFER_IN,
            self::LOAN_DISBURSEMENT => 'green',
            
            self::WITHDRAWAL, 
            self::STARTER_KIT_PURCHASE, self::STARTER_KIT_UPGRADE,
            self::SHOP_PURCHASE, self::SHOP_CREDIT_USAGE,
            self::LGR_TRANSFER_OUT, self::LOAN_REPAYMENT,
            self::SUBSCRIPTION_PAYMENT, self::WORKSHOP_PAYMENT,
            self::LEARNING_PACK_PURCHASE, self::COACHING_PAYMENT,
            self::SERVICE_PAYMENT, self::GROWBUILDER_PAYMENT,
            self::MARKETPLACE_PURCHASE,
            self::MARKETING_EXPENSE, self::OFFICE_EXPENSE,
            self::TRAVEL_EXPENSE, self::INFRASTRUCTURE_EXPENSE,
            self::LEGAL_EXPENSE, self::PROFESSIONAL_FEES,
            self::UTILITIES_EXPENSE, self::GENERAL_EXPENSE => 'red',
            
            self::STARTER_KIT_GIFT, self::SHOP_CREDIT_ALLOCATION => 'blue',
        };
    }

    /**
     * Get all credit transaction types
     */
    public static function credits(): array
    {
        return array_filter(
            self::cases(),
            fn(self $type) => $type->isCredit()
        );
    }

    /**
     * Get all debit transaction types
     */
    public static function debits(): array
    {
        return array_filter(
            self::cases(),
            fn(self $type) => $type->isDebit()
        );
    }
}
