<?php

namespace App\Infrastructure\Persistence\Eloquent\CMS;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SkillModel extends Model
{
    protected $table = 'cms_skills';

    protected $fillable = [
        'company_id',
        'name',
        'description',
        'category',
        'level_required',
        'is_core',
    ];

    protected $casts = [
        'is_core' => 'boolean',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(CompanyModel::class, 'company_id');
    }

    public function workerSkills(): HasMany
    {
        return $this->hasMany(WorkerSkillModel::class, 'skill_id');
    }

    public function scopeCore($query)
    {
        return $query->where('is_core', true);
    }

    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }
}
