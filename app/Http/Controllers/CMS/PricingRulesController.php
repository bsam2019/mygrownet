<?php

namespace App\Http\Controllers\CMS;

use App\Http\Controllers\Controller;
use App\Domain\CMS\Core\Services\PricingRulesService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PricingRulesController extends Controller
{
    public function __construct(
        private PricingRulesService $pricingRulesService
    ) {}

    public function show(Request $request): Response
    {
        $companyId = $request->user()->cmsUser->company_id;
        $rules = $this->pricingRulesService->getOrCreate($companyId);

        return Inertia::render('CMS/Settings/PricingRules', [
            'pricingRules' => $rules,
        ]);
    }

    public function update(Request $request)
    {
        $companyId = $request->user()->cmsUser->company_id;

        $validated = $request->validate([
            'sliding_window_rate'    => 'required|numeric|min:0',
            'casement_window_rate'   => 'required|numeric|min:0',
            'sliding_door_rate'      => 'required|numeric|min:0',
            'hinged_door_rate'       => 'required|numeric|min:0',
            'other_rate'             => 'required|numeric|min:0',
            'material_cost_per_m2'   => 'required|numeric|min:0',
            'labour_cost_per_m2'     => 'required|numeric|min:0',
            'overhead_cost_per_m2'   => 'required|numeric|min:0',
            'minimum_profit_percent' => 'required|numeric|min:0|max:100',
            'tax_rate'               => 'required|numeric|min:0|max:100',
        ]);

        $this->pricingRulesService->update($companyId, $validated, $request->user()->cmsUser->id);

        return back()->with('success', 'Pricing rules updated successfully.');
    }
}
