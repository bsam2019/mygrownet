<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Leads table
        if (!Schema::hasTable('cms_leads')) {
            Schema::create('cms_leads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('cms_companies')->onDelete('cascade');
            $table->string('lead_number')->unique();
            
            // Contact info
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('company_name')->nullable();
            $table->string('job_title')->nullable();
            
            // Lead details
            $table->enum('status', ['new', 'contacted', 'qualified', 'proposal', 'negotiation', 'won', 'lost'])->default('new');
            $table->enum('source', ['website', 'referral', 'social_media', 'cold_call', 'email', 'event', 'other'])->default('other');
            $table->enum('priority', ['low', 'medium', 'high'])->default('medium');
            $table->decimal('estimated_value', 10, 2)->nullable();
            $table->integer('probability')->default(0); // 0-100%
            
            // Assignment
            $table->foreignId('assigned_to')->nullable()->constrained('cms_users')->onDelete('set null');
            
            // Segmentation
            $table->string('industry')->nullable();
            $table->string('company_size')->nullable();
            $table->json('tags')->nullable();
            
            // Tracking
            $table->text('notes')->nullable();
            $table->timestamp('last_contacted_at')->nullable();
            $table->timestamp('next_follow_up')->nullable();
            $table->timestamp('converted_at')->nullable();
            $table->foreignId('converted_to_customer_id')->nullable()->constrained('cms_customers')->onDelete('set null');
            
            $table->foreignId('created_by')->constrained('cms_users')->onDelete('cascade');
            $table->timestamps();
            
            $table->index(['company_id', 'status']);
            $table->index(['company_id', 'assigned_to']);
            $table->index(['company_id', 'next_follow_up']);
        });
        }

        // Opportunities table
        if (!Schema::hasTable('cms_opportunities')) {
            Schema::create('cms_opportunities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('cms_companies')->onDelete('cascade');
            $table->string('opportunity_number')->unique();
            
            // Relationship
            $table->foreignId('customer_id')->nullable()->constrained('cms_customers')->onDelete('cascade');
            $table->foreignId('lead_id')->nullable()->constrained('cms_leads')->onDelete('set null');
            
            // Opportunity details
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('amount', 10, 2);
            $table->integer('probability')->default(50); // 0-100%
            $table->decimal('weighted_amount', 10, 2)->nullable(); // amount * probability
            
            // Pipeline stage
            $table->enum('stage', ['prospecting', 'qualification', 'proposal', 'negotiation', 'closed_won', 'closed_lost'])->default('prospecting');
            $table->enum('priority', ['low', 'medium', 'high'])->default('medium');
            
            // Dates
            $table->date('expected_close_date')->nullable();
            $table->date('actual_close_date')->nullable();
            
            // Assignment
            $table->foreignId('assigned_to')->nullable()->constrained('cms_users')->onDelete('set null');
            
            // Tracking
            $table->text('notes')->nullable();
            $table->text('loss_reason')->nullable();
            $table->timestamp('last_activity_at')->nullable();
            
            $table->foreignId('created_by')->constrained('cms_users')->onDelete('cascade');
            $table->timestamps();
            
            $table->index(['company_id', 'stage']);
            $table->index(['company_id', 'assigned_to']);
            $table->index(['company_id', 'expected_close_date']);
        });
        }

        // Communication history table
        if (!Schema::hasTable('cms_communications')) {
            Schema::create('cms_communications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('cms_companies')->onDelete('cascade');
            
            // Related entity (polymorphic)
            $table->morphs('communicable'); // customer, lead, opportunity
            
            // Communication details
            $table->enum('type', ['call', 'email', 'meeting', 'note', 'sms', 'other'])->default('note');
            $table->enum('direction', ['inbound', 'outbound'])->default('outbound');
            $table->string('subject')->nullable();
            $table->text('content');
            $table->timestamp('communicated_at');
            
            // Tracking
            $table->integer('duration_minutes')->nullable(); // for calls/meetings
            $table->json('attachments')->nullable();
            
            $table->foreignId('created_by')->constrained('cms_users')->onDelete('cascade');
            $table->timestamps();
            
            // Use custom shorter index name
            $table->index(['company_id', 'communicable_type', 'communicable_id'], 'cms_comm_lookup_idx');
            $table->index(['company_id', 'communicated_at']);
        });
        }

        // Follow-up reminders table
        if (!Schema::hasTable('cms_follow_ups')) {
            Schema::create('cms_follow_ups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('cms_companies')->onDelete('cascade');
            
            // Related entity (polymorphic)
            $table->morphs('followable'); // customer, lead, opportunity
            
            // Reminder details
            $table->string('title');
            $table->text('description')->nullable();
            $table->timestamp('due_date');
            $table->enum('priority', ['low', 'medium', 'high'])->default('medium');
            $table->enum('status', ['pending', 'completed', 'cancelled'])->default('pending');
            
            // Assignment
            $table->foreignId('assigned_to')->constrained('cms_users')->onDelete('cascade');
            
            // Completion
            $table->timestamp('completed_at')->nullable();
            $table->text('completion_notes')->nullable();
            
            $table->foreignId('created_by')->constrained('cms_users')->onDelete('cascade');
            $table->timestamps();
            
            $table->index(['company_id', 'assigned_to', 'status']);
            $table->index(['company_id', 'due_date']);
        });
        }

        // Customer segments table
        if (!Schema::hasTable('cms_customer_segments')) {
            Schema::create('cms_customer_segments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('cms_companies')->onDelete('cascade');
            
            $table->string('name');
            $table->text('description')->nullable();
            $table->json('criteria'); // Segmentation rules
            $table->integer('customer_count')->default(0);
            $table->boolean('is_active')->default(true);
            
            $table->foreignId('created_by')->constrained('cms_users')->onDelete('cascade');
            $table->timestamps();
            
            $table->index(['company_id', 'is_active']);
        });
        }

        // Marketing campaigns table
        if (!Schema::hasTable('cms_campaigns')) {
            Schema::create('cms_campaigns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('cms_companies')->onDelete('cascade');
            
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('type', ['email', 'sms', 'social', 'event', 'other'])->default('email');
            $table->enum('status', ['draft', 'scheduled', 'active', 'paused', 'completed'])->default('draft');
            
            // Targeting
            $table->foreignId('segment_id')->nullable()->constrained('cms_customer_segments')->onDelete('set null');
            $table->json('target_customer_ids')->nullable();
            
            // Schedule
            $table->timestamp('start_date')->nullable();
            $table->timestamp('end_date')->nullable();
            
            // Budget & Goals
            $table->decimal('budget', 10, 2)->nullable();
            $table->integer('target_leads')->nullable();
            $table->decimal('target_revenue', 10, 2)->nullable();
            
            // Performance
            $table->integer('sent_count')->default(0);
            $table->integer('opened_count')->default(0);
            $table->integer('clicked_count')->default(0);
            $table->integer('converted_count')->default(0);
            $table->decimal('actual_revenue', 10, 2)->default(0);
            
            $table->foreignId('created_by')->constrained('cms_users')->onDelete('cascade');
            $table->timestamps();
            
            $table->index(['company_id', 'status']);
        });
        }

        // Customer lifetime value tracking
        if (!Schema::hasTable('cms_customer_metrics')) {
            Schema::create('cms_customer_metrics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('cms_companies')->onDelete('cascade');
            $table->foreignId('customer_id')->constrained('cms_customers')->onDelete('cascade');
            
            // Lifetime value
            $table->decimal('lifetime_value', 10, 2)->default(0);
            $table->decimal('average_order_value', 10, 2)->default(0);
            $table->integer('total_orders')->default(0);
            $table->integer('total_jobs')->default(0);
            
            // Engagement
            $table->timestamp('first_purchase_date')->nullable();
            $table->timestamp('last_purchase_date')->nullable();
            $table->integer('days_since_last_purchase')->nullable();
            $table->integer('purchase_frequency_days')->nullable();
            
            // Profitability
            $table->decimal('total_revenue', 10, 2)->default(0);
            $table->decimal('total_profit', 10, 2)->default(0);
            $table->decimal('profit_margin', 5, 2)->default(0);
            
            // Segmentation
            $table->enum('customer_tier', ['bronze', 'silver', 'gold', 'platinum'])->default('bronze');
            $table->enum('churn_risk', ['low', 'medium', 'high'])->default('low');
            
            $table->timestamp('calculated_at')->nullable();
            $table->timestamps();
            
            $table->unique(['company_id', 'customer_id']);
            $table->index(['company_id', 'customer_tier']);
            $table->index(['company_id', 'churn_risk']);
        });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('cms_customer_metrics');
        Schema::dropIfExists('cms_campaigns');
        Schema::dropIfExists('cms_customer_segments');
        Schema::dropIfExists('cms_follow_ups');
        Schema::dropIfExists('cms_communications');
        Schema::dropIfExists('cms_opportunities');
        Schema::dropIfExists('cms_leads');
    }
};
