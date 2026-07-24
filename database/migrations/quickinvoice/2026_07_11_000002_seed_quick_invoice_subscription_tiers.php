<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $tiers = [
            [
                'name' => 'Free',
                'price' => 0,
                'currency' => 'ZMW',
                'documents_per_month' => 3,
                'features' => json_encode([
                    'templates' => ['classic', 'modern'],
                    'sharing' => ['pdf_download'],
                    'watermark' => true,
                    'customization' => true,
                    'design_studio' => false,
                    'custom_branding' => false,
                    'advanced_templates' => false,
                    'api_access' => true,
                    'priority_support' => false,
                    'white_label' => false,
                    'advanced_analytics' => false,
                    'cms_integration' => false,
                ]),
                'is_active' => true,
            ],
            [
                'name' => 'Basic',
                'price' => 150,
                'currency' => 'ZMW',
                'documents_per_month' => 20,
                'features' => json_encode([
                    'templates' => ['classic', 'modern', 'minimal'],
                    'sharing' => ['pdf_download', 'email', 'whatsapp'],
                    'watermark' => false,
                    'customization' => true,
                    'design_studio' => false,
                    'custom_branding' => true,
                    'advanced_templates' => false,
                    'api_access' => true,
                    'priority_support' => false,
                    'white_label' => false,
                    'advanced_analytics' => false,
                    'cms_integration' => false,
                ]),
                'is_active' => true,
            ],
            [
                'name' => 'Pro',
                'price' => 350,
                'currency' => 'ZMW',
                'documents_per_month' => 100,
                'features' => json_encode([
                    'templates' => 'all',
                    'sharing' => ['pdf_download', 'email', 'whatsapp'],
                    'watermark' => false,
                    'customization' => true,
                    'design_studio' => true,
                    'custom_branding' => true,
                    'advanced_templates' => true,
                    'api_access' => true,
                    'priority_support' => true,
                    'white_label' => false,
                    'advanced_analytics' => true,
                    'cms_integration' => true,
                ]),
                'is_active' => true,
            ],
            [
                'name' => 'Enterprise',
                'price' => 750,
                'currency' => 'ZMW',
                'documents_per_month' => -1,
                'features' => json_encode([
                    'templates' => 'all',
                    'sharing' => ['pdf_download', 'email', 'whatsapp'],
                    'watermark' => false,
                    'customization' => true,
                    'design_studio' => true,
                    'custom_branding' => true,
                    'advanced_templates' => true,
                    'api_access' => true,
                    'priority_support' => true,
                    'white_label' => true,
                    'advanced_analytics' => true,
                    'cms_integration' => true,
                ]),
                'is_active' => true,
            ],
        ];

        $table = 'quick_invoice_subscription_tiers';

        // Use updateOrInsert so existing tiers (e.g. Free created by getFreeTier())
        // get updated with correct limits, and new tiers are inserted
        foreach ($tiers as $tier) {
            DB::table($table)->updateOrInsert(
                ['name' => $tier['name']],
                $tier
            );
        }

        // Deactivate any tiers not in the seed list (e.g., legacy tiers)
        $validNames = array_column($tiers, 'name');
        DB::table($table)->whereNotIn('name', $validNames)->update(['is_active' => false]);
    }

    public function down(): void
    {
    }
};
