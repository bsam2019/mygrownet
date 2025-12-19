<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('employee_delegations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->onDelete('cascade');
            $table->string('permission_key'); // e.g., 'delegated.support.handle_tickets'
            $table->foreignId('delegated_by')->constrained('users')->onDelete('cascade');
            $table->boolean('requires_approval')->default(false);
            $table->foreignId('approval_manager_id')->nullable()->constrained('employees')->onDelete('set null');
            $table->boolean('is_active')->default(true);
            $table->timestamp('expires_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->unique(['employee_id', 'permission_key']);
            $table->index(['permission_key', 'is_active']);
        });

        // Audit log for delegation actions
        Schema::create('employee_delegation_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('delegation_id')->nullable()->constrained('employee_delegations')->onDelete('set null');
            $table->foreignId('employee_id')->constrained()->onDelete('cascade');
            $table->string('permission_key');
            $table->string('action'); // granted, revoked, used, approval_requested, approved, rejected
            $table->foreignId('performed_by')->constrained('users')->onDelete('cascade');
            $table->json('metadata')->nullable();
            $table->string('ip_address')->nullable();
            $table->timestamps();
            
            $table->index(['employee_id', 'action']);
            $table->index('created_at');
        });

        // Pending approvals for actions requiring manager sign-off
        Schema::create('delegation_approval_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('delegation_id')->constrained('employee_delegations')->onDelete('cascade');
            $table->foreignId('employee_id')->constrained()->onDelete('cascade');
            $table->string('action_type'); // e.g., 'process_payment', 'process_withdrawal'
            $table->string('resource_type'); // e.g., 'payment', 'withdrawal'
            $table->unsignedBigInteger('resource_id');
            $table->json('action_data'); // Serialized action details
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('reviewed_at')->nullable();
            $table->text('review_notes')->nullable();
            $table->timestamps();
            
            $table->index(['status', 'created_at']);
            $table->index(['employee_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('delegation_approval_requests');
        Schema::dropIfExists('employee_delegation_logs');
        Schema::dropIfExists('employee_delegations');
    }
};
