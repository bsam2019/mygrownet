<?php

namespace App\Infrastructure\Persistence\Eloquent\CMS;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WorkerAllowanceModel extends Model
{
    protected $table = 'cms_worker_allowances';

    protected $fillable = [
        'worker_id',
        'allowance_type_id',
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

    public function allowanceType(): BelongsTo
    {
        return $this->belongsTo(AllowanceTypeModel::class, 'allowance_type_id');
    }
}
