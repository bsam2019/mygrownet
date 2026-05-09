<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('cms_companies') && !Schema::hasColumn('cms_companies', 'integration_settings')) {
            Schema::table('cms_companies', function (Blueprint $table) {
                $table->json('integration_settings')->nullable()->after('settings');
            });
        }
    }

    public function down(): void
    {
        Schema::table('cms_companies', function (Blueprint $table) {
            $table->dropColumn('integration_settings');
        });
    }
};
