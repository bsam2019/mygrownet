<?php

namespace App\Infrastructure\Persistence\Eloquent\CMS;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class TrainingProgramModel extends Model
{
    use SoftDeletes;

    protected $table = 'cms_training_programs';

    protected $fillable = [
        'company_id',
        'title',
        'description',
        'type',
        'category',
        'level',
        'duration_hours',
        'cost',
        'provider',
        'location',
        'max_participants',
        'prerequisites',
        'learning_objectives',
        'materials',
        'status',
        'created_by',
    ];

    protected $casts = [
        'cost' => 'decimal:2',
        'materials' => 'array',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(CompanyModel::class, 'company_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(UserModel::class, 'created_by');
    }

    public function sessions(): HasMany
    {
        return $this->hasMany(TrainingSessionModel::class, 'program_id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }
}
