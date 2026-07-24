<?php

namespace App\Infrastructure\Persistence\Eloquent\GrowNet;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;

class UserBadge extends Model
{
    protected $fillable = [
        'user_id',
        'badge_code',
        'badge_name',
        'lp_reward',
        'earned_at',
    ];

    protected $casts = [
        'lp_reward' => 'integer',
        'earned_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Available badges configuration
     */
    public static function availableBadges(): array
    {
        return [
            'first_sale' => [
                'name' => 'First Sale',
                'description' => 'Make your first product sale',
                'lp_reward' => 50,
                'icon' => '🎯',
            ],
            'network_builder' => [
                'name' => 'Network Builder',
                'description' => 'Get 100 total referrals',
                'lp_reward' => 500,
                'icon' => '👥',
            ],
            'scholar' => [
                'name' => 'Scholar',
                'description' => 'Complete 20 courses',
                'lp_reward' => 300,
                'icon' => '📚',
            ],
            'mentor_master' => [
                'name' => 'Mentor Master',
                'description' => 'Help 10 people advance levels',
                'lp_reward' => 400,
                'icon' => '🎓',
            ],
            'consistent_champion' => [
                'name' => 'Consistent Champion',
                'description' => '12-month active streak',
                'lp_reward' => 1000,
                'icon' => '🔥',
            ],
            'sales_star' => [
                'name' => 'Sales Star',
                'description' => 'K50,000 in personal sales',
                'lp_reward' => 800,
                'icon' => '💰',
            ],
            'community_leader' => [
                'name' => 'Community Leader',
                'description' => '100 forum contributions',
                'lp_reward' => 200,
                'icon' => '💬',
            ],
            'project_pioneer' => [
                'name' => 'Project Pioneer',
                'description' => 'Launch an approved project',
                'lp_reward' => 500,
                'icon' => '🚀',
            ],
        ];
    }

    /**
     * Get badge configuration
     */
    public function getBadgeConfig(): ?array
    {
        return self::availableBadges()[$this->badge_code] ?? null;
    }
}
