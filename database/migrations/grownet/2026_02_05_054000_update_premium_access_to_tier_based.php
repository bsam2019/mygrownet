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
        Schema::table('users', function (Blueprint $table) {
            // Change from boolean to tier-based access
            // Options: null (no access), 'starter' (K120), 'business' (K350), 'agency' (K900)
            $table->string('premium_template_tier', 20)->nullable()->after('has_premium_template_access');
        });
        
        // Migrate existing data: if has_premium_template_access = true, set to 'starter' tier
        DB::table('users')
            ->where('has_premium_template_access', true)
            ->update(['premium_template_tier' => 'starter']);
        
        // Now drop the old boolean column
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('has_premium_template_access');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Restore the boolean column
            $table->boolean('has_premium_template_access')->default(false)->after('email_verified_at');
        });
        
        // Migrate data back: if premium_template_tier is not null, set has_premium_template_access = true
        DB::table('users')
            ->whereNotNull('premium_template_tier')
            ->update(['has_premium_template_access' => true]);
        
        // Drop the tier column
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('premium_template_tier');
        });
    }
};
