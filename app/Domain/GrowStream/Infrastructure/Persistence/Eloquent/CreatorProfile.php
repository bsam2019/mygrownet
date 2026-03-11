<?php

namespace App\Domain\GrowStream\Infrastructure\Persistence\Eloquent;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CreatorProfile extends Model
{
    use HasFactory;

    protected $table = 'growstream_creator_profiles';

    protected $fillable = [
        'user_id',
        'channel_name',
        'display_name',
        'bio',
        'avatar_url',
        'banner_url',
        'website_url',
        'facebook_url',
        'twitter_url',
        'instagram_url',
        'youtube_url',
        'status',
        'is_verified',
        'is_active',
        'verified_at',
        'creator_tier',
        'total_videos',
        'total_views',
        'subscriber_count',
        'revenue_share_percentage',
        'total_earnings',
        'pending_payout',
        'can_upload',
        'upload_limit_per_month',
    ];

    protected $casts = [
        'is_verified' => 'boolean',
        'is_active' => 'boolean',
        'can_upload' => 'boolean',
        'verified_at' => 'datetime',
        'revenue_share_percentage' => 'decimal:2',
        'total_earnings' => 'decimal:2',
        'pending_payout' => 'decimal:2',
    ];

    // Relationships
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function videos(): HasMany
    {
        return $this->hasMany(Video::class, 'creator_id');
    }

    public function series(): HasMany
    {
        return $this->hasMany(VideoSeries::class, 'creator_id');
    }

    // Scopes
    public function scopeVerified($query)
    {
        return $query->where('is_verified', true);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByTier($query, string $tier)
    {
        return $query->where('creator_tier', $tier);
    }

    // Helper Methods
    public function canUploadMore(): bool
    {
        if (!$this->can_upload || !$this->is_active) {
            return false;
        }

        $uploadsThisMonth = $this->videos()
            ->whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->count();

        return $uploadsThisMonth < $this->upload_limit_per_month;
    }

    public function updateStatistics(): void
    {
        $this->total_videos = $this->videos()->count();
        $this->total_views = $this->videos()->sum('view_count');
        $this->save();
    }

    public function addEarnings(float $amount): void
    {
        $this->pending_payout += $amount;
        $this->save();
    }

    public function processPayout(float $amount): void
    {
        $this->pending_payout -= $amount;
        $this->total_earnings += $amount;
        $this->save();
    }
}
