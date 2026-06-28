<?php

namespace App\Infrastructure\Persistence\Eloquent\CMS;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkerDeductionModel extends Model
{
    protected $table = 'cms_worker_deductions';

    protected $fillable = [
        'worker_id',
        'deduction_type_id',
        'amount',
        'effective_from',
        'effective_to',
        'is_active',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'effective_from' => 'date',
        'effective_to' => 'date',
        'is_active' => 'boolean',
    ];

    public function worker(): BelongsTo
    {
        return $this->belongsTo(WorkerModel::class, 'worker_id');
    }

    public function deductionType(): BelongsTo
    {
        return $this->belongsTo(DeductionTypeModel::class, 'deduction_type_id');
    }
}
