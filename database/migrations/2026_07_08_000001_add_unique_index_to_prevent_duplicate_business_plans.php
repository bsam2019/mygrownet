<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

/**
 * Adds a unique index on (user_id, business_name, status) to prevent
 * duplicate draft business plans from accidental concurrent saves.
 *
 * The three-column composite key still allows:
 *  - One draft per (user, business_name)
 *  - One completed plan per (user, business_name)
 *  - One archived plan per (user, business_name)
 *
 * So a user can re-use a business name after the previous draft is completed
 * or archived, but two concurrent draft saves for the same name will conflict
 * at the DB level (defense-in-depth behind the frontend saveInFlight mutex).
 */
return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('user_business_plans')) {
            return;
        }

        // Clean up any existing true duplicates before adding the index,
        // keeping the most recently updated row per (user_id, business_name, status).
        $duplicates = DB::table('user_business_plans')
            ->select('user_id', 'business_name', 'status', DB::raw('COUNT(*) as cnt'))
            ->groupBy('user_id', 'business_name', 'status')
            ->having('cnt', '>', 1)
            ->get();

        foreach ($duplicates as $dup) {
            $rows = DB::table('user_business_plans')
                ->where('user_id', $dup->user_id)
                ->where('business_name', $dup->business_name)
                ->where('status', $dup->status)
                ->orderByDesc('updated_at')
                ->get();

            $keepId = $rows->first()->id;
            DB::table('user_business_plans')
                ->where('user_id', $dup->user_id)
                ->where('business_name', $dup->business_name)
                ->where('status', $dup->status)
                ->where('id', '!=', $keepId)
                ->delete();
        }

        $indexName = 'user_business_plans_user_name_status_unique';

        try {
            Schema::table('user_business_plans', function (Blueprint $table) use ($indexName) {
                $table->unique(['user_id', 'business_name', 'status'], $indexName);
            });
        } catch (\Throwable $e) {
            // Index already exists — safe to ignore.
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('user_business_plans')) {
            return;
        }

        $indexName = 'user_business_plans_user_name_status_unique';

        try {
            Schema::table('user_business_plans', function (Blueprint $table) use ($indexName) {
                $table->dropUnique($indexName);
            });
        } catch (\Throwable $e) {
            // Index doesn't exist — safe to ignore.
        }
    }
};
