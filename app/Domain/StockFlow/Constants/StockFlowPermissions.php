<?php

declare(strict_types=1);

namespace App\Domain\StockFlow\Constants;

class StockFlowPermissions
{
    // Items
    public const ITEMS_VIEW = 'items.view';
    public const ITEMS_CREATE = 'items.create';
    public const ITEMS_EDIT = 'items.edit';
    public const ITEMS_DELETE = 'items.delete';
    public const ITEMS_IMPORT = 'items.import';
    public const ITEMS_EXPORT = 'items.export';

    // Sales
    public const SALES_VIEW = 'sales.view';
    public const SALES_CREATE = 'sales.create';
    public const SALES_EDIT = 'sales.edit';
    public const SALES_VOID = 'sales.void';
    public const SALES_REPORT = 'sales.report';

    // Purchases
    public const PURCHASES_VIEW = 'purchases.view';
    public const PURCHASES_CREATE = 'purchases.create';
    public const PURCHASES_EDIT = 'purchases.edit';
    public const PURCHASES_RECEIVE = 'purchases.receive';
    public const PURCHASES_CANCEL = 'purchases.cancel';

    // Cash Register
    public const CASH_VIEW = 'cash.view';
    public const CASH_OPEN = 'cash.open';
    public const CASH_CLOSE = 'cash.close';
    public const CASH_MOVEMENTS = 'cash.movements';

    // Physical Counts
    public const COUNTS_VIEW = 'counts.view';
    public const COUNTS_CREATE = 'counts.create';
    public const COUNTS_EDIT = 'counts.edit';
    public const COUNTS_COMPLETE = 'counts.complete';
    public const COUNTS_GENERATE_AUDIT = 'counts.generate_audit';

    // Audits
    public const AUDITS_VIEW = 'audits.view';
    public const AUDITS_CREATE = 'audits.create';
    public const AUDITS_EDIT = 'audits.edit';
    public const AUDITS_FINALIZE = 'audits.finalize';
    public const AUDITS_EXPORT = 'audits.export';

    // Stock Movements
    public const MOVEMENTS_VIEW = 'movements.view';
    public const MOVEMENTS_ADJUST = 'movements.adjust';

    // Reports
    public const REPORTS_VIEW = 'reports.view';
    public const REPORTS_EXPORT = 'reports.export';

    // Suppliers
    public const SUPPLIERS_VIEW = 'suppliers.view';
    public const SUPPLIERS_CREATE = 'suppliers.create';
    public const SUPPLIERS_EDIT = 'suppliers.edit';
    public const SUPPLIERS_DELETE = 'suppliers.delete';

    // Departments & Bins
    public const DEPARTMENTS_VIEW = 'departments.view';
    public const DEPARTMENTS_CREATE = 'departments.create';
    public const DEPARTMENTS_EDIT = 'departments.edit';
    public const DEPARTMENTS_DELETE = 'departments.delete';
    public const BINS_VIEW = 'bins.view';
    public const BINS_CREATE = 'bins.create';
    public const BINS_EDIT = 'bins.edit';
    public const BINS_DELETE = 'bins.delete';

    // Employees (Company Users)
    public const EMPLOYEES_VIEW = 'employees.view';
    public const EMPLOYEES_INVITE = 'employees.invite';
    public const EMPLOYEES_EDIT = 'employees.edit';
    public const EMPLOYEES_SUSPEND = 'employees.suspend';
    public const EMPLOYEES_REMOVE = 'employees.remove';

    // Roles
    public const ROLES_VIEW = 'roles.view';
    public const ROLES_CREATE = 'roles.create';
    public const ROLES_EDIT = 'roles.edit';
    public const ROLES_DELETE = 'roles.delete';

    // Company Settings
    public const COMPANY_SETTINGS = 'company.settings';

    // All permissions grouped by category
    public static function all(): array
    {
        return [
            'Items' => [
                self::ITEMS_VIEW,
                self::ITEMS_CREATE,
                self::ITEMS_EDIT,
                self::ITEMS_DELETE,
                self::ITEMS_IMPORT,
                self::ITEMS_EXPORT,
            ],
            'Sales' => [
                self::SALES_VIEW,
                self::SALES_CREATE,
                self::SALES_EDIT,
                self::SALES_VOID,
                self::SALES_REPORT,
            ],
            'Purchases' => [
                self::PURCHASES_VIEW,
                self::PURCHASES_CREATE,
                self::PURCHASES_EDIT,
                self::PURCHASES_RECEIVE,
                self::PURCHASES_CANCEL,
            ],
            'Cash Register' => [
                self::CASH_VIEW,
                self::CASH_OPEN,
                self::CASH_CLOSE,
                self::CASH_MOVEMENTS,
            ],
            'Physical Counts' => [
                self::COUNTS_VIEW,
                self::COUNTS_CREATE,
                self::COUNTS_EDIT,
                self::COUNTS_COMPLETE,
                self::COUNTS_GENERATE_AUDIT,
            ],
            'Audits' => [
                self::AUDITS_VIEW,
                self::AUDITS_CREATE,
                self::AUDITS_EDIT,
                self::AUDITS_FINALIZE,
                self::AUDITS_EXPORT,
            ],
            'Stock Movements' => [
                self::MOVEMENTS_VIEW,
                self::MOVEMENTS_ADJUST,
            ],
            'Reports' => [
                self::REPORTS_VIEW,
                self::REPORTS_EXPORT,
            ],
            'Suppliers' => [
                self::SUPPLIERS_VIEW,
                self::SUPPLIERS_CREATE,
                self::SUPPLIERS_EDIT,
                self::SUPPLIERS_DELETE,
            ],
            'Departments & Bins' => [
                self::DEPARTMENTS_VIEW,
                self::DEPARTMENTS_CREATE,
                self::DEPARTMENTS_EDIT,
                self::DEPARTMENTS_DELETE,
                self::BINS_VIEW,
                self::BINS_CREATE,
                self::BINS_EDIT,
                self::BINS_DELETE,
            ],
            'Employees' => [
                self::EMPLOYEES_VIEW,
                self::EMPLOYEES_INVITE,
                self::EMPLOYEES_EDIT,
                self::EMPLOYEES_SUSPEND,
                self::EMPLOYEES_REMOVE,
            ],
            'Roles' => [
                self::ROLES_VIEW,
                self::ROLES_CREATE,
                self::ROLES_EDIT,
                self::ROLES_DELETE,
            ],
            'Company Settings' => [
                self::COMPANY_SETTINGS,
            ],
        ];
    }

    public static function allFlat(): array
    {
        $all = [];
        foreach (self::all() as $category) {
            $all = array_merge($all, $category);
        }
        return $all;
    }
}