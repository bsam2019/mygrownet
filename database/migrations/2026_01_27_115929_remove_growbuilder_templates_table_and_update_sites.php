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
        // Step 1: Add new site_template_id column to growbuilder_sites
        if (!Schema::hasColumn('growbuilder_sites', 'site_template_id')) {
            Schema::table('growbuilder_sites', function (Blueprint $table) {
                $table->foreignId('site_template_id')->nullable()->after('user_id')->constrained('site_templates')->nullOnDelete();
            });
        }

        // Step 2: Drop old template_id foreign key and column if they exist
        if (Schema::hasColumn('growbuilder_sites', 'template_id')) {
            Schema::table('growbuilder_sites', function (Blueprint $table) {
                $table->dropForeign(['template_id']);
                $table->dropColumn('template_id');
            });
        }

        // Step 3: Drop growbuilder_templates table if it exists
        Schema::dropIfExists('growbuilder_templates');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Recreate growbuilder_templates table
        Schema::create('growbuilder_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('category');
            $table->text('description')->nullable();
            $table->string('preview_image')->nullable();
            $table->string('thumbnail')->nullable();
            $table->json('structure_json');
            $table->json('default_styles')->nullable();
            $table->boolean('is_premium')->default(false);
            $table->integer('price')->default(0);
            $table->boolean('is_active')->default(true);
            $table->integer('usage_count')->default(0);
            $table->timestamps();
        });

        // Add back template_id column
        if (!Schema::hasColumn('growbuilder_sites', 'template_id')) {
            Schema::table('growbuilder_sites', function (Blueprint $table) {
                $table->foreignId('template_id')->nullable()->after('user_id')->constrained('growbuilder_templates')->nullOnDelete();
            });
        }

        // Drop site_template_id column
        if (Schema::hasColumn('growbuilder_sites', 'site_template_id')) {
            Schema::table('growbuilder_sites', function (Blueprint $table) {
                $table->dropForeign(['site_template_id']);
                $table->dropColumn('site_template_id');
            });
        }
    }
};
