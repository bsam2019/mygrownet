<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'lifeplus_onboarded')) {
                $table->boolean('lifeplus_onboarded')->default(false)->after('remember_token');
            }
            if (!Schema::hasColumn('users', 'fcm_token')) {
                $table->string('fcm_token', 500)->nullable()->after('lifeplus_onboarded');
            }
            if (!Schema::hasColumn('users', 'lifeplus_notifications_enabled')) {
                $table->boolean('lifeplus_notifications_enabled')->default(true)->after('fcm_token');
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['lifeplus_onboarded', 'fcm_token', 'lifeplus_notifications_enabled']);
        });
    }
};
