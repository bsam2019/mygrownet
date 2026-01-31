<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Mark all templates as premium except "Consulting Pro" (the free template)
        DB::table('site_templates')
            ->where('slug', '!=', 'consulting-pro')
            ->update(['is_premium' => true]);
        
        // Ensure Consulting Pro is marked as free
        DB::table('site_templates')
            ->where('slug', 'consulting-pro')
            ->update(['is_premium' => false]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert all templates to free
        DB::table('site_templates')
            ->update(['is_premium' => false]);
    }
};
