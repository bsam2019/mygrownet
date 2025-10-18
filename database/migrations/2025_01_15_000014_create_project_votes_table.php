<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('project_votes')) {
            Schema::create('project_votes', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id');
                $table->unsignedBigInteger('community_project_id');
                $table->enum('vote_type', ['approval', 'milestone', 'change_request', 'termination', 'manager_change']);
                $table->string('vote_subject'); // What is being voted on
                $table->text('vote_description')->nullable();
                
                // Vote details
                $table->enum('vote', ['yes', 'no', 'abstain']);
                $table->decimal('voting_power', 8, 4); // User's voting power at time of vote
                $table->string('tier_at_vote')->nullable();
                $table->decimal('contribution_amount', 12, 2); // User's contribution at time of vote
                
                // Vote context
                $table->json('vote_options')->nullable(); // For complex votes with multiple options
                $table->text('voter_comments')->nullable();
                $table->timestamp('voted_at');
                
                // Vote session tracking
                $table->string('vote_session_id'); // Groups related votes together
                $table->date('vote_deadline');
                $table->boolean('is_final_vote')->default(false);
                
                $table->timestamps();

                // Constraints and indexes (use short name to avoid MySQL 64-char limit)
                $table->unique(['user_id', 'community_project_id', 'vote_session_id'], 'proj_votes_uid_pid_sid_uq');
                $table->index(['community_project_id', 'vote_type']);
                $table->index(['vote_session_id', 'vote']);
                $table->index(['voted_at', 'vote_deadline']);
            });
        }

        // Add foreign keys only if referenced tables exist
        try {
            if (Schema::hasTable('project_votes') && Schema::hasTable('users')) {
                Schema::table('project_votes', function (Blueprint $table) {
                    $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
                });
            }
        } catch (Throwable $e) {}

        try {
            if (Schema::hasTable('project_votes') && Schema::hasTable('community_projects')) {
                Schema::table('project_votes', function (Blueprint $table) {
                    $table->foreign('community_project_id')->references('id')->on('community_projects')->onDelete('cascade');
                });
            }
        } catch (Throwable $e) {}
    }

    public function down(): void
    {
        Schema::dropIfExists('project_votes');
    }
};