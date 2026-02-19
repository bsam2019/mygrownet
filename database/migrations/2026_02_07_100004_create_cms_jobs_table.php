<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('cms_jobs')) {
            Schema::create('cms_jobs', function (Blueprint $table) {
                        $table->id();
                        $table->foreignId('company_id')->constrained('cms_companies')->onDelete('cascade');
                        $table->foreignId('customer_id')->constrained('cms_customers');
                        $table->string('job_number', 50);
                        $table->string('job_type', 100);
                        $table->text('description')->nullable();
                        $table->decimal('quoted_value', 15, 2)->nullable();
                        $table->decimal('actual_value', 15, 2)->nullable();
                        $table->decimal('material_cost', 15, 2)->default(0);
                        $table->decimal('labor_cost', 15, 2)->default(0);
                        $table->decimal('overhead_cost', 15, 2)->default(0);
                        $table->decimal('total_cost', 15, 2)->default(0);
                        $table->decimal('profit_amount', 15, 2)->default(0);
                        $table->decimal('profit_margin', 5, 2)->default(0);
                        $table->enum('status', ['pending', 'in_progress', 'completed', 'cancelled'])->default('pending');
                        $table->enum('priority', ['low', 'normal', 'high', 'urgent'])->default('normal');
                        $table->foreignId('assigned_to')->nullable()->constrained('cms_users');
                        $table->foreignId('created_by')->constrained('cms_users');
                        $table->timestamp('started_at')->nullable();
                        $table->timestamp('completed_at')->nullable();
                        $table->timestamp('deadline')->nullable();
                        $table->text('notes')->nullable();
                        $table->boolean('is_locked')->default(false);
                        $table->timestamp('locked_at')->nullable();
                        $table->foreignId('locked_by')->nullable()->constrained('cms_users');
                        $table->timestamps();
            
                        $table->unique(['company_id', 'job_number'], 'unique_job_number');
                        $table->index(['company_id', 'status'], 'idx_company_status');
                        $table->index(['assigned_to', 'status'], 'idx_assigned');
                        $table->index(['company_id', 'deadline'], 'idx_deadline');
                        $table->index(['company_id', 'completed_at'], 'idx_completed');
                    });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cms_jobs');
    }
};
