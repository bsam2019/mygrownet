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
        Schema::create('modules', function (Blueprint $table) {
            $table->string('id', 50)->primary();
            $table->string('name', 100);
            $table->string('slug', 50)->unique();
            $table->enum('category', ['core', 'personal', 'sme', 'enterprise']);
            $table->text('description')->nullable();
            $table->string('icon', 50)->nullable();
            $table->string('color', 50)->nullable();
            $table->string('thumbnail', 255)->nullable();
            
            // Access control
            $table->json('account_types');
            $table->json('required_roles')->nullable();
            $table->integer('min_user_level')->nullable();
            
            // Configuration
            $table->json('routes');
            $table->json('pwa_config')->nullable();
            $table->json('features')->nullable();
            $table->json('subscription_tiers')->nullable();
            $table->boolean('requires_subscription')->default(true);
            
            // Metadata
            $table->string('version', 20)->default('1.0.0');
            $table->enum('status', ['active', 'beta', 'coming_soon', 'inactive'])->default('active');
            $table->timestamps();
            
            $table->index('category');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('modules');
    }
};
