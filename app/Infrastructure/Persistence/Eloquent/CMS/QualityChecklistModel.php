<?php

namespace App\Infrastructure\Persistence\Eloquent\CMS;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class QualityChecklistModel extends Model
{
    use SoftDeletes;

    protected $table = 'cms_quality_checklists';

    protected $fillable = [
        'company_id',
        'name',
        'description',
        'checklist_type',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(CompanyModel::class, 'company_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(QualityChecklistItemModel::class, 'checklist_id');
    }

    public function inspections(): HasMany
    {
        return $this->hasMany(QualityInspectionModel::class, 'checklist_id');
    }
}
