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
        // Update all users who have starter kit but no tier set to 'basic'
        DB::table('users')
            ->where('has_starter_kit', true)
            ->whereNull('starter_kit_tier')
            ->update(['starter_kit_tier' => 'basic']);
        
        // Update all starter_kit_purchases that don't have a tier to 'basic'
        DB::table('starter_kit_purchases')
            ->whereNull('tier')
            ->update(['tier' => 'basic']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No need to reverse this data migration
    }
};
