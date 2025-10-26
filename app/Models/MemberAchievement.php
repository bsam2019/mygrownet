<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MemberAchievement extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'achievement_type',
        'achievement_name',
        'description',
        'badge_icon',
        'badge_color',
        'earned_at',
        'certificate_url',
        'is_displayed',
    ];

    protected $casts = [
        'earned_at' => 'datetime',
        'is_displayed' => 'boolean',
    ];

    /**
     * Achievement types and their details.
     */
    public const ACHIEVEMENTS = [
        'profile_completed' => [
            'name' => 'Profile Complete',
            'description' => 'Completed your profile',
            'icon' => '👤',
            'color' => 'blue',
            'lp_reward' => 25,
        ],
        'first_video_watched' => [
            'name' => 'First Video Watched',
            'description' => 'Watched your first tutorial video',
            'icon' => '🎥',
            'color' => 'purple',
            'bp_reward' => 10,
        ],
        'first_purchase' => [
            'name' => 'First Purchase',
            'description' => 'Made your first shop purchase',
            'icon' => '🛒',
            'color' => 'green',
            'bp_reward' => 20,
        ],
        'first_referral' => [
            'name' => 'First Referral',
            'description' => 'Invited your first member',
            'icon' => '🌟',
            'color' => 'gold',
            'bp_reward' => 100,
        ],
        'starter_graduate' => [
            'name' => 'Starter Graduate',
            'description' => 'Completed all starter kit modules',
            'icon' => '🎓',
            'color' => 'indigo',
            'lp_reward' => 50,
        ],
        'first_earner' => [
            'name' => 'First Earner',
            'description' => 'Received your first commission',
            'icon' => '💰',
            'color' => 'emerald',
        ],
        'level_up' => [
            'name' => 'Level Up',
            'description' => 'Advanced to Professional level',
            'icon' => '📈',
            'color' => 'blue',
            'lp_reward' => 100,
        ],
        'top_performer' => [
            'name' => 'Top Performer',
            'description' => 'Reached monthly targets',
            'icon' => '🏆',
            'color' => 'amber',
        ],
    ];

    /**
     * Get the user.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get achievement details.
     */
    public function getDetails(): array
    {
        return self::ACHIEVEMENTS[$this->achievement_type] ?? [];
    }

    /**
     * Hide achievement from display.
     */
    public function hide(): void
    {
        $this->update(['is_displayed' => false]);
    }

    /**
     * Show achievement in display.
     */
    public function show(): void
    {
        $this->update(['is_displayed' => true]);
    }

    /**
     * Scope for displayed achievements.
     */
    public function scopeDisplayed($query)
    {
        return $query->where('is_displayed', true);
    }

    /**
     * Scope by achievement type.
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('achievement_type', $type);
    }

    /**
     * Scope for recent achievements.
     */
    public function scopeRecent($query, int $days = 30)
    {
        return $query->where('earned_at', '>=', now()->subDays($days));
    }
}
