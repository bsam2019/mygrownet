<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bizboost_businesses', function (Blueprint $table) {
            // Marketplace columns
            $table->boolean('marketplace_listed')->default(false)->after('onboarding_completed');
            $table->timestamp('marketplace_listed_at')->nullable()->after('marketplace_listed');
            
            // White-label columns
            $table->json('white_label_config')->nullable()->after('settings');
        });
    }

    public function down(): void
    {
        Schema::table('bizboost_businesses', function (Blueprint $table) {
            $table->dropColumn(['marketplace_listed', 'marketplace_listed_at', 'white_label_config']);
        });
    }
};
