<?php

namespace App\Infrastructure\Persistence\Eloquent\CMS;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CuttingPatternModel extends Model
{
    protected $table = 'cms_cutting_patterns';

    protected $fillable = [
        'company_id',
        'material_id',
        'pattern_name',
        'stock_length',
        'cuts',
        'total_used',
        'waste',
        'efficiency_percentage',
        'usage_count',
        'is_template',
    ];

    protected $casts = [
        'stock_length' => 'decimal:2',
        'cuts' => 'array',
        'total_used' => 'decimal:2',
        'waste' => 'decimal:2',
        'efficiency_percentage' => 'decimal:2',
        'is_template' => 'boolean',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(CompanyModel::class, 'company_id');
    }

    public function material(): BelongsTo
    {
        return $this->belongsTo(MaterialModel::class, 'material_id');
    }
}
