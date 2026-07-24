<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->renameColumn('monthly_activity_points', 'bonus_points');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->renameColumn('bonus_points', 'monthly_activity_points');
        });
    }
};
