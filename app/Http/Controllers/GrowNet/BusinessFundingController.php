<?php

namespace App\Http\Controllers\GrowNet;

use App\Domain\GrowNet\Services\BusinessFundingEligibilityService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class BusinessFundingController extends Controller
{
    public function __construct(
        protected BusinessFundingEligibilityService $fundingService
    ) {}

    /**
     * Level 5 & 6 Business Funding Assessment Portal (Sections 34–36).
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $eligibility = $this->fundingService->evaluateEligibility($user);

        return Inertia::render('GrowNet/Funding/Eligibility', [
            'eligibility' => $eligibility,
        ]);
    }

    /**
     * Submit Business Funding Application.
     */
    public function apply(Request $request)
    {
        $request->validate([
            'business_name' => 'required|string|max:150',
            'funding_purpose' => 'required|string|min:10',
            'requested_amount' => 'required|numeric|min:100',
        ]);

        $user = $request->user();
        $result = $this->fundingService->submitApplication($user, $request->all());

        if (!$result['success']) {
            return back()->with('error', $result['message']);
        }

        return back()->with('success', $result['message']);
    }
}
