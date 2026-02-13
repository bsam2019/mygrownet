<?php

namespace App\Domain\CMS\Core\Services;

use App\Infrastructure\Persistence\Eloquent\CMS\QuotationModel;
use App\Infrastructure\Persistence\Eloquent\CMS\QuotationItemModel;
use App\Infrastructure\Persistence\Eloquent\CMS\JobModel;
use Illuminate\Support\Facades\DB;

class QuotationService
{
    public function createQuotation(array $data, int $companyId, int $userId): QuotationModel
    {
        return DB::transaction(function () use ($data, $companyId, $userId) {
            // Generate quotation number
            $quotationNumber = $this->generateQuotationNumber($companyId);

            // Calculate totals
            $subtotal = 0;
            foreach ($data['items'] as $item) {
                $subtotal += $item['line_total'];
            }

            $taxAmount = $data['tax_amount'] ?? 0;
            $discountAmount = $data['discount_amount'] ?? 0;
            $totalAmount = $subtotal + $taxAmount - $discountAmount;

            $quotation = QuotationModel::create([
                'company_id' => $companyId,
                'customer_id' => $data['customer_id'],
                'quotation_number' => $quotationNumber,
                'quotation_date' => $data['quotation_date'] ?? now(),
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

            // Create line items
            foreach ($data['items'] as $item) {
                QuotationItemModel::create([
                    'quotation_id' => $quotation->id,
                    'description' => $item['description'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'tax_rate' => $item['tax_rate'] ?? 0,
                    'discount_rate' => $item['discount_rate'] ?? 0,
                    'line_total' => $item['line_total'],
                ]);
            }

            // Log audit trail
            app(AuditTrailService::class)->log(
                $companyId,
                $userId,
                'quotation',
                $quotation->id,
                'created',
                null,
                $quotation->toArray()
            );

            return $quotation->load('items');
        });
    }

    public function updateQuotation(int $quotationId, array $data, int $userId): QuotationModel
    {
        return DB::transaction(function () use ($quotationId, $data, $userId) {
            $quotation = QuotationModel::findOrFail($quotationId);

            if (!$quotation->isDraft()) {
                throw new \Exception('Only draft quotations can be edited');
            }

            $oldData = $quotation->toArray();

            // Calculate totals
            $subtotal = 0;
            foreach ($data['items'] as $item) {
                $subtotal += $item['line_total'];
            }

            $taxAmount = $data['tax_amount'] ?? 0;
            $discountAmount = $data['discount_amount'] ?? 0;
            $totalAmount = $subtotal + $taxAmount - $discountAmount;

            $quotation->update([
                'customer_id' => $data['customer_id'],
                'quotation_date' => $data['quotation_date'],
                'expiry_date' => $data['expiry_date'] ?? null,
                'subtotal' => $subtotal,
                'tax_amount' => $taxAmount,
                'discount_amount' => $discountAmount,
                'total_amount' => $totalAmount,
                'notes' => $data['notes'] ?? null,
                'terms' => $data['terms'] ?? null,
            ]);

            // Delete old items and create new ones
            $quotation->items()->delete();
            foreach ($data['items'] as $item) {
                QuotationItemModel::create([
                    'quotation_id' => $quotation->id,
                    'description' => $item['description'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'tax_rate' => $item['tax_rate'] ?? 0,
                    'discount_rate' => $item['discount_rate'] ?? 0,
                    'line_total' => $item['line_total'],
                ]);
            }

            // Log audit trail
            app(AuditTrailService::class)->log(
                $quotation->company_id,
                $userId,
                'quotation',
                $quotation->id,
                'updated',
                $oldData,
                $quotation->fresh()->toArray()
            );

            return $quotation->fresh()->load('items');
        });
    }

    public function sendQuotation(int $quotationId, int $userId): QuotationModel
    {
        $quotation = QuotationModel::findOrFail($quotationId);
        
        $oldData = $quotation->toArray();
        
        $quotation->update(['status' => 'sent']);

        // Log audit trail
        app(AuditTrailService::class)->log(
            $quotation->company_id,
            $userId,
            'quotation',
            $quotation->id,
            'sent',
            $oldData,
            $quotation->fresh()->toArray()
        );

        return $quotation->fresh();
    }

    public function convertToJob(int $quotationId, int $userId): JobModel
    {
        return DB::transaction(function () use ($quotationId, $userId) {
            $quotation = QuotationModel::with('items')->findOrFail($quotationId);

            if ($quotation->isConverted()) {
                throw new \Exception('Quotation already converted to job');
            }

            // Create job from quotation
            $jobService = app(JobService::class);
            
            $jobData = [
                'customer_id' => $quotation->customer_id,
                'job_type' => 'From Quotation ' . $quotation->quotation_number,
                'description' => $quotation->notes ?? 'Converted from quotation',
                'quoted_value' => $quotation->total_amount,
                'priority' => 'normal',
            ];

            $job = $jobService->createJob($jobData, $quotation->company_id, $userId);

            // Update quotation
            $quotation->update([
                'status' => 'accepted',
                'converted_to_job_id' => $job->id,
            ]);

            // Log audit trail
            app(AuditTrailService::class)->log(
                $quotation->company_id,
                $userId,
                'quotation',
                $quotation->id,
                'converted_to_job',
                null,
                ['job_id' => $job->id]
            );

            return $job;
        });
    }

    private function generateQuotationNumber(int $companyId): string
    {
        $year = date('Y');
        
        $lastQuotation = QuotationModel::where('company_id', $companyId)
            ->where('quotation_number', 'like', "QUO-{$year}-%")
            ->orderBy('quotation_number', 'desc')
            ->first();

        if ($lastQuotation) {
            $lastNumber = (int) substr($lastQuotation->quotation_number, -4);
            $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '0001';
        }

        return "QUO-{$year}-{$newNumber}";
    }
}
