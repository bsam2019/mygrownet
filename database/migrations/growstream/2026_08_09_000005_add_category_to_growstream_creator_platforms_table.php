<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('growstream_creator_platforms')) {
            if (!Schema::hasColumn('growstream_creator_platforms', 'category')) {
                Schema::table('growstream_creator_platforms', function (Blueprint $table) {
                    $table->string('category', 30)->default('education')->after('brand_name');
                });
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('growstream_creator_platforms')) {
            if (Schema::hasColumn('growstream_creator_platforms', 'category')) {
                Schema::table('growstream_creator_platforms', function (Blueprint $table) {
                    $table->dropColumn('category');
                });
            }
        }
    }
};
