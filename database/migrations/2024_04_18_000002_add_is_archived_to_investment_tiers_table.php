<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('investment_tiers', function (Blueprint $table) {
            if (!Schema::hasColumn('investment_tiers', 'is_archived')) {
                $table->boolean('is_archived')->default(false)->after('is_active');
            }
        });
    }

    public function down(): void
    {
        Schema::table('investment_tiers', function (Blueprint $table) {
            if (Schema::hasColumn('investment_tiers', 'is_archived')) {
                $table->dropColumn('is_archived');
            }
        });
    }
}; 