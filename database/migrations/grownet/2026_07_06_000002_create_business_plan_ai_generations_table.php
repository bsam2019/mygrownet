<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('business_plan_ai_generations')) {
            return;
        }

        Schema::create('business_plan_ai_generations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_plan_id')->constrained('user_business_plans')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('section');
            $table->text('prompt')->nullable();
            $table->longText('generated_content');
            $table->boolean('was_accepted')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('business_plan_ai_generations');
    }
};
