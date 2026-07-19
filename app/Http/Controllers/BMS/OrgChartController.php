<?php

namespace App\Http\Controllers\BMS;

use App\Http\Controllers\Controller;
use App\Http\Controllers\BMS\Concerns\HasBmsAccess;
use App\Infrastructure\Persistence\Eloquent\BMS\WorkerModel;
use App\Infrastructure\Persistence\Eloquent\BMS\DepartmentModel;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class OrgChartController extends Controller
{
    use HasBmsAccess;

    public function index(Request $request): Response
    {
        $companyId = $this->getCompanyId($request);

        $workers = WorkerModel::forCompany($companyId)
            ->with(['department', 'manager'])
            ->orderBy('name')
            ->get();

        $departments = DepartmentModel::forCompany($companyId)
            ->with('subDepartments')
            ->whereNull('parent_department_id')
            ->orderBy('department_name')
            ->get();

        $tree = $this->buildTree($workers, $departments);

        return Inertia::render('BMS/OrgChart/Index', [
            'orgTree' => $tree,
            'workers' => $workers->map(fn ($w) => [
                'id' => $w->id,
                'name' => $w->first_name ? "{$w->first_name} {$w->last_name}" : $w->name,
                'job_title' => $w->job_title,
                'department_name' => $w->department?->department_name,
                'manager_id' => $w->manager_id,
                'photo' => $w->photo_path,
            ]),
            'departmentTree' => $departments->map(fn ($d) => $this->deptNode($d)),
        ]);
    }

    private function buildTree($workers, $departments): array
    {
        $roots = [];
        $mapped = $workers->keyBy('id');

        foreach ($workers as $w) {
            if ($w->manager_id && $mapped->has($w->manager_id)) continue;
            $roots[] = $this->workerNode($w, $mapped);
        }

        return $roots;
    }

    private function workerNode($worker, $mapped): array
    {
        $name = $worker->first_name ? "{$worker->first_name} {$worker->last_name}" : $worker->name;
        return [
            'id' => $worker->id,
            'name' => $name,
            'job_title' => $worker->job_title,
            'department' => $worker->department?->department_name,
            'photo' => $worker->photo_path,
            'children' => $mapped
                ->where('manager_id', $worker->id)
                ->values()
                ->map(fn ($child) => $this->workerNode($child, $mapped))
                ->values()
                ->all(),
        ];
    }

    private function deptNode($dept): array
    {
        return [
            'id' => $dept->id,
            'name' => $dept->department_name,
            'children' => $dept->subDepartments->map(fn ($child) => $this->deptNode($child))->values()->all(),
        ];
    }
}
