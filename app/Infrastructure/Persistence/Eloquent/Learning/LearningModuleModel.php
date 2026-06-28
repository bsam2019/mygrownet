<?php

namespace App\Infrastructure\Persistence\Eloquent\Learning;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LearningModuleModel extends Model
{
    protected $table = 'learning_modules';

    protected $fillable = [
        'title',
        'slug',
        'description',
        'content',
        'content_type',
        'video_url',
        'estimated_minutes',
        'category',
        'sort_order',
        'is_published',
        'is_required',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'is_required' => 'boolean',
        'estimated_minutes' => 'integer',
        'sort_order' => 'integer',
    ];

    public function completions(): HasMany
    {
        return $this->hasMany(LearningCompletionModel::class, 'learning_module_id');
    }

    public function scopePublished($query)
    {
        return $query->where('is_published', true);
    }

    public function scopeRequired($query)
    {
        return $query->where('is_required', true);
    }

    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }
}
