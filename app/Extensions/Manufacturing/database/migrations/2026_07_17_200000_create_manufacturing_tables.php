<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('sa_bill_of_materials')) {
            Schema::create('sa_bill_of_materials', function (Blueprint $table) {
                $table->id();
                $table->foreignId('sa_company_id')->constrained('sa_companies')->cascadeOnDelete();
                $table->foreignId('sa_item_id')->constrained('sa_items')->cascadeOnDelete();
                $table->string('name');
                $table->decimal('quantity', 12, 2)->comment('Output quantity this BOM produces');
                $table->string('uom', 20)->default('each')->comment('Unit of measure for output');
                $table->string('status', 20)->default('draft');
                $table->string('version', 10)->default('1.0');
                $table->text('notes')->nullable();
                $table->timestamps();
                $table->index(['sa_company_id', 'sa_item_id']);
            });
        }

        if (!Schema::hasTable('sa_bom_materials')) {
            Schema::create('sa_bom_materials', function (Blueprint $table) {
                $table->id();
                $table->foreignId('sa_bom_id')->constrained('sa_bill_of_materials')->cascadeOnDelete();
                $table->foreignId('sa_item_id')->constrained('sa_items')->cascadeOnDelete();
                $table->decimal('quantity', 12, 2);
                $table->string('uom', 20)->default('each');
                $table->decimal('waste_factor', 5, 2)->default(0)->comment('% waste expected');
                $table->integer('sort_order')->default(0);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('sa_work_orders')) {
            Schema::create('sa_work_orders', function (Blueprint $table) {
                $table->id();
                $table->foreignId('sa_company_id')->constrained('sa_companies')->cascadeOnDelete();
                $table->foreignId('sa_bom_id')->nullable()->constrained('sa_bill_of_materials')->nullOnDelete();
                $table->foreignId('sa_item_id')->constrained('sa_items')->cascadeOnDelete();
                $table->string('order_number', 50)->unique();
                $table->decimal('quantity', 12, 2);
                $table->decimal('completed_quantity', 12, 2)->default(0);
                $table->decimal('scrapped_quantity', 12, 2)->default(0);
                $table->string('status', 20)->default('draft');
                $table->date('due_date')->nullable();
                $table->date('started_at')->nullable();
                $table->date('completed_at')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();
                $table->index(['sa_company_id', 'status']);
            });
        }

        if (!Schema::hasTable('sa_work_order_material_issues')) {
            Schema::create('sa_work_order_material_issues', function (Blueprint $table) {
                $table->id();
                $table->foreignId('sa_work_order_id')->constrained('sa_work_orders')->cascadeOnDelete();
                $table->foreignId('sa_item_id')->constrained('sa_items')->cascadeOnDelete();
                $table->decimal('quantity', 12, 2);
                $table->decimal('unit_cost', 12, 2)->default(0);
                $table->string('issue_type', 20)->default('issued')->comment('issued, returned, scrapped');
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('sa_work_order_material_issues');
        Schema::dropIfExists('sa_work_orders');
        Schema::dropIfExists('sa_bom_materials');
        Schema::dropIfExists('sa_bill_of_materials');
    }
};
