<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasColumn('users', 'premium_template_tier')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('premium_template_tier', 20)->nullable();
            });
        }

        if (Schema::hasColumn('users', 'has_premium_template_access')) {
            // Migrate existing data
            DB::table('users')
                ->where('has_premium_template_access', true)
                ->update(['premium_template_tier' => 'starter']);

            // Drop old boolean column safely
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('has_premium_template_access');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (!Schema::hasColumn('users', 'has_premium_template_access')) {
            Schema::table('users', function (Blueprint $table) {
                $table->boolean('has_premium_template_access')->default(false);
            });
        }

        if (Schema::hasColumn('users', 'premium_template_tier')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('premium_template_tier');
            });
        }
    }
};
