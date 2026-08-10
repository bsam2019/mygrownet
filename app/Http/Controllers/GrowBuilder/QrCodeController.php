<?php

namespace App\Http\Controllers\GrowBuilder;

use App\Http\Controllers\Controller;
use App\Services\GrowBuilder\QrCodeService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

/**
 * QrCodeController — Physical-to-Digital Bridge endpoint.
 *
 * Generates, lists, and tracks QR codes linking offline print materials
 * to GrowBuilder published sites with UTM attribution.
 *
 * §30 of GROWBUILDER_PLATFORM.md
 */
class QrCodeController extends Controller
{
    public function __construct(private QrCodeService $qrService) {}

    /**
     * GET /dashboard/sites/{siteId}/qr-codes
     * List all QR codes for a site, or render page.
     */
    public function index(Request $request, int $siteId): \Inertia\Response|JsonResponse
    {
        $this->authorizeForSite($siteId);

        if ($request->wantsJson() || $request->header('X-Inertia') === null && $request->ajax()) {
            $qrCodes = $this->qrService->getForSite($siteId);
            return response()->json(['qr_codes' => $qrCodes]);
        }

        $site = \DB::table('growbuilder_sites')->where('id', $siteId)->firstOrFail();

        return \Inertia\Inertia::render('GrowBuilder/QrCodes/Index', [
            'site' => (array) $site,
        ]);
    }

    /**
     * POST /dashboard/sites/{siteId}/qr-codes
     * Create a new QR code.
     */
    public function store(Request $request, int $siteId): JsonResponse
    {
        $this->authorizeForSite($siteId);

        $validated = $request->validate([
            'label'        => 'required|string|max:191',
            'custom_url'   => 'nullable|url|max:1000',
            'utm_source'   => 'nullable|string|max:191',
            'utm_medium'   => 'nullable|string|max:191',
            'utm_campaign' => 'nullable|string|max:191',
        ]);

        $qr = $this->qrService->createQrCode(
            siteId:      $siteId,
            label:       $validated['label'],
            customUrl:   $validated['custom_url'] ?? null,
            utmSource:   $validated['utm_source'] ?? 'qr_code',
            utmMedium:   $validated['utm_medium'] ?? 'offline',
            utmCampaign: $validated['utm_campaign'] ?? null,
        );

        return response()->json(['qr_code' => $qr], 201);
    }

    /**
     * DELETE /dashboard/sites/{siteId}/qr-codes/{code}
     * Delete a QR code.
     */
    public function destroy(int $siteId, string $code): JsonResponse
    {
        $this->authorizeForSite($siteId);

        $deleted = $this->qrService->delete($code, $siteId);

        return response()->json(['success' => $deleted]);
    }

    /**
     * GET /qr/{code}
     * PUBLIC — Track a QR code scan and redirect to the target URL.
     */
    public function redirect(string $code): \Illuminate\Http\RedirectResponse
    {
        $targetUrl = $this->qrService->trackAndResolve(
            code:       $code,
            ipAddress:  request()->ip(),
            userAgent:  request()->userAgent(),
        );

        if (!$targetUrl) {
            abort(404, 'QR code not found.');
        }

        return redirect()->away($targetUrl);
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
