<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('extensions', function (Blueprint $table) {
            if (!Schema::hasColumn('extensions', 'trial_days')) {
                $table->integer('trial_days')->default(0)->after('is_active');
            }
        });

        Schema::table('company_extensions', function (Blueprint $table) {
            if (!Schema::hasColumn('company_extensions', 'trial_ends_at')) {
                $table->timestamp('trial_ends_at')->nullable()->after('subscribed_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('extensions', function (Blueprint $table) {
            $table->dropColumn('trial_days');
        });

        Schema::table('company_extensions', function (Blueprint $table) {
            $table->dropColumn('trial_ends_at');
        });
    }
};
