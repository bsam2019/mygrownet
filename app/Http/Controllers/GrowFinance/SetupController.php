<?php

namespace App\Http\Controllers\GrowFinance;

use App\Domain\GrowFinance\Services\AccountingService;
use App\Domain\GrowFinance\Services\ProfileSetupService;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SetupController extends Controller
{
    public function __construct(
        private readonly AccountingService $accountingService,
        private readonly ProfileSetupService $profileSetupService
    ) {}

    public function index(): Response
    {
        return Inertia::render('GrowFinance/Setup/Index');
    }

    public function initialize(Request $request): RedirectResponse
    {
        $businessId = $request->user()->id;

        $this->accountingService->initializeChartOfAccounts($businessId);

        return redirect()->route('growfinance.dashboard')
            ->with('success', 'GrowFinance has been set up successfully!');
    }

    public function wizard(Request $request): Response
    {
        $userId = $request->user()->id;
        $wizardData = $this->profileSetupService->getWizardData($userId);

        return Inertia::render('GrowFinance/Setup/SetupWizard', [
            'wizardData' => $wizardData,
        ]);
    }

    public function saveIncome(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'sources' => 'required|array|min:1',
            'sources.*.source_type' => 'required|string|in:salary,business,freelance,investment,rental,other',
            'sources.*.amount' => 'required|numeric|min:0',
            'sources.*.frequency' => 'required|string|in:weekly,bi-weekly,monthly,quarterly,annually',
            'sources.*.next_expected_date' => 'nullable|date',
            'sources.*.is_primary' => 'nullable|boolean',
            'replace_existing' => 'nullable|boolean',
        ]);

        $this->profileSetupService->saveIncome($request->user()->id, $validated);

        return back()->with('success', 'Income sources saved successfully.');
    }

    public function saveCategories(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'categories' => 'required|array|min:3',
            'categories.*.category_name' => 'required|string|max:100',
            'categories.*.category_type' => 'required|string|in:essential,lifestyle,financial,other',
            'categories.*.estimated_monthly_amount' => 'nullable|numeric|min:0',
            'categories.*.is_fixed' => 'nullable|boolean',
            'categories.*.is_active' => 'nullable|boolean',
            'replace_existing' => 'nullable|boolean',
        ]);

        $this->profileSetupService->saveCategories($request->user()->id, $validated);

        return back()->with('success', 'Expense categories saved successfully.');
    }

    public function saveGoals(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'goals' => 'nullable|array',
            'goals.*.goal_name' => 'required|string|max:200',
            'goals.*.target_amount' => 'required|numeric|min:1',
            'goals.*.target_date' => 'nullable|date|after:today',
            'goals.*.priority' => 'nullable|string|in:high,medium,low',
        ]);

        if (!empty($validated['goals'])) {
            $this->profileSetupService->saveGoals($request->user()->id, $validated);
        }

        return back()->with('success', 'Savings goals saved successfully.');
    }

    public function savePreferences(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'budget_method' => 'required|string|in:50/30/20,zero_based,custom',
            'budget_period' => 'required|string|in:weekly,bi-weekly,monthly',
            'currency' => 'nullable|string|size:3',
            'alert_preferences' => 'nullable|array',
            'alert_preferences.budget_warning' => 'nullable|boolean',
            'alert_preferences.over_budget' => 'nullable|boolean',
            'alert_preferences.weekly_summary' => 'nullable|boolean',
        ]);

        $this->profileSetupService->savePreferences($request->user()->id, $validated);

        return back()->with('success', 'Preferences saved successfully.');
    }

    public function complete(Request $request): RedirectResponse
    {
        $userId = $request->user()->id;

        // Initialize chart of accounts if not already done
        $this->accountingService->initializeChartOfAccounts($userId);

        // Mark profile setup as complete
        $this->profileSetupService->completeSetup($userId);

        return redirect()->route('growfinance.dashboard')
            ->with('success', 'Welcome to GrowFinance! Your profile is all set up.');
    }

    public function skip(Request $request): RedirectResponse
    {
        $userId = $request->user()->id;

        // Initialize chart of accounts
        $this->accountingService->initializeChartOfAccounts($userId);

        // Mark as complete (skipped)
        $this->profileSetupService->completeSetup($userId);

        return redirect()->route('growfinance.dashboard')
            ->with('info', 'Setup skipped. You can complete your profile later in Settings.');
    }

    public function summary(Request $request): Response
    {
        $userId = $request->user()->id;
        $summary = $this->profileSetupService->getSetupSummary($userId);
        $wizardData = $this->profileSetupService->getWizardData($userId);

        return Inertia::render('GrowFinance/Setup/SetupWizard', [
            'wizardData' => $wizardData,
            'summary' => $summary,
            'currentStep' => 6,
        ]);
    }
}
