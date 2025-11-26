<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('investor_notification_preferences', 'digest_frequency')) {
            Schema::table('investor_notification_preferences', function (Blueprint $table) {
                $table->enum('digest_frequency', ['immediate', 'daily', 'weekly', 'none'])
                    ->default('immediate')
                    ->after('email_urgent_only');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('investor_notification_preferences', 'digest_frequency')) {
            Schema::table('investor_notification_preferences', function (Blueprint $table) {
                $table->dropColumn('digest_frequency');
            });
        }
    }
};
