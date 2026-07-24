<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bizboost_business_profiles', function (Blueprint $table) {
            $table->string('about_image_path')->nullable()->after('hero_image_path');
        });
    }

    public function down(): void
    {
        Schema::table('bizboost_business_profiles', function (Blueprint $table) {
            $table->dropColumn('about_image_path');
        });
    }
};
