<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LibraryResource extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'type',
        'category',
        'resource_url',
        'thumbnail',
        'author',
        'duration_minutes',
        'difficulty',
        'is_external',
        'is_featured',
        'is_active',
        'sort_order',
        'view_count',
    ];

    protected $casts = [
        'duration_minutes' => 'integer',
        'is_external' => 'boolean',
        'is_featured' => 'boolean',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
        'view_count' => 'integer',
    ];

    public function accesses(): HasMany
    {
        return $this->hasMany(LibraryResourceAccess::class);
    }

    public function getTypeLabelAttribute(): string
    {
        return match($this->type) {
            'pdf' => 'PDF Document',
            'video' => 'Video',
            'article' => 'Article',
            'course' => 'Online Course',
            'tool' => 'Tool/Template',
            'template' => 'Template',
            default => ucfirst($this->type),
        };
    }

    public function getCategoryLabelAttribute(): string
    {
        return match($this->category) {
            'business' => 'Business Fundamentals',
            'marketing' => 'Marketing & Sales',
            'finance' => 'Financial Management',
            'leadership' => 'Leadership',
            'personal_development' => 'Personal Development',
            'network_building' => 'Network Building',
            default => ucfirst(str_replace('_', ' ', $this->category)),
        };
    }

    public function getDifficultyLabelAttribute(): string
    {
        return ucfirst($this->difficulty);
    }

    public function getDurationFormattedAttribute(): ?string
    {
        if (!$this->duration_minutes) {
            return null;
        }

        if ($this->duration_minutes < 60) {
            return $this->duration_minutes . ' min';
        }

        $hours = floor($this->duration_minutes / 60);
        $minutes = $this->duration_minutes % 60;
        
        return $minutes > 0 ? "{$hours}h {$minutes}m" : "{$hours}h";
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('title');
    }

    public function incrementViewCount(): void
    {
        $this->increment('view_count');
    }
}
