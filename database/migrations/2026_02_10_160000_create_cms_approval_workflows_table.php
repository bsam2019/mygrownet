<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('cms_approval_requests')) {
            Schema::create('cms_approval_requests', function (Blueprint $table) {
                        $table->id();
                        $table->foreignId('company_id')->constrained('cms_companies')->cascadeOnDelete();
                        $table->string('approvable_type'); // Expense, Quotation, Payment, etc.
                        $table->unsignedBigInteger('approvable_id');
                        $table->string('request_type'); // expense, quotation, payment, purchase_order
                        $table->decimal('amount', 15, 2);
                        $table->string('status')->default('pending'); // pending, approved, rejected, cancelled
                        $table->foreignId('requested_by')->constrained('cms_users')->cascadeOnDelete();
                        $table->text('request_notes')->nullable();
                        $table->integer('approval_level')->default(1); // Current approval level
                        $table->integer('required_levels')->default(1); // Total levels required
                        $table->timestamp('submitted_at');
                        $table->timestamp('completed_at')->nullable();
                        $table->timestamps();
            
                        $table->index(['company_id', 'status']);
                        $table->index(['approvable_type', 'approvable_id']);
                    });
        }

        if (!Schema::hasTable('cms_approval_steps')) {
            Schema::create('cms_approval_steps', function (Blueprint $table) {
                        $table->id();
                        $table->foreignId('approval_request_id')->constrained('cms_approval_requests')->cascadeOnDelete();
                        $table->integer('step_level'); // 1, 2, 3, etc.
                        $table->foreignId('approver_id')->nullable()->constrained('cms_users')->nullOnDelete();
                        $table->string('approver_role')->nullable(); // owner, manager, accountant
                        $table->string('status')->default('pending'); // pending, approved, rejected, skipped
                        $table->text('comments')->nullable();
                        $table->timestamp('actioned_at')->nullable();
                        $table->timestamps();
            
                        $table->index(['approval_request_id', 'step_level']);
                    });
        }

        if (!Schema::hasTable('cms_approval_chains')) {
            Schema::create('cms_approval_chains', function (Blueprint $table) {
                        $table->id();
                        $table->foreignId('company_id')->constrained('cms_companies')->cascadeOnDelete();
                        $table->string('name'); // "Standard Expense Approval", "High-Value Purchase"
                        $table->string('entity_type'); // expense, quotation, payment, purchase_order
                        $table->decimal('min_amount', 15, 2)->default(0);
                        $table->decimal('max_amount', 15, 2)->nullable();
                        $table->json('approval_steps'); // [{level: 1, role: 'manager'}, {level: 2, role: 'owner'}]
                        $table->boolean('is_active')->default(true);
                        $table->integer('priority')->default(0); // Higher priority chains are checked first
                        $table->timestamps();
            
                        $table->index(['company_id', 'entity_type', 'is_active']);
                    });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('cms_approval_steps');
        Schema::dropIfExists('cms_approval_requests');
        Schema::dropIfExists('cms_approval_chains');
    }
};
