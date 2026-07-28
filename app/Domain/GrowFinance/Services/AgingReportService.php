<?php

namespace App\Domain\GrowFinance\Services;

use App\Domain\GrowFinance\Repositories\InvoiceRepositoryInterface;
use App\Domain\GrowFinance\Repositories\ExpenseRepositoryInterface;
use App\Domain\GrowFinance\Repositories\CustomerRepositoryInterface;
use App\Domain\GrowFinance\Repositories\VendorRepositoryInterface;
use App\Domain\GrowFinance\ValueObjects\InvoiceStatus;

class AgingReportService
{
    private array $customerCache = [];
    private array $vendorCache = [];

    public function __construct(
        private InvoiceRepositoryInterface $invoiceRepo,
        private ExpenseRepositoryInterface $expenseRepo,
        private CustomerRepositoryInterface $customerRepo,
        private VendorRepositoryInterface $vendorRepo,
    ) {}

    public function getArAging(int $businessId): array
    {
        $invoices = $this->invoiceRepo->findByBusiness($businessId);
        $buckets = $this->emptyBuckets();
        $byCustomer = [];
        $now = new \DateTimeImmutable('today');

        foreach ($invoices as $inv) {
            if ($inv->status === InvoiceStatus::PAID || $inv->status === InvoiceStatus::CANCELLED) {
                continue;
            }
            $balanceDue = $inv->totalAmount - $inv->amountPaid;
            if ($balanceDue <= 0) continue;

            $daysOverdue = $inv->dueDate ? (int) $now->diff($inv->dueDate)->format('%r%a') : 0;
            $bucketKey = $this->bucketKey($daysOverdue);
            $customer = $inv->customerId ? ($this->customerCache[$inv->customerId] ??= $this->customerRepo->findById($inv->customerId)) : null;

            $cid = $inv->customerId ?? 0;
            if (!isset($byCustomer[$cid])) {
                $byCustomer[$cid] = [
                    'customer_id' => $cid,
                    'customer_name' => $customer?->name ?? 'Unknown',
                    'email' => $customer?->email ?? '',
                    'phone' => $customer?->phone ?? '',
                    'total_due' => 0.0,
                    'buckets' => array_fill_keys(array_keys($this->emptyBuckets()), 0.0),
                ];
            }
            $byCustomer[$cid]['total_due'] += $balanceDue;
            $byCustomer[$cid]['buckets'][$bucketKey] += $balanceDue;

            $buckets[$bucketKey]['total'] += $balanceDue;
            $buckets[$bucketKey]['count']++;
            $buckets[$bucketKey]['invoices'][] = [
                'id' => $inv->id,
                'number' => $inv->invoiceNumber,
                'customer_name' => $customer?->name ?? 'Unknown',
                'date' => $inv->invoiceDate?->format('Y-m-d'),
                'due_date' => $inv->dueDate?->format('Y-m-d'),
                'balance_due' => $balanceDue,
                'days_overdue' => $daysOverdue,
                'status' => $inv->status->value,
            ];
        }

        uasort($byCustomer, fn($a, $b) => $b['total_due'] <=> $a['total_due']);

        return [
            'buckets' => $buckets,
            'by_customer' => array_values($byCustomer),
            'total_outstanding' => array_sum(array_column($byCustomer, 'total_due')),
        ];
    }

    public function getApAging(int $businessId, int $defaultTerms = 30): array
    {
        $expenses = $this->expenseRepo->findByBusiness($businessId);
        $buckets = $this->emptyBuckets();
        $byVendor = [];
        $now = new \DateTimeImmutable('today');
        $paidViaApp = $this->getPaidExpenseIds($businessId);

        foreach ($expenses as $exp) {
            if (in_array($exp->id, $paidViaApp)) continue;
            $balanceDue = $exp->amount + ($exp->taxAmount ?? 0);
            if ($balanceDue <= 0) continue;

            $effectiveDue = $exp->expenseDate?->modify("+{$defaultTerms} days") ?? $now;
            $daysOverdue = (int) $now->diff($effectiveDue)->format('%r%a');
            $bucketKey = $this->bucketKey($daysOverdue);
            $vendor = $exp->vendorId ? ($this->vendorCache[$exp->vendorId] ??= $this->vendorRepo->findById($exp->vendorId)) : null;

            $vid = $exp->vendorId ?? 0;
            if (!isset($byVendor[$vid])) {
                $byVendor[$vid] = [
                    'vendor_id' => $vid,
                    'vendor_name' => $vendor?->name ?? 'Unknown',
                    'email' => $vendor?->email ?? '',
                    'phone' => $vendor?->phone ?? '',
                    'total_due' => 0.0,
                    'buckets' => array_fill_keys(array_keys($this->emptyBuckets()), 0.0),
                ];
            }
            $byVendor[$vid]['total_due'] += $balanceDue;
            $byVendor[$vid]['buckets'][$bucketKey] += $balanceDue;

            $buckets[$bucketKey]['total'] += $balanceDue;
            $buckets[$bucketKey]['count']++;
            $buckets[$bucketKey]['expenses'][] = [
                'id' => $exp->id,
                'description' => $exp->description ?? '',
                'vendor_name' => $vendor?->name ?? 'Unknown',
                'date' => $exp->expenseDate?->format('Y-m-d'),
                'amount' => $balanceDue,
                'days_overdue' => $daysOverdue,
                'category' => $exp->category ?? '',
            ];
        }

        uasort($byVendor, fn($a, $b) => $b['total_due'] <=> $a['total_due']);

        return [
            'buckets' => $buckets,
            'by_vendor' => array_values($byVendor),
            'total_outstanding' => array_sum(array_column($byVendor, 'total_due')),
        ];
    }

    public function getCustomerAgingDetail(int $businessId, int $customerId): array
    {
        $invoices = $this->invoiceRepo->findByCustomer($customerId);
        $now = new \DateTimeImmutable('today');
        $items = [];

        foreach ($invoices as $inv) {
            if ($inv->status === InvoiceStatus::PAID || $inv->status === InvoiceStatus::CANCELLED) continue;
            $balanceDue = $inv->totalAmount - $inv->amountPaid;
            if ($balanceDue <= 0) continue;

            $daysOverdue = $inv->dueDate ? (int) $now->diff($inv->dueDate)->format('%r%a') : 0;
            $items[] = [
                'id' => $inv->id,
                'number' => $inv->invoiceNumber,
                'date' => $inv->invoiceDate?->format('Y-m-d'),
                'due_date' => $inv->dueDate?->format('Y-m-d'),
                'total' => $inv->totalAmount,
                'paid' => $inv->amountPaid,
                'balance_due' => $balanceDue,
                'days_overdue' => $daysOverdue,
                'bucket' => $this->bucketKey($daysOverdue),
                'status' => $inv->status->value,
            ];
        }

        return [
            'customer_id' => $customerId,
            'invoices' => $items,
            'total_outstanding' => array_sum(array_column($items, 'balance_due')),
        ];
    }

    public function getVendorAgingDetail(int $businessId, int $vendorId, int $defaultTerms = 30): array
    {
        $expenses = $this->expenseRepo->findByVendor($vendorId);
        $now = new \DateTimeImmutable('today');
        $paidViaApp = $this->getPaidExpenseIds($businessId);
        $items = [];

        foreach ($expenses as $exp) {
            if (in_array($exp->id, $paidViaApp)) continue;
            $balanceDue = $exp->amount + ($exp->taxAmount ?? 0);
            if ($balanceDue <= 0) continue;

            $effectiveDue = $exp->expenseDate?->modify("+{$defaultTerms} days") ?? $now;
            $daysOverdue = (int) $now->diff($effectiveDue)->format('%r%a');
            $items[] = [
                'id' => $exp->id,
                'description' => $exp->description ?? '',
                'date' => $exp->expenseDate?->format('Y-m-d'),
                'amount' => $balanceDue,
                'days_overdue' => $daysOverdue,
                'bucket' => $this->bucketKey($daysOverdue),
                'category' => $exp->category ?? '',
            ];
        }

        return [
            'vendor_id' => $vendorId,
            'expenses' => $items,
            'total_outstanding' => array_sum(array_column($items, 'balance_due')),
        ];
    }

    private function bucketKey(int $daysOverdue): string
    {
        return match (true) {
            $daysOverdue <= 0  => 'current',
            $daysOverdue <= 30 => '1_30',
            $daysOverdue <= 60 => '31_60',
            $daysOverdue <= 90 => '61_90',
            default            => '90_plus',
        };
    }

    private function emptyBuckets(): array
    {
        return [
            'current' => ['label' => 'Current', 'total' => 0.0, 'count' => 0, 'invoices' => []],
            '1_30'    => ['label' => '1-30 Days', 'total' => 0.0, 'count' => 0, 'invoices' => []],
            '31_60'   => ['label' => '31-60 Days', 'total' => 0.0, 'count' => 0, 'invoices' => []],
            '61_90'   => ['label' => '61-90 Days', 'total' => 0.0, 'count' => 0, 'invoices' => []],
            '90_plus' => ['label' => '90+ Days', 'total' => 0.0, 'count' => 0, 'invoices' => []],
        ];
    }

    private function getPaidExpenseIds(int $businessId): array
    {
        return \App\Infrastructure\Persistence\Eloquent\GrowFinance\GrowFinancePaymentModel::forBusiness($businessId)
            ->where('payable_type', \App\Infrastructure\Persistence\Eloquent\GrowFinance\GrowFinanceExpenseModel::class)
            ->pluck('payable_id')
            ->unique()
            ->values()
            ->toArray();
    }
}
