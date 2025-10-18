<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class Course extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'description',
        'learning_objectives',
        'category',
        'difficulty_level',
        'estimated_duration_minutes',
        'thumbnail_url',
        'required_subscription_packages',
        'required_membership_tiers',
        'is_premium',
        'certificate_eligible',
        'status',
        'created_by',
        'published_at',
        'order',
        'content_update_frequency',
        'last_content_update',
        'tier_specific_content',
        'monthly_content_schedule'
    ];

    protected $casts = [
        'required_subscription_packages' => 'array',
        'required_membership_tiers' => 'array',
        'is_premium' => 'boolean',
        'certificate_eligible' => 'boolean',
        'published_at' => 'date',
        'last_content_update' => 'datetime',
        'tier_specific_content' => 'array',
        'monthly_content_schedule' => 'array'
    ];

    protected $attributes = [
        'required_subscription_packages' => '[]',
        'required_membership_tiers' => '[]',
        'tier_specific_content' => '[]',
        'monthly_content_schedule' => '[]'
    ];

    // Relationships
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(CourseEnrollment::class);
    }

    public function lessons(): HasMany
    {
        return $this->hasMany(CourseLesson::class)->orderBy('order');
    }

    public function completions(): HasMany
    {
        return $this->hasMany(CourseCompletion::class);
    }

    // Scopes
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published');
    }

    public function scopeForTier(Builder $query, string $tierName): Builder
    {
        return $query->where(function ($q) use ($tierName) {
            $q->whereJsonContains('required_membership_tiers', $tierName)
              ->orWhereJsonLength('required_membership_tiers', 0);
        });
    }

    public function scopeByCategory(Builder $query, string $category): Builder
    {
        return $query->where('category', $category);
    }

    public function scopeByDifficulty(Builder $query, string $difficulty): Builder
    {
        return $query->where('difficulty_level', $difficulty);
    }

    // Business Logic Methods
    public function isAccessibleByTier(string $tierName): bool
    {
        // If no specific tiers required, accessible to all
        if (empty($this->required_membership_tiers)) {
            return true;
        }

        return in_array($tierName, $this->required_membership_tiers);
    }

    public function isAccessibleByUser(User $user): bool
    {
        // Check if user's current tier can access this course
        if ($user->currentTier) {
            return $this->isAccessibleByTier($user->currentTier->name);
        }

        return false;
    }

    public function getTierSpecificContent(string $tierName): array
    {
        return $this->tier_specific_content[$tierName] ?? [];
    }

    public function hasContentUpdateScheduled(): bool
    {
        $currentMonth = now()->format('Y-m');
        return isset($this->monthly_content_schedule[$currentMonth]);
    }

    public function getScheduledContentForMonth(string $month = null): array
    {
        $month = $month ?? now()->format('Y-m');
        return $this->monthly_content_schedule[$month] ?? [];
    }

    public function needsContentUpdate(): bool
    {
        if (!$this->last_content_update) {
            return true;
        }

        // Check if content should be updated based on frequency
        $frequency = $this->content_update_frequency ?? 'monthly';
        $lastUpdate = $this->last_content_update;

        return match ($frequency) {
            'weekly' => $lastUpdate->diffInWeeks(now()) >= 1,
            'monthly' => $lastUpdate->diffInMonths(now()) >= 1,
            'quarterly' => $lastUpdate->diffInMonths(now()) >= 3,
            default => false
        };
    }

    public function markContentUpdated(): void
    {
        $this->update(['last_content_update' => now()]);
    }

    // Static methods for tier-based content management
    public static function getAvailableCategories(): array
    {
        return [
            'financial_literacy' => 'Financial Literacy',
            'business_skills' => 'Business Skills', 
            'life_skills' => 'Life Skills',
            'investment_strategies' => 'Investment Strategies',
            'mlm_training' => 'MLM Training',
            'leadership_development' => 'Leadership Development'
        ];
    }

    public static function getDifficultyLevels(): array
    {
        return [
            'beginner' => 'Beginner',
            'intermediate' => 'Intermediate',
            'advanced' => 'Advanced'
        ];
    }

    public static function getContentUpdateFrequencies(): array
    {
        return [
            'weekly' => 'Weekly',
            'monthly' => 'Monthly',
            'quarterly' => 'Quarterly'
        ];
    }

    public static function getTierBasedContentStructure(): array
    {
        return [
            'Bronze' => [
                'categories' => ['financial_literacy', 'life_skills'],
                'max_difficulty' => 'beginner',
                'content_types' => ['ebooks', 'templates', 'basic_tips'],
                'monthly_content_limit' => 5
            ],
            'Silver' => [
                'categories' => ['financial_literacy', 'life_skills', 'business_skills'],
                'max_difficulty' => 'intermediate',
                'content_types' => ['ebooks', 'templates', 'videos', 'webinars'],
                'monthly_content_limit' => 10
            ],
            'Gold' => [
                'categories' => ['financial_literacy', 'business_skills', 'investment_strategies', 'mlm_training'],
                'max_difficulty' => 'advanced',
                'content_types' => ['videos', 'webinars', 'courses', 'toolkits'],
                'monthly_content_limit' => 15
            ],
            'Diamond' => [
                'categories' => ['business_skills', 'investment_strategies', 'mlm_training', 'leadership_development'],
                'max_difficulty' => 'advanced',
                'content_types' => ['advanced_courses', 'toolkits', 'mentorship_content'],
                'monthly_content_limit' => 20
            ],
            'Elite' => [
                'categories' => ['investment_strategies', 'leadership_development', 'business_skills'],
                'max_difficulty' => 'advanced',
                'content_types' => ['vip_mentorship', 'innovation_lab', 'exclusive_content'],
                'monthly_content_limit' => 25
            ]
        ];
    }
}