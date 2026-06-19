<?php

namespace App\Http\Controllers\CMS;

use App\Http\Controllers\Controller;
use App\Http\Controllers\CMS\Concerns\HasCmsAccess;
use App\Infrastructure\Persistence\Eloquent\CMS\KpiModel;
use App\Infrastructure\Persistence\Eloquent\CMS\KpiValueModel;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class KpiController extends Controller
{
    use HasCmsAccess;

    public function index(Request $request): Response
    {
        $companyId = $this->getCompanyId($request);

        $kpis = KpiModel::forCompany($companyId)
            ->with(['values' => function ($q) { $q->orderBy('period_end', 'desc')->take(10); }])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get()
            ->map(fn ($kpi) => [
                'id' => $kpi->id,
                'name' => $kpi->name,
                'description' => $kpi->description,
                'category' => $kpi->category,
                'unit' => $kpi->unit,
                'frequency' => $kpi->frequency,
                'target_min' => $kpi->target_min,
                'target_max' => $kpi->target_max,
                'direction' => $kpi->direction,
                'owner' => $kpi->owner,
                'status' => $kpi->status,
                'latest_value' => $kpi->latestValue()?->value,
                'latest_period' => $kpi->latestValue()?->period_end?->toDateString(),
                'trend' => $kpi->trend(),
                'status_color' => $kpi->statusColor(),
                'values_count' => $kpi->values->count(),
                'sparkline' => $kpi->values->sortBy('period_end')->values()->map(fn ($v) => $v->value),
            ]);

        return Inertia::render('CMS/Kpis/Index', [
            'kpis' => $kpis,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('CMS/Kpis/Create');
    }

    public function store(Request $request)
    {
        $companyId = $this->getCompanyId($request);
        $cmsUser = $this->getCmsUserOrFail($request);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category' => 'required|in:financial,operational,customer,employee,quality',
            'unit' => 'nullable|string|max:50',
            'frequency' => 'required|in:weekly,monthly,quarterly,yearly',
            'target_min' => 'nullable|numeric',
            'target_max' => 'nullable|numeric',
            'direction' => 'required|in:up,down,neutral',
            'formula' => 'nullable|string|max:500',
            'owner' => 'nullable|string|max:255',
            'status' => 'nullable|in:active,inactive,archived',
        ]);

        $kpi = KpiModel::create([
            'company_id' => $companyId,
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'category' => $validated['category'],
            'metric_type' => $validated['category'],
            'unit' => $validated['unit'] ?? null,
            'frequency' => $validated['frequency'],
            'target_min' => $validated['target_min'] ?? null,
            'target_max' => $validated['target_max'] ?? null,
            'direction' => $validated['direction'],
            'calculation_method' => $validated['formula'] ?? null,
            'owner' => $validated['owner'] ?? null,
            'is_active' => ($validated['status'] ?? 'active') === 'active',
            'sort_order' => 0,
            'created_by' => $cmsUser->user_id,
        ]);

        return redirect()->route('cms.kpis.show', $kpi->id)
            ->with('success', 'KPI created.');
    }

    public function show(Request $request, int $id): Response
    {
        $companyId = $this->getCompanyId($request);

        $kpi = KpiModel::forCompany($companyId)
            ->with(['values.recordedBy'])
            ->findOrFail($id);

        $values = $kpi->values()->orderBy('period_end', 'desc')->get();

        return Inertia::render('CMS/Kpis/Show', [
            'kpi' => $kpi,
            'values' => $values,
        ]);
    }

    public function edit(Request $request, int $id): Response
    {
        $companyId = $this->getCompanyId($request);
        $kpi = KpiModel::forCompany($companyId)->findOrFail($id);

        return Inertia::render('CMS/Kpis/Edit', [
            'kpi' => $kpi,
        ]);
    }

    public function update(Request $request, int $id)
    {
        $companyId = $this->getCompanyId($request);
        $kpi = KpiModel::forCompany($companyId)->findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category' => 'required|in:financial,operational,customer,employee,quality',
            'unit' => 'nullable|string|max:50',
            'frequency' => 'required|in:weekly,monthly,quarterly,yearly',
            'target_min' => 'nullable|numeric',
            'target_max' => 'nullable|numeric',
            'direction' => 'required|in:up,down,neutral',
            'formula' => 'nullable|string|max:500',
            'owner' => 'nullable|string|max:255',
            'status' => 'nullable|in:active,inactive,archived',
        ]);

        $kpi->update([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'category' => $validated['category'],
            'metric_type' => $validated['category'],
            'unit' => $validated['unit'] ?? null,
            'frequency' => $validated['frequency'],
            'target_min' => $validated['target_min'] ?? null,
            'target_max' => $validated['target_max'] ?? null,
            'direction' => $validated['direction'],
            'calculation_method' => $validated['formula'] ?? null,
            'owner' => $validated['owner'] ?? null,
            'is_active' => ($validated['status'] ?? 'active') === 'active',
        ]);

        return redirect()->route('cms.kpis.show', $kpi->id)
            ->with('success', 'KPI updated.');
    }

    public function destroy(Request $request, int $id)
    {
        $companyId = $this->getCompanyId($request);
        $kpi = KpiModel::forCompany($companyId)->findOrFail($id);
        $kpi->delete();

        return redirect()->route('cms.kpis.index')
            ->with('success', 'KPI deleted.');
    }

    public function recordValue(Request $request, int $id)
    {
        $companyId = $this->getCompanyId($request);
        $kpi = KpiModel::forCompany($companyId)->findOrFail($id);
        $cmsUser = $this->getCmsUserOrFail($request);

        $validated = $request->validate([
            'value' => 'required|numeric',
            'period_start' => 'required|date',
            'period_end' => 'required|date|after_or_equal:period_start',
            'notes' => 'nullable|string',
        ]);

        $kpi->values()->create([
            'value' => $validated['value'],
            'period_date' => $validated['period_start'],
            'period_start' => $validated['period_start'],
            'period_end' => $validated['period_end'],
            'notes' => $validated['notes'] ?? null,
            'recorded_by' => $cmsUser->user_id,
        ]);

        return redirect()->route('cms.kpis.show', $kpi->id)
            ->with('success', 'Value recorded.');
    }

    public function deleteValue(Request $request, int $id, int $valueId)
    {
        $companyId = $this->getCompanyId($request);
        $kpi = KpiModel::forCompany($companyId)->findOrFail($id);
        $kpi->values()->where('id', $valueId)->delete();

        return redirect()->route('cms.kpis.show', $kpi->id)
            ->with('success', 'Value removed.');
    }
}
