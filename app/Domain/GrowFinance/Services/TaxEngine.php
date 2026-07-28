<?php

declare(strict_types=1);

namespace App\Domain\GrowFinance\Services;

use App\Domain\GrowFinance\Entities\TaxRate;
use App\Domain\GrowFinance\Entities\TaxReturn;
use App\Domain\GrowFinance\Repositories\InvoiceRepositoryInterface;
use App\Domain\GrowFinance\Repositories\ExpenseRepositoryInterface;
use App\Domain\GrowFinance\Repositories\TaxRateRepositoryInterface;
use App\Domain\GrowFinance\Repositories\TaxReturnRepositoryInterface;
use App\Domain\GrowFinance\ValueObjects\InvoiceStatus;
use App\Domain\GrowFinance\ValueObjects\TaxReturnStatus;
use App\Domain\GrowFinance\ValueObjects\TaxType;
use DateTimeImmutable;

class TaxEngine
{
    public function __construct(
        private TaxRateRepositoryInterface $taxRateRepo,
        private TaxReturnRepositoryInterface $taxReturnRepo,
        private InvoiceRepositoryInterface $invoiceRepo,
        private ExpenseRepositoryInterface $expenseRepo,
    ) {}

    public function computeVat(int $businessId, float $amount, ?TaxRate $rate = null): array
    {
        $rate ??= $this->taxRateRepo->findDefault($businessId, TaxType::VAT->value);
        if (!$rate) return ['vat_amount' => 0, 'exclusive' => $amount, 'inclusive' => $amount, 'rate' => 0];

        $vatRate = $rate->rate / 100;
        $exclusive = round($amount / (1 + $vatRate), 2);
        $vatAmount = round($amount - $exclusive, 2);

        return [
            'vat_amount' => $vatAmount,
            'exclusive' => $exclusive,
            'inclusive' => $amount,
            'rate' => $rate->rate,
        ];
    }

    public function computeWithholding(int $businessId, float $amount, string $supplierType = 'service'): array
    {
        $rate = $this->taxRateRepo->findEffective($businessId, TaxType::WITHHOLDING->value, new DateTimeImmutable('now'));
        $whRate = $rate?->rate ?? 15.0;

        $rates = [
            'service' => $whRate,
            'goods' => $whRate,
            'rent' => 15.0,
            'contractor' => 15.0,
            'management' => 15.0,
            'commission' => 15.0,
        ];

        $appliedRate = $rates[$supplierType] ?? $whRate;
        $withholdingAmount = round($amount * ($appliedRate / 100), 2);

        return [
            'withholding_amount' => $withholdingAmount,
            'rate' => $appliedRate,
            'net_amount' => round($amount - $withholdingAmount, 2),
            'gross_amount' => $amount,
        ];
    }

    public function getVatReturn(int $businessId, string $periodStart, string $periodEnd): array
    {
        $invoices = $this->invoiceRepo->findByBusiness($businessId);
        $expenses = $this->expenseRepo->findByBusiness($businessId);

        $salesTotal = 0.0;
        $outputVat = 0.0;
        $purchasesTotal = 0.0;
        $inputVat = 0.0;

        foreach ($invoices as $inv) {
            if ($inv->status === InvoiceStatus::PAID || $inv->status === InvoiceStatus::SENT || $inv->status === InvoiceStatus::PARTIAL) {
                $invDate = $inv->invoiceDate?->format('Y-m-d');
                if ($invDate && $invDate >= $periodStart && $invDate <= $periodEnd) {
                    $salesTotal += $inv->totalAmount;
                    $outputVat += $inv->taxAmount;
                }
            }
        }

        foreach ($expenses as $exp) {
            $expDate = $exp->expenseDate?->format('Y-m-d');
            if ($expDate && $expDate >= $periodStart && $expDate <= $periodEnd) {
                $purchasesTotal += $exp->amount;
                $inputVat += $exp->taxAmount;
            }
        }

        $netVatPayable = round(max(0, $outputVat - $inputVat), 2);

        return [
            'return_type' => 'vat',
            'period_start' => $periodStart,
            'period_end' => $periodEnd,
            'total_sales' => round($salesTotal, 2),
            'total_purchases' => round($purchasesTotal, 2),
            'output_vat' => round($outputVat, 2),
            'input_vat' => round($inputVat, 2),
            'net_vat_payable' => $netVatPayable,
        ];
    }

    public function getWithholdingSchedule(int $businessId, string $periodStart, string $periodEnd): array
    {
        $expenses = $this->expenseRepo->findByBusiness($businessId);
        $items = [];

        foreach ($expenses as $exp) {
            $expDate = $exp->expenseDate?->format('Y-m-d');
            if ($expDate && $expDate >= $periodStart && $expDate <= $periodEnd) {
                $amount = $exp->amount + ($exp->taxAmount ?? 0);
                $result = $this->computeWithholding($businessId, $amount, $exp->category ?? 'service');
                if ($result['withholding_amount'] > 0) {
                    $items[] = [
                        'date' => $expDate,
                        'vendor_name' => '', // resolved in controller
                        'description' => $exp->description ?? '',
                        'category' => $exp->category ?? '',
                        'gross_amount' => $result['gross_amount'],
                        'withholding_amount' => $result['withholding_amount'],
                        'rate' => $result['rate'],
                        'net_amount' => $result['net_amount'],
                    ];
                }
            }
        }

        $totalWithholding = array_sum(array_column($items, 'withholding_amount'));

        return [
            'return_type' => 'withholding',
            'period_start' => $periodStart,
            'period_end' => $periodEnd,
            'items' => $items,
            'total_withholding' => round($totalWithholding, 2),
        ];
    }

    public function saveTaxReturn(int $businessId, string $returnType, string $periodStart, string $periodEnd): TaxReturn
    {
        $data = $returnType === 'vat'
            ? $this->getVatReturn($businessId, $periodStart, $periodEnd)
            : $this->getWithholdingSchedule($businessId, $periodStart, $periodEnd);

        $periodLabel = (new DateTimeImmutable($periodStart))->format('F Y');
        $dueDate = (new DateTimeImmutable($periodEnd))->modify('+30 days');

        $return = new TaxReturn(
            id: null,
            businessId: $businessId,
            returnType: $returnType,
            periodLabel: $periodLabel,
            periodStart: new DateTimeImmutable($periodStart),
            periodEnd: new DateTimeImmutable($periodEnd),
            dueDate: $dueDate,
            outputVat: $data['output_vat'] ?? 0,
            inputVat: $data['input_vat'] ?? 0,
            netVatPayable: $data['net_vat_payable'] ?? 0,
            totalSales: $data['total_sales'] ?? 0,
            totalPurchases: $data['total_purchases'] ?? 0,
            withholdingCollected: $data['total_withholding'] ?? 0,
            withholdingPaid: 0,
            status: TaxReturnStatus::DRAFT,
        );

        return $this->taxReturnRepo->save($return);
    }

    public function getSavedReturns(int $businessId, ?string $returnType = null): array
    {
        $returns = $returnType
            ? $this->taxReturnRepo->findByType($businessId, $returnType)
            : $this->taxReturnRepo->findByBusiness($businessId);

        return array_map(fn(TaxReturn $r) => $r->toArray(), $returns);
    }

    public function getDefaultVatRate(int $businessId): ?TaxRate
    {
        return $this->taxRateRepo->findDefault($businessId, TaxType::VAT->value);
    }

    public function seedDefaultRates(int $businessId): void
    {
        $existing = $this->taxRateRepo->findByBusiness($businessId);
        if (!empty($existing)) return;

        $now = new DateTimeImmutable('now');
        $this->taxRateRepo->save(new TaxRate(
            id: null,
            businessId: $businessId,
            name: 'Zambia Standard VAT (16%)',
            taxType: TaxType::VAT,
            rate: 16.00,
            effectiveFrom: $now->modify('-5 years'),
            isDefault: true,
            jurisdiction: 'ZM',
            glCode: '2400',
            notes: 'Standard VAT rate for Zambia',
        ));

        $this->taxRateRepo->save(new TaxRate(
            id: null,
            businessId: $businessId,
            name: 'Zambia Withholding Tax (15%)',
            taxType: TaxType::WITHHOLDING,
            rate: 15.00,
            effectiveFrom: $now->modify('-5 years'),
            isDefault: true,
            jurisdiction: 'ZM',
            glCode: '2500',
            notes: 'Standard withholding tax for service providers',
        ));
    }
}
