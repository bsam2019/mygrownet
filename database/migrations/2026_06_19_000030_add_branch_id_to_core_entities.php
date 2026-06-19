<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tables = [
            'cms_invoices',
            'cms_expenses',
            'cms_jobs',
            'cms_projects',
            'cms_quotations',
            'cms_inventory_items',
            'cms_assets',
            'cms_equipment',
        ];

        foreach ($tables as $table) {
            if (!Schema::hasColumn($table, 'branch_id')) {
                Schema::table($table, function (Blueprint $table) {
                    $table->foreignId('branch_id')->nullable()->after('company_id')
                        ->constrained('cms_branches')->nullOnDelete();
                });
            }
        }
    }

    public function down(): void
    {
        $tables = [
            'cms_invoices',
            'cms_expenses',
            'cms_jobs',
            'cms_projects',
            'cms_quotations',
            'cms_inventory_items',
            'cms_assets',
            'cms_equipment',
        ];

        foreach ($tables as $table) {
            Schema::table($table, function (Blueprint $table) {
                $table->dropForeign(['branch_id']);
                $table->dropColumn('branch_id');
            });
        }
    }
};
