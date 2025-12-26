<?php

namespace App\Infrastructure\GrowBuilder\Models;

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
        'published_at',
        'plan_expires_at',
    ];

    protected $casts = [
        'settings' => 'array',
        'theme' => 'array',
        'social_links' => 'array',
        'contact_info' => 'array',
        'business_hours' => 'array',
        'seo_settings' => 'array',
        'published_at' => 'datetime',
        'plan_expires_at' => 'datetime',
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
}
