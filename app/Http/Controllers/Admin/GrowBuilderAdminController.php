<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

/**
 * GrowBuilderAdminController — Tier 1 Platform Governance Admin for GrowBuilder.
 *
 * Provides platform super-admins with aggregate visibility across all GrowBuilder tenants:
 * total sites, active sites, custom domains, SSG deployments, AI usage, and revenue.
 *
 * Routes: GET /admin/growbuilder
 */
class GrowBuilderAdminController extends Controller
{
    /**
     * GET /admin/growbuilder
     * Platform-wide GrowBuilder dashboard for super-admins.
     */
    public function dashboard(Request $request): Response
    {
        $stats = $this->aggregateStats();
        $topSites = $this->getTopSites();
        $recentDeployments = $this->getRecentSsgDeployments();
        $recentActivity = $this->getRecentActivity();

        return Inertia::render('Admin/GrowBuilder/Dashboard/Index', [
            'stats'             => $stats,
            'topSites'          => $topSites,
            'recentDeployments' => $recentDeployments,
            'recentActivity'    => $recentActivity,
        ]);
    }

    private function aggregateStats(): array
    {
        $totalSites = DB::table('growbuilder_sites')
            ->whereNull('deleted_at')
            ->count();

        $activeSites = DB::table('growbuilder_sites')
            ->whereNull('deleted_at')
            ->where('status', 'published')
            ->count();

        $customDomains = DB::table('growbuilder_sites')
            ->whereNull('deleted_at')
            ->whereNotNull('custom_domain')
            ->count();

        $ssgEnabledSites = DB::table('growbuilder_sites')
            ->whereNull('deleted_at')
            ->where('ssg_enabled', true)
            ->count();

        $totalPages = DB::table('growbuilder_pages')
            ->whereNull('deleted_at')
            ->count();

        $totalOrders = DB::table('growbuilder_orders')->count();

        $totalRevenue = DB::table('growbuilder_orders')
            ->where('status', 'completed')
            ->sum('total_amount');

        // AI usage this month
        $aiUsageThisMonth = DB::table('growbuilder_ai_usage')
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('tokens_used');

        // Business profiles created
        $businessProfiles = DB::table('growbuilder_business_profiles')->count();
        $profilesWithTpin = DB::table('growbuilder_business_profiles')
            ->whereNotNull('tpin')
            ->where('tpin', '!=', '')
            ->count();

        // SSG deployments this month
        $ssgDeploymentsThisMonth = DB::table('growbuilder_ssg_deployments')
            ->where('status', 'deployed')
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        // QR codes generated
        $qrCodesTotal = DB::table('growbuilder_qr_codes')->count();
        $qrScansTotal = DB::table('growbuilder_qr_codes')->sum('scan_count');

        // Page revisions saved
        $pageRevisions = DB::table('growbuilder_page_revisions')->count();

        // Sites created this month
        $newSitesThisMonth = DB::table('growbuilder_sites')
            ->whereNull('deleted_at')
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();

        return [
            'total_sites'             => $totalSites,
            'active_sites'            => $activeSites,
            'custom_domains'          => $customDomains,
            'ssg_enabled_sites'       => $ssgEnabledSites,
            'total_pages'             => $totalPages,
            'total_orders'            => $totalOrders,
            'total_revenue_zmw'       => (float) $totalRevenue,
            'ai_usage_this_month'     => (int) $aiUsageThisMonth,
            'business_profiles'       => $businessProfiles,
            'profiles_with_tpin'      => $profilesWithTpin,
            'ssg_deployments_month'   => $ssgDeploymentsThisMonth,
            'qr_codes_total'          => $qrCodesTotal,
            'qr_scans_total'          => (int) $qrScansTotal,
            'page_revisions_saved'    => $pageRevisions,
            'new_sites_this_month'    => $newSitesThisMonth,
        ];
    }

    private function getTopSites(): array
    {
        return DB::table('growbuilder_sites as s')
            ->leftJoin('users as u', 'u.id', '=', 's.user_id')
            ->leftJoin('growbuilder_business_profiles as bp', 'bp.site_id', '=', 's.id')
            ->whereNull('s.deleted_at')
            ->orderBy('s.updated_at', 'desc')
            ->limit(10)
            ->select([
                's.id',
                's.name',
                's.subdomain',
                's.custom_domain',
                's.status',
                's.ssg_enabled',
                's.template_version',
                's.created_at',
                'u.email as user_email',
                'bp.trade_name as business_name',
                'bp.industry',
                'bp.city',
            ])
            ->get()
            ->map(fn($s) => (array) $s)
            ->toArray();
    }

    private function getRecentSsgDeployments(): array
    {
        return DB::table('growbuilder_ssg_deployments as d')
            ->leftJoin('growbuilder_sites as s', 's.id', '=', 'd.site_id')
            ->orderByDesc('d.created_at')
            ->limit(10)
            ->select([
                'd.id',
                'd.status',
                'd.build_duration_ms',
                'd.triggered_by',
                'd.deployed_at',
                'd.created_at',
                's.name as site_name',
                's.subdomain',
            ])
            ->get()
            ->map(fn($d) => (array) $d)
            ->toArray();
    }

    private function getRecentActivity(): array
    {
        $activity = [];

        // New sites
        $newSites = DB::table('growbuilder_sites')
            ->whereNull('deleted_at')
            ->orderByDesc('created_at')
            ->limit(5)
            ->select(['id', 'name', 'subdomain', 'created_at'])
            ->get();

        foreach ($newSites as $site) {
            $activity[] = [
                'type'        => 'new_site',
                'description' => "New site created: \"{$site->name}\"",
                'time'        => $site->created_at,
            ];
        }

        // New business profiles
        $newProfiles = DB::table('growbuilder_business_profiles as bp')
            ->leftJoin('growbuilder_sites as s', 's.id', '=', 'bp.site_id')
            ->orderByDesc('bp.created_at')
            ->limit(5)
            ->select(['bp.trade_name', 'bp.industry', 'bp.created_at'])
            ->get();

        foreach ($newProfiles as $profile) {
            $activity[] = [
                'type'        => 'business_profile',
                'description' => "Business profile created: \"{$profile->trade_name}\" ({$profile->industry})",
                'time'        => $profile->created_at,
            ];
        }

        // Sort all activity by time DESC
        usort($activity, fn($a, $b) => strtotime($b['time']) - strtotime($a['time']));

        return array_slice($activity, 0, 10);
    }
}
