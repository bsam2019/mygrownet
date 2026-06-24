<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('venture_votes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('resolution_id')->constrained('venture_resolutions')->onDelete('restrict');
            $table->foreignId('shareholder_id')->constrained('venture_shareholders')->onDelete('restrict');
            $table->foreignId('user_id')->constrained('users')->onDelete('restrict');
            $table->enum('vote', ['for', 'against', 'abstain']);
            $table->decimal('equity_at_vote', 5, 4)->comment('Shareholder equity percentage at time of vote');
            $table->text('comment')->nullable();
            $table->timestamp('voted_at')->nullable();
            $table->timestamps();

            $table->unique(['resolution_id', 'shareholder_id'], 'unique_vote_per_shareholder');
            $table->index(['shareholder_id', 'resolution_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('venture_votes');
    }
};
