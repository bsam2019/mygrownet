<?php

namespace App\Http\Controllers\GrowFinance;

use App\Domain\GrowFinance\Services\WorkflowEngine;
use App\Domain\GrowFinance\Repositories\WorkflowTemplateRepositoryInterface;
use App\Domain\GrowFinance\Repositories\WorkflowInstanceRepositoryInterface;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class WorkflowController extends Controller
{
    public function __construct(
        private WorkflowEngine $workflowEngine,
        private WorkflowTemplateRepositoryInterface $templateRepo,
        private WorkflowInstanceRepositoryInterface $instanceRepo,
    ) {}

    public function index(Request $request): Response
    {
        $businessId = $request->user()->id;

        $pendingApprovals = $this->workflowEngine->getPendingApprovals($businessId, $request->user()->id);
        $templates = $this->workflowEngine->getTemplates($businessId);

        return Inertia::render('GrowFinance/Workflow/Index', [
            'pendingApprovals' => array_map(fn($i) => $this->enrichInstance($i, $request), $pendingApprovals),
            'templates' => array_map(fn($t) => $t->toArray(), $templates),
        ]);
    }

    public function storeTemplate(Request $request): RedirectResponse
    {
        $businessId = $request->user()->id;

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'entity_type' => 'required|string|in:journal,invoice,expense',
            'steps' => 'required|array|min:1',
            'steps.*.step_order' => 'required|integer|min:0',
            'steps.*.role' => 'required|string|max:50',
            'steps.*.approver_id' => 'nullable|integer|exists:users,id',
            'steps.*.action' => 'nullable|string|max:50',
        ]);

        $template = new \App\Domain\GrowFinance\Entities\WorkflowTemplate(
            id: null,
            businessId: $businessId,
            name: $validated['name'],
            description: $validated['description'] ?? null,
            entityType: $validated['entity_type'],
            steps: $validated['steps'],
        );

        $this->templateRepo->save($template);

        return redirect()->route('growfinance.workflow.templates')
            ->with('success', 'Workflow template created successfully.');
    }

    public function approve(Request $request, int $instanceId): RedirectResponse
    {
        $validated = $request->validate([
            'comment' => 'nullable|string|max:500',
        ]);

        try {
            $this->workflowEngine->approve($instanceId, $request->user()->id, $validated['comment'] ?? null);
            return redirect()->route('growfinance.workflow.index')
                ->with('success', 'Step approved successfully.');
        } catch (\DomainException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function reject(Request $request, int $instanceId): RedirectResponse
    {
        $validated = $request->validate([
            'comment' => 'required|string|max:500',
        ]);

        try {
            $this->workflowEngine->reject($instanceId, $request->user()->id, $validated['comment']);
            return redirect()->route('growfinance.workflow.index')
                ->with('info', 'Step rejected.');
        } catch (\DomainException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function templates(Request $request): Response
    {
        $businessId = $request->user()->id;
        $templates = $this->workflowEngine->getTemplates($businessId);

        return Inertia::render('GrowFinance/Workflow/Templates', [
            'templates' => array_map(fn($t) => $t->toArray(), $templates),
        ]);
    }

    private function enrichInstance(\App\Domain\GrowFinance\Entities\WorkflowInstance $instance, Request $request): array
    {
        $data = $instance->toArray();
        $data['approval_log'] = $instance->approvalLog;

        $template = $this->templateRepo->findById($instance->workflowTemplateId);
        $data['template_name'] = $template?->name ?? 'Unknown';
        $data['steps_config'] = $template ? (is_string($template->steps) ? json_decode($template->steps, true) : $template->steps) : [];

        $requester = \App\Models\User::find($instance->requestedBy);
        $data['requester_name'] = $requester?->name ?? 'Unknown';

        return $data;
    }
}
