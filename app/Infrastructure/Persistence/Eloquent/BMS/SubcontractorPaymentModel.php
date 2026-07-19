<?php

namespace App\Infrastructure\Persistence\Eloquent\CMS;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubcontractorPaymentModel extends Model
{
    protected $table = 'cms_subcontractor_payments';

    protected $fillable = [
        'subcontractor_id', 'assignment_id', 'payment_reference', 'amount',
        'payment_date', 'payment_method', 'description', 'receipt_path',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'payment_date' => 'date',
    ];

    public function subcontractor(): BelongsTo
    {
        return $this->belongsTo(SubcontractorModel::class, 'subcontractor_id');
    }

    public function assignment(): BelongsTo
    {
        return $this->belongsTo(SubcontractorAssignmentModel::class, 'assignment_id');
    }
}
