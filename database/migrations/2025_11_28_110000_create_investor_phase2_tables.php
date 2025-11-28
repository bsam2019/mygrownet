<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('investor_risk_assessments')) {
            Schema::create('investor_risk_assessments', function (Blueprint $table) {
                $table->id();
                $table->foreignId('investor_account_id')->constrained('investor_accounts')->onDelete('cascade');
                $table->string('assessment_period');
                $table->decimal('market_risk_score', 5, 2);
                $table->decimal('liquidity_risk_score', 5, 2);
                $table->decimal('operational_risk_score', 5, 2);
                $table->decimal('overall_risk_score', 5, 2);
                $table->string('risk_level');
                $table->json('risk_factors')->nullable();
                $table->json('mitigation_strategies')->nullable();
                $table->text('analyst_notes')->nullable();
                $table->timestamp('assessed_at');
                $table->timestamps();
            });
        }

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
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('company_valuations');
        Schema::dropIfExists('investor_poll_votes');
        Schema::dropIfExists('investor_polls');
        Schema::dropIfExists('investor_survey_responses');
        Schema::dropIfExists('investor_surveys');
        Schema::dropIfExists('investor_feedback');
        Schema::dropIfExists('investor_question_answers');
        Schema::dropIfExists('investor_questions');
        Schema::dropIfExists('investor_exit_opportunities');
        Schema::dropIfExists('investor_scenario_projections');
        Schema::dropIfExists('investor_risk_assessments');
    }
};
