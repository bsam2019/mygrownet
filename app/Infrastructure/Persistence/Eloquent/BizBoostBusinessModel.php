<?php

namespace App\Infrastructure\Persistence\Eloquent;

use Database\Factories\BizBoostBusinessFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class BizBoostBusinessModel extends Model
{
    use HasFactory;

    protected $table = 'bizboost_businesses';

    protected static function newFactory(): BizBoostBusinessFactory
    {
        return BizBoostBusinessFactory::new();
    }

    protected $fillable = [
        'user_id',
        'name',
        'slug',
        'description',
        'logo_path',
        'industry',
        'address',
        'city',
        'province',
        'country',
        'phone',
        'whatsapp',
        'email',
        'website',
        'timezone',
        'locale',
        'currency',
        'social_links',
        'business_hours',
        'settings',
        'white_label_config',
        'is_active',
        'onboarding_completed',
        'marketplace_listed',
        'marketplace_listed_at',
    ];

    protected $casts = [
        'social_links' => 'array',
        'business_hours' => 'array',
        'settings' => 'array',
        'white_label_config' => 'array',
        'is_active' => 'boolean',
        'onboarding_completed' => 'boolean',
        'marketplace_listed' => 'boolean',
        'marketplace_listed_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    public function profile(): HasOne
    {
        return $this->hasOne(BizBoostBusinessProfileModel::class, 'business_id');
    }

    public function products(): HasMany
    {
        return $this->hasMany(BizBoostProductModel::class, 'business_id');
    }

    public function customers(): HasMany
    {
        return $this->hasMany(BizBoostCustomerModel::class, 'business_id');
    }

    public function sales(): HasMany
    {
        return $this->hasMany(BizBoostSaleModel::class, 'business_id');
    }

    public function posts(): HasMany
    {
        return $this->hasMany(BizBoostPostModel::class, 'business_id');
    }

    public function campaigns(): HasMany
    {
        return $this->hasMany(BizBoostCampaignModel::class, 'business_id');
    }

    public function integrations(): HasMany
    {
        return $this->hasMany(BizBoostIntegrationModel::class, 'business_id');
    }

    public function customTemplates(): HasMany
    {
        return $this->hasMany(BizBoostCustomTemplateModel::class, 'business_id');
    }

    public function aiUsageLogs(): HasMany
    {
        return $this->hasMany(BizBoostAiUsageLogModel::class, 'business_id');
    }

    public function analyticsEvents(): HasMany
    {
        return $this->hasMany(BizBoostAnalyticsEventModel::class, 'business_id');
    }

    public function locations(): HasMany
    {
        return $this->hasMany(BizBoostLocationModel::class, 'business_id');
    }

    public function teamMembers(): HasMany
    {
        return $this->hasMany(BizBoostTeamMemberModel::class, 'business_id');
    }

    public function qrCodes(): HasMany
    {
        return $this->hasMany(BizBoostQrCodeModel::class, 'business_id');
    }

    public function customerTags(): HasMany
    {
        return $this->hasMany(BizBoostCustomerTagModel::class, 'business_id');
    }

    public function categories(): HasMany
    {
        return $this->hasMany(BizBoostCategoryModel::class, 'business_id');
    }
}
