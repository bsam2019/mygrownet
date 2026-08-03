<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('growfinance_report_snapshots')) {
            // Base table may be missing if the CREATE was recorded as complete
            // without actually running (mark-complete scenario). Skip so the
            // migrate run does not crash.
            return;
        }

        Schema::table('growfinance_report_snapshots', function (Blueprint $table) {
            $table->string('integrity_hash', 64)->nullable()->after('report_data');
            $table->timestamp('locked_at')->nullable()->after('integrity_hash');
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('growfinance_report_snapshots')) {
            return;
        }

        Schema::table('growfinance_report_snapshots', function (Blueprint $table) {
            $table->dropColumn(['integrity_hash', 'locked_at']);
        });
    }
};
