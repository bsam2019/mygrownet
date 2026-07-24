<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('growbuilder_sites', function (Blueprint $table) {
            $table->timestamp('scheduled_deletion_at')->nullable()->after('plan_expires_at');
            $table->text('deletion_reason')->nullable()->after('scheduled_deletion_at');
        });
    }

    public function down(): void
    {
        Schema::table('growbuilder_sites', function (Blueprint $table) {
            $table->dropColumn(['scheduled_deletion_at', 'deletion_reason']);
        });
    }
};
