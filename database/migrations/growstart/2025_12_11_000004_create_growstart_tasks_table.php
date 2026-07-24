<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('growstart_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stage_id')->constrained('growstart_stages')->cascadeOnDelete();
            $table->foreignId('industry_id')->nullable()->constrained('growstart_industries')->nullOnDelete();
            $table->foreignId('country_id')->nullable()->constrained('growstart_countries')->nullOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->text('instructions')->nullable();
            $table->string('external_link')->nullable();
            $table->integer('estimated_hours')->default(1);
            $table->integer('order')->default(0);
            $table->boolean('is_required')->default(true);
            $table->boolean('is_premium')->default(false);
            $table->timestamps();

            $table->index(['stage_id', 'industry_id', 'country_id']);
            $table->index(['stage_id', 'order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('growstart_tasks');
    }
};
