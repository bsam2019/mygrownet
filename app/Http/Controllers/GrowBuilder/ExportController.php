<?php

namespace App\Http\Controllers\GrowBuilder;

use App\Http\Controllers\Controller;
use App\Infrastructure\GrowBuilder\Models\GrowBuilderSite;
use App\Services\GrowBuilder\StaticExportService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ExportController extends Controller
{
    public function __construct(
        private StaticExportService $exportService
    ) {}

    /**
     * Export site as static HTML
     */
    public function export(Request $request, int $siteId)
    {
        $user = $request->user();
        
        // Check if user can export
        if (!$this->exportService->canExport($user)) {
            return back()->withErrors([
                'export' => 'Static export is only available for Business and Agency tier subscribers. Please upgrade your subscription to access this feature.'
            ]);
        }
        
        // Find site and verify ownership
        $site = GrowBuilderSite::findOrFail($siteId);
        
        if ($site->user_id !== $user->id) {
            abort(403, 'Unauthorized');
        }
        
        try {
            // Generate export
            $zipPath = $this->exportService->exportSite($site);
            
            // Return download response
            return response()->download($zipPath, basename($zipPath))->deleteFileAfterSend(true);
            
        } catch (\Exception $e) {
            return back()->withErrors([
                'export' => 'Failed to export site: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Show export information page
     */
    public function show(Request $request, int $siteId)
    {
        $user = $request->user();
        $site = GrowBuilderSite::findOrFail($siteId);
        
        if ($site->user_id !== $user->id) {
            abort(403, 'Unauthorized');
        }
        
        $canExport = $this->exportService->canExport($user);
        
        return inertia('GrowBuilder/Sites/Export', [
            'site' => [
                'id' => $site->id,
                'name' => $site->name,
                'subdomain' => $site->subdomain,
            ],
            'canExport' => $canExport,
        ]);
    }
}
