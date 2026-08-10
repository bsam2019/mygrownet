<?php

namespace App\Http\Controllers\GrowBuilder;

use App\Http\Controllers\Controller;
use App\Services\GrowBuilder\SsgPublisherService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

/**
 * SsgDeployController — manages Static Site Generation (SSG) builds and deployments.
 *
 * Triggers static HTML compilation for published GrowBuilder sites
 * and tracks deployment history for CDN-first serving.
 *
 * §32 of GROWBUILDER_PLATFORM.md
 */
class SsgDeployController extends Controller
{
    public function __construct(private SsgPublisherService $ssgPublisher) {}

    /**
     * GET /dashboard/sites/{siteId}/ssg
     * Render the SSG dashboard page.
     */
    public function index(int $siteId): \Inertia\Response
    {
        $this->authorizeForSite($siteId);

        $site = \DB::table('growbuilder_sites')->where('id', $siteId)->firstOrFail();
        $deployments = \DB::table('growbuilder_ssg_deployments')
            ->where('site_id', $siteId)
            ->orderByDesc('created_at')
            ->limit(20)
            ->get()
            ->map(fn($d) => (array) $d)
            ->toArray();

        return \Inertia\Inertia::render('GrowBuilder/Ssg/Index', [
            'site'        => (array) $site,
            'deployments' => $deployments,
        ]);
    }

    /**
     * POST /dashboard/sites/{siteId}/ssg/deploy
     * Trigger a full static site build and deploy.
     */
    public function deploy(Request $request, int $siteId): JsonResponse
    {
        $this->authorizeForSite($siteId);

        $trigger = $request->input('trigger', 'manual');

        $result = $this->ssgPublisher->buildAndDeploy($siteId, $trigger);

        return response()->json([
            'success'           => $result['success'],
            'cdn_url'           => $result['cdn_url'],
            'pages_compiled'    => $result['pages_compiled'],
            'build_duration_ms' => $result['build_duration_ms'],
            'errors'            => $result['errors'],
            'message'           => $result['success']
                ? "Site compiled successfully in {$result['build_duration_ms']}ms — {$result['pages_compiled']} pages built."
                : 'Build completed with errors. Check the build log.',
        ]);
    }

    /**
     * GET /dashboard/sites/{siteId}/ssg/deployments
     * List all SSG deployment history for a site.
     */
    public function history(int $siteId): JsonResponse
    {
        $this->authorizeForSite($siteId);

        $deployments = \DB::table('growbuilder_ssg_deployments')
            ->where('site_id', $siteId)
            ->orderByDesc('created_at')
            ->limit(20)
            ->get()
            ->map(fn($d) => (array) $d)
            ->toArray();

        return response()->json(['deployments' => $deployments]);
    }

    /**
     * POST /dashboard/sites/{siteId}/ssg/enable
     * Enable or disable SSG for a site.
     */
    public function toggleSsg(Request $request, int $siteId): JsonResponse
    {
        $this->authorizeForSite($siteId);

        $enabled = (bool) $request->input('enabled', true);

        \DB::table('growbuilder_sites')
            ->where('id', $siteId)
            ->update(['ssg_enabled' => $enabled, 'updated_at' => now()]);

        return response()->json([
            'success' => true,
            'ssg_enabled' => $enabled,
            'message' => $enabled
                ? 'Static Site Generation enabled. Your next publish will generate a CDN-optimised static build.'
                : 'Static Site Generation disabled. Your site will be served dynamically.',
        ]);
    }

    private function authorizeForSite(int $siteId): void
    {
        $site = \DB::table('growbuilder_sites')
            ->where('id', $siteId)
            ->where('user_id', auth()->id())
            ->first();

        abort_if(!$site, 403, 'You do not have access to this site.');
    }
}
