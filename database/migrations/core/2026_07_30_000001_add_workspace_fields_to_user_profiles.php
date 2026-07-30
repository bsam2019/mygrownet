<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_profiles', function (Blueprint $table) {
            $table->string('first_name')->nullable()->after('user_id');
            $table->string('last_name')->nullable()->after('first_name');
            $table->string('avatar')->nullable()->after('last_name');
            $table->string('timezone')->default('Africa/Lusaka')->after('avatar');
            $table->string('language', 10)->default('en')->after('timezone');
        });
    }

    public function down(): void
    {
        Schema::table('user_profiles', function (Blueprint $table) {
            $table->dropColumn(['first_name', 'last_name', 'avatar', 'timezone', 'language']);
        });
    }
};
