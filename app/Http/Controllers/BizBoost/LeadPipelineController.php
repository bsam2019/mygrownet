<?php

namespace App\Http\Controllers\BizBoost;

use App\Http\Controllers\Controller;
use App\Services\BizBoost\LeadManagementService;
use App\Domain\BizBoost\Services\BusinessService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class LeadPipelineController extends Controller
{
    public function __construct(
        protected LeadManagementService $leadService,
        protected BusinessService $businessService
    ) {}

    public function index(Request $request): Response|\Illuminate\Http\RedirectResponse
    {
        $user = $request->user();
        $business = $this->businessService->getBusinessByUser($user->id);

        if (!$business) {
            return redirect()->route('bizboost.setup');
        }

        $board = $this->leadService->getPipelineBoard($business->id);

        return Inertia::render('BizBoost/Leads/Pipeline', [
            'business' => ['id' => $business->id, 'name' => $business->name, 'slug' => $business->slug],
            'pipeline' => $board,
        ]);
    }

    public function updateStage(Request $request, int $leadId)
    {
        $request->validate([
            'stage_id' => 'required|integer|exists:bizboost_pipeline_stages,id',
        ]);

        $user = $request->user();
        $business = $this->businessService->getBusinessByUser($user->id);

        DB::table('bizboost_leads')
            ->where('id', $leadId)
            ->where('business_id', $business->id)
            ->update([
                'stage_id' => $request->stage_id,
                'stage_changed_at' => now(),
                'first_response_at' => DB::raw('COALESCE(first_response_at, NOW())'),
                'updated_at' => now(),
            ]);

        return redirect()->back()->with('success', 'Lead stage updated successfully.');
    }
}
