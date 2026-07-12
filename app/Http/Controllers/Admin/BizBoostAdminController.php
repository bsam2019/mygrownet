<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Infrastructure\Persistence\Eloquent\BizBoostBusinessModel;
use App\Infrastructure\Persistence\Eloquent\BizBoostAiUsageLogModel;
use App\Infrastructure\Persistence\Eloquent\BizBoostBillingLedgerModel;
use App\Infrastructure\Persistence\Eloquent\BizBoostClientWalletModel;
use App\Infrastructure\Persistence\Eloquent\BizBoostAdCampaignModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class BizBoostAdminController extends Controller
{
    public function dashboard()
    {
        $totalBusinesses = BizBoostBusinessModel::count();
        $activeBusinesses = BizBoostBusinessModel::where('is_active', true)->count();
        $onboardedBusinesses = BizBoostBusinessModel::where('onboarding_completed', true)->count();

        $postsThisMonth = DB::table('bizboost_posts')
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        $campaignsThisMonth = DB::table('bizboost_campaigns')
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        $aiCreditsUsed = BizBoostAiUsageLogModel::where('was_successful', true)
            ->sum('credits_used');

        $aiCreditsThisMonth = BizBoostAiUsageLogModel::where('was_successful', true)
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('credits_used');

        $revenue = BizBoostBillingLedgerModel::sum('gross_amount_charged');

        $revenueThisMonth = BizBoostBillingLedgerModel::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('gross_amount_charged');

        $totalWalletBalance = BizBoostClientWalletModel::sum('balance');
        $totalLockedBalance = BizBoostClientWalletModel::sum('locked_balance');

        $adCampaignsTotal = BizBoostAdCampaignModel::count();
        $adCampaignsActive = BizBoostAdCampaignModel::where('status', 'active')->count();
        $adSpendTotal = BizBoostAdCampaignModel::sum('client_budget');

        $topBusinesses = BizBoostBusinessModel::with('user')
            ->withCount('posts', 'campaigns', 'sales')
            ->orderByDesc('posts_count')
            ->take(10)
            ->get()
            ->map(fn($b) => [
                'id' => $b->id,
                'name' => $b->name,
                'industry' => $b->industry,
                'user_name' => $b->user?->name ?? 'Unknown',
                'is_active' => $b->is_active,
                'onboarding_completed' => $b->onboarding_completed,
                'posts_count' => $b->posts_count,
                'campaigns_count' => $b->campaigns_count,
                'sales_count' => $b->sales_count,
                'created_at' => $b->created_at,
            ]);

        $recentActivity = collect();

        $recentBusinesses = BizBoostBusinessModel::with('user')
            ->latest()->take(5)->get()
            ->map(fn($b) => [
                'type' => 'business_created',
                'description' => "{$b->name} registered",
                'user' => $b->user?->name ?? 'Unknown',
                'time' => $b->created_at,
            ]);

        $recentBilling = BizBoostBillingLedgerModel::with('user')
            ->latest()->take(5)->get()
            ->map(fn($b) => [
                'type' => 'billing',
                'description' => "{$b->service_type}: \${$b->gross_amount_charged}",
                'user' => $b->user?->name ?? 'Unknown',
                'time' => $b->created_at,
            ]);

        $recentActivity = $recentBusinesses->concat($recentBilling)->sortByDesc('time')->take(8)->values();

        return Inertia::render('Admin/BizBoost/Dashboard/Index', [
            'stats' => [
                'total_businesses' => $totalBusinesses,
                'active_businesses' => $activeBusinesses,
                'onboarded_businesses' => $onboardedBusinesses,
                'posts_this_month' => $postsThisMonth,
                'campaigns_this_month' => $campaignsThisMonth,
                'ai_credits_used_total' => $aiCreditsUsed,
                'ai_credits_used_this_month' => $aiCreditsThisMonth,
                'revenue_total' => round($revenue, 2),
                'revenue_this_month' => round($revenueThisMonth, 2),
                'total_wallet_balance' => round($totalWalletBalance, 2),
                'total_locked_balance' => round($totalLockedBalance, 2),
                'ad_campaigns_total' => $adCampaignsTotal,
                'ad_campaigns_active' => $adCampaignsActive,
                'ad_spend_total' => round($adSpendTotal, 2),
            ],
            'topBusinesses' => $topBusinesses,
            'recentActivity' => $recentActivity,
        ]);
    }

    public function businesses(Request $request)
    {
        $query = BizBoostBusinessModel::with('user')
            ->withCount('posts', 'campaigns', 'sales', 'products', 'customers');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhereHas('user', fn($uq) => $uq->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%"));
            });
        }

        if ($industry = $request->input('industry')) {
            $query->where('industry', $industry);
        }

        if ($request->boolean('onboarded')) {
            $query->where('onboarding_completed', true);
        }

        $businesses = $query->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString()
            ->through(fn($b) => [
                'id' => $b->id,
                'name' => $b->name,
                'slug' => $b->slug,
                'industry' => $b->industry,
                'user_name' => $b->user?->name ?? 'Unknown',
                'user_email' => $b->user?->email ?? '',
                'is_active' => $b->is_active,
                'onboarding_completed' => $b->onboarding_completed,
                'posts_count' => $b->posts_count,
                'campaigns_count' => $b->campaigns_count,
                'sales_count' => $b->sales_count,
                'products_count' => $b->products_count,
                'customers_count' => $b->customers_count,
                'created_at' => $b->created_at,
            ]);

        $industries = BizBoostBusinessModel::distinct()->whereNotNull('industry')->pluck('industry');

        return Inertia::render('Admin/BizBoost/Businesses/Index', [
            'businesses' => $businesses,
            'industries' => $industries,
            'filters' => $request->only(['search', 'industry', 'onboarded']),
        ]);
    }

    public function showBusiness(int $id)
    {
        $business = BizBoostBusinessModel::with('user')->withCount('posts', 'campaigns', 'sales', 'products', 'customers')->findOrFail($id);

        $recentPosts = $business->posts()->with('media')->latest()->take(10)->get();
        $recentSales = $business->sales()->with('product')->latest()->take(10)->get();
        $aiUsage = BizBoostAiUsageLogModel::where('business_id', $business->id)
            ->selectRaw('SUM(credits_used) as total_credits, COUNT(*) as total_calls, SUM(was_successful) as successful_calls')
            ->first();

        $wallet = BizBoostClientWalletModel::where('user_id', $business->user_id)->first();

        return Inertia::render('Admin/BizBoost/Businesses/Show', [
            'business' => [
                'id' => $business->id,
                'name' => $business->name,
                'slug' => $business->slug,
                'industry' => $business->industry,
                'description' => $business->description,
                'phone' => $business->phone,
                'email' => $business->email,
                'website' => $business->website,
                'city' => $business->city,
                'province' => $business->province,
                'is_active' => $business->is_active,
                'onboarding_completed' => $business->onboarding_completed,
                'created_at' => $business->created_at,
                'user_name' => $business->user?->name ?? 'Unknown',
                'user_email' => $business->user?->email ?? '',
                'posts_count' => $business->posts_count,
                'campaigns_count' => $business->campaigns_count,
                'sales_count' => $business->sales_count,
                'products_count' => $business->products_count,
                'customers_count' => $business->customers_count,
                'social_links' => $business->social_links,
            ],
            'recentPosts' => $recentPosts->map(fn($p) => [
                'id' => $p->id,
                'title' => $p->title,
                'caption' => str($p->caption)->limit(100),
                'status' => $p->status,
                'created_at' => $p->created_at,
            ]),
            'recentSales' => $recentSales->map(fn($s) => [
                'id' => $s->id,
                'amount' => $s->total_amount,
                'product_name' => $s->product?->name ?? 'N/A',
                'payment_method' => $s->payment_method,
                'created_at' => $s->created_at,
            ]),
            'aiUsage' => [
                'total_credits' => (int) ($aiUsage->total_credits ?? 0),
                'total_calls' => (int) ($aiUsage->total_calls ?? 0),
                'successful_calls' => (int) ($aiUsage->successful_calls ?? 0),
            ],
            'wallet' => $wallet ? [
                'balance' => $wallet->balance,
                'locked_balance' => $wallet->locked_balance,
                'available' => $wallet->balance - $wallet->locked_balance,
            ] : null,
        ]);
    }

    public function toggleBusinessActive(int $id)
    {
        $business = BizBoostBusinessModel::findOrFail($id);
        $business->is_active = !$business->is_active;
        $business->save();

        return back()->with('success', $business->is_active ? 'Business activated.' : 'Business deactivated.');
    }

    public function aiUsage(Request $request)
    {
        $query = BizBoostAiUsageLogModel::with('business')
            ->select('business_id', DB::raw('SUM(credits_used) as total_credits'), DB::raw('COUNT(*) as total_calls'), DB::raw('SUM(was_successful) as successful_calls'), DB::raw('MAX(created_at) as last_used'))
            ->groupBy('business_id');

        if ($request->input('search')) {
            $search = $request->input('search');
            $query->whereHas('business', fn($q) => $q->where('name', 'like', "%{$search}%"));
        }

        if ($request->input('content_type')) {
            $query->where('content_type', $request->input('content_type'));
        }

        $usage = $query->orderByDesc('total_credits')
            ->paginate(20)
            ->withQueryString()
            ->through(fn($u) => [
                'business_name' => $u->business?->name ?? 'Deleted',
                'business_id' => $u->business_id,
                'total_credits' => (int) $u->total_credits,
                'total_calls' => (int) $u->total_calls,
                'successful_calls' => (int) ($u->successful_calls ?? 0),
                'last_used' => $u->last_used,
            ]);

        $contentTypes = BizBoostAiUsageLogModel::distinct()->pluck('content_type');

        $globalStats = [
            'total_credits' => BizBoostAiUsageLogModel::sum('credits_used'),
            'total_calls' => BizBoostAiUsageLogModel::count(),
            'successful_calls' => BizBoostAiUsageLogModel::where('was_successful', true)->count(),
        ];

        return Inertia::render('Admin/BizBoost/AiUsage', [
            'usage' => $usage,
            'contentTypes' => $contentTypes,
            'globalStats' => $globalStats,
            'filters' => $request->only(['search', 'content_type']),
        ]);
    }
}
