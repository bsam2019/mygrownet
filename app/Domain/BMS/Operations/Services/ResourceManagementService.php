<?php

namespace App\Domain\BMS\Operations\Services;

use App\Infrastructure\Persistence\Eloquent\CMS\TaskModel;
use App\Infrastructure\Persistence\Eloquent\CMS\TaskResourceModel;
use App\Infrastructure\Persistence\Eloquent\CMS\ResourceAvailabilityModel;
use Illuminate\Support\Facades\DB;

class ResourceManagementService
{
    /**
     * Allocate resource to task
     */
    public function allocateResource(int $companyId, array $data): array
    {
        // Check for conflicts
        $conflicts = $this->checkResourceConflicts(
            $companyId,
            $data['resource_type'],
            $data['resource_id'],
            $data['allocated_from'],
            $data['allocated_to']
        );

        if (!empty($conflicts)) {
            return [
                'success' => false,
                'error' => 'Resource conflict detected',
                'conflicts' => $conflicts,
            ];
        }

        $allocation = TaskResourceModel::create([
            'company_id' => $companyId,
            'task_id' => $data['task_id'],
            'resource_type' => $data['resource_type'],
            'resource_id' => $data['resource_id'],
            'allocated_from' => $data['allocated_from'],
            'allocated_to' => $data['allocated_to'],
            'status' => 'allocated',
            'notes' => $data['notes'] ?? null,
        ]);

        return [
            'success' => true,
            'allocation_id' => $allocation->id,
            'message' => 'Resource allocated successfully',
        ];
    }

    /**
     * Check for resource conflicts
     */
    public function checkResourceConflicts(
        int $companyId,
        string $resourceType,
        int $resourceId,
        string $from,
        string $to
    ): array {
        $conflicts = TaskResourceModel::where('company_id', $companyId)
            ->where('resource_type', $resourceType)
            ->where('resource_id', $resourceId)
            ->whereIn('status', ['allocated', 'in_use'])
            ->where(function ($q) use ($from, $to) {
                $q->whereBetween('allocated_from', [$from, $to])
                    ->orWhereBetween('allocated_to', [$from, $to])
                    ->orWhere(function ($q2) use ($from, $to) {
                        $q2->where('allocated_from', '<=', $from)
                            ->where('allocated_to', '>=', $to);
                    });
            })
            ->with('task')
            ->get();

        return $conflicts->map(fn($c) => [
            'task_id' => $c->task_id,
            'task_title' => $c->task->title,
            'allocated_from' => $c->allocated_from->toDateTimeString(),
            'allocated_to' => $c->allocated_to->toDateTimeString(),
        ])->toArray();
    }

    /**
     * Get resource availability
     */
    public function getResourceAvailability(
        int $companyId,
        string $resourceType,
        int $resourceId,
        string $startDate,
        string $endDate
    ): array {
        $start = \Carbon\Carbon::parse($startDate);
        $end = \Carbon\Carbon::parse($endDate);
        
        $availability = [];
        
        for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
            $dateStr = $date->toDateString();
            
            // Check availability record
            $availRecord = ResourceAvailabilityModel::where('company_id', $companyId)
                ->where('resource_type', $resourceType)
                ->where('resource_id', $resourceId)
                ->where('date', $dateStr)
                ->first();

            // Check allocations
            $allocations = TaskResourceModel::where('company_id', $companyId)
                ->where('resource_type', $resourceType)
                ->where('resource_id', $resourceId)
                ->whereDate('allocated_from', '<=', $dateStr)
                ->whereDate('allocated_to', '>=', $dateStr)
                ->whereIn('status', ['allocated', 'in_use'])
                ->with('task')
                ->get();

            $isAvailable = $availRecord ? $availRecord->is_available : true;
            $hasAllocations = $allocations->isNotEmpty();

            $availability[] = [
                'date' => $dateStr,
                'is_available' => $isAvailable && !$hasAllocations,
                'available_from' => $availRecord?->available_from,
                'available_to' => $availRecord?->available_to,
                'unavailability_reason' => $availRecord?->unavailability_reason,
                'allocations' => $allocations->map(fn($a) => [
                    'task_id' => $a->task_id,
                    'task_title' => $a->task->title,
                    'allocated_from' => $a->allocated_from->toDateTimeString(),
                    'allocated_to' => $a->allocated_to->toDateTimeString(),
                ])->toArray(),
            ];
        }

        return $availability;
    }

    /**
     * Set resource unavailability
     */
    public function setResourceUnavailability(int $companyId, array $data): array
    {
        ResourceAvailabilityModel::updateOrCreate(
            [
                'company_id' => $companyId,
                'resource_type' => $data['resource_type'],
                'resource_id' => $data['resource_id'],
                'date' => $data['date'],
            ],
            [
                'is_available' => false,
                'unavailability_reason' => $data['reason'],
                'available_from' => null,
                'available_to' => null,
            ]
        );

        return [
            'success' => true,
            'message' => 'Resource unavailability set successfully',
        ];
    }

    /**
     * Get task resources
     */
    public function getTaskResources(int $taskId): array
    {
        $resources = TaskResourceModel::where('task_id', $taskId)
            ->get();

        return $resources->map(function ($resource) {
            $resourceData = $this->getResourceDetails($resource->resource_type, $resource->resource_id);
            
            return [
                'id' => $resource->id,
                'resource_type' => $resource->resource_type,
                'resource_id' => $resource->resource_id,
                'resource_name' => $resourceData['name'] ?? 'Unknown',
                'allocated_from' => $resource->allocated_from->toDateTimeString(),
                'allocated_to' => $resource->allocated_to->toDateTimeString(),
                'status' => $resource->status,
                'notes' => $resource->notes,
            ];
        })->toArray();
    }

    private function getResourceDetails(string $type, int $id): array
    {
        switch ($type) {
            case 'employee':
                $worker = \App\Infrastructure\Persistence\Eloquent\CMS\WorkerModel::find($id);
                return ['name' => $worker ? $worker->first_name . ' ' . $worker->last_name : 'Unknown'];
            
            case 'vehicle':
                $vehicle = \App\Infrastructure\Persistence\Eloquent\CMS\VehicleModel::find($id);
                return ['name' => $vehicle ? $vehicle->registration_number : 'Unknown'];
            
            case 'equipment':
                $equipment = \App\Infrastructure\Persistence\Eloquent\CMS\EquipmentModel::find($id);
                return ['name' => $equipment ? $equipment->name : 'Unknown'];
            
            default:
                return ['name' => 'Unknown'];
        }
    }

    /**
     * Release resource from task
     */
    public function releaseResource(int $allocationId): array
    {
        $allocation = TaskResourceModel::findOrFail($allocationId);
        $allocation->update(['status' => 'completed']);

        return [
            'success' => true,
            'message' => 'Resource released successfully',
        ];
    }

    /**
     * Get resource utilization report
     */
    public function getResourceUtilization(int $companyId, string $resourceType, string $startDate, string $endDate): array
    {
        $allocations = TaskResourceModel::where('company_id', $companyId)
            ->where('resource_type', $resourceType)
            ->whereBetween('allocated_from', [$startDate, $endDate])
            ->with('task')
            ->get();

        $utilizationByResource = $allocations->groupBy('resource_id')->map(function ($resourceAllocations, $resourceId) use ($resourceType) {
            $totalHours = 0;
            foreach ($resourceAllocations as $allocation) {
                $hours = $allocation->allocated_from->diffInHours($allocation->allocated_to);
                $totalHours += $hours;
            }

            $resourceData = $this->getResourceDetails($resourceType, $resourceId);

            return [
                'resource_id' => $resourceId,
                'resource_name' => $resourceData['name'],
                'allocation_count' => $resourceAllocations->count(),
                'total_hours' => $totalHours,
                'allocations' => $resourceAllocations->map(fn($a) => [
                    'task_id' => $a->task_id,
                    'task_title' => $a->task->title,
                    'hours' => $a->allocated_from->diffInHours($a->allocated_to),
                ])->toArray(),
            ];
        });

        return $utilizationByResource->values()->toArray();
    }
}
