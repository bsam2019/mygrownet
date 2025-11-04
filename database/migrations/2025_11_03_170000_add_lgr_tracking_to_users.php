<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Track total LGR ever awarded to user
            $table->decimal('loyalty_points_awarded_total', 10, 2)->default(0)->after('loyalty_points');
            
            // Track total LGR withdrawn (transferred to wallet or withdrawn directly)
            $table->decimal('loyalty_points_withdrawn_total', 10, 2)->default(0)->after('loyalty_points_awarded_total');
        });
        
        // Backfill: Set awarded_total = current balance for existing users
        // (This assumes current balance represents what was awarded minus what was used)
        DB::statement('UPDATE users SET loyalty_points_awarded_total = loyalty_points WHERE loyalty_points > 0');
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['loyalty_points_awarded_total', 'loyalty_points_withdrawn_total']);
        });
    }
};
