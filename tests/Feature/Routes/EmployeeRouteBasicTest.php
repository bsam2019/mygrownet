<?php

namespace Tests\Feature\Routes;

use Tests\TestCase;
use Illuminate\Support\Facades\Route;

class EmployeeRouteBasicTest extends TestCase
{
    public function test_employee_routes_are_registered(): void
    {
        $expectedRoutes = [
            'employees.index',
            'employees.create',
            'employees.store',
            'employees.show',
            'employees.edit',
            'employees.update',
            'employees.destroy',
        ];

        foreach ($expectedRoutes as $routeName) {
            $this->assertTrue(
                Route::has($routeName),
                "Route {$routeName} is not registered"
            );
        }
    }

    public function test_department_routes_are_registered(): void
    {
        $expectedRoutes = [
            'departments.index',
            'departments.create',
            'departments.store',
            'departments.show',
            'departments.edit',
            'departments.update',
            'departments.destroy',
            'departments.hierarchy',
            'departments.assign-head',
            'departments.remove-head',
        ];

        foreach ($expectedRoutes as $routeName) {
            $this->assertTrue(
                Route::has($routeName),
                "Route {$routeName} is not registered"
            );
        }
    }

    public function test_position_routes_are_registered(): void
    {
        $expectedRoutes = [
            'positions.index',
            'positions.create',
            'positions.store',
            'positions.show',
            'positions.edit',
            'positions.update',
            'positions.destroy',
            'positions.by-department',
            'positions.salary-ranges',
            'positions.commission-eligible',
        ];

        foreach ($expectedRoutes as $routeName) {
            $this->assertTrue(
                Route::has($routeName),
                "Route {$routeName} is not registered"
            );
        }
    }

    public function test_performance_routes_are_registered(): void
    {
        $expectedRoutes = [
            'performance.index',
            'performance.create',
            'performance.store',
            'performance.show',
            'performance.edit',
            'performance.update',
            'performance.destroy',
            'performance.analytics',
            'performance.goals.set',
            'performance.goals.track',
        ];

        foreach ($expectedRoutes as $routeName) {
            $this->assertTrue(
                Route::has($routeName),
                "Route {$routeName} is not registered"
            );
        }
    }

    public function test_commission_routes_are_registered(): void
    {
        $expectedRoutes = [
            'commissions.index',
            'commissions.calculate',
            'commissions.store',
            'commissions.approve',
            'commissions.mark-paid',
            'commissions.reports.monthly',
            'commissions.reports.quarterly',
            'commissions.analytics',
        ];

        foreach ($expectedRoutes as $routeName) {
            $this->assertTrue(
                Route::has($routeName),
                "Route {$routeName} is not registered"
            );
        }
    }

    public function test_api_routes_are_registered(): void
    {
        $expectedRoutes = [
            'api.employee.profile',
            'api.employee.performance-summary',
            'api.employee.client-portfolio',
            'api.admin.employee-management-summary',
            'api.admin.department-overview',
            'api.admin.performance-stats',
        ];

        foreach ($expectedRoutes as $routeName) {
            $this->assertTrue(
                Route::has($routeName),
                "Route {$routeName} is not registered"
            );
        }
    }
}