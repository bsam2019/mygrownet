<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('sa_companies', 'tagline')) {
            Schema::table('sa_companies', function (Blueprint $table) {
                $table->string('tagline')->nullable()->after('name');
                $table->string('brand_color', 7)->default('#059669')->after('logo_path');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('sa_companies', 'tagline')) {
            Schema::table('sa_companies', function (Blueprint $table) {
                $table->dropColumn(['tagline', 'brand_color']);
            });
        }
    }
};
