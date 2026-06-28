<?php

namespace App\Infrastructure\Persistence\Eloquent\CMS;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PerformanceCriteriaModel extends Model
{
    protected $table = 'cms_performance_criteria';

    protected $fillable = [
        'company_id',
        'criteria_name',
        'description',
        'category',
        'weight_percentage',
        'is_active',
    ];

    protected $casts = [
        'weight_percentage' => 'integer',
        'is_active' => 'boolean',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(CompanyModel::class, 'company_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
