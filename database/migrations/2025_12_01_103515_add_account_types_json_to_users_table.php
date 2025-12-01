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
            // Add account_types JSON column to support multiple account types
            $table->json('account_types')->nullable()->after('account_type');
        });

        // Migrate existing account_type values to account_types array
        DB::table('users')->whereNotNull('account_type')->update([
            'account_types' => DB::raw("JSON_ARRAY(account_type)")
        ]);

        // Set default for users without account_type
        // Users with referrer = MEMBER, without = CLIENT
        DB::statement("
            UPDATE users 
            SET account_types = JSON_ARRAY(
                CASE 
                    WHEN referrer_id IS NOT NULL THEN 'member'
                    ELSE 'client'
                END
            )
            WHERE account_type IS NULL AND account_types IS NULL
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('account_types');
        });
    }
};
