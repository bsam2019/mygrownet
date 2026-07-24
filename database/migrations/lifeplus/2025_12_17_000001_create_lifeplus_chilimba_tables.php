<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Chilimba Groups
        Schema::create('lifeplus_chilimba_groups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('name');
            $table->enum('meeting_frequency', ['weekly', 'bi-weekly', 'monthly'])->default('monthly');
            $table->string('meeting_day')->nullable();
            $table->time('meeting_time')->nullable();
            $table->text('meeting_location')->nullable();
            $table->decimal('contribution_amount', 10, 2);
            $table->integer('total_members');
            $table->date('start_date');
            $table->enum('user_role', ['member', 'secretary', 'treasurer'])->default('member');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['user_id', 'is_active']);
        });

        // Chilimba Members (for tracking all members in a group)
        Schema::create('lifeplus_chilimba_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_id')->constrained('lifeplus_chilimba_groups')->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null');
            $table->string('name');
            $table->string('phone', 20)->nullable();
            $table->integer('position_in_queue')->nullable();
            $table->boolean('has_received_payout')->default(false);
            $table->date('payout_date')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['group_id', 'position_in_queue']);
        });

        // Chilimba Contributions
        Schema::create('lifeplus_chilimba_contributions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_id')->constrained('lifeplus_chilimba_groups')->onDelete('cascade');
            $table->foreignId('member_id')->constrained('lifeplus_chilimba_members')->onDelete('cascade');
            $table->foreignId('recorded_by')->constrained('users')->onDelete('cascade');
            $table->date('contribution_date');
            $table->decimal('amount', 10, 2);
            $table->enum('payment_method', ['cash', 'mobile_money'])->default('cash');
            $table->string('receipt_number', 100)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['group_id', 'contribution_date'], 'lp_contrib_group_date_idx');
            $table->index(['member_id', 'contribution_date'], 'lp_contrib_member_date_idx');
        });

        // Chilimba Payouts
        Schema::create('lifeplus_chilimba_payouts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_id')->constrained('lifeplus_chilimba_groups')->onDelete('cascade');
            $table->foreignId('member_id')->constrained('lifeplus_chilimba_members')->onDelete('cascade');
            $table->foreignId('recorded_by')->constrained('users')->onDelete('cascade');
            $table->date('payout_date');
            $table->decimal('amount', 10, 2);
            $table->integer('cycle_number')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['group_id', 'cycle_number']);
        });

        // Chilimba Loans
        Schema::create('lifeplus_chilimba_loans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_id')->constrained('lifeplus_chilimba_groups')->onDelete('cascade');
            $table->foreignId('member_id')->constrained('lifeplus_chilimba_members')->onDelete('cascade');
            $table->decimal('loan_amount', 10, 2);
            $table->decimal('interest_rate', 5, 2);
            $table->date('loan_date');
            $table->date('due_date');
            $table->text('purpose')->nullable();
            $table->enum('status', ['pending', 'approved', 'active', 'paid', 'defaulted'])->default('pending');
            $table->decimal('total_repaid', 10, 2)->default(0);
            $table->timestamps();

            $table->index(['member_id', 'status']);
            $table->index(['group_id', 'status']);
        });

        // Chilimba Loan Payments
        Schema::create('lifeplus_chilimba_loan_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('loan_id')->constrained('lifeplus_chilimba_loans')->onDelete('cascade');
            $table->foreignId('recorded_by')->constrained('users')->onDelete('cascade');
            $table->date('payment_date');
            $table->decimal('amount', 10, 2);
            $table->enum('payment_method', ['cash', 'mobile_money'])->default('cash');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['loan_id', 'payment_date']);
        });

        // Chilimba Meetings
        Schema::create('lifeplus_chilimba_meetings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_id')->constrained('lifeplus_chilimba_groups')->onDelete('cascade');
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->date('meeting_date');
            $table->json('attendees')->nullable();
            $table->decimal('total_collected', 10, 2)->nullable();
            $table->string('payout_given_to')->nullable();
            $table->json('loans_approved')->nullable();
            $table->text('decisions')->nullable();
            $table->date('next_meeting_date')->nullable();
            $table->timestamps();

            $table->index(['group_id', 'meeting_date']);
        });

        // Chilimba Audit Log
        Schema::create('lifeplus_chilimba_audit_log', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_id')->constrained('lifeplus_chilimba_groups')->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('action_type', ['create', 'update', 'delete', 'approve', 'reject']);
            $table->enum('entity_type', ['contribution', 'payout', 'loan', 'member', 'meeting', 'group']);
            $table->unsignedBigInteger('entity_id');
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->text('reason')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index(['group_id', 'entity_type', 'entity_id'], 'lp_audit_group_entity_idx');
            $table->index(['user_id', 'action_type', 'created_at'], 'lp_audit_user_action_idx');
        });

        // Edit Requests (for approval workflow)
        Schema::create('lifeplus_chilimba_edit_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('group_id')->constrained('lifeplus_chilimba_groups')->onDelete('cascade');
            $table->foreignId('requested_by')->constrained('users')->onDelete('cascade');
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->enum('entity_type', ['contribution', 'payout', 'loan']);
            $table->unsignedBigInteger('entity_id');
            $table->json('old_values');
            $table->json('new_values');
            $table->text('reason');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('review_notes')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();

            $table->index(['group_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lifeplus_chilimba_edit_requests');
        Schema::dropIfExists('lifeplus_chilimba_audit_log');
        Schema::dropIfExists('lifeplus_chilimba_meetings');
        Schema::dropIfExists('lifeplus_chilimba_loan_payments');
        Schema::dropIfExists('lifeplus_chilimba_loans');
        Schema::dropIfExists('lifeplus_chilimba_payouts');
        Schema::dropIfExists('lifeplus_chilimba_contributions');
        Schema::dropIfExists('lifeplus_chilimba_members');
        Schema::dropIfExists('lifeplus_chilimba_groups');
    }
};
