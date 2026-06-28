<?php

declare(strict_types=1);

namespace App\Http\Resources\Employee;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmployeeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'employeeNumber' => $this->employee_number,
            'fullName' => $this->full_name ?? "{$this->first_name} {$this->last_name}",
            'firstName' => $this->first_name,
            'lastName' => $this->last_name,
            'email' => $this->email,
            'phone' => $this->phone,
            'address' => $this->address,
            'employmentStatus' => $this->employment_status,
            'hireDate' => $this->hire_date?->toISOString(),
            'terminationDate' => $this->termination_date?->toISOString(),
            'yearsOfService' => $this->years_of_service ?? $this->hire_date?->diffInYears(now()),
            'currentSalary' => $this->current_salary,
            'department' => $this->whenLoaded('department', function () {
                return [
                    'id' => $this->department->id,
                    'name' => $this->department->name,
                ];
            }),
            'position' => $this->whenLoaded('position', function () {
                return [
                    'id' => $this->position->id,
                    'title' => $this->position->title,
                    'commissionEligible' => $this->position->commission_eligible,
                ];
            }),
            'manager' => $this->whenLoaded('manager', function () {
                return $this->manager ? [
                    'id' => $this->manager->id,
                    'fullName' => "{$this->manager->first_name} {$this->manager->last_name}",
                ] : null;
            }),
            'user' => $this->whenLoaded('user', function () {
                return $this->user ? [
                    'id' => $this->user->id,
                    'email' => $this->user->email,
                ] : null;
            }),
            'performanceMetrics' => $this->when(
                $request->routeIs('*.show') || $request->has('include_performance'),
                function () {
                    return [
                        'lastReviewDate' => $this->last_performance_review?->toISOString(),
                        'averageScore' => $this->performance_reviews_avg_overall_score ?? 0,
                        'totalCommissions' => $this->commissions_sum_commission_amount ?? 0,
                        'activeClients' => $this->client_assignments_count ?? 0,
                    ];
                }
            ),
            'timestamps' => [
                'createdAt' => $this->created_at?->toISOString(),
                'updatedAt' => $this->updated_at?->toISOString(),
            ],
        ];
    }
}