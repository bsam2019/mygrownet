<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cms_companies', function (Blueprint $table) {
            $table->boolean('onboarding_completed')->default(false)->after('settings');
            $table->json('onboarding_progress')->nullable()->after('onboarding_completed');
            $table->timestamp('onboarding_completed_at')->nullable()->after('onboarding_progress');
        });

        Schema::table('cms_users', function (Blueprint $table) {
            $table->boolean('tour_completed')->default(false)->after('last_login_at');
            $table->json('tour_progress')->nullable()->after('tour_completed');
        });
    }

    public function down(): void
    {
        Schema::table('cms_companies', function (Blueprint $table) {
            $table->dropColumn(['onboarding_completed', 'onboarding_progress', 'onboarding_completed_at']);
        });

        Schema::table('cms_users', function (Blueprint $table) {
            $table->dropColumn(['tour_completed', 'tour_progress']);
        });
    }
};
