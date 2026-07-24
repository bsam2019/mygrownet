<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('module_tier_features', function (Blueprint $table) {
            $table->id();
            $table->foreignId('module_tier_id')->constrained('module_tiers')->onDelete('cascade');
            $table->string('feature_key', 100);
            $table->string('feature_name', 100);
            $table->enum('feature_type', ['boolean', 'limit', 'text'])->default('boolean');
            $table->boolean('value_boolean')->default(false);
            $table->integer('value_limit')->nullable();
            $table->string('value_text', 255)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['module_tier_id', 'feature_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('module_tier_features');
    }
};
