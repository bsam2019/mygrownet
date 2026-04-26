<?php

namespace App\Infrastructure\Persistence\Eloquent\CMS;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ProgressCertificateModel extends Model
{
    protected $table = 'cms_progress_certificates';

    protected $fillable = [
        'project_id', 'billing_stage_id', 'certificate_number', 'certificate_date',
        'period_from', 'period_to', 'work_completed_value', 'materials_on_site_value',
        'previous_certificates_total', 'current_certificate_value', 'retention_percentage',
        'retention_amount', 'vat_amount', 'net_payment_due', 'status',
        'prepared_by', 'approved_by', 'approved_date', 'payment_date', 'notes',
    ];

    protected $casts = [
        'certificate_date' => 'date',
        'period_from' => 'date',
        'period_to' => 'date',
        'work_completed_value' => 'decimal:2',
        'materials_on_site_value' => 'decimal:2',
        'previous_certificates_total' => 'decimal:2',
        'current_certificate_value' => 'decimal:2',
        'retention_percentage' => 'decimal:2',
        'retention_amount' => 'decimal:2',
        'vat_amount' => 'decimal:2',
        'net_payment_due' => 'decimal:2',
        'approved_date' => 'date',
        'payment_date' => 'date',
    ];

    protected static function boot()
    {
        parent::boot();
        
        static::saving(function ($cert) {
            $grossValue = $cert->work_completed_value + $cert->materials_on_site_value;
            $cert->current_certificate_value = $grossValue - $cert->previous_certificates_total;
            $cert->retention_amount = $cert->current_certificate_value * ($cert->retention_percentage / 100);
            $afterRetention = $cert->current_certificate_value - $cert->retention_amount;
            $cert->net_payment_due = $afterRetention + $cert->vat_amount;
        });
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(ProjectModel::class, 'project_id');
    }

    public function billingStage(): BelongsTo
    {
        return $this->belongsTo(BillingStageModel::class, 'billing_stage_id');
    }

    public function preparer(): BelongsTo
    {
        return $this->belongsTo(EmployeeModel::class, 'prepared_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(EmployeeModel::class, 'approved_by');
    }

    public function retentionTracking(): HasMany
    {
        return $this->hasMany(RetentionTrackingModel::class, 'progress_certificate_id');
    }

    public function paymentApplications(): HasMany
    {
        return $this->hasMany(PaymentApplicationModel::class, 'progress_certificate_id');
    }
}
