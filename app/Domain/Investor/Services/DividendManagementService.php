<?php

namespace App\Domain\Investor\Services;

use App\Models\InvestorAccount;
use App\Models\InvestorDividend;
use App\Models\InvestorPaymentMethod;
use Illuminate\Support\Collection;

class DividendManagementService
{
    /**
     * Get dividend history for an investor
     */
    public function getDividendHistory(int $investorId): Collection
    {
        return InvestorDividend::where('investor_account_id', $investorId)
            ->orderBy('payment_date', 'desc')
            ->get()
            ->map(fn($dividend) => [
                'id' => $dividend->id,
                'period' => $dividend->dividend_period,
                'gross_amount' => $dividend->gross_amount,
                'tax_withheld' => $dividend->tax_withheld,
                'net_amount' => $dividend->net_amount,
                'declaration_date' => $dividend->declaration_date->format('Y-m-d'),
                'declaration_date_formatted' => $dividend->declaration_date->format('M j, Y'),
                'payment_date' => $dividend->payment_date?->format('Y-m-d'),
                'payment_date_formatted' => $dividend->payment_date?->format('M j, Y'),
                'status' => $dividend->status,
                'status_label' => $this->getStatusLabel($dividend->status),
                'payment_method' => $dividend->payment_method,
                'payment_reference' => $dividend->payment_reference,
            ]);
    }

    /**
     * Get dividend summary for an investor
     */
    public function getDividendSummary(int $investorId): array
    {
        $dividends = InvestorDividend::where('investor_account_id', $investorId)->get();
        
        $totalGross = $dividends->sum('gross_amount');
        $totalTax = $dividends->sum('tax_withheld');
        $totalNet = $dividends->sum('net_amount');
        $totalPaid = $dividends->where('status', 'paid')->sum('net_amount');
        $totalPending = $dividends->whereIn('status', ['declared', 'pending'])->sum('net_amount');
        
        $lastDividend = $dividends->where('status', 'paid')
            ->sortByDesc('payment_date')
            ->first();
            
        $nextDividend = $dividends->whereIn('status', ['declared', 'pending'])
            ->sortBy('payment_date')
            ->first();

        return [
            'total_gross' => $totalGross,
            'total_tax' => $totalTax,
            'total_net' => $totalNet,
            'total_paid' => $totalPaid,
            'total_pending' => $totalPending,
            'dividend_count' => $dividends->count(),
            'last_dividend' => $lastDividend ? [
                'amount' => $lastDividend->net_amount,
                'period' => $lastDividend->dividend_period,
                'date' => $lastDividend->payment_date->format('M j, Y'),
            ] : null,
            'next_dividend' => $nextDividend ? [
                'amount' => $nextDividend->net_amount,
                'period' => $nextDividend->dividend_period,
                'expected_date' => $nextDividend->payment_date?->format('M j, Y'),
            ] : null,
        ];
    }

    /**
     * Declare dividend for all investors
     */
    public function declareDividend(
        string $period,
        float $totalDividendPool,
        \DateTime $declarationDate,
        ?\DateTime $paymentDate = null
    ): int {
        $investors = InvestorAccount::where('status', 'shareholder')->get();
        $totalEquity = $investors->sum('equity_percentage');
        
        $dividendsCreated = 0;
        
        foreach ($investors as $investor) {
            $investorShare = ($investor->equity_percentage / $totalEquity) * $totalDividendPool;
            $taxRate = 0.15; // 15% withholding tax (Zambia standard)
            $taxWithheld = $investorShare * $taxRate;
            $netAmount = $investorShare - $taxWithheld;
            
            InvestorDividend::create([
                'investor_account_id' => $investor->id,
                'dividend_period' => $period,
                'gross_amount' => $investorShare,
                'tax_withheld' => $taxWithheld,
                'net_amount' => $netAmount,
                'declaration_date' => $declarationDate,
                'payment_date' => $paymentDate,
                'status' => 'declared',
            ]);
            
            $dividendsCreated++;
        }
        
        return $dividendsCreated;
    }

    /**
     * Mark dividend as paid
     */
    public function markDividendAsPaid(
        int $dividendId,
        string $paymentMethod,
        string $paymentReference
    ): bool {
        $dividend = InvestorDividend::findOrFail($dividendId);
        
        return $dividend->update([
            'status' => 'paid',
            'payment_date' => now(),
            'payment_method' => $paymentMethod,
            'payment_reference' => $paymentReference,
        ]);
    }

    /**
     * Get or create payment method for investor
     */
    public function getPaymentMethod(int $investorId): ?InvestorPaymentMethod
    {
        return InvestorPaymentMethod::where('investor_account_id', $investorId)
            ->where('is_primary', true)
            ->first();
    }

    /**
     * Update payment method
     */
    public function updatePaymentMethod(int $investorId, array $data): InvestorPaymentMethod
    {
        // Set all existing methods to non-primary
        InvestorPaymentMethod::where('investor_account_id', $investorId)
            ->update(['is_primary' => false]);
        
        // Create or update primary method
        return InvestorPaymentMethod::updateOrCreate(
            [
                'investor_account_id' => $investorId,
                'method_type' => $data['method_type'],
            ],
            [
                'bank_name' => $data['bank_name'] ?? null,
                'account_number' => $data['account_number'] ?? null,
                'account_name' => $data['account_name'] ?? null,
                'branch_code' => $data['branch_code'] ?? null,
                'mobile_provider' => $data['mobile_provider'] ?? null,
                'mobile_number' => $data['mobile_number'] ?? null,
                'is_primary' => true,
                'is_verified' => false, // Requires verification
            ]
        );
    }

    /**
     * Calculate tax withholding
     */
    public function calculateTaxWithholding(float $grossAmount): array
    {
        $taxRate = 0.15; // 15% standard rate in Zambia
        $taxWithheld = $grossAmount * $taxRate;
        $netAmount = $grossAmount - $taxWithheld;
        
        return [
            'gross_amount' => $grossAmount,
            'tax_rate' => $taxRate * 100,
            'tax_withheld' => $taxWithheld,
            'net_amount' => $netAmount,
        ];
    }

    /**
     * Get status label
     */
    private function getStatusLabel(string $status): string
    {
        return match($status) {
            'declared' => 'Declared',
            'pending' => 'Pending Payment',
            'paid' => 'Paid',
            'cancelled' => 'Cancelled',
            default => ucfirst($status),
        };
    }
}
