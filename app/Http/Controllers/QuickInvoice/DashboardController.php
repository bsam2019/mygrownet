<?php

namespace App\Http\Controllers\QuickInvoice;

use App\Http\Controllers\Controller;
use App\Models\QuickInvoice\UserSubscription;
use App\Models\QuickInvoice\UsageTracking;
use App\Models\QuickInvoiceDocument;
use App\Models\QuickInvoiceProfile;
use App\Services\QuickInvoice\TemplateService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();
        $isAuthenticated = !!$user;
        
        // Get user subscription and usage stats
        $subscription = null;
        $monthlyUsage = 0;
        $recentDocuments = collect();
        $profile = null;
        
        if ($isAuthenticated) {
            $subscription = UserSubscription::getOrCreateFreeSubscription($user->id);
            $monthlyUsage = UsageTracking::getUserMonthlyUsage($user->id);
            
            // Get recent documents
            $recentDocuments = QuickInvoiceDocument::where('user_id', $user->id)
                ->with('items')
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get()
                ->map(function ($doc) {
                    return [
                        'id' => $doc->id,
                        'document_number' => $doc->document_number,
                        'type' => $doc->document_type,
                        'type_label' => $doc->getTypeLabel(),
                        'client_name' => $doc->client_name,
                        'total' => $doc->total,
                        'currency' => $doc->currency,
                        'currency_symbol' => $doc->getCurrencySymbol(),
                        'formatted_total' => $doc->getFormattedTotal(),
                        'created_at' => $doc->created_at->format('M j, Y'),
                        'created_at_human' => $doc->created_at->diffForHumans(),
                    ];
                });
            
            // Get user profile
            $profile = QuickInvoiceProfile::where('user_id', $user->id)->first();
        }
        
        // Get overall stats (for guest users too)
        $overallStats = [
            'total_documents_created' => QuickInvoiceDocument::count(),
            'active_users_this_month' => UsageTracking::where('created_at', '>=', now()->startOfMonth())
                ->whereNotNull('user_id')
                ->distinct('user_id')
                ->count(),
            'popular_templates' => UsageTracking::where('created_at', '>=', now()->subMonth())
                ->groupBy('template_used')
                ->selectRaw('template_used, COUNT(*) as count')
                ->orderByDesc('count')
                ->limit(3)
                ->pluck('count', 'template_used')
                ->toArray(),
        ];
        
        // Quick action templates
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
        
        // Get templates available for user's tier
        $userTier = $subscription ? $subscription->tier->name : 'Free';
        $availableTemplates = TemplateService::getTemplatesForTier($userTier);
        
        // Template gallery with usage stats
        $templates = array_map(function ($template) use ($overallStats) {
            return [
                'id' => $template['id'],
                'name' => $template['name'],
                'description' => $template['description'],
                'preview_image' => $template['preview_image'],
                'tier_required' => $template['tier_required'],
                'category' => $template['category'],
                'usage_count' => $overallStats['popular_templates'][$template['id']] ?? 0,
                'features' => $template['features'],
            ];
        }, $availableTemplates);
        
        // Sort templates by usage count
        usort($templates, function ($a, $b) {
            return $b['usage_count'] <=> $a['usage_count'];
        });
        
        return Inertia::render('QuickInvoice/Dashboard', [
            'isAuthenticated' => $isAuthenticated,
            'subscription' => $subscription ? [
                'tier_name' => $subscription->tier->name,
                'tier_price' => $subscription->tier->formatted_price,
                'documents_per_month' => $subscription->tier->documents_per_month == -1 ? 'Unlimited' : $subscription->tier->documents_per_month,
                'documents_used' => $monthlyUsage,
                'remaining_documents' => $subscription->tier->documents_per_month == -1 ? 'Unlimited' : $subscription->getRemainingDocuments(),
                'usage_percentage' => $subscription->getUsagePercentage(),
                'features' => $subscription->tier->features,
                'expires_at' => $subscription->expires_at?->format('M j, Y'),
            ] : null,
            'recentDocuments' => $recentDocuments,
            'profile' => $profile ? [
                'name' => $profile->name,
                'email' => $profile->email,
                'logo' => $profile->logo,
                'completion_percentage' => $this->calculateProfileCompletion($profile),
            ] : null,
            'overallStats' => $overallStats,
            'quickActions' => $quickActions,
            'templates' => $templates,
            'monthlyUsage' => $monthlyUsage,
        ]);
    }
    
    private function calculateProfileCompletion($profile): int
    {
        if (!$profile) return 0;
        
        $fields = ['name', 'address', 'phone', 'email', 'logo'];
        $completed = 0;
        
        foreach ($fields as $field) {
            if (!empty($profile->$field)) {
                $completed++;
            }
        }
        
        return (int) (($completed / count($fields)) * 100);
    }
}