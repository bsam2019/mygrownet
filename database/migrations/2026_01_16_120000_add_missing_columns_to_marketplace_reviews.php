<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('marketplace_reviews', function (Blueprint $table) {
            if (!Schema::hasColumn('marketplace_reviews', 'images')) {
                $table->json('images')->nullable()->after('comment');
            }
            if (!Schema::hasColumn('marketplace_reviews', 'is_verified_purchase')) {
                $table->boolean('is_verified_purchase')->default(true)->after('comment');
            }
            if (!Schema::hasColumn('marketplace_reviews', 'is_approved')) {
                $table->boolean('is_approved')->default(true)->after('is_verified_purchase');
            }
            if (!Schema::hasColumn('marketplace_reviews', 'helpful_count')) {
                $table->unsignedInteger('helpful_count')->default(0)->after('is_approved');
            }
            if (!Schema::hasColumn('marketplace_reviews', 'not_helpful_count')) {
                $table->unsignedInteger('not_helpful_count')->default(0)->after('helpful_count');
            }
            if (!Schema::hasColumn('marketplace_reviews', 'seller_responded_at')) {
                $table->timestamp('seller_responded_at')->nullable()->after('seller_response');
            }
        });
    }

    public function down(): void
    {
        Schema::table('marketplace_reviews', function (Blueprint $table) {
            $columns = ['images', 'is_verified_purchase', 'is_approved', 'helpful_count', 'not_helpful_count', 'seller_responded_at'];
            foreach ($columns as $column) {
                if (Schema::hasColumn('marketplace_reviews', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
