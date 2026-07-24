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
        Schema::create('ubumi_duplicate_alerts', function (Blueprint $table) {
            $table->id();
            $table->uuid('family_id');
            $table->uuid('person_a_id');
            $table->uuid('person_b_id');
            $table->decimal('confidence_score', 3, 2);
            $table->enum('status', ['pending', 'reviewed', 'merged', 'dismissed'])->default('pending');
            $table->timestamp('reviewed_at')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users');
            $table->timestamps();

            $table->foreign('family_id')->references('id')->on('ubumi_families')->onDelete('cascade');
            $table->foreign('person_a_id')->references('id')->on('ubumi_persons')->onDelete('cascade');
            $table->foreign('person_b_id')->references('id')->on('ubumi_persons')->onDelete('cascade');
            
            $table->index(['family_id', 'status']);
            $table->index('confidence_score');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ubumi_duplicate_alerts');
    }
};
