<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('benefits', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('category'); // 'apps', 'cloud', 'learning', 'media', 'resources'
            $table->text('description');
            $table->string('icon')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_coming_soon')->default(false);
            $table->timestamps();

            $table->index('category');
            $table->index('is_active');
        });

        Schema::create('starter_kit_benefits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('starter_kit_id')->constrained('starter_kit_purchases')->onDelete('cascade');
            $table->foreignId('benefit_id')->constrained('benefits')->onDelete('cascade');
            $table->boolean('included')->default(true);
            $table->integer('limit_value')->nullable();
            $table->timestamps();

            $table->unique(['starter_kit_id', 'benefit_id']);
            $table->index('starter_kit_id');
        });

        Schema::create('user_benefit_status', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('benefit_id')->constrained('benefits')->onDelete('cascade');
            $table->enum('status', ['active', 'inactive', 'pending', 'locked'])->default('inactive');
            $table->timestamp('activated_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'benefit_id']);
            $table->index('user_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_benefit_status');
        Schema::dropIfExists('starter_kit_benefits');
        Schema::dropIfExists('benefits');
    }
};
