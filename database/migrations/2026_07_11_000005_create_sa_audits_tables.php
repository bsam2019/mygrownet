<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sa_audits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sa_company_id')->constrained('sa_companies')->cascadeOnDelete();
            $table->string('title');
            $table->string('report_reference')->unique()->nullable();
            $table->date('audit_date');
            $table->string('prepared_for')->nullable();
            $table->string('prepared_by')->nullable();
            $table->enum('status', ['draft', 'finalized', 'archived'])->default('draft');
            $table->decimal('total_system_value', 16, 2)->default(0);
            $table->decimal('total_physical_value', 16, 2)->default(0);
            $table->decimal('total_recorded_sales', 16, 2)->default(0);
            $table->decimal('total_variance', 16, 2)->default(0);
            $table->decimal('unaccounted_value', 16, 2)->default(0);
            $table->text('executive_summary')->nullable();
            $table->text('recommendations')->nullable();
            $table->text('conclusion')->nullable();
            $table->json('attachments')->nullable();
            $table->timestamps();
        });

        Schema::create('sa_audit_reconciliations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sa_audit_id')->constrained('sa_audits')->cascadeOnDelete();
            $table->foreignId('sa_department_id')->nullable()->constrained('sa_departments')->nullOnDelete();
            $table->foreignId('sa_bin_id')->nullable()->constrained('sa_bins')->nullOnDelete();
            $table->decimal('system_value', 16, 2)->default(0);
            $table->decimal('physical_value', 16, 2)->default(0);
            $table->decimal('variance', 16, 2)->default(0);
            $table->decimal('variance_percent', 6, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('sa_audit_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sa_audit_id')->constrained('sa_audits')->cascadeOnDelete();
            $table->foreignId('sa_item_id')->constrained('sa_items')->cascadeOnDelete();
            $table->foreignId('sa_bin_id')->nullable()->constrained('sa_bins')->nullOnDelete();
            $table->string('item_name');
            $table->decimal('unit_price', 14, 2)->default(0);
            $table->decimal('system_qty', 12, 2)->default(0);
            $table->decimal('physical_qty', 12, 2)->default(0);
            $table->decimal('system_value', 16, 2)->default(0);
            $table->decimal('physical_value', 16, 2)->default(0);
            $table->decimal('gap_qty', 12, 2)->default(0);
            $table->decimal('gap_value', 16, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['sa_audit_id', 'sa_item_id'], 'sa_audit_item_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sa_audit_items');
        Schema::dropIfExists('sa_audit_reconciliations');
        Schema::dropIfExists('sa_audits');
    }
};
