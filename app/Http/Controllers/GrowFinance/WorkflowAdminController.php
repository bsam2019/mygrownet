<?php

namespace App\Http\Controllers\GrowFinance;

use App\Domain\GrowFinance\Services\WorkflowEngine;
use App\Domain\GrowFinance\Repositories\WorkflowTemplateRepositoryInterface;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class WorkflowAdminController extends Controller
{
    public function __construct(
        private WorkflowEngine $workflowEngine,
        private WorkflowTemplateRepositoryInterface $templateRepo,
    ) {}

    public function index(Request $request): Response
    {
        $businessId = $request->user()->id;

        return Inertia::render('GrowFinance/Workflow/Admin', [
            'templates' => array_map(fn($t) => $t->toArray(), $this->workflowEngine->getTemplates($businessId)),
        ]);
    }

    public function updateSla(Request $request, int $id): RedirectResponse
    {
        $validated = $request->validate([
            'sla_hours' => 'nullable|integer|min:1|max:8760',
            'allow_escalation' => 'boolean',
        ]);

        $this->workflowEngine->setSla($id, $validated['sla_hours'] ?? null, $validated['allow_escalation'] ?? false);

        return redirect()->back()->with('success', 'SLA settings updated');
    }
}
