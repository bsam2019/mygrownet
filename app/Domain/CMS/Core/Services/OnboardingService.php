<?php

namespace App\Domain\CMS\Core\Services;

use App\Infrastructure\Persistence\Eloquent\CMS\CompanyModel;
use App\Infrastructure\Persistence\Eloquent\CMS\CmsUserModel;
use Illuminate\Support\Facades\DB;

class OnboardingService
{
    public function __construct(
        private IndustryPresetService $industryPresetService,
        private CompanySettingsService $settingsService,
        private SampleDataService $sampleDataService
    ) {}

    /**
     * Get onboarding status for a company
     */
    public function getOnboardingStatus(int $companyId): array
    {
        $company = CompanyModel::find($companyId);

        if (!$company) {
            return [
                'completed' => false,
                'progress' => [],
                'current_step' => 1,
            ];
        }

        $progress = $company->onboarding_progress ?? [];

        return [
            'completed' => $company->onboarding_completed,
            'completed_at' => $company->onboarding_completed_at,
            'progress' => $progress,
            'current_step' => $this->getCurrentStep($progress),
            'steps' => $this->getSteps(),
        ];
    }

    /**
     * Get onboarding steps
     */
    public function getSteps(): array
    {
        return [
            [
                'id' => 1,
                'name' => 'Company Information',
                'description' => 'Basic company details and contact information',
                'required' => true,
            ],
            [
                'id' => 2,
                'name' => 'Industry Selection',
                'description' => 'Choose your industry and apply preset configurations',
                'required' => true,
            ],
            [
                'id' => 3,
                'name' => 'Business Settings',
                'description' => 'Configure business hours, tax settings, and preferences',
                'required' => true,
            ],
            [
                'id' => 4,
                'name' => 'Team Setup',
                'description' => 'Add team members and assign roles',
                'required' => false,
            ],
            [
                'id' => 5,
                'name' => 'First Customer',
                'description' => 'Add your first customer or job (optional)',
                'required' => false,
            ],
        ];
    }

    /**
     * Get current step based on progress
     */
    private function getCurrentStep(array $progress): int
    {
        if (empty($progress)) {
            return 1;
        }

        $completedSteps = array_filter($progress, fn($step) => $step['completed'] ?? false);
        $maxCompletedStep = empty($completedSteps) ? 0 : max(array_keys($completedSteps));

        return min($maxCompletedStep + 1, 5);
    }

    /**
     * Complete a step
     */
    public function completeStep(int $companyId, int $stepId, array $data = []): bool
    {
        $company = CompanyModel::find($companyId);

        if (!$company) {
            return false;
        }

        $progress = $company->onboarding_progress ?? [];
        $progress[$stepId] = [
            'completed' => true,
            'completed_at' => now()->toISOString(),
            'data' => $data,
        ];

        $company->update([
            'onboarding_progress' => $progress,
        ]);

        // Check if all required steps are completed
        $this->checkOnboardingCompletion($company);

        return true;
    }

    /**
     * Update company information (Step 1)
     */
    public function updateCompanyInformation(int $companyId, array $data): bool
    {
        $company = CompanyModel::find($companyId);

        if (!$company) {
            return false;
        }

        $company->update([
            'name' => $data['name'] ?? $company->name,
            'business_registration_number' => $data['business_registration_number'] ?? null,
            'tax_number' => $data['tax_number'] ?? null,
            'address' => $data['address'] ?? null,
            'city' => $data['city'] ?? null,
            'country' => $data['country'] ?? 'Zambia',
            'phone' => $data['phone'] ?? null,
            'email' => $data['email'] ?? null,
            'website' => $data['website'] ?? null,
        ]);

        $this->completeStep($companyId, 1, [
            'company_name' => $data['name'] ?? $company->name,
        ]);

        return true;
    }

    /**
     * Apply industry preset (Step 2)
     */
    public function applyIndustryPreset(int $companyId, string $presetCode): bool
    {
        $success = $this->industryPresetService->applyPresetToCompany($companyId, $presetCode);

        if ($success) {
            $this->completeStep($companyId, 2, [
                'industry' => $presetCode,
            ]);
        }

        return $success;
    }

    /**
     * Configure business settings (Step 3)
     */
    public function configureBusinessSettings(int $companyId, array $settings): bool
    {
        DB::beginTransaction();

        try {
            // Update business hours
            if (isset($settings['business_hours'])) {
                $this->settingsService->updateBusinessHours($companyId, $settings['business_hours']);
            }

            // Update tax settings
            if (isset($settings['tax'])) {
                $this->settingsService->updateTaxSettings($companyId, $settings['tax']);
            }

            // Update approval thresholds
            if (isset($settings['approval_thresholds'])) {
                $this->settingsService->updateApprovalThresholds($companyId, $settings['approval_thresholds']);
            }

            $this->completeStep($companyId, 3, [
                'settings_configured' => true,
            ]);

            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            return false;
        }
    }

    /**
     * Skip a step
     */
    public function skipStep(int $companyId, int $stepId): bool
    {
        $company = CompanyModel::find($companyId);

        if (!$company) {
            return false;
        }

        $progress = $company->onboarding_progress ?? [];
        $progress[$stepId] = [
            'completed' => true,
            'skipped' => true,
            'completed_at' => now()->toISOString(),
        ];

        $company->update([
            'onboarding_progress' => $progress,
        ]);

        $this->checkOnboardingCompletion($company);

        return true;
    }

    /**
     * Check if onboarding is complete
     */
    private function checkOnboardingCompletion(CompanyModel $company): void
    {
        $progress = $company->onboarding_progress ?? [];
        $steps = $this->getSteps();

        // Check if all required steps are completed
        $requiredSteps = array_filter($steps, fn($step) => $step['required']);
        $allRequiredCompleted = true;

        foreach ($requiredSteps as $step) {
            if (!isset($progress[$step['id']]) || !($progress[$step['id']]['completed'] ?? false)) {
                $allRequiredCompleted = false;
                break;
            }
        }

        if ($allRequiredCompleted && !$company->onboarding_completed) {
            $company->update([
                'onboarding_completed' => true,
                'onboarding_completed_at' => now(),
            ]);
        }
    }

    /**
     * Reset onboarding
     */
    public function resetOnboarding(int $companyId): bool
    {
        $company = CompanyModel::find($companyId);

        if (!$company) {
            return false;
        }

        $company->update([
            'onboarding_completed' => false,
            'onboarding_progress' => [],
            'onboarding_completed_at' => null,
        ]);

        return true;
    }

    /**
     * Get tour status for a user
     */
    public function getTourStatus(int $userId): array
    {
        $cmsUser = CmsUserModel::where('user_id', $userId)->first();

        if (!$cmsUser) {
            return [
                'completed' => false,
                'progress' => [],
            ];
        }

        return [
            'completed' => $cmsUser->tour_completed,
            'progress' => $cmsUser->tour_progress ?? [],
            'steps' => $this->getTourSteps(),
        ];
    }

    /**
     * Get tour steps
     */
    public function getTourSteps(): array
    {
        return [
            [
                'id' => 'dashboard',
                'name' => 'Dashboard Overview',
                'description' => 'Learn about your dashboard and key metrics',
            ],
            [
                'id' => 'navigation',
                'name' => 'Navigation',
                'description' => 'Explore the main navigation menu',
            ],
            [
                'id' => 'jobs',
                'name' => 'Jobs & Projects',
                'description' => 'Create and manage jobs',
            ],
            [
                'id' => 'customers',
                'name' => 'Customer Management',
                'description' => 'Add and manage customers',
            ],
            [
                'id' => 'invoicing',
                'name' => 'Invoicing',
                'description' => 'Create and send invoices',
            ],
        ];
    }

    /**
     * Complete tour step
     */
    public function completeTourStep(int $userId, string $stepId): bool
    {
        $cmsUser = CmsUserModel::where('user_id', $userId)->first();

        if (!$cmsUser) {
            return false;
        }

        $progress = $cmsUser->tour_progress ?? [];
        $progress[$stepId] = [
            'completed' => true,
            'completed_at' => now()->toISOString(),
        ];

        $cmsUser->update([
            'tour_progress' => $progress,
        ]);

        // Check if all steps are completed
        $steps = $this->getTourSteps();
        $allCompleted = true;

        foreach ($steps as $step) {
            if (!isset($progress[$step['id']]) || !($progress[$step['id']]['completed'] ?? false)) {
                $allCompleted = false;
                break;
            }
        }

        if ($allCompleted && !$cmsUser->tour_completed) {
            $cmsUser->update([
                'tour_completed' => true,
            ]);
        }

        return true;
    }

    /**
     * Generate sample data (Step 4/5)
     */
    public function generateSampleData(int $companyId, int $userId): array
    {
        try {
            $result = $this->sampleDataService->generateSampleData($companyId, $userId);
            
            $this->completeStep($companyId, 5, [
                'sample_data_generated' => true,
                'customers_created' => $result['customers'],
                'jobs_created' => $result['jobs'],
                'invoices_created' => $result['invoices'],
            ]);

            return [
                'success' => true,
                'data' => $result,
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Clear sample data
     */
    public function clearSampleData(int $companyId): bool
    {
        return $this->sampleDataService->clearSampleData($companyId);
    }

    /**
     * Save progress (for resume later functionality)
     */
    public function saveProgress(int $companyId, int $stepId, array $formData): bool
    {
        $company = CompanyModel::find($companyId);

        if (!$company) {
            return false;
        }

        $progress = $company->onboarding_progress ?? [];
        
        if (!isset($progress[$stepId])) {
            $progress[$stepId] = [
                'completed' => false,
            ];
        }

        $progress[$stepId]['saved_data'] = $formData;
        $progress[$stepId]['last_saved_at'] = now()->toISOString();

        $company->update([
            'onboarding_progress' => $progress,
        ]);

        return true;
    }

    /**
     * Get saved progress for a step
     */
    public function getSavedProgress(int $companyId, int $stepId): ?array
    {
        $company = CompanyModel::find($companyId);

        if (!$company) {
            return null;
        }

        $progress = $company->onboarding_progress ?? [];
        
        return $progress[$stepId]['saved_data'] ?? null;
    }

    /**
     * Skip tour
     */
    public function skipTour(int $userId): bool
    {
        $cmsUser = CmsUserModel::where('user_id', $userId)->first();

        if (!$cmsUser) {
            return false;
        }

        $cmsUser->update([
            'tour_completed' => true,
            'tour_progress' => ['skipped' => true],
        ]);

        return true;
    }
}
