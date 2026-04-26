<?php

namespace App\Infrastructure\Persistence\Eloquent\CMS;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentApplicationModel extends Model
{
    protected $table = 'cms_payment_applications';

    protected $fillable = [
        'project_id', 'progress_certificate_id', 'application_number', 'application_date',
        'amount_applied', 'amount_approved', 'status', 'supporting_documents',
        'reviewed_by', 'review_date', 'review_notes',
    ];

    protected $casts = [
        'application_date' => 'date',
        'amount_applied' => 'decimal:2',
        'amount_approved' => 'decimal:2',
        'review_date' => 'date',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(ProjectModel::class, 'project_id');
    }

    public function progressCertificate(): BelongsTo
    {
        return $this->belongsTo(ProgressCertificateModel::class, 'progress_certificate_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(EmployeeModel::class, 'reviewed_by');
    }
}
