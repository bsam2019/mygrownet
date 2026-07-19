<?php

namespace App\Infrastructure\Persistence\Eloquent\CMS;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AllowanceTypeModel extends Model
{
    protected $table = 'cms_allowance_types';

    protected $fillable = [
        'company_id',
        'allowance_name',
        'allowance_code',
        'calculation_type',
        'default_amount',
        'is_taxable',
        'is_pensionable',
        'is_active',
    ];

    protected $casts = [
        'default_amount' => 'decimal:2',
        'is_taxable' => 'boolean',
        'is_pensionable' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(CompanyModel::class, 'company_id');
    }

    public function workerAllowances(): HasMany
    {
        return $this->hasMany(WorkerAllowanceModel::class, 'allowance_type_id');
    }
}
