<?php

namespace App\Domain\BMS\Core\Services;

use App\Domain\BMS\Entities\Quotation;
use App\Domain\BMS\Entities\QuotationItem;
use App\Domain\BMS\Entities\Job;
use App\Domain\BMS\Repositories\QuotationRepositoryInterface;
use App\Domain\BMS\Repositories\QuotationItemRepositoryInterface;
use Illuminate\Support\Facades\DB;

class QuotationService
{
    public function __construct(
        private QuotationRepositoryInterface $quotationRepo,
        private QuotationItemRepositoryInterface $quotationItemRepo,
        private JobService $jobService,
        private AuditTrailService $auditTrail
    ) {}

    public function createQuotation(array $data, int $companyId, int $userId): Quotation
    {
        return DB::transaction(function () use ($data, $companyId, $userId) {
            $quotationNumber = $this->generateQuotationNumber($companyId);

            $subtotal = 0;
            foreach ($data['items'] as $item) {
                $subtotal += $item['line_total'];
            }
            $taxAmount = $data['tax_amount'] ?? 0;
            $discountAmount = $data['discount_amount'] ?? 0;
            $totalAmount = $subtotal + $taxAmount - $discountAmount;

            $quotation = Quotation::reconstitute([
                'company_id' => $companyId,
                'customer_id' => $data['customer_id'],
                'quotation_number' => $quotationNumber,
                'quotation_date' => $data['quotation_date'] ?? now()->format('Y-m-d'),
                'expiry_date' => $data['expiry_date'] ?? null,
                'subtotal' => $subtotal,
                'tax_amount' => $taxAmount,
                'discount_amount' => $discountAmount,
                'total_amount' => $totalAmount,
                'status' => 'draft',
                'notes' => $data['notes'] ?? null,
                'terms' => $data['terms'] ?? null,
                'created_by' => $userId,
            ]);
            $quotation = $this->quotationRepo->save($quotation);

            foreach ($data['items'] as $item) {
                $quotationItem = QuotationItem::reconstitute([
                    'quotation_id' => $quotation->id,
                    'description' => $item['description'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'tax_rate' => $item['tax_rate'] ?? 0,
                    'discount_rate' => $item['discount_rate'] ?? 0,
                    'line_total' => $item['line_total'],
                    'dimensions' => $item['dimensions'] ?? null,
                    'dimensions_value' => $item['dimensions_value'] ?? 1,
                ]);
                $this->quotationItemRepo->save($quotationItem);
            }

            $this->auditTrail->log($companyId, $userId, 'quotation', $quotation->id, 'created', null, $quotation->toArray());
            return $quotation;
        });
    }

    public function updateQuotation(int $quotationId, array $data, int $userId): Quotation
    {
        return DB::transaction(function () use ($quotationId, $data, $userId) {
            $quotation = $this->quotationRepo->findById($quotationId);
            if (!$quotation || !$quotation->isDraft()) throw new \Exception('Only draft quotations can be edited');

            $oldData = $quotation->toArray();

            $subtotal = 0;
            foreach ($data['items'] as $item) {
                $subtotal += $item['line_total'];
            }
            $taxAmount = $data['tax_amount'] ?? 0;
            $discountAmount = $data['discount_amount'] ?? 0;
            $totalAmount = $subtotal + $taxAmount - $discountAmount;

            $updated = Quotation::reconstitute(array_merge($quotation->toArray(), [
                'customer_id' => $data['customer_id'],
                'quotation_date' => $data['quotation_date'],
                'expiry_date' => $data['expiry_date'] ?? null,
                'subtotal' => $subtotal,
                'tax_amount' => $taxAmount,
                'discount_amount' => $discountAmount,
                'total_amount' => $totalAmount,
                'notes' => $data['notes'] ?? null,
                'terms' => $data['terms'] ?? null,
            ]));
            $this->quotationRepo->save($updated);
            $this->quotationItemRepo->deleteByQuotation($quotationId);

            foreach ($data['items'] as $item) {
                $quotationItem = QuotationItem::reconstitute([
                    'quotation_id' => $quotationId,
                    'description' => $item['description'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'tax_rate' => $item['tax_rate'] ?? 0,
                    'discount_rate' => $item['discount_rate'] ?? 0,
                    'line_total' => $item['line_total'],
                    'dimensions' => $item['dimensions'] ?? null,
                    'dimensions_value' => $item['dimensions_value'] ?? 1,
                ]);
                $this->quotationItemRepo->save($quotationItem);
            }

            $this->auditTrail->log($updated->companyId, $userId, 'quotation', $quotationId, 'updated', $oldData, $updated->toArray());
            return $this->quotationRepo->findById($quotationId);
        });
    }

    public function sendQuotation(int $quotationId, int $userId): Quotation
    {
        $quotation = $this->quotationRepo->findById($quotationId);
        if (!$quotation) throw new \Exception('Quotation not found');

        $updated = Quotation::reconstitute(array_merge($quotation->toArray(), ['status' => 'sent']));
        $this->quotationRepo->save($updated);

        $this->auditTrail->log($quotation->companyId, $userId, 'quotation', $quotationId, 'sent', $quotation->toArray(), $updated->toArray());
        return $this->quotationRepo->findById($quotationId);
    }

    public function convertToJob(int $quotationId, int $userId): Job
    {
        return DB::transaction(function () use ($quotationId, $userId) {
            $quotation = $this->quotationRepo->findById($quotationId);
            if (!$quotation || $quotation->isConverted()) throw new \Exception('Quotation already converted to job');

            $jobData = [
                'company_id' => $quotation->companyId,
                'customer_id' => $quotation->customerId,
                'job_type' => 'Aluminium Fabrication – ' . $quotation->quotationNumber,
                'description' => $quotation->notes ?? 'Converted from quotation ' . $quotation->quotationNumber,
                'quoted_value' => $quotation->totalAmount,
                'priority' => 'normal',
                'created_by' => $userId,
            ];

            $job = $this->jobService->createJob($jobData);

            $updated = Quotation::reconstitute(array_merge($quotation->toArray(), [
                'status' => 'accepted',
                'converted_to_job_id' => $job->id,
            ]));
            $this->quotationRepo->save($updated);

            $this->auditTrail->log($quotation->companyId, $userId, 'quotation', $quotationId, 'converted', null, ['job_id' => $job->id]);
            return $job;
        });
    }

    private function generateQuotationNumber(int $companyId): string
    {
        $year = date('Y');
        $quotations = $this->quotationRepo->findByCompany($companyId);
        $lastQuotation = null;
        foreach ($quotations as $q) {
            if (str_starts_with($q->quotationNumber, "QUO-{$year}-")) $lastQuotation = $q;
        }

        if ($lastQuotation) {
            $lastNumber = (int) substr($lastQuotation->quotationNumber, -4);
            $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '0001';
        }
        return "QUO-{$year}-{$newNumber}";
    }
}
