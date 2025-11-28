<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('shareholder_resolutions')) {
            Schema::create('shareholder_resolutions', function (Blueprint $table) {
                $table->id();
                $table->string('title');
                $table->text('description');
                $table->text('full_text')->nullable();
                $table->string('resolution_type');
                $table->decimal('required_majority', 5, 2)->default(50.01);
                $table->datetime('voting_start');
                $table->datetime('voting_end');
                $table->string('status')->default('draft');
                $table->string('document_path')->nullable();
                $table->json('options')->nullable();
                $table->timestamps();
                $table->index('status');
            });
        }

        if (!Schema::hasTable('shareholder_votes')) {
            Schema::create('shareholder_votes', function (Blueprint $table) {
                $table->id();
                $table->foreignId('resolution_id')->constrained('shareholder_resolutions')->onDelete('cascade');
                $table->foreignId('investor_account_id')->constrained('investor_accounts')->onDelete('cascade');
                $table->string('vote');
                $table->decimal('voting_power', 10, 4);
                $table->string('selected_option')->nullable();
                $table->ipAddress('ip_address')->nullable();
                $table->timestamp('voted_at');
                $table->timestamps();
                $table->unique(['resolution_id', 'investor_account_id'], 'sh_vote_unique');
            });
        }

        if (!Schema::hasTable('proxy_delegations')) {
            Schema::create('proxy_delegations', function (Blueprint $table) {
                $table->id();
                $table->foreignId('delegator_id')->constrained('investor_accounts')->onDelete('cascade');
                $table->foreignId('delegate_id')->constrained('investor_accounts')->onDelete('cascade');
                $table->foreignId('resolution_id')->nullable()->constrained('shareholder_resolutions')->onDelete('cascade');
                $table->boolean('is_general')->default(false);
                $table->datetime('valid_from');
                $table->datetime('valid_until')->nullable();
                $table->string('status')->default('active');
                $table->text('instructions')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('risk_assessments')) {
            Schema::create('risk_assessments', function (Blueprint $table) {
                $table->id();
                $table->date('assessment_date');
                $table->integer('overall_risk_score');
                $table->json('risk_factors');
                $table->json('mitigation_strategies')->nullable();
                $table->text('summary');
                $table->string('risk_level');
                $table->string('assessed_by')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('scenario_models')) {
            Schema::create('scenario_models', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('scenario_type');
                $table->json('assumptions');
                $table->json('projections');
                $table->decimal('projected_valuation_1y', 15, 2)->nullable();
                $table->decimal('projected_valuation_3y', 15, 2)->nullable();
                $table->decimal('projected_valuation_5y', 15, 2)->nullable();
                $table->decimal('projected_roi_1y', 8, 2)->nullable();
                $table->decimal('projected_roi_3y', 8, 2)->nullable();
                $table->decimal('projected_roi_5y', 8, 2)->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('exit_projections')) {
            Schema::create('exit_projections', function (Blueprint $table) {
                $table->id();
                $table->string('exit_type');
                $table->string('title');
                $table->text('description');
                $table->date('projected_date')->nullable();
                $table->decimal('projected_valuation', 15, 2)->nullable();
                $table->decimal('projected_multiple', 8, 2)->nullable();
                $table->integer('probability_percentage')->nullable();
                $table->json('assumptions')->nullable();
                $table->boolean('is_featured')->default(false);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('investor_question_upvotes')) {
            Schema::create('investor_question_upvotes', function (Blueprint $table) {
                $table->id();
                $table->foreignId('question_id')->constrained('investor_questions')->onDelete('cascade');
                $table->foreignId('investor_account_id')->constrained('investor_accounts')->onDelete('cascade');
                $table->timestamp('upvoted_at');
                $table->unique(['question_id', 'investor_account_id'], 'inv_q_upvote_unique');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('investor_question_upvotes');
        Schema::dropIfExists('exit_projections');
        Schema::dropIfExists('scenario_models');
        Schema::dropIfExists('risk_assessments');
        Schema::dropIfExists('proxy_delegations');
        Schema::dropIfExists('shareholder_votes');
        Schema::dropIfExists('shareholder_resolutions');
    }
};
