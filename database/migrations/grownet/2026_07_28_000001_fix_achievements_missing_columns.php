<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('achievements', 'counts_for_leaderboard')) {
            Schema::table('achievements', function (Blueprint $table) {
                $table->boolean('counts_for_leaderboard')->default(true);
            });
        }

        if (!Schema::hasColumn('achievements', 'leaderboard_weight')) {
            Schema::table('achievements', function (Blueprint $table) {
                $table->integer('leaderboard_weight')->default(1);
            });
        }

        if (!Schema::hasColumn('achievements', 'available_from')) {
            Schema::table('achievements', function (Blueprint $table) {
                $table->date('available_from')->nullable();
            });
        }

        if (!Schema::hasColumn('achievements', 'available_until')) {
            Schema::table('achievements', function (Blueprint $table) {
                $table->date('available_until')->nullable();
            });
        }

        if (!Schema::hasColumn('achievements', 'is_seasonal')) {
            Schema::table('achievements', function (Blueprint $table) {
                $table->boolean('is_seasonal')->default(false);
            });
        }

        if (!Schema::hasColumn('achievements', 'is_shareable')) {
            Schema::table('achievements', function (Blueprint $table) {
                $table->boolean('is_shareable')->default(true);
            });
        }

        if (!Schema::hasColumn('achievements', 'share_message')) {
            Schema::table('achievements', function (Blueprint $table) {
                $table->string('share_message')->nullable();
            });
        }
    }

    public function down(): void
    {
        $columns = [
            'counts_for_leaderboard',
            'leaderboard_weight',
            'available_from',
            'available_until',
            'is_seasonal',
            'is_shareable',
            'share_message',
        ];

        foreach ($columns as $column) {
            if (Schema::hasColumn('achievements', $column)) {
                Schema::table('achievements', function (Blueprint $table) use ($column) {
                    $table->dropColumn($column);
                });
            }
        }
    }
};
