<?php

namespace App\Http\Controllers\CMS;

use App\Http\Controllers\Controller;
use App\Domain\CMS\Core\Services\OnboardingService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;

class OnboardingController extends Controller
{
    public function __construct(
        private OnboardingService $onboardingService
    ) {}

    public function templatesIndex(Request $request)
    {
        $companyId = $request->user()->company_id;
        
        $templates = \App\Infrastructure\Persistence\Eloquent\CMS\OnboardingTemplateModel::where('company_id', $companyId)
            ->with(['department', 'tasks'])
            ->get();

        return Inertia::render('CMS/Onboarding/Templates', [
            'templates' => $templates,
        ]);
    }

    public function templatesStore(Request $request)
    {
        $validated = $request->validate([
            'template_name' => 'required|string|max:255',
            'department_id' => 'nullable|exists:cms_departments,id',
            'is_default' => 'boolean',
        ]);

        $this->onboardingService->createTemplate($request->user()->company_id, $validated);

        return back()->with('success', 'Template created successfully');
    }

    public function tasksStore(Request $request, int $templateId)
    {
        $validated = $request->validate([
            'task_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'task_category' => 'required|in:documentation,system_access,training,equipment,introduction,other',
            'assigned_to_role' => 'required|in:hr,it,manager,employee',
            'due_days_after_start' => 'required|integer|min:0',
            'is_mandatory' => 'boolean',
            'display_order' => 'nullable|integer',
        ]);

        $this->onboardingService->addTaskToTemplate($templateId, $validated);

        return back()->with('success', 'Task added successfully');
    }

    public function employeeOnboardingIndex(Request $request)
    {
        $companyId = $request->user()->company_id;
        
        $onboardings = \App\Infrastructure\Persistence\Eloquent\CMS\EmployeeOnboardingModel::whereHas('worker', function ($query) use ($companyId) {
            $query->where('company_id', $companyId);
        })
            ->with(['worker', 'template', 'taskProgress'])
            ->orderBy('start_date', 'desc')
            ->get();

        return Inertia::render('CMS/Onboarding/EmployeeOnboarding', [
            'onboardings' => $onboardings,
        ]);
    }

    public function employeeOnboardingStore(Request $request)
    {
        $validated = $request->validate([
            'worker_id' => 'required|exists:cms_workers,id',
            'template_id' => 'required|exists:cms_onboarding_templates,id',
            'start_date' => 'required|date',
        ]);

        $this->onboardingService->startOnboarding(
            $validated['worker_id'],
            $validated['template_id'],
            Carbon::parse($validated['start_date'])
        );

        return back()->with('success', 'Onboarding started successfully');
    }

    public function taskProgressComplete(Request $request, int $taskProgressId)
    {
        $validated = $request->validate([
            'notes' => 'nullable|string',
        ]);

        $this->onboardingService->completeTask(
            $taskProgressId,
            $request->user()->id,
            $validated['notes'] ?? null
        );

        return back()->with('success', 'Task marked as complete');
    }

    public function employeeOnboardingShow(Request $request, int $workerId)
    {
        $onboarding = $this->onboardingService->getOnboardingProgress($workerId);

        return Inertia::render('CMS/Onboarding/Show', [
            'onboarding' => $onboarding,
        ]);
    }
}
