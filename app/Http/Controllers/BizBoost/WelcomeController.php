<?php

namespace App\Http\Controllers\BizBoost;

use App\Http\Controllers\Controller;
use App\Domain\Module\Services\TierConfigurationService;
use Inertia\Inertia;
use Inertia\Response;

class WelcomeController extends Controller
{
    public function __construct(
        private TierConfigurationService $tierConfigService
    ) {}

    /**
     * Display the BizBoost welcome/landing page.
     * This page is publicly accessible without authentication.
     */
    public function index(): Response
    {
        return Inertia::render('BizBoost/Welcome', [
            'features' => $this->getFeatures(),
            'pricingTiers' => $this->getPricingTiers(),
        ]);
    }

    /**
     * Get the list of BizBoost features to display on the landing page.
     */
    private function getFeatures(): array
    {
        return [
            [
                'icon' => 'sparkles',
                'title' => 'AI Content Generator',
                'description' => 'Create engaging social media posts, product descriptions, and marketing copy with AI assistance.',
            ],
            [
                'icon' => 'users',
                'title' => 'Customer Management',
                'description' => 'Track and manage your customer relationships, follow-ups, and sales history in one place.',
            ],
            [
                'icon' => 'share',
                'title' => 'Social Media Tools',
                'description' => 'Schedule and publish posts to multiple platforms. Plan your content calendar ahead.',
            ],
            [
                'icon' => 'chart-bar',
                'title' => 'Analytics Dashboard',
                'description' => 'Track your business growth, sales performance, and customer engagement metrics.',
            ],
            [
                'icon' => 'shopping-bag',
                'title' => 'Product Catalog',
                'description' => 'Manage your products, track inventory, and showcase your offerings professionally.',
            ],
            [
                'icon' => 'currency-dollar',
                'title' => 'Sales Tracking',
                'description' => 'Record sales, generate receipts, and monitor your revenue growth over time.',
            ],
        ];
    }

    /**
     * Get the pricing tiers for BizBoost subscriptions from centralized config.
     */
    private function getPricingTiers(): array
    {
        $tiers = $this->tierConfigService->getTiers('bizboost');
        $pricingTiers = [];

        // Feature descriptions for each tier (human-readable)
        $tierFeatureDescriptions = [
            'free' => [
                '10 posts per month',
                '10 AI credits',
                '20 customers',
                '10 products',
                'Mini website',
                'Basic templates',
                'Email notifications',
            ],
            'basic' => [
                '50 posts per month',
                '50 AI credits',
                'Unlimited customers',
                'Unlimited products',
                '3 campaigns',
                '100 MB storage',
                'Industry kits',
                'Basic analytics',
                'Product catalog',
            ],
            'professional' => [
                'Unlimited posts',
                '200 AI credits',
                'Unlimited customers',
                'Unlimited campaigns',
                '1 GB storage',
                '3 team members',
                'Auto posting',
                'WhatsApp tools',
                'Advanced analytics',
                'AI content generator',
                'Social integrations',
            ],
            'business' => [
                'Unlimited posts',
                'Unlimited AI credits',
                'Unlimited everything',
                '5 GB storage',
                '10 team members',
                '5 locations',
                'API access',
                'White-label options',
                'Learning hub',
                'AI advisor',
                'Custom reports',
                'Dedicated support',
            ],
        ];

        // CTA text for each tier
        $tierCtas = [
            'free' => 'Get Started Free',
            'basic' => 'Start Free Trial',
            'professional' => 'Start Free Trial',
            'business' => 'Contact Sales',
        ];

        foreach ($tiers as $tierKey => $tierConfig) {
            $pricingTiers[] = [
                'id' => $tierKey,
                'name' => $tierConfig['name'],
                'price' => $tierConfig['price_monthly'],
                'price_annual' => $tierConfig['price_annual'],
                'currency' => 'K',
                'period' => $tierKey === 'free' ? 'forever' : 'month',
                'description' => $tierConfig['description'],
                'features' => $tierFeatureDescriptions[$tierKey] ?? [],
                'cta' => $tierCtas[$tierKey] ?? 'Get Started',
                'popular' => $tierConfig['popular'] ?? false,
            ];
        }

        return $pricingTiers;
    }
}
