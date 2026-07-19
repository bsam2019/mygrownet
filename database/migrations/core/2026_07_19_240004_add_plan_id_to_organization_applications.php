<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('organization_applications', function (Blueprint $table) {
            $table->unsignedBigInteger('plan_id')->nullable()->after('application_id');
            $table->index('plan_id');
        });
    }

    public function down(): void
    {
        Schema::table('organization_applications', function (Blueprint $table) {
            $table->dropIndex(['plan_id']);
            $table->dropColumn('plan_id');
        });
    }
};
