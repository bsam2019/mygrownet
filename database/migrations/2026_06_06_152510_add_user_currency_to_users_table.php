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
            // Add user_currency column - ZMW for Zambians, USD for foreigners
            $table->string('user_currency', 3)->default('ZMW')->after('preferred_currency');
            $table->index('user_currency');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['user_currency']);
            $table->dropColumn('user_currency');
        });
    }
};
