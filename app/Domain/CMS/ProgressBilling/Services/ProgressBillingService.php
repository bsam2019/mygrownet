<?php

namespace App\Domain\CMS\ProgressBilling\Services;

use App\Infrastructure\Persistence\Eloquent\CMS\ProjectModel;
use App\Infrastructure\Persistence\Eloquent\CMS\ProgressCertificateModel;
use App\Infrastructure\Persistence\Eloquent\CMS\RetentionTrackingModel;
use App\Infrastructure\Persistence\Eloquent\CMS\PaymentApplicationModel;
use Illuminate\Support\Facades\DB;

class ProgressBillingService
{
    public function generateCertificateNumber(int $companyId): string
    {
        $year = date('Y');
        $lastCert = ProgressCertificateModel::whereHas('project', function ($q) use ($companyId) {
            $q->where('company_id', $companyId);
        })
            ->where('certificate_number', 'like', "PC-{$year}-%")
            ->orderBy('id', 'desc')
            ->first();

        if ($lastCert) {
            $lastNumber = (int) substr($lastCert->certificate_number, -4);
            $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '0001';
        }

        return "PC-{$year}-{$newNumber}";
    }

    public function createProgressCertificate(ProjectModel $project, array $data): ProgressCertificateModel
    {
        return DB::transaction(function () use ($project, $data) {
            // Get previous certificates total
            $previousTotal = $project->progressCertificates()
                ->where('status', '!=', 'draft')
                ->sum('current_certificate_value');

            $certificate = ProgressCertificateModel::create([
                'project_id' => $project->id,
                'billing_stage_id' => $data['billing_stage_id'] ?? null,
                'certificate_number' => $data['certificate_number'],
                'certificate_date' => $data['certificate_date'] ?? now(),
                'period_from' => $data['period_from'],
                'period_to' => $data['period_to'],
                'work_completed_value' => $data['work_completed_value'],
                'materials_on_site_value' => $data['materials_on_site_value'] ?? 0,
                'previous_certificates_total' => $previousTotal,
                'retention_percentage' => $data['retention_percentage'] ?? 10,
                'vat_amount' => $data['vat_amount'] ?? 0,
                'prepared_by' => $data['prepared_by'],
            ]);

            // Create retention tracking
            if ($certificate->retention_amount > 0) {
                RetentionTrackingModel::create([
                    'project_id' => $project->id,
                    'progress_certificate_id' => $certificate->id,
                    'retention_amount' => $certificate->retention_amount,
                    'released_amount' => 0,
                    'balance' => $certificate->retention_amount,
                    'release_due_date' => $data['release_due_date'] ?? null,
                ]);
            }

            return $certificate->fresh(['project', 'billingStage', 'retentionTracking']);
        });
    }

    public function approveCertificate(ProgressCertificateModel $certificate, int $approvedBy): ProgressCertificateModel
    {
        $certificate->status = 'approved';
        $certificate->approved_by = $approvedBy;
        $certificate->approved_date = now();
        $certificate->save();

        return $certificate;
    }

    public function recordPayment(ProgressCertificateModel $certificate, array $data): ProgressCertificateModel
    {
        $certificate->status = 'paid';
        $certificate->payment_date = $data['payment_date'];
        $certificate->save();

        return $certificate;
    }

    public function releaseRetention(RetentionTrackingModel $retention, float $amount, string $notes = null): RetentionTrackingModel
    {
        $retention->released_amount += $amount;
        $retention->release_notes = $notes;
        
        if (!$retention->actual_release_date) {
            $retention->actual_release_date = now();
        }
        
        $retention->save();

        return $retention;
    }

    public function createPaymentApplication(ProgressCertificateModel $certificate, array $data): PaymentApplicationModel
    {
        $applicationNumber = $this->generateApplicationNumber($certificate->project->company_id);

        return PaymentApplicationModel::create([
            'project_id' => $certificate->project_id,
            'progress_certificate_id' => $certificate->id,
            'application_number' => $applicationNumber,
            'application_date' => $data['application_date'] ?? now(),
            'amount_applied' => $data['amount_applied'],
            'supporting_documents' => $data['supporting_documents'] ?? null,
        ]);
    }

    private function generateApplicationNumber(int $companyId): string
    {
        $year = date('Y');
        $lastApp = PaymentApplicationModel::whereHas('project', function ($q) use ($companyId) {
            $q->where('company_id', $companyId);
        })
            ->where('application_number', 'like', "PA-{$year}-%")
            ->orderBy('id', 'desc')
            ->first();

        if ($lastApp) {
            $lastNumber = (int) substr($lastApp->application_number, -4);
            $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '0001';
        }

        return "PA-{$year}-{$newNumber}";
    }

    public function getProjectBillingSummary(ProjectModel $project): array
    {
        $certificates = $project->progressCertificates()->where('status', '!=', 'draft')->get();
        $retentions = RetentionTrackingModel::where('project_id', $project->id)->get();

        return [
            'total_certified' => $certificates->sum('current_certificate_value'),
            'total_retention_held' => $retentions->sum('retention_amount'),
            'total_retention_released' => $retentions->sum('released_amount'),
            'retention_balance' => $retentions->sum('balance'),
            'total_paid' => $certificates->where('status', 'paid')->sum('net_payment_due'),
            'outstanding_payment' => $certificates->where('status', 'approved')->sum('net_payment_due'),
            'certificates_count' => $certificates->count(),
            'project_value' => $project->budget,
            'percentage_billed' => $project->budget > 0 
                ? ($certificates->sum('current_certificate_value') / $project->budget) * 100 
                : 0,
        ];
    }
}
