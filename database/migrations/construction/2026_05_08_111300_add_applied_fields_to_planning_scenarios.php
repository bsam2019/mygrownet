<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cms_planning_scenarios', function (Blueprint $table) {
            $table->foreignId('applied_by')->nullable()->after('created_by')->constrained('users')->onDelete('set null');
            $table->timestamp('applied_at')->nullable()->after('applied_by');
        });
    }

    public function down(): void
    {
        Schema::table('cms_planning_scenarios', function (Blueprint $table) {
            $table->dropForeign(['applied_by']);
            $table->dropColumn(['applied_by', 'applied_at']);
        });
    }
};
