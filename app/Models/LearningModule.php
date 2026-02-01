<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class LearningModule extends Model
{
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

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($module) {
            if (empty($module->slug)) {
                $module->slug = Str::slug($module->title);
            }
        });
    }

    public function completions(): HasMany
    {
        return $this->hasMany(LearningCompletion::class);
    }

    public function isCompletedBy(int $userId): bool
    {
        return $this->completions()->where('user_id', $userId)->exists();
    }

    public function getCompletionRate(): float
    {
        $totalUsers = User::count();
        if ($totalUsers === 0) return 0;
        
        $completions = $this->completions()->count();
        return ($completions / $totalUsers) * 100;
    }

    public static function getPublished()
    {
        return static::where('is_published', true)
            ->orderBy('sort_order')
            ->orderBy('created_at')
            ->get();
    }

    public static function getRequired()
    {
        return static::where('is_published', true)
            ->where('is_required', true)
            ->orderBy('sort_order')
            ->get();
    }
}
