<?php

namespace App\Infrastructure\Persistence\Eloquent\CMS;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DeductionTypeModel extends Model
{
    protected $table = 'cms_deduction_types';

    protected $fillable = [
        'company_id',
        'deduction_name',
        'deduction_code',
        'calculation_type',
        'default_amount',
        'default_percentage',
        'is_statutory',
        'is_active',
    ];

    protected $casts = [
        'default_amount' => 'decimal:2',
        'default_percentage' => 'decimal:2',
        'is_statutory' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(CompanyModel::class, 'company_id');
    }

    public function workerDeductions(): HasMany
    {
        return $this->hasMany(WorkerDeductionModel::class, 'deduction_type_id');
    }
}
