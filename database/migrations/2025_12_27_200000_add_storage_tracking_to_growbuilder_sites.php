<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('growbuilder_sites', function (Blueprint $table) {
            $table->bigInteger('storage_used')->default(0)->after('plan'); // in bytes
            $table->bigInteger('storage_limit')->default(104857600)->after('storage_used'); // 100MB default
            $table->timestamp('storage_calculated_at')->nullable()->after('storage_limit');
        });
    }

    public function down(): void
    {
        Schema::table('growbuilder_sites', function (Blueprint $table) {
            $table->dropColumn(['storage_used', 'storage_limit', 'storage_calculated_at']);
        });
    }
};
