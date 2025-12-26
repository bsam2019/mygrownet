<?php

namespace App\Http\Controllers\GrowBuilder;

use App\Http\Controllers\Controller;
use App\Infrastructure\GrowBuilder\Models\GrowBuilderOrder;
use App\Infrastructure\GrowBuilder\Models\SitePost;
use App\Infrastructure\GrowBuilder\Models\SiteUser;
use Illuminate\Http\Request;
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

        // Get stats
        $ordersCount = GrowBuilderOrder::where('site_id', $site->id)
            ->where('site_user_id', $user->id)
            ->count();

        $recentOrders = GrowBuilderOrder::where('site_id', $site->id)
            ->where('site_user_id', $user->id)
            ->latest()
            ->take(5)
            ->get();

        // Get recent posts (if user has permission)
        $recentPosts = [];
        if ($user->hasPermission('posts.view')) {
            $recentPosts = SitePost::forSite($site->id)
                ->where('author_id', $user->id)
                ->latest()
                ->take(5)
                ->get();
        }

        return Inertia::render('SiteMember/Dashboard', [
            'site' => $this->getSiteData($site),
            'user' => $this->getUserData($user),
            'stats' => [
                'orders_count' => $ordersCount,
                'posts_count' => $user->hasPermission('posts.view') 
                    ? SitePost::forSite($site->id)->where('author_id', $user->id)->count() 
                    : 0,
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
            'user' => $this->getUserData($user),
            'order' => $order,
        ]);
    }

    /**
     * Get site data for frontend
     */
    protected function getSiteData($site): array
    {
        return [
            'id' => $site->id,
            'name' => $site->name,
            'subdomain' => $site->subdomain,
            'logo' => $site->logo,
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
            ] : null,
            'permissions' => $user->role 
                ? $user->role->permissions->pluck('slug')->toArray() 
                : [],
        ];
    }
}
