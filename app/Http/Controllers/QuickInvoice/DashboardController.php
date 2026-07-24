<?php

namespace App\Http\Controllers\QuickInvoice;

use App\Domain\QuickInvoice\Services\DocumentService;
use App\Domain\QuickInvoice\Services\ProfileService;
use App\Domain\QuickInvoice\Services\SubscriptionService;
use App\Domain\QuickInvoice\Repositories\UsageTrackingRepositoryInterface;
use App\Http\Controllers\Controller;
use App\Services\QuickInvoice\TemplateService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __construct(
        private readonly DocumentService $documentService,
        private readonly ProfileService $profileService,
        private readonly SubscriptionService $subscriptionService,
        private readonly UsageTrackingRepositoryInterface $usageTracking,
    ) {}

    public function index(Request $request): Response
    {
        $user = $request->user();
        $isAuthenticated = !!$user;

        $subscription = null;
        $monthlyUsage = 0;
        $recentDocuments = [];
        $profile = null;

        if ($isAuthenticated) {
            $subscriptionData = $this->subscriptionService->getSubscriptionForDashboard($user->id);
            $monthlyUsage = $this->usageTracking->getUserMonthlyUsage($user->id);

            $recentDocuments = $this->documentService->getRecentDocumentsByUser($user->id, 5);

            $profileEntity = $this->profileService->getProfile($user->id);
            $profile = $this->profileService->profileToArray($profileEntity);
        }

        $totalDocuments = $this->documentService->getTotalDocumentCount();
        $activeUsers = $this->usageTracking->getStats(now()->startOfMonth()->format('Y-m-d'), now()->format('Y-m-d'));
        $popularTemplates = $this->usageTracking->getStats(now()->subMonth()->format('Y-m-d'), now()->format('Y-m-d'));

        $overallStats = [
            'total_documents_created' => $totalDocuments,
            'active_users_this_month' => $activeUsers['unique_users'] ?? 0,
            'popular_templates' => $popularTemplates['by_template'] ?? [],
        ];

        $quickActions = [
            [
                'type' => 'invoice',
                'label' => 'Create Invoice',
                'description' => 'Professional invoices for your business',
                'icon' => 'DocumentTextIcon',
                'color' => 'blue',
                'route' => route('quick-invoice.create', ['type' => 'invoice']),
            ],
            [
                'type' => 'quotation',
                'label' => 'Create Quotation',
                'description' => 'Price quotes for potential clients',
                'icon' => 'DocumentCheckIcon',
                'color' => 'amber',
                'route' => route('quick-invoice.create', ['type' => 'quotation']),
            ],
            [
                'type' => 'receipt',
                'label' => 'Create Receipt',
                'description' => 'Payment confirmations and receipts',
                'icon' => 'ReceiptPercentIcon',
                'color' => 'green',
                'route' => route('quick-invoice.create', ['type' => 'receipt']),
            ],
            [
                'type' => 'delivery_note',
                'label' => 'Create Delivery Note',
                'description' => 'Shipping and delivery documentation',
                'icon' => 'TruckIcon',
                'color' => 'purple',
                'route' => route('quick-invoice.create', ['type' => 'delivery_note']),
            ],
        ];

        $userTier = $subscription ? $subscription['tier_name'] : 'Free';
        $availableTemplates = TemplateService::getTemplatesForTier($userTier);

        $popularKeys = $overallStats['popular_templates'];
        $templates = array_map(function ($template) use ($popularKeys) {
            return [
                'id' => $template['id'],
                'name' => $template['name'],
                'description' => $template['description'],
                'preview_image' => $template['preview_image'],
                'tier_required' => $template['tier_required'],
                'category' => $template['category'],
                'usage_count' => $popularKeys[$template['id']] ?? 0,
                'features' => $template['features'],
            ];
        }, $availableTemplates);

        usort($templates, function ($a, $b) {
            return $b['usage_count'] <=> $a['usage_count'];
        });

        return Inertia::render('QuickInvoice/Dashboard', [
            'isAuthenticated' => $isAuthenticated,
            'subscription' => $subscription,
            'recentDocuments' => $recentDocuments,
            'profile' => $profile,
            'overallStats' => $overallStats,
            'quickActions' => $quickActions,
            'templates' => $templates,
            'monthlyUsage' => $monthlyUsage,
        ]);
    }
}