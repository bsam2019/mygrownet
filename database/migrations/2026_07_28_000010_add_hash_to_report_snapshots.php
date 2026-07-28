<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('growfinance_report_snapshots', function (Blueprint $table) {
            $table->string('integrity_hash', 64)->nullable()->after('report_data');
            $table->timestamp('locked_at')->nullable()->after('integrity_hash');
        });
    }

    public function down(): void
    {
        Schema::table('growfinance_report_snapshots', function (Blueprint $table) {
            $table->dropColumn(['integrity_hash', 'locked_at']);
        });
    }
};
