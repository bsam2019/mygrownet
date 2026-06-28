<?php

declare(strict_types=1);

namespace App\Domain\GrowFinance\Services;

use App\Models\User;
use App\Notifications\GrowFinance\InvoiceCreatedNotification;
use App\Notifications\GrowFinance\InvoicePaidNotification;
use App\Notifications\GrowFinance\InvoiceOverdueNotification;
use App\Notifications\GrowFinance\SaleRecordedNotification;
use App\Notifications\GrowFinance\ExpenseRecordedNotification;
use App\Notifications\GrowFinance\LowBalanceAlertNotification;
use App\Notifications\GrowFinance\DailySummaryNotification;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Centralized notification service for GrowFinance module
 * Uses Laravel's database notification channel
 */
class NotificationService
{
    /**
     * Notify user when an invoice is created
     */
    public function notifyInvoiceCreated(
        User $user,
        int $invoiceId,
        string $invoiceNumber,
        string $customerName,
        float $totalAmount,
        string $dueDate
    ): void {
        try {
            $user->notify(new InvoiceCreatedNotification(
                $invoiceId,
                $invoiceNumber,
                $customerName,
                $totalAmount,
                $dueDate
            ));

            Log::info('GrowFinance: Invoice created notification sent', [
                'invoice_id' => $invoiceId,
                'user_id' => $user->id,
            ]);
        } catch (Throwable $e) {
            Log::error('GrowFinance: Failed to send invoice created notification', [
                'invoice_id' => $invoiceId,
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Notify user when an invoice is paid
     */
    public function notifyInvoicePaid(
        User $user,
        int $invoiceId,
        string $invoiceNumber,
        string $customerName,
        float $amountPaid,
        float $totalAmount,
        bool $isFullyPaid = true
    ): void {
        try {
            $user->notify(new InvoicePaidNotification(
                $invoiceId,
                $invoiceNumber,
                $customerName,
                $amountPaid,
                $totalAmount,
                $isFullyPaid
            ));

            Log::info('GrowFinance: Invoice paid notification sent', [
                'invoice_id' => $invoiceId,
                'user_id' => $user->id,
                'is_fully_paid' => $isFullyPaid,
            ]);
        } catch (Throwable $e) {
            Log::error('GrowFinance: Failed to send invoice paid notification', [
                'invoice_id' => $invoiceId,
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Notify user when an invoice becomes overdue
     */
    public function notifyInvoiceOverdue(
        User $user,
        int $invoiceId,
        string $invoiceNumber,
        string $customerName,
        float $amountDue,
        string $dueDate,
        int $daysOverdue
    ): void {
        try {
            $user->notify(new InvoiceOverdueNotification(
                $invoiceId,
                $invoiceNumber,
                $customerName,
                $amountDue,
                $dueDate,
                $daysOverdue
            ));

            Log::info('GrowFinance: Invoice overdue notification sent', [
                'invoice_id' => $invoiceId,
                'user_id' => $user->id,
                'days_overdue' => $daysOverdue,
            ]);
        } catch (Throwable $e) {
            Log::error('GrowFinance: Failed to send invoice overdue notification', [
                'invoice_id' => $invoiceId,
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Notify user when a sale is recorded
     */
    public function notifySaleRecorded(
        User $user,
        int $saleId,
        string $description,
        float $amount,
        string $paymentMethod,
        ?string $customerName = null
    ): void {
        try {
            $user->notify(new SaleRecordedNotification(
                $saleId,
                $description,
                $amount,
                $paymentMethod,
                $customerName
            ));

            Log::info('GrowFinance: Sale recorded notification sent', [
                'sale_id' => $saleId,
                'user_id' => $user->id,
            ]);
        } catch (Throwable $e) {
            Log::error('GrowFinance: Failed to send sale recorded notification', [
                'sale_id' => $saleId,
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Notify user when an expense is recorded
     */
    public function notifyExpenseRecorded(
        User $user,
        int $expenseId,
        string $category,
        string $description,
        float $amount,
        string $paymentMethod
    ): void {
        try {
            $user->notify(new ExpenseRecordedNotification(
                $expenseId,
                $category,
                $description,
                $amount,
                $paymentMethod
            ));

            Log::info('GrowFinance: Expense recorded notification sent', [
                'expense_id' => $expenseId,
                'user_id' => $user->id,
            ]);
        } catch (Throwable $e) {
            Log::error('GrowFinance: Failed to send expense recorded notification', [
                'expense_id' => $expenseId,
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Notify user of low account balance
     */
    public function notifyLowBalance(
        User $user,
        int $accountId,
        string $accountName,
        float $currentBalance,
        float $threshold
    ): void {
        try {
            $user->notify(new LowBalanceAlertNotification(
                $accountId,
                $accountName,
                $currentBalance,
                $threshold
            ));

            Log::info('GrowFinance: Low balance alert sent', [
                'account_id' => $accountId,
                'user_id' => $user->id,
                'balance' => $currentBalance,
            ]);
        } catch (Throwable $e) {
            Log::error('GrowFinance: Failed to send low balance alert', [
                'account_id' => $accountId,
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Send daily financial summary
     */
    public function notifyDailySummary(
        User $user,
        string $date,
        float $totalSales,
        float $totalExpenses,
        float $netIncome,
        int $invoiceCount,
        int $expenseCount
    ): void {
        try {
            $user->notify(new DailySummaryNotification(
                $date,
                $totalSales,
                $totalExpenses,
                $netIncome,
                $invoiceCount,
                $expenseCount
            ));

            Log::info('GrowFinance: Daily summary notification sent', [
                'date' => $date,
                'user_id' => $user->id,
            ]);
        } catch (Throwable $e) {
            Log::error('GrowFinance: Failed to send daily summary notification', [
                'date' => $date,
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
