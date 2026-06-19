<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plan_objectives', function (Blueprint $table) {
            $table->id();
            $table->foreignId('plan_id')->constrained('cms_plans')->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('type')->default('kpi'); // kpi, milestone, key_result
            $table->decimal('target_value', 15, 2)->nullable();
            $table->decimal('current_value', 15, 2)->nullable();
            $table->string('unit')->nullable(); // %, K, count, boolean, currency
            $table->string('owner')->nullable();
            $table->date('target_date')->nullable();
            $table->date('completed_at')->nullable();
            $table->string('status')->default('not_started'); // not_started, on_track, at_risk, behind, completed
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plan_objectives');
    }
};
