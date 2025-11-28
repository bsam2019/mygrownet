<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

echo "Creating Phase 2 & 3 tables...\n\n";

// Phase 2 Tables
if (!Schema::hasTable('investor_scenario_projections')) {
    Schema::create('investor_scenario_projections', function (Blueprint $table) {
        $table->id();
        $table->foreignId('investor_account_id')->constrained('investor_accounts')->onDelete('cascade');
        $table->string('scenario_type');
        $table->integer('projection_years');
        $table->decimal('projected_valuation', 15, 2);
        $table->decimal('projected_equity_value', 15, 2);
        $table->decimal('projected_roi_percentage', 8, 2);
        $table->decimal('projected_annual_dividends', 15, 2)->nullable();
        $table->json('assumptions')->nullable();
        $table->json('milestones')->nullable();
        $table->timestamp('generated_at');
        $table->timestamps();
    });
    echo "✓ investor_scenario_projections\n";
}

if (!Schema::hasTable('investor_exit_opportunities')) {
    Schema::create('investor_exit_opportunities', function (Blueprint $table) {
        $table->id();
        $table->foreignId('investment_round_id')->constrained('investment_rounds')->onDelete('cascade');
        $table->string('opportunity_type');
        $table->string('status');
        $table->decimal('estimated_valuation', 15, 2)->nullable();
        $table->decimal('estimated_share_price', 10, 2)->nullable();
        $table->date('target_date')->nullable();
        $table->text('description')->nullable();
        $table->json('terms')->nullable();
        $table->boolean('is_public')->default(false);
        $table->timestamps();
    });
    echo "✓ investor_exit_opportunities\n";
}

if (!Schema::hasTable('investor_questions')) {
    Schema::create('investor_questions', function (Blueprint $table) {
        $table->id();
        $table->foreignId('investor_account_id')->constrained('investor_accounts')->onDelete('cascade');
        $table->string('category');
        $table->string('subject');
        $table->text('question');
        $table->string('status')->default('pending');
        $table->boolean('is_public')->default(false);
        $table->integer('upvotes')->default(0);
        $table->timestamps();
    });
    echo "✓ investor_questions\n";
}

if (!Schema::hasTable('investor_question_answers')) {
    Schema::create('investor_question_answers', function (Blueprint $table) {
        $table->id();
        $table->foreignId('question_id')->constrained('investor_questions')->onDelete('cascade');
        $table->foreignId('answered_by_user_id')->nullable()->constrained('users')->onDelete('set null');
        $table->text('answer');
        $table->json('attachments')->nullable();
        $table->timestamp('answered_at');
        $table->timestamps();
    });
    echo "✓ investor_question_answers\n";
}

if (!Schema::hasTable('investor_feedback')) {
    Schema::create('investor_feedback', function (Blueprint $table) {
        $table->id();
        $table->foreignId('investor_account_id')->constrained('investor_accounts')->onDelete('cascade');
        $table->string('feedback_type');
        $table->string('category');
        $table->string('subject');
        $table->text('feedback');
        $table->integer('satisfaction_rating')->nullable();
        $table->string('status')->default('submitted');
        $table->text('admin_response')->nullable();
        $table->timestamp('responded_at')->nullable();
        $table->timestamps();
    });
    echo "✓ investor_feedback\n";
}

if (!Schema::hasTable('investor_surveys')) {
    Schema::create('investor_surveys', function (Blueprint $table) {
        $table->id();
        $table->string('title');
        $table->text('description')->nullable();
        $table->string('survey_type');
        $table->json('questions');
        $table->date('start_date');
        $table->date('end_date');
        $table->string('status')->default('draft');
        $table->boolean('is_anonymous')->default(false);
        $table->timestamps();
    });
    echo "✓ investor_surveys\n";
}

if (!Schema::hasTable('investor_survey_responses')) {
    Schema::create('investor_survey_responses', function (Blueprint $table) {
        $table->id();
        $table->foreignId('survey_id')->constrained('investor_surveys')->onDelete('cascade');
        $table->foreignId('investor_account_id')->constrained('investor_accounts')->onDelete('cascade');
        $table->json('responses');
        $table->timestamp('submitted_at');
        $table->timestamps();
    });
    echo "✓ investor_survey_responses\n";
}

if (!Schema::hasTable('investor_polls')) {
    Schema::create('investor_polls', function (Blueprint $table) {
        $table->id();
        $table->string('question');
        $table->json('options');
        $table->string('poll_type')->default('opinion');
        $table->date('start_date');
        $table->date('end_date');
        $table->string('status')->default('active');
        $table->boolean('allow_multiple')->default(false);
        $table->timestamps();
    });
    echo "✓ investor_polls\n";
}

if (!Schema::hasTable('investor_poll_votes')) {
    Schema::create('investor_poll_votes', function (Blueprint $table) {
        $table->id();
        $table->foreignId('poll_id')->constrained('investor_polls')->onDelete('cascade');
        $table->foreignId('investor_account_id')->constrained('investor_accounts')->onDelete('cascade');
        $table->json('selected_options');
        $table->timestamp('voted_at');
        $table->timestamps();
    });
    echo "✓ investor_poll_votes\n";
}

if (!Schema::hasTable('company_valuations')) {
    Schema::create('company_valuations', function (Blueprint $table) {
        $table->id();
        $table->date('valuation_date');
        $table->decimal('valuation_amount', 15, 2);
        $table->string('valuation_method');
        $table->text('notes')->nullable();
        $table->json('assumptions')->nullable();
        $table->foreignId('created_by_user_id')->nullable()->constrained('users')->onDelete('set null');
        $table->timestamps();
    });
    echo "✓ company_valuations\n";
}

// Phase 3 Tables
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
    });
    echo "✓ share_transfer_requests\n";
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
    echo "✓ liquidity_events\n";
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
    });
    echo "✓ liquidity_event_participations\n";
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
    echo "✓ shareholder_forum_categories\n";
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
    echo "✓ shareholder_forum_topics\n";
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
    echo "✓ shareholder_forum_replies\n";
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
    echo "✓ shareholder_directory_profiles\n";
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
    echo "✓ shareholder_contact_requests\n";
}

echo "\n✅ All Phase 2 & 3 tables created successfully!\n";
