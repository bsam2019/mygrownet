<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Email Campaigns
        Schema::create('email_campaigns', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('type', ['onboarding', 'engagement', 'reactivation', 'upgrade', 'custom'])->default('custom');
            $table->enum('status', ['draft', 'active', 'paused', 'completed'])->default('draft');
            $table->enum('trigger_type', ['immediate', 'scheduled', 'behavioral'])->default('immediate');
            $table->json('trigger_config')->nullable();
            $table->timestamp('start_date')->nullable();
            $table->timestamp('end_date')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            
            $table->index(['type', 'status']);
        });

        // Email Templates
        Schema::create('email_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('subject');
            $table->string('preview_text')->nullable();
            $table->text('html_content');
            $table->text('text_content')->nullable();
            $table->json('variables')->nullable();
            $table->string('category', 100)->nullable();
            $table->boolean('is_system')->default(false);
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
            
            $table->index('category');
        });

        // Email Sequences
        Schema::create('email_sequences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->constrained('email_campaigns')->onDelete('cascade');
            $table->integer('sequence_order');
            $table->integer('delay_days')->default(0);
            $table->integer('delay_hours')->default(0);
            $table->foreignId('template_id')->constrained('email_templates')->onDelete('cascade');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->index(['campaign_id', 'sequence_order']);
        });

        // Campaign Subscribers
        Schema::create('campaign_subscribers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->constrained('email_campaigns')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->enum('status', ['enrolled', 'active', 'completed', 'unsubscribed', 'bounced'])->default('enrolled');
            $table->timestamp('enrolled_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('unsubscribed_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            
            $table->unique(['campaign_id', 'user_id']);
            $table->index('status');
        });

        // Email Queue
        Schema::create('email_queue', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->nullable()->constrained('email_campaigns')->onDelete('set null');
            $table->foreignId('sequence_id')->nullable()->constrained('email_sequences')->onDelete('set null');
            $table->foreignId('subscriber_id')->constrained('campaign_subscribers')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('template_id')->constrained('email_templates')->onDelete('cascade');
            $table->timestamp('scheduled_at');
            $table->timestamp('sent_at')->nullable();
            $table->enum('status', ['pending', 'sending', 'sent', 'failed', 'cancelled'])->default('pending');
            $table->text('error_message')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            
            $table->index(['scheduled_at', 'status']);
            $table->index('user_id');
        });

        // Email Tracking
        Schema::create('email_tracking', function (Blueprint $table) {
            $table->id();
            $table->foreignId('queue_id')->constrained('email_queue')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('campaign_id')->nullable()->constrained('email_campaigns')->onDelete('set null');
            $table->enum('event_type', ['sent', 'delivered', 'opened', 'clicked', 'bounced', 'complained', 'unsubscribed']);
            $table->json('event_data')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamps();
            
            $table->index('queue_id');
            $table->index(['event_type', 'created_at']);
        });

        // A/B Testing
        Schema::create('email_ab_tests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->constrained('email_campaigns')->onDelete('cascade');
            $table->string('name');
            $table->enum('test_type', ['subject', 'content', 'send_time']);
            $table->foreignId('variant_a_id')->constrained('email_templates')->onDelete('cascade');
            $table->foreignId('variant_b_id')->constrained('email_templates')->onDelete('cascade');
            $table->integer('split_percentage')->default(50);
            $table->enum('winner_variant', ['a', 'b', 'none'])->default('none');
            $table->enum('winner_metric', ['open_rate', 'click_rate', 'conversion_rate']);
            $table->enum('status', ['draft', 'running', 'completed'])->default('draft');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->timestamps();
        });

        // Campaign Analytics
        Schema::create('campaign_analytics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('campaign_id')->constrained('email_campaigns')->onDelete('cascade');
            $table->date('date');
            $table->integer('emails_sent')->default(0);
            $table->integer('emails_delivered')->default(0);
            $table->integer('emails_opened')->default(0);
            $table->integer('emails_clicked')->default(0);
            $table->integer('emails_bounced')->default(0);
            $table->integer('emails_unsubscribed')->default(0);
            $table->integer('unique_opens')->default(0);
            $table->integer('unique_clicks')->default(0);
            $table->integer('conversions')->default(0);
            $table->decimal('revenue', 15, 2)->default(0);
            $table->timestamps();
            
            $table->unique(['campaign_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('campaign_analytics');
        Schema::dropIfExists('email_ab_tests');
        Schema::dropIfExists('email_tracking');
        Schema::dropIfExists('email_queue');
        Schema::dropIfExists('campaign_subscribers');
        Schema::dropIfExists('email_sequences');
        Schema::dropIfExists('email_templates');
        Schema::dropIfExists('email_campaigns');
    }
};
