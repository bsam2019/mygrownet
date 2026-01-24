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
        Schema::create('ubumi_merge_proposals', function (Blueprint $table) {
            $table->id();
            $table->uuid('family_id');
            $table->uuid('person_a_id');
            $table->uuid('person_b_id');
            $table->string('proposed_name');
            $table->enum('keep_photo_from', ['person_a', 'person_b', 'both'])->default('person_a');
            $table->text('notes')->nullable();
            $table->foreignId('proposed_by')->constrained('users');
            $table->enum('status', ['pending', 'approved', 'rejected', 'merged'])->default('pending');
            $table->foreignId('reviewed_by')->nullable()->constrained('users');
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();

            $table->foreign('family_id')->references('id')->on('ubumi_families')->onDelete('cascade');
            $table->foreign('person_a_id')->references('id')->on('ubumi_persons')->onDelete('cascade');
            $table->foreign('person_b_id')->references('id')->on('ubumi_persons')->onDelete('cascade');
            
            $table->index(['family_id', 'status']);
            $table->index('proposed_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ubumi_merge_proposals');
    }
};
