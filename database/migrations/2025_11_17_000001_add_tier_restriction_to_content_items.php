<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('starter_kit_content_items', function (Blueprint $table) {
            $table->enum('tier_restriction', ['all', 'premium'])->default('all')->after('category');
            $table->integer('download_count')->default(0)->after('estimated_value');
            $table->boolean('is_downloadable')->default(true)->after('file_type');
            $table->string('file_url')->nullable()->after('file_path');
            $table->integer('access_duration_days')->nullable()->after('is_downloadable');
            $table->timestamp('last_updated_at')->nullable()->after('updated_at');
        });
    }

    public function down(): void
    {
        Schema::table('starter_kit_content_items', function (Blueprint $table) {
            $table->dropColumn([
                'tier_restriction',
                'download_count',
                'is_downloadable',
                'file_url',
                'access_duration_days',
                'last_updated_at',
            ]);
        });
    }
};
