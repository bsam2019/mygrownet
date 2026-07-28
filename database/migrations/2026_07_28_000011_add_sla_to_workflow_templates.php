<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('growfinance_workflow_templates', function (Blueprint $table) {
            $table->integer('sla_hours')->nullable()->after('is_active');
            $table->boolean('allow_escalation')->default(false)->after('sla_hours');
        });
    }

    public function down(): void
    {
        Schema::table('growfinance_workflow_templates', function (Blueprint $table) {
            $table->dropColumn(['sla_hours', 'allow_escalation']);
        });
    }
};
