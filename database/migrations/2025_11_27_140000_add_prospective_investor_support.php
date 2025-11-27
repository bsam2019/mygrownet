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
        // Make investment_round_id nullable for prospective investors
        Schema::table('investor_accounts', function (Blueprint $table) {
            $table->foreignId('investment_round_id')->nullable()->change();
        });

        // Update status enum to include 'prospective'
        // For MySQL, we need to modify the enum
        DB::statement("ALTER TABLE investor_accounts MODIFY COLUMN status ENUM('prospective', 'ciu', 'shareholder', 'exited') DEFAULT 'ciu'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert status enum
        DB::statement("ALTER TABLE investor_accounts MODIFY COLUMN status ENUM('ciu', 'shareholder', 'exited') DEFAULT 'ciu'");

        // Make investment_round_id required again
        Schema::table('investor_accounts', function (Blueprint $table) {
            $table->foreignId('investment_round_id')->nullable(false)->change();
        });
    }
};
