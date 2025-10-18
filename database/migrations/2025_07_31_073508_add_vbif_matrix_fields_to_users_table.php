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
            // Add matrix_position field for 3x3 matrix tracking
            if (!Schema::hasColumn('users', 'matrix_position')) {
                $table->json('matrix_position')->nullable()->after('last_referral_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'matrix_position')) {
                $table->dropColumn('matrix_position');
            }
        });
    }
};
