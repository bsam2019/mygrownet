<?php

namespace App\Infrastructure\GrowBuilder\Models;

use App\Models\MarketplaceSeller;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class GrowBuilderSite extends Model
{
    use SoftDeletes;

    protected $table = 'growbuilder_sites';

    protected $fillable = [
        'user_id',
        'marketplace_seller_id',
        'marketplace_enabled',
        'marketplace_linked_at',
        'template_id',
        'name',
        'subdomain',
        'custom_domain',
        'description',
        'logo',
        'favicon',
        'settings',
        'theme',
        'social_links',
        'contact_info',
        'business_hours',
        'seo_settings',
        'status',
        'plan',
        'storage_used',
        'storage_limit',
        'storage_calculated_at',
        'published_at',
        'plan_expires_at',
        'scheduled_deletion_at',
        'deletion_reason',
    ];

    protected $casts = [
        'settings' => 'array',
        'theme' => 'array',
        'social_links' => 'array',
        'contact_info' => 'array',
        'business_hours' => 'array',
        'seo_settings' => 'array',
        'marketplace_enabled' => 'boolean',
        'marketplace_linked_at' => 'datetime',
        'published_at' => 'datetime',
        'plan_expires_at' => 'datetime',
        'scheduled_deletion_at' => 'datetime',
        'storage_calculated_at' => 'datetime',
        'storage_used' => 'integer',
        'storage_limit' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(GrowBuilderTemplate::class, 'template_id');
    }

    public function pages(): HasMany
    {
        return $this->hasMany(GrowBuilderPage::class, 'site_id');
    }

    public function media(): HasMany
    {
        return $this->hasMany(GrowBuilderMedia::class, 'site_id');
    }

    public function forms(): HasMany
    {
        return $this->hasMany(GrowBuilderForm::class, 'site_id');
    }

    public function pageViews(): HasMany
    {
        return $this->hasMany(GrowBuilderPageView::class, 'site_id');
    }

    public function siteUsers(): HasMany
    {
        return $this->hasMany(SiteUser::class, 'site_id');
    }

    public function siteRoles(): HasMany
    {
        return $this->hasMany(SiteRole::class, 'site_id');
    }

    public function sitePosts(): HasMany
    {
        return $this->hasMany(SitePost::class, 'site_id');
    }

    public function marketplaceSeller(): BelongsTo
    {
        return $this->belongsTo(MarketplaceSeller::class, 'marketplace_seller_id');
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    public function scopeByUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function getUrlAttribute(): string
    {
        if ($this->custom_domain) {
            $scheme = app()->environment('local') ? 'http' : 'https';
            return $scheme . '://' . $this->custom_domain;
        }
        
        // For local development, use APP_URL with subdomain simulation
        if (app()->environment('local', 'development')) {
            $appUrl = config('app.url', 'http://127.0.0.1:8001');
            return $appUrl . '/sites/' . $this->subdomain;
        }
        
        return 'https://' . $this->subdomain . '.mygrownet.com';
    }

    public function isPublished(): bool
    {
        return $this->status === 'published';
    }

    public function isPlanExpired(): bool
    {
        if (!$this->plan_expires_at) {
            return false;
        }
        return $this->plan_expires_at->isPast();
    }

    public function getStoragePercentageAttribute(): float
    {
        if ($this->storage_limit <= 0) {
            return 100;
        }
        return min(100, round(($this->storage_used / $this->storage_limit) * 100, 2));
    }

    public function getStorageUsedFormattedAttribute(): string
    {
        return $this->formatBytes($this->storage_used ?? 0);
    }

    public function getStorageLimitFormattedAttribute(): string
    {
        return $this->formatBytes($this->storage_limit ?? 0);
    }

    public function getRemainingStorageAttribute(): int
    {
        return max(0, ($this->storage_limit ?? 0) - ($this->storage_used ?? 0));
    }

    public function getRemainingStorageFormattedAttribute(): string
    {
        return $this->formatBytes($this->remaining_storage);
    }

    public function isStorageNearLimit(): bool
    {
        return $this->storage_percentage >= 80;
    }

    public function isStorageOverLimit(): bool
    {
        return ($this->storage_used ?? 0) > ($this->storage_limit ?? 0);
    }

    public function hasMarketplaceIntegration(): bool
    {
        return $this->marketplace_enabled && $this->marketplace_seller_id !== null;
    }

    public function canEnableMarketplace(): bool
    {
        // Can enable if user has a marketplace seller account
        return $this->user->marketplaceSeller()->exists();
    }

    private function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        if ($bytes <= 0) {
            return '0 B';
        }

        $pow = floor(log($bytes) / log(1024));
        $pow = min($pow, count($units) - 1);

        return round($bytes / pow(1024, $pow), $precision) . ' ' . $units[$pow];
    }
}
