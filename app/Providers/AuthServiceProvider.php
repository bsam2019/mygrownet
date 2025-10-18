<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\Permission;
use App\Models\Investment;
use App\Policies\InvestmentPolicy;
use App\Infrastructure\Persistence\Eloquent\EmployeeCommissionModel;
use App\Infrastructure\Persistence\Eloquent\EmployeeModel;
use App\Policies\CommissionPolicy;
use App\Policies\EmployeePolicy;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Investment::class => InvestmentPolicy::class,
        EmployeeCommissionModel::class => CommissionPolicy::class,
        EmployeeModel::class => EmployeePolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();
        $this->registerPermissions();
    }

    protected function registerPermissions(): void
    {
        try {
            // Only try to load permissions if we can connect to the database
            // and the permissions table exists
            if (\Schema::hasTable('permissions')) {
                $permissions = Permission::all();

                foreach ($permissions as $permission) {
                    Gate::define($permission->slug, function ($user) use ($permission) {
                        return $user->hasPermission($permission->slug);
                    });
                }
            }
        } catch (\Exception $e) {
            // Handle database not migrated yet or connection issues
            \Log::debug('Could not load permissions: ' . $e->getMessage());
        }
    }
}