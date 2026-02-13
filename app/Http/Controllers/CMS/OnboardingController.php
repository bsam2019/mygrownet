<?php

namespace App\Http\Controllers\CMS;

use App\Http\Controllers\Controller;
use App\Domain\CMS\Core\Services\OnboardingService;
use App\Domain\CMS\Core\Services\IndustryPresetService;
use App\Infrastructure\Persistence\Eloquent\CMS\CompanyModel;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class OnboardingController extends Controller
{
    public function __construct(
        private OnboardingService $onboardingService,
        private IndustryPresetService $industryPresetService
    ) {}

    /**
     * Show onboarding wizard
     */
    public function index(Request $request): Response
    {
        $companyId = $request->user()->cmsUser->company_id;
        $company = CompanyModel::find($companyId);

        $status = $this->onboardingService->getOnboardingStatus($companyId);
        $presets = $this->industryPresetService->getAllPresets();

        return Inertia::render('CMS/Onboarding/Wizard', [
            'company' => $company,
            'status' => $status,
            'presets' => $presets,
        ]);
    }

    /**
     * Get onboarding status
     */
    public function status(Request $request)
    {
        $companyId = $request->user()->cmsUser->company_id;
        $status = $this->onboardingService->getOnboardingStatus($companyId);

        return response()->json($status);
    }

    /**
     * Update company information (Step 1)
     */
    public function updateCompanyInfo(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'business_registration_number' => 'nullable|string|max:100',
            'tax_number' => 'nullable|string|max:100',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'phone' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'website' => 'nullable|url|max:255',
        ]);

        $companyId = $request->user()->cmsUser->company_id;
        $success = $this->onboardingService->updateCompanyInformation($companyId, $validated);

        if ($success) {
            return response()->json([
                'success' => true,
                'message' => 'Company information updated successfully',
            ]);
        }

        return response()->json([
            'success' => false,
            'error' => 'Failed to update company information',
        ], 500);
    }

    /**
     * Apply industry preset (Step 2)
     */
    public function applyPreset(Request $request)
    {
        $validated = $request->validate([
            'preset_code' => 'required|string',
        ]);

        $companyId = $request->user()->cmsUser->company_id;
        $success = $this->onboardingService->applyIndustryPreset($companyId, $validated['preset_code']);

        if ($success) {
            return response()->json([
                'success' => true,
                'message' => 'Industry preset applied successfully',
            ]);
        }

        return response()->json([
            'success' => false,
            'error' => 'Failed to apply industry preset',
        ], 500);
    }

    /**
     * Configure business settings (Step 3)
     */
    public function configureSettings(Request $request)
    {
        $validated = $request->validate([
            'business_hours' => 'nullable|array',
            'tax' => 'nullable|array',
            'approval_thresholds' => 'nullable|array',
        ]);

        $companyId = $request->user()->cmsUser->company_id;
        $success = $this->onboardingService->configureBusinessSettings($companyId, $validated);

        if ($success) {
            return response()->json([
                'success' => true,
                'message' => 'Business settings configured successfully',
            ]);
        }

        return response()->json([
            'success' => false,
            'error' => 'Failed to configure business settings',
        ], 500);
    }

    /**
     * Complete a step
     */
    public function completeStep(Request $request)
    {
        $validated = $request->validate([
            'step_id' => 'required|integer|min:1|max:5',
            'data' => 'nullable|array',
        ]);

        $companyId = $request->user()->cmsUser->company_id;
        $success = $this->onboardingService->completeStep(
            $companyId,
            $validated['step_id'],
            $validated['data'] ?? []
        );

        if ($success) {
            return response()->json([
                'success' => true,
                'message' => 'Step completed successfully',
            ]);
        }

        return response()->json([
            'success' => false,
            'error' => 'Failed to complete step',
        ], 500);
    }

    /**
     * Skip a step
     */
    public function skipStep(Request $request)
    {
        $validated = $request->validate([
            'step_id' => 'required|integer|min:1|max:5',
        ]);

        $companyId = $request->user()->cmsUser->company_id;
        $success = $this->onboardingService->skipStep($companyId, $validated['step_id']);

        if ($success) {
            return response()->json([
                'success' => true,
                'message' => 'Step skipped successfully',
            ]);
        }

        return response()->json([
            'success' => false,
            'error' => 'Failed to skip step',
        ], 500);
    }

    /**
     * Complete onboarding
     */
    public function complete(Request $request)
    {
        $companyId = $request->user()->cmsUser->company_id;
        $company = CompanyModel::find($companyId);

        if (!$company) {
            return response()->json([
                'success' => false,
                'error' => 'Company not found',
            ], 404);
        }

        $company->update([
            'onboarding_completed' => true,
            'onboarding_completed_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Onboarding completed successfully',
            'redirect' => route('cms.dashboard'),
        ]);
    }

    /**
     * Get tour status
     */
    public function tourStatus(Request $request)
    {
        $userId = $request->user()->id;
        $status = $this->onboardingService->getTourStatus($userId);

        return response()->json($status);
    }

    /**
     * Complete tour step
     */
    public function completeTourStep(Request $request)
    {
        $validated = $request->validate([
            'step_id' => 'required|string',
        ]);

        $userId = $request->user()->id;
        $success = $this->onboardingService->completeTourStep($userId, $validated['step_id']);

        if ($success) {
            return response()->json([
                'success' => true,
                'message' => 'Tour step completed',
            ]);
        }

        return response()->json([
            'success' => false,
            'error' => 'Failed to complete tour step',
        ], 500);
    }

    /**
     * Skip tour
     */
    public function skipTour(Request $request)
    {
        $userId = $request->user()->id;
        $success = $this->onboardingService->skipTour($userId);

        if ($success) {
            return response()->json([
                'success' => true,
                'message' => 'Tour skipped',
            ]);
        }

        return response()->json([
            'success' => false,
            'error' => 'Failed to skip tour',
        ], 500);
    }

    /**
     * Generate sample data (Step 5)
     */
    public function generateSampleData(Request $request)
    {
        $companyId = $request->user()->cmsUser->company_id;
        $userId = $request->user()->id;

        $result = $this->onboardingService->generateSampleData($companyId, $userId);

        if ($result['success']) {
            return response()->json([
                'success' => true,
                'message' => 'Sample data generated successfully',
                'data' => $result['data'],
            ]);
        }

        return response()->json([
            'success' => false,
            'error' => $result['error'] ?? 'Failed to generate sample data',
        ], 500);
    }

    /**
     * Clear sample data
     */
    public function clearSampleData(Request $request)
    {
        $companyId = $request->user()->cmsUser->company_id;
        $success = $this->onboardingService->clearSampleData($companyId);

        if ($success) {
            return response()->json([
                'success' => true,
                'message' => 'Sample data cleared successfully',
            ]);
        }

        return response()->json([
            'success' => false,
            'error' => 'Failed to clear sample data',
        ], 500);
    }

    /**
     * Save progress (for resume later functionality)
     */
    public function saveProgress(Request $request)
    {
        $validated = $request->validate([
            'step_id' => 'required|integer|min:1|max:5',
            'form_data' => 'required|array',
        ]);

        $companyId = $request->user()->cmsUser->company_id;
        $success = $this->onboardingService->saveProgress(
            $companyId,
            $validated['step_id'],
            $validated['form_data']
        );

        if ($success) {
            return response()->json([
                'success' => true,
                'message' => 'Progress saved',
            ]);
        }

        return response()->json([
            'success' => false,
            'error' => 'Failed to save progress',
        ], 500);
    }

    /**
     * Get saved progress for a step
     */
    public function getSavedProgress(Request $request)
    {
        $validated = $request->validate([
            'step_id' => 'required|integer|min:1|max:5',
        ]);

        $companyId = $request->user()->cmsUser->company_id;
        $savedData = $this->onboardingService->getSavedProgress(
            $companyId,
            $validated['step_id']
        );

        return response()->json([
            'success' => true,
            'data' => $savedData,
        ]);
    }
}
