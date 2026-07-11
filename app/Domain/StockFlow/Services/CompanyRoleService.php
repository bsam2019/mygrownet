<?php

declare(strict_types=1);

namespace App\Domain\StockFlow\Services;

use App\Domain\StockFlow\Entities\CompanyRole;
use App\Domain\StockFlow\Exceptions\OperationFailedException;
use App\Domain\StockFlow\Repositories\CompanyRoleRepositoryInterface;
use App\Domain\StockFlow\ValueObjects\CompanyId;
use App\Domain\StockFlow\ValueObjects\CompanyRoleId;

class CompanyRoleService
{
    // Default permissions that can be assigned
    public const PERMISSIONS = [
        // Dashboard
        'dashboard.view',

        // Items
        'items.view',
        'items.create',
        'items.edit',
        'items.delete',
        'items.import',
        'items.export',
        'items.low_stock',

        // Sales
        'sales.view',
        'sales.create',
        'sales.edit',
        'sales.delete',
        'sales.void',
        'sales.report',
        'sales.daily',

        // Purchases
        'purchases.view',
        'purchases.create',
        'purchases.edit',
        'purchases.delete',
        'purchases.receive',
        'purchases.report',

        // Cash Register
        'cash.view',
        'cash.open',
        'cash.close',
        'cash.movements',
        'cash.verify',
        'cash.summary',

        // Physical Counts
        'counts.view',
        'counts.create',
        'counts.edit',
        'counts.complete',
        'counts.delete',
        'counts.generate_audit',

        // Audits
        'audits.view',
        'audits.create',
        'audits.finalize',
        'audits.export',

        // Stock Movements
        'movements.view',
        'movements.adjust',

        // Suppliers
        'suppliers.view',
        'suppliers.create',
        'suppliers.edit',
        'suppliers.delete',

        // Departments & Bins
        'departments.view',
        'departments.create',
        'departments.edit',
        'bins.view',
        'bins.create',
        'bins.edit',

        // Employees (Company-level)
        'employees.view',
        'employees.invite',
        'employees.edit',
        'employees.remove',
        'employees.roles',
    ];

    // System role definitions (seeded for each company)
    private const SYSTEM_ROLES = [
        'owner' => [
            'name' => 'Owner',
            'slug' => 'owner',
            'description' => 'Full access including company settings and employee management',
            'permissions_key' => 'owner',
        ],
        'admin' => [
            'name' => 'Administrator',
            'slug' => 'admin',
            'description' => 'Full operational access, can manage employees and roles',
            'permissions_key' => 'admin',
        ],
        'manager' => [
            'name' => 'Manager',
            'slug' => 'manager',
            'description' => 'Full operational access, can manage employees but not roles',
            'permissions_key' => 'manager',
        ],
        'cashier' => [
            'name' => 'Cashier',
            'slug' => 'cashier',
            'description' => 'Sales, cash register, and basic inventory viewing',
            'permissions_key' => 'cashier',
        ],
        'auditor' => [
            'name' => 'Auditor',
            'slug' => 'auditor',
            'description' => 'Physical counts, audits, and stock verification',
            'permissions_key' => 'auditor',
        ],
        'viewer' => [
            'name' => 'Viewer',
            'slug' => 'viewer',
            'description' => 'Read-only access to all reports and data',
            'permissions_key' => 'viewer',
        ],
    ];

    private function getSystemRolePermissions(string $key): array
    {
        return match ($key) {
            'owner' => self::PERMISSIONS,
            'admin' => array_diff(self::PERMISSIONS, ['employees.roles']),
            'manager' => array_diff(self::PERMISSIONS, ['employees.roles', 'employees.invite', 'employees.remove']),
            'cashier' => [
                'dashboard.view', 'items.view', 'items.low_stock',
                'sales.view', 'sales.create', 'sales.daily',
                'cash.view', 'cash.open', 'cash.close', 'cash.movements',
                'purchases.view',
            ],
            'auditor' => [
                'dashboard.view', 'items.view', 'items.low_stock',
                'counts.view', 'counts.create', 'counts.edit', 'counts.complete', 'counts.generate_audit',
                'audits.view', 'audits.create', 'audits.finalize', 'audits.export',
                'movements.view',
            ],
            'viewer' => [
                'dashboard.view', 'items.view', 'sales.view', 'sales.report', 'sales.daily',
                'purchases.view', 'purchases.report', 'cash.view', 'cash.summary',
                'counts.view', 'audits.view', 'audits.export', 'movements.view',
                'suppliers.view', 'departments.view', 'bins.view',
            ],
            default => [],
        };
    }

    public function __construct(
        private CompanyRoleRepositoryInterface $roleRepository,
    ) {}

    public function getAvailablePermissions(): array
    {
        return self::PERMISSIONS;
    }

    public function seedDefaultRoles(int $companyId): array
    {
        $companyIdObj = CompanyId::fromInt($companyId);
        $created = [];

        foreach (self::SYSTEM_ROLES as $key => $roleData) {
            $existing = $this->roleRepository->findByCompanyIdAndSlug($companyIdObj, $roleData['slug']);
            if (!$existing) {
                $role = CompanyRole::create(
                    companyId: $companyIdObj,
                    name: $roleData['name'],
                    slug: $roleData['slug'],
                    description: $roleData['description'],
                    permissions: $this->getSystemRolePermissions($roleData['permissions_key']),
                    isSystem: true,
                );
                $saved = $this->roleRepository->save($role);
                $created[] = $saved->toArray();
            }
        }

        return $created;
    }

    public function getRolesForCompany(int $companyId): array
    {
        return array_map(
            fn($r) => $r->toArray(),
            $this->roleRepository->findByCompanyId(CompanyId::fromInt($companyId))
        );
    }

    public function getRole(int $roleId): ?array
    {
        $role = $this->roleRepository->findById(CompanyRoleId::fromInt($roleId));
        return $role ? $role->toArray() : null;
    }

    public function createRole(
        int $companyId,
        string $name,
        string $slug,
        string $description,
        array $permissions,
    ): array {
        // Validate slug uniqueness
        $existing = $this->roleRepository->findByCompanyIdAndSlug(CompanyId::fromInt($companyId), $slug);
        if ($existing) {
            throw new OperationFailedException('create role', 'A role with this slug already exists');
        }

        $role = CompanyRole::create(
            companyId: CompanyId::fromInt($companyId),
            name: $name,
            slug: $slug,
            description: $description,
            permissions: $permissions,
        );

        return $this->roleRepository->save($role)->toArray();
    }

    public function updateRole(
        int $roleId,
        string $name,
        string $description,
        array $permissions,
    ): array {
        $role = $this->roleRepository->findById(CompanyRoleId::fromInt($roleId));
        if (!$role) {
            throw new OperationFailedException('update role', 'Role not found');
        }

        if ($role->isSystem()) {
            throw new OperationFailedException('update role', 'Cannot modify system roles');
        }

        $role->updateDetails($name, $description, 0);
        $role->setPermissions($permissions);

        return $this->roleRepository->save($role)->toArray();
    }

    public function deleteRole(int $roleId): void
    {
        $role = $this->roleRepository->findById(CompanyRoleId::fromInt($roleId));
        if (!$role) {
            throw new OperationFailedException('delete role', 'Role not found');
        }

        if ($role->isSystem()) {
            throw new OperationFailedException('delete role', 'Cannot delete system roles');
        }

        $this->roleRepository->delete(CompanyRoleId::fromInt($roleId));
    }

    public function getRolePermissions(int $roleId): array
    {
        $role = $this->roleRepository->findById(CompanyRoleId::fromInt($roleId));
        return $role ? $role->getPermissions() : [];
    }
}