<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cms_payroll_items', function (Blueprint $table) {
            // Check if columns don't exist before adding (remove after() to avoid column dependency)
            if (!Schema::hasColumn('cms_payroll_items', 'napsa_employee')) {
                $table->decimal('napsa_employee', 15, 2)->default(0);
            }
            if (!Schema::hasColumn('cms_payroll_items', 'napsa_employer')) {
                $table->decimal('napsa_employer', 15, 2)->default(0);
            }
            if (!Schema::hasColumn('cms_payroll_items', 'nhima')) {
                $table->decimal('nhima', 15, 2)->default(0);
            }
            if (!Schema::hasColumn('cms_payroll_items', 'paye')) {
                $table->decimal('paye', 15, 2)->default(0);
            }
            if (!Schema::hasColumn('cms_payroll_items', 'total_statutory_deductions')) {
                $table->decimal('total_statutory_deductions', 15, 2)->default(0);
            }
            if (!Schema::hasColumn('cms_payroll_items', 'total_other_deductions')) {
                $table->decimal('total_other_deductions', 15, 2)->default(0);
            }
        });
    }

    public function down(): void
    {
        Schema::table('cms_payroll_items', function (Blueprint $table) {
            $columns = ['napsa_employee', 'napsa_employer', 'nhima', 'paye', 'total_statutory_deductions', 'total_other_deductions'];
            foreach ($columns as $column) {
                if (Schema::hasColumn('cms_payroll_items', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
