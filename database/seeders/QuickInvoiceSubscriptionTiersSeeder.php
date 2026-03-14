<?php

namespace Database\Seeders;

use App\Models\QuickInvoice\SubscriptionTier;
use Illuminate\Database\Seeder;

class QuickInvoiceSubscriptionTiersSeeder extends Seeder
{
    public function run(): void
    {
        $tiers = [
            [
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
            ],
            [
                'name' => 'Basic',
                'price' => 50,
                'currency' => 'ZMW',
                'documents_per_month' => 25,
                'features' => [
                    'templates' => ['classic', 'modern', 'minimal', 'professional', 'bold'],
                    'sharing' => ['pdf_download', 'email'],
                    'watermark' => false,
                    'customization' => true,
                    'api_access' => false,
                    'priority_support' => false,
                    'custom_branding' => false,
                    'advanced_templates' => false,
                ],
                'is_active' => true,
            ],
            [
                'name' => 'Pro',
                'price' => 150,
                'currency' => 'ZMW',
                'documents_per_month' => 100,
                'features' => [
                    'templates' => ['classic', 'modern', 'minimal', 'professional', 'bold', 'construction', 'service'],
                    'sharing' => ['pdf_download', 'email', 'whatsapp'],
                    'watermark' => false,
                    'customization' => true,
                    'api_access' => false,
                    'priority_support' => true,
                    'custom_branding' => true,
                    'advanced_templates' => true,
                    'custom_fields' => true,
                    'design_studio' => true,
                ],
                'is_active' => true,
            ],
            [
                'name' => 'Enterprise',
                'price' => 500,
                'currency' => 'ZMW',
                'documents_per_month' => -1, // Unlimited
                'features' => [
                    'templates' => 'all',
                    'sharing' => ['pdf_download', 'email', 'whatsapp', 'api'],
                    'watermark' => false,
                    'customization' => true,
                    'api_access' => true,
                    'priority_support' => true,
                    'custom_branding' => true,
                    'advanced_templates' => true,
                    'custom_fields' => true,
                    'design_studio' => true,
                    'white_label' => true,
                    'advanced_analytics' => true,
                    'cms_integration' => true,
                ],
                'is_active' => true,
            ],
        ];

        foreach ($tiers as $tierData) {
            SubscriptionTier::updateOrCreate(
                ['name' => $tierData['name']],
                $tierData
            );
        }
    }
}