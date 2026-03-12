<?php

namespace App\Http\Controllers\GrowBuilder;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SiteController extends Controller
{
    private const MODULE_ID = 'growbuilder';

    public function __construct()
    {
        // Dependencies commented out for now
    }

    // DEBUG: Simple test method
    public function test(Request $request)
    {
        return Inertia::render('GrowBuilder/Dashboard', [
            'sites' => [],
            'stats' => ['totalSites' => 0],
            'subscription' => ['tier' => 'free'],
            'modules' => [],
        ]);
    }
    
    public function index(Request $request)
    {
        // DEBUG: Log that we're hitting this method
        \Log::info('GrowBuilder SiteController@index hit', [
            'user_id' => $request->user()?->id,
            'user_email' => $request->user()?->email,
            'is_admin' => $request->user()?->hasRole(['Administrator', 'admin', 'superadmin']),
            'url' => $request->url(),
            'route_name' => $request->route()?->getName(),
            'middleware' => $request->route()?->middleware(),
        ]);

        $user = $request->user();
        
        // Get user's sites
        $sites = \App\Infrastructure\GrowBuilder\Models\GrowBuilderSite::byUser($user->id)
            ->with(['pages', 'media'])
            ->orderBy('created_at', 'desc')
            ->get();

        // Get basic stats
        $stats = [
            'totalSites' => $sites->count(),
            'publishedSites' => $sites->where('status', 'published')->count(),
            'totalPageViews' => 0, // TODO: Calculate from page views
            'totalOrders' => 0, // TODO: Calculate from orders
            'totalRevenue' => 0, // TODO: Calculate from orders
            'totalMessages' => 0, // TODO: Calculate from contact messages
            'unreadMessages' => 0, // TODO: Calculate unread messages
        ];

        // Format sites for frontend
        $sitesData = $sites->map(function ($site) {
            return [
                'id' => $site->id,
                'name' => $site->name,
                'subdomain' => $site->subdomain,
                'customDomain' => $site->custom_domain,
                'description' => $site->description,
                'status' => $site->status,
                'url' => $site->url,
                'logo' => $site->logo,
                'favicon' => $site->favicon,
                'isPublished' => $site->isPublished(),
                'onboardingCompleted' => $site->onboarding_completed,
                'createdAt' => $site->created_at->diffForHumans(),
                'updatedAt' => $site->updated_at->diffForHumans(),
                'publishedAt' => $site->published_at?->diffForHumans(),
                'storageUsed' => $site->storage_used ?? 0,
                'storageLimit' => $site->storage_limit ?? 104857600, // 100MB default
                'storageUsedFormatted' => $site->storage_used_formatted,
                'storageLimitFormatted' => $site->storage_limit_formatted,
                'storagePercentage' => $site->storage_percentage,
                'pageCount' => $site->pages->count(),
                'mediaCount' => $site->media->count(),
                'pageViews' => 0, // TODO: Get actual page views
                'ordersCount' => 0, // TODO: Get actual orders
                'revenue' => 0, // TODO: Get actual revenue
                'messagesCount' => 0, // TODO: Get actual messages
                'unreadMessages' => 0, // TODO: Get unread messages
            ];
        });

        // Basic subscription info (simplified for now)
        $subscription = [
            'tier' => 'free',
            'tierName' => 'Free',
            'sitesLimit' => 3,
            'sitesUsed' => $sites->count(),
            'canCreateSite' => $sites->count() < 3,
            'expiresAt' => null,
        ];

        return Inertia::render('GrowBuilder/Dashboard', [
            'sites' => $sitesData,
            'stats' => $stats,
            'subscription' => $subscription,
            'modules' => [],
        ]);
    }

    // Placeholder methods to prevent route errors
    public function create(Request $request)
    {
        return response()->json(['message' => 'Create method - under development']);
    }

    public function store(Request $request)
    {
        return response()->json(['message' => 'Store method - under development']);
    }

    public function show(Request $request, int $id)
    {
        return response()->json(['message' => 'Show method - under development']);
    }

    public function update(Request $request, int $id)
    {
        return response()->json(['message' => 'Update method - under development']);
    }

    public function destroy(Request $request, int $id)
    {
        return response()->json(['message' => 'Destroy method - under development']);
    }

    public function publish(Request $request, int $id)
    {
        return response()->json(['message' => 'Publish method - under development']);
    }

    public function unpublish(Request $request, int $id)
    {
        return response()->json(['message' => 'Unpublish method - under development']);
    }

    public function settings(Request $request, int $id)
    {
        return response()->json(['message' => 'Settings method - under development']);
    }

    public function analytics(Request $request, int $id)
    {
        return response()->json(['message' => 'Analytics method - under development']);
    }

    public function exportAnalytics(Request $request, int $id)
    {
        return response()->json(['message' => 'Export analytics method - under development']);
    }

    public function messages(Request $request, int $id)
    {
        return response()->json(['message' => 'Messages method - under development']);
    }

    public function showMessage(Request $request, int $id, int $messageId)
    {
        return response()->json(['message' => 'Show message method - under development']);
    }

    public function replyMessage(Request $request, int $id, int $messageId)
    {
        return response()->json(['message' => 'Reply message method - under development']);
    }

    public function updateMessageStatus(Request $request, int $id, int $messageId)
    {
        return response()->json(['message' => 'Update message status method - under development']);
    }

    public function deleteMessage(Request $request, int $id, int $messageId)
    {
        return response()->json(['message' => 'Delete message method - under development']);
    }

    public function exportMessages(Request $request, int $id)
    {
        return response()->json(['message' => 'Export messages method - under development']);
    }

    public function completeOnboarding(Request $request, int $id)
    {
        return response()->json(['message' => 'Complete onboarding method - under development']);
    }

    public function users(Request $request, int $id)
    {
        return response()->json(['message' => 'Users method - under development']);
    }

    public function createUser(Request $request, int $id)
    {
        return response()->json(['message' => 'Create user method - under development']);
    }

    public function updateUserRole(Request $request, int $id, int $userId)
    {
        return response()->json(['message' => 'Update user role method - under development']);
    }

    public function deleteUser(Request $request, int $id, int $userId)
    {
        return response()->json(['message' => 'Delete user method - under development']);
    }

    public function roles(Request $request, int $id)
    {
        return response()->json(['message' => 'Roles method - under development']);
    }

    public function permissions(Request $request, int $id)
    {
        return response()->json(['message' => 'Permissions method - under development']);
    }

    public function createRole(Request $request, int $id)
    {
        return response()->json(['message' => 'Create role method - under development']);
    }

    public function updateRole(Request $request, int $id, int $roleId)
    {
        return response()->json(['message' => 'Update role method - under development']);
    }

    public function deleteRole(Request $request, int $id, int $roleId)
    {
        return response()->json(['message' => 'Delete role method - under development']);
    }

    public function restore(Request $request, int $id)
    {
        return response()->json(['message' => 'Restore method - under development']);
    }

    public function preview(Request $request, string $subdomain, ?string $page = null)
    {
        return response()->json(['message' => 'Preview method - under development']);
    }

    public function switchTier(Request $request)
    {
        return response()->json(['message' => 'Switch tier method - under development']);
    }

    public function showProduct(Request $request, string $subdomain, string $slug)
    {
        return response()->json(['message' => 'Show product method - under development']);
    }

    public function checkout(Request $request, string $subdomain)
    {
        return response()->json(['message' => 'Checkout method - under development']);
    }
}