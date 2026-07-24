<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plan_links', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plan_objective_id')->constrained('plan_objectives')->cascadeOnDelete();
            $table->morphs('linkable');
            $table->string('metric_field')->nullable();
            $table->boolean('auto_sync')->default(false);
            $table->string('label')->nullable();
            $table->timestamps();

            $table->unique(['plan_objective_id', 'linkable_type', 'linkable_id'], 'plan_link_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plan_links');
    }
};
