<?php

namespace App\Http\Controllers\BMS;

use App\Http\Controllers\Controller;
use App\Http\Controllers\BMS\Concerns\HasBmsAccess;
use App\Infrastructure\Persistence\Eloquent\BMS\PlanModel;
use App\Infrastructure\Persistence\Eloquent\BMS\PlanObjectiveModel;
use App\Infrastructure\Persistence\Eloquent\BMS\PlanLinkModel;
use App\Infrastructure\Persistence\Eloquent\BMS\BudgetModel;
use App\Infrastructure\Persistence\Eloquent\BMS\JobModel;
use App\Infrastructure\Persistence\Eloquent\BMS\GoalModel;
use App\Infrastructure\Persistence\Eloquent\BMS\CustomerModel;
use App\Infrastructure\Persistence\Eloquent\BMS\InvoiceModel;
use App\Infrastructure\Persistence\Eloquent\BMS\TaskModel;
use Illuminate\Http\Request;

class PlanObjectiveController extends Controller
{
    use HasBmsAccess;

    private function findPlan(Request $request, int $planId): PlanModel
    {
        $companyId = $this->getCompanyId($request);
        return PlanModel::forCompany($companyId)->findOrFail($planId);
    }

    private function findObjective(Request $request, int $planId, int $id): PlanObjectiveModel
    {
        $plan = $this->findPlan($request, $planId);
        return $plan->objectives()->findOrFail($id);
    }

    public function store(Request $request, int $planId)
    {
        $plan = $this->findPlan($request, $planId);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:kpi,milestone,key_result',
            'kpi_id' => 'nullable|integer|exists:cms_kpis,id',
            'target_value' => 'nullable|numeric|min:0',
            'current_value' => 'nullable|numeric|min:0',
            'unit' => 'nullable|string|max:50',
            'owner' => 'nullable|string|max:255',
            'target_date' => 'nullable|date',
            'status' => 'nullable|in:not_started,on_track,at_risk,behind,completed',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $objective = $plan->objectives()->create($validated);

        return redirect()->route('bms.plans.show', $plan->id)
            ->with('success', 'Objective added.');
    }

    public function update(Request $request, int $planId, int $id)
    {
        $objective = $this->findObjective($request, $planId, $id);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'type' => 'required|in:kpi,milestone,key_result',
            'kpi_id' => 'nullable|integer|exists:cms_kpis,id',
            'target_value' => 'nullable|numeric|min:0',
            'current_value' => 'nullable|numeric|min:0',
            'unit' => 'nullable|string|max:50',
            'owner' => 'nullable|string|max:255',
            'target_date' => 'nullable|date',
            'status' => 'nullable|in:not_started,on_track,at_risk,behind,completed',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $objective->update($validated);

        return redirect()->route('bms.plans.show', $objective->plan_id)
            ->with('success', 'Objective updated.');
    }

    public function destroy(Request $request, int $planId, int $id)
    {
        $objective = $this->findObjective($request, $planId, $id);
        $planId = $objective->plan_id;
        $objective->delete();

        return redirect()->route('bms.plans.show', $planId)
            ->with('success', 'Objective removed.');
    }

    public function syncKpi(Request $request, int $planId, int $id)
    {
        $objective = $this->findObjective($request, $planId, $id);
        $synced = $objective->syncFromKpi();

        return redirect()->route('bms.plans.show', $objective->plan_id)
            ->with('success', $synced ? 'Value synced from linked KPI.' : 'No KPI linked or no values found.');
    }

    public function link(Request $request, int $planId, int $id)
    {
        $objective = $this->findObjective($request, $planId, $id);

        $validated = $request->validate([
            'linkable_type' => 'required|string',
            'linkable_id' => 'required|integer',
            'metric_field' => 'nullable|string|max:100',
            'auto_sync' => 'nullable|boolean',
            'label' => 'nullable|string|max:255',
        ]);

        $link = $objective->links()->create([
            'linkable_type' => $validated['linkable_type'],
            'linkable_id' => $validated['linkable_id'],
            'metric_field' => $validated['metric_field'] ?? null,
            'auto_sync' => $validated['auto_sync'] ?? false,
            'label' => $validated['label'] ?? null,
        ]);

        if ($link->auto_sync) {
            $link->load('linkable');
            $link->syncValue();
        }

        return redirect()->route('bms.plans.show', $objective->plan_id)
            ->with('success', 'Entity linked to objective.');
    }

    public function unlink(Request $request, int $planId, int $id, int $linkId)
    {
        $objective = $this->findObjective($request, $planId, $id);
        $objective->links()->where('id', $linkId)->delete();

        return redirect()->route('bms.plans.show', $objective->plan_id)
            ->with('success', 'Link removed.');
    }

    public function syncLink(Request $request, int $planId, int $id, int $linkId)
    {
        $objective = $this->findObjective($request, $planId, $id);
        $link = $objective->links()->findOrFail($linkId);
        $link->load('linkable');
        $link->syncValue();

        return redirect()->route('bms.plans.show', $objective->plan_id)
            ->with('success', 'Value synced from linked entity.');
    }

    public function searchEntities(Request $request)
    {
        $companyId = $this->getCompanyId($request);
        $type = $request->query('type');
        $q = $request->query('q', '');

        $map = [
            'budget'   => ['model' => BudgetModel::class, 'label' => 'name', 'search' => 'name'],
            'job'      => ['model' => JobModel::class, 'label' => 'job_title', 'search' => 'job_title'],
            'goal'     => ['model' => GoalModel::class, 'label' => 'goal_title', 'search' => 'goal_title'],
            'customer' => ['model' => CustomerModel::class, 'label' => 'name', 'search' => 'name'],
            'invoice'  => ['model' => InvoiceModel::class, 'label' => 'invoice_number', 'search' => 'invoice_number'],
            'task'     => ['model' => TaskModel::class, 'label' => 'title', 'search' => 'title'],
        ];

        if (!$type || !isset($map[$type])) {
            return response()->json(['results' => []]);
        }

        $cfg = $map[$type];
        $model = $cfg['model'];

        $query = $model::forCompany($companyId);

        if ($q) {
            $query->where($cfg['search'], 'like', "%{$q}%");
        }

        $results = $query->limit(20)->get()->map(fn ($item) => [
            'id'    => $item->id,
            'label' => $item->{$cfg['label']} ?? "#{$item->id}",
            'type'  => $type,
        ]);

        return response()->json(['results' => $results]);
    }

    public function availableFields(Request $request)
    {
        $type = $request->query('type');

        $fields = [
            'budget'   => ['total_budget', 'total_spent', 'remaining'],
            'job'      => ['actual_value', 'quoted_value', 'profit_amount', 'total_cost'],
            'goal'     => ['progress_percentage'],
            'customer' => ['outstanding_balance'],
            'invoice'  => ['total_amount', 'amount_due', 'amount_paid'],
            'task'     => ['estimated_hours', 'actual_hours'],
        ];

        return response()->json(['fields' => $fields[$type] ?? []]);
    }
}
