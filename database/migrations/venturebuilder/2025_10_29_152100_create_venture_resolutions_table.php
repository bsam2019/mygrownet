<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('venture_resolutions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('venture_id')->constrained('ventures')->onDelete('restrict');
            $table->string('title');
            $table->text('description');
            $table->enum('type', ['ordinary', 'special', 'board'])->default('ordinary');
            $table->enum('status', ['draft', 'voting', 'passed', 'rejected', 'cancelled'])->default('draft');
            $table->timestamp('voting_starts_at')->nullable();
            $table->timestamp('voting_ends_at')->nullable();
            $table->decimal('pass_threshold_percentage', 5, 2)->default(50.00)->comment('Minimum % of votes needed to pass');
            $table->decimal('votes_for', 15, 4)->default(0);
            $table->decimal('votes_against', 15, 4)->default(0);
            $table->decimal('votes_abstain', 15, 4)->default(0);
            $table->decimal('total_voted_equity', 15, 4)->default(0)->comment('Total equity % of all votes cast');
            $table->text('result_notes')->nullable();
            $table->foreignId('created_by')->constrained('users')->onDelete('restrict');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['venture_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('venture_resolutions');
    }
};
