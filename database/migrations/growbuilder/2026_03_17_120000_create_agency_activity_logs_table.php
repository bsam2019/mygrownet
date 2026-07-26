<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('agency_activity_logs')) {
            Schema::create('agency_activity_logs', function (Blueprint $table) {
                $table->id();
                $table->foreignId('agency_id')->constrained('agencies')->onDelete('cascade');
                $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
                $table->string('action_type', 50); // created, updated, deleted, published, suspended, etc.
                $table->string('entity_type', 50); // site, client, invoice, team_member, etc.
                $table->unsignedBigInteger('entity_id')->nullable(); // ID of the entity being acted upon
                $table->text('description'); // Human-readable description of the action
                $table->json('metadata')->nullable(); // Additional data about the action
                $table->timestamps();

                // Indexes for better query performance
                $table->index(['agency_id', 'created_at']);
                $table->index(['agency_id', 'action_type']);
                $table->index(['agency_id', 'entity_type']);
                $table->index(['agency_id', 'entity_type', 'entity_id']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('agency_activity_logs');
    }
};