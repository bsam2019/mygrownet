<?php

namespace App\Http\Controllers\GrowBuilder;

use App\Http\Controllers\Controller;
use App\Infrastructure\GrowBuilder\Models\GrowBuilderOrder;
use App\Infrastructure\GrowBuilder\Models\GrowBuilderPageView;
use App\Infrastructure\GrowBuilder\Models\SitePost;
use App\Infrastructure\GrowBuilder\Models\SiteUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;

class SiteMemberController extends Controller
{
    /**
     * Member dashboard
     */
    public function dashboard(Request $request, string $subdomain)
    {
        $site = $request->attributes->get('site');
        $user = $request->attributes->get('site_user');
        $isAdmin = $user->role && $user->role->level >= 100;

        // Get user's orders stats
        $ordersQuery = GrowBuilderOrder::where('site_id', $site->id)
            ->where('site_user_id', $user->id);
        
        $ordersCount = $ordersQuery->count();
        $ordersTotal = $ordersQuery->sum('total');

        $recentOrders = GrowBuilderOrder::where('site_id', $site->id)
            ->where('site_user_id', $user->id)
            ->latest()
            ->take(5)
            ->get();

        // Get posts stats (if user has permission)
        $postsCount = 0;
        $recentPosts = [];
        if ($user->hasPermission('posts.view')) {
            $postsCount = SitePost::forSite($site->id)->where('author_id', $user->id)->count();
            $recentPosts = SitePost::forSite($site->id)
                ->where('author_id', $user->id)
                ->latest()
                ->take(5)
                ->get();
        }

        // Admin-only stats
        $usersCount = null;
        $pendingOrders = null;
        if ($isAdmin) {
            $usersCount = SiteUser::forSite($site->id)->count();
            $pendingOrders = GrowBuilderOrder::where('site_id', $site->id)
                ->where('status', 'pending')
                ->count();
        }

        return Inertia::render('SiteMember/Dashboard', [
            'site' => $this->getSiteData($site),
            'settings' => $site->settings,
            'user' => $this->getUserData($user),
            'stats' => [
                'orders_count' => $ordersCount,
                'orders_total' => $ordersTotal,
                'posts_count' => $postsCount,
                'users_count' => $usersCount,
                'pending_orders' => $pendingOrders,
            ],
            'recentOrders' => $recentOrders,
            'recentPosts' => $recentPosts,
        ]);
    }

    /**
     * Show profile page
     */
    public function profile(Request $request, string $subdomain)
    {
        $site = $request->attributes->get('site');
        $user = $request->attributes->get('site_user');

        return Inertia::render('SiteMember/Profile/Index', [
            'site' => $this->getSiteData($site),
            'settings' => $site->settings,
            'user' => $this->getUserData($user),
        ]);
    }

    /**
     * Update profile
     */
    public function updateProfile(Request $request, string $subdomain)
    {
        $site = $request->attributes->get('site');
        $user = $request->attributes->get('site_user');

        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:50',
            'current_password' => 'nullable|required_with:new_password|string',
            'new_password' => 'nullable|string|min:8|confirmed',
        ]);

        // Update basic info
        $user->update([
            'name' => $request->name,
            'phone' => $request->phone,
        ]);

        // Update password if provided
        if ($request->filled('new_password')) {
            if (!Hash::check($request->current_password, $user->password)) {
                return back()->withErrors([
                    'current_password' => 'Current password is incorrect.',
                ]);
            }

            $user->update([
                'password' => Hash::make($request->new_password),
            ]);
        }

        return back()->with('success', 'Profile updated successfully.');
    }

    /**
     * List orders
     */
    public function orders(Request $request, string $subdomain)
    {
        $site = $request->attributes->get('site');
        $user = $request->attributes->get('site_user');

        $orders = GrowBuilderOrder::where('site_id', $site->id)
            ->where('site_user_id', $user->id)
            ->latest()
            ->paginate(10);

        return Inertia::render('SiteMember/Orders/Index', [
            'site' => $this->getSiteData($site),
            'settings' => $site->settings,
            'user' => $this->getUserData($user),
            'orders' => $orders,
        ]);
    }

    /**
     * Show order detail
     */
    public function orderDetail(Request $request, string $subdomain, int $id)
    {
        $site = $request->attributes->get('site');
        $user = $request->attributes->get('site_user');

        $order = GrowBuilderOrder::where('site_id', $site->id)
            ->where('site_user_id', $user->id)
            ->where('id', $id)
            ->firstOrFail();

        return Inertia::render('SiteMember/Orders/Show', [
            'site' => $this->getSiteData($site),
            'settings' => $site->settings,
            'user' => $this->getUserData($user),
            'order' => $order,
        ]);
    }

    /**
     * Analytics page (admin/analytics permission)
     */
    public function analytics(Request $request, string $subdomain)
    {
        $site = $request->attributes->get('site');
        $user = $request->attributes->get('site_user');

        $period = $request->get('period', '30d');
        $days = match ($period) {
            '7d' => 7,
            '90d' => 90,
            default => 30,
        };

        $startDate = now()->subDays($days);
        $previousStartDate = now()->subDays($days * 2);

        // Daily stats
        $dailyStats = GrowBuilderPageView::where('site_id', $site->id)
            ->where('created_at', '>=', $startDate)
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('COUNT(*) as views'))
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date');

        // Fill missing dates
        $allDates = collect();
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $allDates->push([
                'date' => now()->subDays($i)->format('M j'),
                'views' => $dailyStats[$date]->views ?? 0,
            ]);
        }

        // Total views
        $totalViews = GrowBuilderPageView::where('site_id', $site->id)
            ->where('created_at', '>=', $startDate)
            ->count();

        $previousViews = GrowBuilderPageView::where('site_id', $site->id)
            ->whereBetween('created_at', [$previousStartDate, $startDate])
            ->count();

        $viewsChange = $previousViews > 0
            ? round((($totalViews - $previousViews) / $previousViews) * 100)
            : 0;

        // Unique visitors
        $totalVisitors = GrowBuilderPageView::where('site_id', $site->id)
            ->where('created_at', '>=', $startDate)
            ->distinct('ip_address')
            ->count('ip_address');

        // Device stats
        $deviceStats = GrowBuilderPageView::where('site_id', $site->id)
            ->where('created_at', '>=', $startDate)
            ->select('device_type', DB::raw('COUNT(*) as count'))
            ->groupBy('device_type')
            ->get()
            ->map(fn($row) => [
                'device' => $row->device_type ?? 'desktop',
                'count' => $row->count,
                'percentage' => $totalViews > 0 ? round(($row->count / $totalViews) * 100) : 0,
            ]);

        // Top pages
        $topPages = GrowBuilderPageView::where('site_id', $site->id)
            ->where('created_at', '>=', $startDate)
            ->select('path', DB::raw('COUNT(*) as views'))
            ->groupBy('path')
            ->orderByDesc('views')
            ->limit(10)
            ->get()
            ->map(fn($row) => ['path' => $row->path, 'views' => $row->views]);

        return Inertia::render('SiteMember/Analytics/Index', [
            'site' => $this->getSiteData($site),
            'settings' => $site->settings,
            'user' => $this->getUserData($user),
            'totalViews' => $totalViews,
            'totalVisitors' => $totalVisitors,
            'viewsChange' => $viewsChange,
            'dailyStats' => $allDates,
            'deviceStats' => $deviceStats,
            'topPages' => $topPages,
            'period' => $period,
        ]);
    }

    /**
     * Settings page (admin only)
     */
    public function settings(Request $request, string $subdomain)
    {
        $site = $request->attributes->get('site');
        $user = $request->attributes->get('site_user');

        $siteSettings = $site->settings['dashboard'] ?? [];

        return Inertia::render('SiteMember/Settings/Index', [
            'site' => $this->getSiteData($site),
            'settings' => $site->settings,
            'user' => $this->getUserData($user),
            'siteSettings' => [
                'notifications' => $siteSettings['notifications'] ?? ['email_orders' => true, 'email_users' => true],
                'security' => $siteSettings['security'] ?? ['require_email_verification' => false, 'allow_registration' => true],
                'appearance' => $siteSettings['appearance'] ?? ['show_powered_by' => true],
            ],
        ]);
    }

    /**
     * Update settings (admin only)
     */
    public function updateSettings(Request $request, string $subdomain)
    {
        $site = $request->attributes->get('site');

        $settings = $site->settings ?? [];
        $settings['dashboard'] = [
            'notifications' => [
                'email_orders' => $request->boolean('notifications_email_orders'),
                'email_users' => $request->boolean('notifications_email_users'),
            ],
            'security' => [
                'require_email_verification' => $request->boolean('security_require_email_verification'),
                'allow_registration' => $request->boolean('security_allow_registration'),
            ],
            'appearance' => [
                'show_powered_by' => $request->boolean('appearance_show_powered_by'),
            ],
        ];

        $site->update(['settings' => $settings]);

        return back()->with('success', 'Settings updated successfully.');
    }

    /**
     * Get site data for frontend
     */
    protected function getSiteData($site): array
    {
        // Get logo from settings.navigation.logo or site.logo
        $settings = $site->settings ?? [];
        $logo = $settings['navigation']['logo'] ?? $site->logo ?? null;
        
        return [
            'id' => $site->id,
            'name' => $site->name,
            'subdomain' => $site->subdomain,
            'logo' => $logo,
            'theme' => $site->theme,
        ];
    }

    /**
     * Get user data for frontend
     */
    protected function getUserData(SiteUser $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'avatar' => $user->avatar,
            'role' => $user->role ? [
                'name' => $user->role->name,
                'slug' => $user->role->slug,
                'level' => $user->role->level,
                'type' => $user->role->type ?? 'client',
                'icon' => $user->role->icon,
                'color' => $user->role->color,
            ] : null,
            'permissions' => $user->role 
                ? $user->role->permissions->pluck('slug')->toArray() 
                : [],
            'created_at' => $user->created_at?->toISOString(),
        ];
    }
}
