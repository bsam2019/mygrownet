<?php

namespace App\Infrastructure\Persistence\Eloquent\StarterKit;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContentItemModel extends Model
{
    use HasFactory;

    protected $table = 'starter_kit_content_items';

    protected $fillable = [
        'title',
        'description',
        'category',
        'tier_restriction',
        'unlock_day',
        'file_path',
        'original_filename',
        'file_url',
        'file_type',
        'file_size',
        'is_downloadable',
        'access_duration_days',
        'thumbnail',
        'estimated_value',
        'download_count',
        'sort_order',
        'is_active',
        'last_updated_at',
    ];

    protected $casts = [
        'unlock_day' => 'integer',
        'file_size' => 'integer',
        'estimated_value' => 'integer',
        'download_count' => 'integer',
        'access_duration_days' => 'integer',
        'sort_order' => 'integer',
        'is_active' => 'boolean',
        'is_downloadable' => 'boolean',
        'last_updated_at' => 'datetime',
    ];

    public function getCategoryLabelAttribute(): string
    {
        return match($this->category) {
            'training' => 'Training Module',
            'ebook' => 'eBook',
            'video' => 'Video Tutorial',
            'tool' => 'Marketing Tool',
            'library' => 'Library Access',
            default => ucfirst($this->category),
        };
    }

    public function getUnlockDescriptionAttribute(): string
    {
        if ($this->unlock_day === 0) {
            return 'Immediate Access';
        }
        return "Unlocks Day {$this->unlock_day}";
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('unlock_day')->orderBy('title');
    }
}
