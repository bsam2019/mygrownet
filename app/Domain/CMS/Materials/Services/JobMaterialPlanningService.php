<?php

namespace App\Domain\CMS\Materials\Services;

use App\Infrastructure\Persistence\Eloquent\CMS\JobModel;
use App\Infrastructure\Persistence\Eloquent\CMS\JobMaterialPlanModel;
use App\Infrastructure\Persistence\Eloquent\CMS\MaterialModel;
use App\Infrastructure\Persistence\Eloquent\CMS\MaterialTemplateModel;
use Illuminate\Support\Facades\DB;

class JobMaterialPlanningService
{
    public function addMaterialToJob(array $data): JobMaterialPlanModel
    {
        return DB::transaction(function () use ($data) {
            $material = MaterialModel::findOrFail($data['material_id']);

            $plan = JobMaterialPlanModel::create([
                'job_id' => $data['job_id'],
                'material_id' => $data['material_id'],
                'planned_quantity' => $data['planned_quantity'],
                'unit_price' => $data['unit_price'] ?? $material->current_price,
                'total_cost' => 0, // Will be calculated
                'wastage_percentage' => $data['wastage_percentage'] ?? 0,
                'notes' => $data['notes'] ?? null,
                'status' => 'planned',
                'created_by' => $data['created_by'],
            ]);

            $plan->calculateTotalCost();
            $plan->save();

            return $plan;
        });
    }

    public function updateMaterialPlan(JobMaterialPlanModel $plan, array $data): JobMaterialPlanModel
    {
        return DB::transaction(function () use ($plan, $data) {
            $plan->update($data);
            
            if (isset($data['planned_quantity']) || isset($data['unit_price'])) {
                $plan->calculateTotalCost();
                $plan->save();
            }

            return $plan->fresh();
        });
    }

    public function removeMaterialFromJob(JobMaterialPlanModel $plan): bool
    {
        if ($plan->status !== 'planned') {
            throw new \Exception('Cannot remove material that has been ordered or received');
        }

        return $plan->delete();
    }

    public function applyTemplateToJob(int $jobId, int $templateId, float $jobSize, int $createdBy): array
    {
        return DB::transaction(function () use ($jobId, $templateId, $jobSize, $createdBy) {
            $template = MaterialTemplateModel::with('items.material')->findOrFail($templateId);
            $added = [];

            foreach ($template->items as $templateItem) {
                $quantity = $templateItem->calculateQuantity($jobSize);

                $plan = $this->addMaterialToJob([
                    'job_id' => $jobId,
                    'material_id' => $templateItem->material_id,
                    'planned_quantity' => $quantity,
                    'unit_price' => $templateItem->material->current_price,
                    'wastage_percentage' => $templateItem->wastage_percentage,
                    'notes' => $templateItem->notes,
                    'created_by' => $createdBy,
                ]);

                $added[] = $plan;
            }

            return $added;
        });
    }

    public function getJobMaterialSummary(int $jobId): array
    {
        $plans = JobMaterialPlanModel::with('material')
            ->forJob($jobId)
            ->get();

        $totalPlannedCost = $plans->sum('total_cost');
        $totalActualCost = $plans->sum('actual_total_cost');
        $variance = $totalActualCost - $totalPlannedCost;
        $variancePercentage = $totalPlannedCost > 0 
            ? ($variance / $totalPlannedCost) * 100 
            : 0;

        return [
            'total_planned_cost' => $totalPlannedCost,
            'total_actual_cost' => $totalActualCost,
            'variance' => $variance,
            'variance_percentage' => $variancePercentage,
            'materials_count' => $plans->count(),
            'planned_count' => $plans->where('status', 'planned')->count(),
            'ordered_count' => $plans->where('status', 'ordered')->count(),
            'received_count' => $plans->where('status', 'received')->count(),
            'used_count' => $plans->where('status', 'used')->count(),
        ];
    }

    public function updateActualCosts(JobMaterialPlanModel $plan, float $actualQuantity, float $actualUnitPrice): JobMaterialPlanModel
    {
        return DB::transaction(function () use ($plan, $actualQuantity, $actualUnitPrice) {
            $plan->actual_quantity = $actualQuantity;
            $plan->actual_unit_price = $actualUnitPrice;
            $plan->calculateActualTotalCost();
            $plan->save();

            return $plan;
        });
    }

    public function bulkUpdateStatus(array $planIds, string $status): int
    {
        return JobMaterialPlanModel::whereIn('id', $planIds)
            ->update(['status' => $status]);
    }
}
