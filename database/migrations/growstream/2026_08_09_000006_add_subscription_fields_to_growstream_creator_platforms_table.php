<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('growstream_creator_platforms')) {
            Schema::table('growstream_creator_platforms', function (Blueprint $table) {
                if (!Schema::hasColumn('growstream_creator_platforms', 'subscription_plan')) {
                    $table->string('subscription_plan', 50)->nullable()->after('brand_color');
                }
                if (!Schema::hasColumn('growstream_creator_platforms', 'subscription_status')) {
                    $table->string('subscription_status', 30)->default('pending')->after('subscription_plan');
                }
                if (!Schema::hasColumn('growstream_creator_platforms', 'subscribed_at')) {
                    $table->timestamp('subscribed_at')->nullable()->after('subscription_status');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('growstream_creator_platforms')) {
            Schema::table('growstream_creator_platforms', function (Blueprint $table) {
                $columns = array_filter(['subscription_plan', 'subscription_status', 'subscribed_at'], fn($col) => Schema::hasColumn('growstream_creator_platforms', $col));
                if (!empty($columns)) {
                    $table->dropColumn($columns);
                }
            });
        }
    }
};
