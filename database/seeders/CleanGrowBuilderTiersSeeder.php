<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * One-time seeder to clean up old GrowBuilder tiers and seed new ones
 * 
 * This seeder creates tiers with both boolean features AND numeric limits.
 * The limits are used by TierRestrictionService and AIUsageService.
 */
class CleanGrowBuilderTiersSeeder extends Seeder
{
    public function run(): void
    {
        $moduleId = 'growbuilder';
        
        // Step 1: Delete ALL existing GrowBuilder tier features
        $tierIds = DB::table('module_tiers')
            ->where('module_id', $moduleId)
            ->pluck('id');
        
        if ($tierIds->isNotEmpty()) {
            $deleted = DB::table('module_tier_features')
                ->whereIn('module_tier_id', $tierIds)
                ->delete();
            $this->command->info("Deleted {$deleted} tier features");
        }
        
        // Step 2: Delete ALL existing GrowBuilder tiers
        $deletedTiers = DB::table('module_tiers')
            ->where('module_id', $moduleId)
            ->delete();
        $this->command->info("Deleted {$deletedTiers} tiers");
        
        // Step 3: Insert new tiers with storage_limit_mb
        $tiers = [
            [
                'module_id' => $moduleId,
                'tier_key' => 'free',
                'name' => 'Free',
                'description' => 'For testing and exploration',
                'price_monthly' => 0,
                'price_annual' => 0,
                'storage_limit_mb' => 500,
                'is_active' => true,
                'is_default' => true,
                'is_popular' => false,
                'sort_order' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'module_id' => $moduleId,
                'tier_key' => 'starter',
                'name' => 'Starter',
                'description' => 'For small businesses',
                'price_monthly' => 120,
                'price_annual' => 1200,
                'storage_limit_mb' => 1024,
                'is_active' => true,
                'is_default' => false,
                'is_popular' => false,
                'sort_order' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'module_id' => $moduleId,
                'tier_key' => 'business',
                'name' => 'Business',
                'description' => 'For growing businesses',
                'price_monthly' => 350,
                'price_annual' => 3500,
                'storage_limit_mb' => 2048,
                'is_active' => true,
                'is_default' => false,
                'is_popular' => true,
                'sort_order' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'module_id' => $moduleId,
                'tier_key' => 'agency',
                'name' => 'Agency',
                'description' => 'For agencies & resellers',
                'price_monthly' => 900,
                'price_annual' => 9000,
                'storage_limit_mb' => 10240,
                'is_active' => true,
                'is_default' => false,
                'is_popular' => false,
                'sort_order' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];
        
        foreach ($tiers as $tier) {
            $tierId = DB::table('module_tiers')->insertGetId($tier);
            $this->command->info("Created tier: {$tier['name']} (ID: {$tierId})");
            
            // Insert boolean features for this tier
            $features = $this->getFeaturesForTier($tier['tier_key']);
            foreach ($features as $feature) {
                DB::table('module_tier_features')->insert([
                    'module_tier_id' => $tierId,
                    'feature_key' => $feature['key'],
                    'feature_name' => $feature['name'],
                    'feature_type' => 'boolean',
                    'value_boolean' => $feature['value'],
                    'value_limit' => null,
                    'value_text' => null,
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
            
            // Insert limit-type features for this tier
            $limits = $this->getLimitsForTier($tier['tier_key']);
            foreach ($limits as $limit) {
                DB::table('module_tier_features')->insert([
                    'module_tier_id' => $tierId,
                    'feature_key' => $limit['key'],
                    'feature_name' => $limit['name'],
                    'feature_type' => 'limit',
                    'value_boolean' => false,
                    'value_limit' => $limit['value'],
                    'value_text' => '',
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
            
            $this->command->info("  Added " . count($features) . " features + " . count($limits) . " limits");
        }
        
        // Clear cache
        \Illuminate\Support\Facades\Cache::forget("module_tiers:{$moduleId}");
        $this->command->info("Cache cleared");
    }
    
    /**
     * Get numeric limits for a tier
     * -1 = unlimited, 0 = not available
     */
    private function getLimitsForTier(string $tierKey): array
    {
        $limits = [
            'free' => [
                ['key' => 'sites', 'name' => 'Sites limit', 'value' => 1],
                ['key' => 'products', 'name' => 'Products limit', 'value' => 0],
                ['key' => 'ai_prompts', 'name' => 'AI prompts per month', 'value' => 5],
                ['key' => 'storage_mb', 'name' => 'Storage (MB)', 'value' => 500],
            ],
            'starter' => [
                ['key' => 'sites', 'name' => 'Sites limit', 'value' => 1],
                ['key' => 'products', 'name' => 'Products limit', 'value' => 20],
                ['key' => 'ai_prompts', 'name' => 'AI prompts per month', 'value' => 100],
                ['key' => 'storage_mb', 'name' => 'Storage (MB)', 'value' => 1024],
            ],
            'business' => [
                ['key' => 'sites', 'name' => 'Sites limit', 'value' => 1],
                ['key' => 'products', 'name' => 'Products limit', 'value' => -1], // unlimited
                ['key' => 'ai_prompts', 'name' => 'AI prompts per month', 'value' => -1], // unlimited
                ['key' => 'storage_mb', 'name' => 'Storage (MB)', 'value' => 2048],
            ],
            'agency' => [
                ['key' => 'sites', 'name' => 'Sites limit', 'value' => 20],
                ['key' => 'products', 'name' => 'Products limit', 'value' => -1], // unlimited
                ['key' => 'ai_prompts', 'name' => 'AI prompts per month', 'value' => -1], // unlimited
                ['key' => 'storage_mb', 'name' => 'Storage (MB)', 'value' => 10240],
            ],
        ];
        
        return $limits[$tierKey] ?? [];
    }
    
    private function getFeaturesForTier(string $tierKey): array
    {
        $features = [
            'free' => [
                ['key' => 'websites', 'name' => '1 website', 'value' => true],
                ['key' => 'subdomain', 'name' => 'Subdomain: username.mygrownet.com', 'value' => true],
                ['key' => 'templates', 'name' => 'Limited templates', 'value' => true],
                ['key' => 'storage', 'name' => '500MB storage', 'value' => true],
                ['key' => 'ai_prompts', 'name' => 'AI content (5 prompts/month)', 'value' => true],
                ['key' => 'custom_domain', 'name' => 'Custom domain', 'value' => false],
                ['key' => 'payment_integration', 'name' => 'Payment integration', 'value' => false],
                ['key' => 'ecommerce', 'name' => 'E-commerce', 'value' => false],
            ],
            'starter' => [
                ['key' => 'websites', 'name' => '1 website', 'value' => true],
                ['key' => 'custom_domain', 'name' => 'Custom domain (buy or bring your own)', 'value' => true],
                ['key' => 'products', 'name' => 'Up to 20 products (basic e-commerce)', 'value' => true],
                ['key' => 'email_support', 'name' => 'Basic email support', 'value' => true],
                ['key' => 'manual_payments', 'name' => 'Manual/offline payments only', 'value' => true],
                ['key' => 'shared_smtp', 'name' => 'Shared SMTP (200 emails/month)', 'value' => true],
                ['key' => 'ai_prompts', 'name' => 'AI content (100 prompts/month)', 'value' => true],
                ['key' => 'section_generator', 'name' => 'AI section generator', 'value' => true],
                ['key' => 'full_payment_gateways', 'name' => 'Full payment gateways', 'value' => false],
                ['key' => 'marketing_tools', 'name' => 'Marketing tools', 'value' => false],
                ['key' => 'ai_unlimited', 'name' => 'Unlimited AI prompts', 'value' => false],
            ],
            'business' => [
                ['key' => 'websites', 'name' => '1 website', 'value' => true],
                ['key' => 'free_domain', 'name' => 'Free domain after 3 months prepayment', 'value' => true],
                ['key' => 'unlimited_products', 'name' => 'Unlimited store products', 'value' => true],
                ['key' => 'payment_integrations', 'name' => 'Full payment integrations (MTN, Airtel, Visa)', 'value' => true],
                ['key' => 'marketing_tools', 'name' => 'Marketing tools & email campaigns', 'value' => true],
                ['key' => 'priority_support', 'name' => 'Priority support', 'value' => true],
                ['key' => 'remove_branding', 'name' => 'Remove MyGrowNet branding', 'value' => true],
                ['key' => 'own_smtp', 'name' => 'Connect own SMTP', 'value' => true],
                ['key' => 'ai_unlimited', 'name' => 'Unlimited AI prompts', 'value' => true],
                ['key' => 'ai_seo', 'name' => 'AI SEO assistant', 'value' => true],
            ],
            'agency' => [
                ['key' => 'multi_sites', 'name' => 'Build/manage 10-20 websites', 'value' => true],
                ['key' => 'white_label', 'name' => 'White-label option (your branding)', 'value' => true],
                ['key' => 'billing_options', 'name' => 'Monthly or annual billing', 'value' => true],
                ['key' => 'payment_choice', 'name' => 'Choose: you pay or client pays directly', 'value' => true],
                ['key' => 'commission', 'name' => 'Commission integrated with MyGrowNet', 'value' => true],
                ['key' => 'sms_discount', 'name' => 'Discounted SMS credits', 'value' => true],
                ['key' => 'business_features', 'name' => 'All Business plan features', 'value' => true],
                ['key' => 'dedicated_onboarding', 'name' => 'Dedicated onboarding', 'value' => true],
                ['key' => 'ai_unlimited', 'name' => 'Unlimited AI prompts', 'value' => true],
                ['key' => 'ai_priority', 'name' => 'Priority AI processing', 'value' => true],
                ['key' => 'ai_early_access', 'name' => 'Early access to new AI features', 'value' => true],
            ],
        ];
        
        return $features[$tierKey] ?? [];
    }
}
