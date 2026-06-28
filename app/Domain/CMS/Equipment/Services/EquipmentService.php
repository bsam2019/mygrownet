<?php

namespace App\Domain\CMS\Equipment\Services;

use App\Infrastructure\Persistence\Eloquent\CMS\EquipmentModel;
use App\Infrastructure\Persistence\Eloquent\CMS\EquipmentMaintenanceModel;
use App\Infrastructure\Persistence\Eloquent\CMS\EquipmentUsageModel;
use Illuminate\Support\Facades\DB;

class EquipmentService
{
    public function generateEquipmentCode(int $companyId): string
    {
        $lastEquipment = EquipmentModel::where('company_id', $companyId)
            ->orderBy('id', 'desc')
            ->first();

        if ($lastEquipment) {
            $lastNumber = (int) substr($lastEquipment->equipment_code, 4);
            $newNumber = str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $newNumber = '0001';
        }

        return "EQP-{$newNumber}";
    }

    public function recordMaintenance(EquipmentModel $equipment, array $data): EquipmentMaintenanceModel
    {
        return DB::transaction(function () use ($equipment, $data) {
            $maintenance = EquipmentMaintenanceModel::create([
                'equipment_id' => $equipment->id,
                'performed_by' => $data['performed_by'] ?? null,
                'maintenance_date' => $data['maintenance_date'],
                'type' => $data['type'],
                'description' => $data['description'],
                'cost' => $data['cost'] ?? 0,
                'downtime_hours' => $data['downtime_hours'] ?? 0,
                'service_provider' => $data['service_provider'] ?? null,
                'parts_replaced' => $data['parts_replaced'] ?? null,
                'next_maintenance_date' => $data['next_maintenance_date'] ?? null,
            ]);

            // Update equipment maintenance dates
            $equipment->last_maintenance_date = $data['maintenance_date'];
            if ($data['next_maintenance_date']) {
                $equipment->next_maintenance_date = $data['next_maintenance_date'];
            } elseif ($equipment->maintenance_interval_days) {
                $equipment->next_maintenance_date = now()->addDays($equipment->maintenance_interval_days);
            }
            $equipment->save();

            return $maintenance;
        });
    }

    public function recordUsage(EquipmentModel $equipment, array $data): EquipmentUsageModel
    {
        return DB::transaction(function () use ($equipment, $data) {
            $usage = EquipmentUsageModel::create([
                'equipment_id' => $equipment->id,
                'project_id' => $data['project_id'] ?? null,
                'job_id' => $data['job_id'] ?? null,
                'assigned_to' => $data['assigned_to'] ?? null,
                'start_date' => $data['start_date'],
                'end_date' => $data['end_date'] ?? null,
                'start_time' => $data['start_time'] ?? null,
                'end_time' => $data['end_time'] ?? null,
                'hours_used' => $data['hours_used'] ?? 0,
                'fuel_cost' => $data['fuel_cost'] ?? 0,
                'notes' => $data['notes'] ?? null,
            ]);

            // Update equipment status
            if (!$data['end_date']) {
                $equipment->status = 'in_use';
                $equipment->save();
            }

            return $usage;
        });
    }

    public function completeUsage(EquipmentUsageModel $usage, array $data): EquipmentUsageModel
    {
        $usage->update([
            'end_date' => $data['end_date'],
            'end_time' => $data['end_time'] ?? null,
            'hours_used' => $data['hours_used'],
            'fuel_cost' => $data['fuel_cost'] ?? 0,
        ]);

        // Check if equipment has other active usage
        $activeUsage = EquipmentUsageModel::where('equipment_id', $usage->equipment_id)
            ->whereNull('end_date')
            ->exists();

        if (!$activeUsage) {
            $usage->equipment->status = 'available';
            $usage->equipment->save();
        }

        return $usage;
    }

    public function getEquipmentUtilization(EquipmentModel $equipment, $startDate, $endDate): array
    {
        $totalDays = now()->parse($startDate)->diffInDays($endDate);
        $usageRecords = $equipment->usageRecords()
            ->whereBetween('start_date', [$startDate, $endDate])
            ->get();

        $totalHoursUsed = $usageRecords->sum('hours_used');
        $totalFuelCost = $usageRecords->sum('fuel_cost');
        $daysUsed = $usageRecords->count();

        $maintenanceCost = $equipment->maintenanceRecords()
            ->whereBetween('maintenance_date', [$startDate, $endDate])
            ->sum('cost');

        return [
            'total_hours_used' => $totalHoursUsed,
            'days_used' => $daysUsed,
            'utilization_rate' => $totalDays > 0 ? ($daysUsed / $totalDays) * 100 : 0,
            'total_fuel_cost' => $totalFuelCost,
            'total_maintenance_cost' => $maintenanceCost,
            'total_operating_cost' => $totalFuelCost + $maintenanceCost,
        ];
    }
}
