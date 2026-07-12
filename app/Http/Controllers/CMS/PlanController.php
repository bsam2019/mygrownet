<?php

namespace App\Http\Controllers\CMS;

use App\Http\Controllers\Controller;
use App\Http\Controllers\CMS\Concerns\HasCmsAccess;
use App\Infrastructure\Persistence\Eloquent\CMS\PlanModel;
use App\Infrastructure\Persistence\Eloquent\CMS\PlanObjectiveModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class PlanController extends Controller
{
    use HasCmsAccess;

    public function index(Request $request): Response
    {
        $companyId = $this->getCompanyId($request);

        $plans = PlanModel::forCompany($companyId)
            ->with(['children', 'createdBy'])
            ->whereNull('parent_id')
            ->orderBy('sort_order')
            ->orderBy('start_date', 'desc')
            ->get();

        return Inertia::render('CMS/Plans/Index', [
            'plans' => $plans,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('CMS/Plans/Create');
    }

    public function store(Request $request)
    {
        $companyId = $this->getCompanyId($request);
        $cmsUser = $this->getCmsUserOrFail($request);

        $validated = $request->validate([
            'type' => 'required|in:strategic,business,operational,schedule',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'status' => 'nullable|in:draft,active,completed,archived',
            'parent_id' => 'nullable|integer|exists:cms_plans,id',
            'sort_order' => 'nullable|integer|min:0',
            'metadata' => 'nullable|array',
        ]);

        $plan = PlanModel::create([
            'company_id' => $companyId,
            'parent_id' => $validated['parent_id'] ?? null,
            'type' => $validated['type'],
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'start_date' => $validated['start_date'] ?? null,
            'end_date' => $validated['end_date'] ?? null,
            'status' => $validated['status'] ?? 'draft',
            'sort_order' => $validated['sort_order'] ?? 0,
            'metadata' => $validated['metadata'] ?? null,
            'created_by' => $cmsUser->user_id,
        ]);

        return redirect()->route('cms.plans.show', $plan->id)
            ->with('success', 'Plan created successfully.');
    }

    public function show(Request $request, int $id): Response
    {
        $companyId = $this->getCompanyId($request);

        $plan = PlanModel::forCompany($companyId)
            ->with(['children', 'parent', 'createdBy', 'objectives.links.linkable'])
            ->findOrFail($id);

        return Inertia::render('CMS/Plans/Show', [
            'plan' => $plan,
        ]);
    }

    public function edit(Request $request, int $id): Response
    {
        $companyId = $this->getCompanyId($request);

        $plan = PlanModel::forCompany($companyId)->findOrFail($id);

        return Inertia::render('CMS/Plans/Edit', [
            'plan' => $plan,
        ]);
    }

    public function update(Request $request, int $id)
    {
        $companyId = $this->getCompanyId($request);

        $plan = PlanModel::forCompany($companyId)->findOrFail($id);

        $validated = $request->validate([
            'type' => 'required|in:strategic,business,operational,schedule',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'status' => 'nullable|in:draft,active,completed,archived',
            'parent_id' => 'nullable|integer|exists:cms_plans,id',
            'sort_order' => 'nullable|integer|min:0',
            'metadata' => 'nullable|array',
        ]);

        $plan->update($validated);

        return redirect()->route('cms.plans.show', $plan->id)
            ->with('success', 'Plan updated successfully.');
    }

    public function destroy(Request $request, int $id)
    {
        $companyId = $this->getCompanyId($request);

        $plan = PlanModel::forCompany($companyId)->findOrFail($id);

        $plan->delete();

        return redirect()->route('cms.plans.index')
            ->with('success', 'Plan deleted.');
    }

    public function commandCenter(Request $request): Response
    {
        $companyId = $this->getCompanyId($request);

        $plans = PlanModel::forCompany($companyId)
            ->with('objectives')
            ->get();

        $planMap = $plans->keyBy('id');

        $progressData = [];
        foreach ($plans as $plan) {
            $objectives = $plan->objectives;
            $total = $objectives->count();
            $breakdown = [
                'completed' => 0, 'on_track' => 0, 'at_risk' => 0, 'behind' => 0, 'not_started' => 0,
            ];
            $progressSum = 0;
            $progressCount = 0;

            foreach ($objectives as $obj) {
                $breakdown[$obj->status] = ($breakdown[$obj->status] ?? 0) + 1;
                if ($obj->target_value && $obj->target_value > 0) {
                    $progressSum += min(100, ($obj->current_value / $obj->target_value) * 100);
                    $progressCount++;
                }
            }

            $progressData[$plan->id] = [
                'objectives_count' => $total,
                'breakdown' => $breakdown,
                'progress' => $progressCount > 0 ? round($progressSum / $progressCount, 1) : 0,
            ];
        }

        $tree = $this->buildTree($plans->whereNull('parent_id'), $plans, $progressData);

        $summary = [
            'total_plans' => $plans->count(),
            'active_plans' => $plans->where('status', 'active')->count(),
            'total_objectives' => $plans->sum(fn ($p) => $p->objectives->count()),
            'on_track_objectives' => collect($progressData)->sum(fn ($d) => $d['breakdown']['on_track']),
            'at_risk_objectives' => collect($progressData)->sum(fn ($d) => $d['breakdown']['at_risk']),
            'behind_objectives' => collect($progressData)->sum(fn ($d) => $d['breakdown']['behind']),
            'completed_objectives' => collect($progressData)->sum(fn ($d) => $d['breakdown']['completed']),
        ];

        return Inertia::render('CMS/Plans/CommandCenter', [
            'tree' => $tree,
            'summary' => $summary,
        ]);
    }

    private function buildTree($roots, $allPlans, $progressData): array
    {
        return $roots->map(function ($plan) use ($allPlans, $progressData) {
            $children = $allPlans->where('parent_id', $plan->id);
            $childData = $children->isNotEmpty()
                ? $this->buildTree($children, $allPlans, $progressData)
                : [];

            $own = $progressData[$plan->id] ?? ['objectives_count' => 0, 'breakdown' => [], 'progress' => 0];
            $childrenProgress = collect($childData);

            // Roll up children breakdowns
            $mergedBreakdown = $own['breakdown'];
            foreach ($childrenProgress as $child) {
                foreach ($child['breakdown'] as $status => $count) {
                    $mergedBreakdown[$status] = ($mergedBreakdown[$status] ?? 0) + $count;
                }
            }

            $totalObjectives = $own['objectives_count'] + $childrenProgress->sum('objectives_count');

            // Weighted progress rollup
            $totalProgressWeight = $own['progress'] * $own['objectives_count']
                + $childrenProgress->sum(fn ($c) => $c['progress'] * $c['objectives_count']);
            $totalWeightCount = $own['objectives_count'] + $childrenProgress->sum('objectives_count');
            $rolledUpProgress = $totalWeightCount > 0
                ? round($totalProgressWeight / $totalWeightCount, 1)
                : 0;

            return [
                'id' => $plan->id,
                'title' => $plan->title,
                'type' => $plan->type,
                'status' => $plan->status,
                'start_date' => $plan->start_date?->toDateString(),
                'end_date' => $plan->end_date?->toDateString(),
                'objectives_count' => $totalObjectives,
                'progress' => $rolledUpProgress,
                'breakdown' => $mergedBreakdown,
                'children' => $childData,
            ];
        })->values()->toArray();
    }
}
