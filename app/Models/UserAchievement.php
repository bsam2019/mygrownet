<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class UserAchievement extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'achievement_id',
        'earned_at',
        'progress',
        'times_earned',
        'tier_at_earning',
        'earning_context',
        'is_celebrated',
        'is_shared'
    ];

    protected $casts = [
        'earned_at' => 'datetime',
        'progress' => 'decimal:2',
        'earning_context' => 'array',
        'is_celebrated' => 'boolean',
        'is_shared' => 'boolean'
    ];

    protected $attributes = [
        'earning_context' => '[]'
    ];

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function achievement(): BelongsTo
    {
        return $this->belongsTo(Achievement::class);
    }

    // Scopes
    public function scopeForUser(Builder $query, User $user): Builder
    {
        return $query->where('user_id', $user->id);
    }

    public function scopeRecent(Builder $query, int $days = 30): Builder
    {
        return $query->where('earned_at', '>=', now()->subDays($days));
    }

    public function scopeByCategory(Builder $query, string $category): Builder
    {
        return $query->whereHas('achievement', function ($q) use ($category) {
            $q->where('category', $category);
        });
    }

    public function scopeByTier(Builder $query, string $tier): Builder
    {
        return $query->where('tier_at_earning', $tier);
    }

    // Business Logic Methods
    public function markAsCelebrated(): void
    {
        $this->update(['is_celebrated' => true]);
    }

    public function markAsShared(): void
    {
        $this->update(['is_shared' => true]);
    }

    public function getShareMessage(): string
    {
        $achievement = $this->achievement;
        
        if ($achievement->share_message) {
            return str_replace(
                ['{user_name}', '{achievement_name}', '{tier}'],
                [$this->user->name, $achievement->name, $this->tier_at_earning],
                $achievement->share_message
            );
        }

        return "I just earned the '{$achievement->name}' achievement in MyGrowNet! 🎉";
    }

    public function getAchievementSummary(): array
    {
        return [
            'achievement_id' => $this->achievement_id,
            'achievement_name' => $this->achievement->name,
            'achievement_description' => $this->achievement->description,
            'category' => $this->achievement->category,
            'badge_icon' => $this->achievement->badge_icon,
            'badge_color' => $this->achievement->badge_color,
            'points' => $this->achievement->points,
            'difficulty_level' => $this->achievement->difficulty_level,
            'earned_at' => $this->earned_at,
            'tier_at_earning' => $this->tier_at_earning,
            'times_earned' => $this->times_earned,
            'progress' => $this->progress,
            'is_celebrated' => $this->is_celebrated,
            'is_shared' => $this->is_shared,
            'earning_context' => $this->earning_context
        ];
    }
}