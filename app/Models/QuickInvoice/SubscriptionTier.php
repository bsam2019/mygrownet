<?php

namespace App\Models\QuickInvoice;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SubscriptionTier extends Model
{
    protected $table = 'quick_invoice_subscription_tiers';

    protected $fillable = [
        'name',
        'price',
        'currency',
        'documents_per_month',
        'features',
        'is_active',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'documents_per_month' => 'integer',
        'features' => 'array',
        'is_active' => 'boolean',
    ];

    public function userSubscriptions(): HasMany
    {
        return $this->hasMany(UserSubscription::class, 'tier_id');
    }

    /**
     * Get the default free tier
     */
    public static function getFreeTier(): self
    {
        return self::where('name', 'Free')->where('is_active', true)->first()
            ?? self::create([
                'name' => 'Free',
                'price' => 0,
                'currency' => 'ZMW',
                'documents_per_month' => -1, // Unlimited for now
                'features' => [
                    'templates' => 'all', // All templates available
                    'sharing' => ['pdf_download', 'email', 'whatsapp'],
                    'watermark' => false,
                    'customization' => true,
                    'api_access' => true,
                    'priority_support' => true,
                    'custom_branding' => true,
                    'advanced_templates' => true,
                    'custom_fields' => true,
                    'design_studio' => true, // Design Studio available
                    'white_label' => true,
                    'advanced_analytics' => true,
                    'cms_integration' => true,
                ],
                'is_active' => true,
            ]);
    }

    /**
     * Check if tier has feature
     */
    public function hasFeature(string $feature): bool
    {
        $features = $this->features ?? [];
        return isset($features[$feature]) && $features[$feature] !== false;
    }

    /**
     * Get allowed templates for this tier
     */
    public function getAllowedTemplates(): array
    {
        $templates = $this->features['templates'] ?? ['classic'];
        
        // If 'all' templates are allowed, return all available templates
        if ($templates === 'all') {
            return array_keys(\App\Services\QuickInvoice\TemplateService::getTemplates());
        }
        
        return is_array($templates) ? $templates : ['classic'];
    }

    /**
     * Get formatted price
     */
    public function getFormattedPriceAttribute(): string
    {
        if ($this->price == 0) {
            return 'Free';
        }
        
        $symbol = match($this->currency) {
            'ZMW' => 'K',
            'USD' => '$',
            'EUR' => '€',
            'GBP' => '£',
            default => $this->currency,
        };
        
        return $symbol . number_format($this->price, 0);
    }
}