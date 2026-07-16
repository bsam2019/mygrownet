<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('sa_branches')) {
            Schema::create('sa_branches', function (Blueprint $table) {
                $table->id();
                $table->foreignId('sa_company_id')->constrained('sa_companies')->cascadeOnDelete();
                $table->string('name');
                $table->string('code', 50)->nullable();
                $table->string('phone', 50)->nullable();
                $table->string('email')->nullable();
                $table->text('address')->nullable();
                $table->string('city')->nullable();
                $table->string('country')->nullable();
                $table->boolean('is_head_office')->default(false);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
                $table->unique(['sa_company_id', 'code']);
            });
        }

        $branchesExist = Schema::hasTable('sa_branches');

        $tables = ['sa_warehouses', 'sa_sales', 'sa_purchase_orders', 'sa_cash_registers',
            'sa_stock_movements', 'sa_physical_counts', 'sa_audits', 'sa_quotations',
            'sa_invoices', 'sa_receipts', 'sa_items'];

        foreach ($tables as $table) {
            if (Schema::hasTable($table) && $branchesExist && !Schema::hasColumn($table, 'sa_branch_id')) {
                Schema::table($table, function (Blueprint $t) {
                    $t->foreignId('sa_branch_id')->nullable()->constrained('sa_branches')->nullOnDelete();
                });
            } elseif (Schema::hasTable($table) && !Schema::hasColumn($table, 'sa_branch_id')) {
                Schema::table($table, function (Blueprint $t) {
                    $t->unsignedBigInteger('sa_branch_id')->nullable();
                });
            }
        }
    }

    public function down(): void
    {
        $tables = ['sa_items', 'sa_receipts', 'sa_invoices', 'sa_quotations', 'sa_audits',
            'sa_physical_counts', 'sa_stock_movements', 'sa_cash_registers',
            'sa_purchase_orders', 'sa_sales', 'sa_warehouses'];

        foreach ($tables as $table) {
            if (Schema::hasTable($table) && Schema::hasColumn($table, 'sa_branch_id')) {
                Schema::table($table, function (Blueprint $t) {
                    $t->dropForeign(['sa_branch_id']);
                    $t->dropColumn('sa_branch_id');
                });
            }
        }
        Schema::dropIfExists('sa_branches');
    }
};
