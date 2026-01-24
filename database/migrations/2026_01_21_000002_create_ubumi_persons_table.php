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
        Schema::create('ubumi_persons', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('family_id');
            $table->string('name');
            $table->string('photo_url')->nullable();
            $table->date('date_of_birth')->nullable();
            $table->integer('approximate_age')->nullable();
            $table->enum('gender', ['male', 'female', 'other', 'prefer_not_to_say'])->nullable();
            $table->boolean('is_deceased')->default(false);
            $table->boolean('is_merged')->default(false);
            $table->json('merged_from')->nullable();
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('family_id')->references('id')->on('ubumi_families')->onDelete('cascade');
            
            $table->index('family_id');
            $table->index('name');
            $table->index('created_by');
            $table->index('deleted_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ubumi_persons');
    }
};
