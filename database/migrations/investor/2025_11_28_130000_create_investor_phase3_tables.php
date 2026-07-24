<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('share_transfer_requests')) {
            Schema::create('share_transfer_requests', function (Blueprint $table) {
                $table->id();
                $table->foreignId('seller_investor_id')->constrained('investor_accounts')->onDelete('cascade');
                $table->foreignId('proposed_buyer_id')->nullable()->constrained('investor_accounts')->onDelete('set null');
                $table->string('proposed_buyer_name')->nullable();
                $table->string('proposed_buyer_email')->nullable();
                $table->decimal('shares_percentage', 10, 4);
                $table->decimal('proposed_price', 15, 2);
                $table->decimal('approved_price', 15, 2)->nullable();
                $table->string('transfer_type')->default('internal');
                $table->string('status')->default('draft');
                $table->text('reason_for_sale')->nullable();
                $table->text('board_notes')->nullable();
                $table->text('rejection_reason')->nullable();
                $table->foreignId('reviewed_by')->nullable()->constrained('users')->onDelete('set null');
                $table->timestamp('submitted_at')->nullable();
                $table->timestamp('reviewed_at')->nullable();
                $table->timestamp('completed_at')->nullable();
                $table->json('required_documents')->nullable();
                $table->json('submitted_documents')->nullable();
                $table->timestamps();
                $table->index(['status', 'transfer_type'], 'str_idx');
            });
        }

        if (!Schema::hasTable('liquidity_events')) {
            Schema::create('liquidity_events', function (Blueprint $table) {
                $table->id();
                $table->string('title');
                $table->text('description');
                $table->string('event_type');
                $table->string('status')->default('announced');
                $table->date('announcement_date');
                $table->date('registration_deadline')->nullable();
                $table->date('expected_completion')->nullable();
                $table->date('actual_completion')->nullable();
                $table->decimal('price_per_share', 15, 2)->nullable();
                $table->decimal('total_budget', 15, 2)->nullable();
                $table->decimal('shares_available', 10, 4)->nullable();
                $table->json('eligibility_criteria')->nullable();
                $table->json('documents')->nullable();
                $table->text('terms_conditions')->nullable();
                $table->text('board_resolution_reference')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('liquidity_event_participations')) {
            Schema::create('liquidity_event_participations', function (Blueprint $table) {
                $table->id();
                $table->foreignId('liquidity_event_id')->constrained('liquidity_events')->onDelete('cascade');
                $table->foreignId('investor_account_id')->constrained('investor_accounts')->onDelete('cascade');
                $table->string('status')->default('interested');
                $table->decimal('shares_offered', 10, 4)->nullable();
                $table->decimal('shares_accepted', 10, 4)->nullable();
                $table->decimal('amount_to_receive', 15, 2)->nullable();
                $table->decimal('amount_received', 15, 2)->nullable();
                $table->json('bank_details')->nullable();
                $table->timestamp('registered_at')->nullable();
                $table->timestamp('completed_at')->nullable();
                $table->timestamps();
                $table->unique(['liquidity_event_id', 'investor_account_id'], 'liq_part_unique');
            });
        }

        if (!Schema::hasTable('shareholder_forum_categories')) {
            Schema::create('shareholder_forum_categories', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('slug')->unique();
                $table->text('description')->nullable();
                $table->string('icon')->nullable();
                $table->integer('sort_order')->default(0);
                $table->boolean('is_active')->default(true);
                $table->boolean('requires_moderation')->default(true);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('shareholder_forum_topics')) {
            Schema::create('shareholder_forum_topics', function (Blueprint $table) {
                $table->id();
                $table->foreignId('category_id')->constrained('shareholder_forum_categories')->onDelete('cascade');
                $table->foreignId('author_investor_id')->constrained('investor_accounts')->onDelete('cascade');
                $table->string('title');
                $table->string('slug')->unique();
                $table->text('content');
                $table->string('status')->default('pending_moderation');
                $table->boolean('is_pinned')->default(false);
                $table->boolean('is_announcement')->default(false);
                $table->integer('views_count')->default(0);
                $table->integer('replies_count')->default(0);
                $table->timestamp('last_reply_at')->nullable();
                $table->foreignId('moderated_by')->nullable()->constrained('users')->onDelete('set null');
                $table->timestamp('moderated_at')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('shareholder_forum_replies')) {
            Schema::create('shareholder_forum_replies', function (Blueprint $table) {
                $table->id();
                $table->foreignId('topic_id')->constrained('shareholder_forum_topics')->onDelete('cascade');
                $table->foreignId('author_investor_id')->constrained('investor_accounts')->onDelete('cascade');
                $table->text('content');
                $table->string('status')->default('pending_moderation');
                $table->foreignId('moderated_by')->nullable()->constrained('users')->onDelete('set null');
                $table->timestamp('moderated_at')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('shareholder_directory_profiles')) {
            Schema::create('shareholder_directory_profiles', function (Blueprint $table) {
                $table->id();
                $table->foreignId('investor_account_id')->constrained('investor_accounts')->onDelete('cascade');
                $table->boolean('is_listed')->default(false);
                $table->string('display_name')->nullable();
                $table->string('industry')->nullable();
                $table->string('location')->nullable();
                $table->text('bio')->nullable();
                $table->boolean('show_investment_date')->default(false);
                $table->boolean('allow_contact')->default(false);
                $table->timestamps();
                $table->unique('investor_account_id');
            });
        }

        if (!Schema::hasTable('shareholder_contact_requests')) {
            Schema::create('shareholder_contact_requests', function (Blueprint $table) {
                $table->id();
                $table->foreignId('requester_id')->constrained('investor_accounts')->onDelete('cascade');
                $table->foreignId('recipient_id')->constrained('investor_accounts')->onDelete('cascade');
                $table->text('message');
                $table->string('status')->default('pending');
                $table->text('response')->nullable();
                $table->timestamp('responded_at')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('shareholder_contact_requests');
        Schema::dropIfExists('shareholder_directory_profiles');
        Schema::dropIfExists('shareholder_forum_replies');
        Schema::dropIfExists('shareholder_forum_topics');
        Schema::dropIfExists('shareholder_forum_categories');
        Schema::dropIfExists('liquidity_event_participations');
        Schema::dropIfExists('liquidity_events');
        Schema::dropIfExists('share_transfer_requests');
    }
};
