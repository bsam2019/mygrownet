<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('recognition_awards')) {
            Schema::create('recognition_awards', function (Blueprint $table) {
                $table->id();
                // Define FK columns first; add constraints conditionally later
                $table->unsignedBigInteger('recognition_event_id');
                $table->unsignedBigInteger('user_id');
                $table->string('award_type');
                $table->string('award_title');
                $table->text('award_description');
                $table->decimal('award_value', 10, 2)->default(0);
                $table->dateTime('presented_at');
                $table->timestamps();

                // Short explicit index/unique names to avoid identifier length issues
                $table->unique(['recognition_event_id', 'user_id', 'award_type'], 'ra_event_user_type_uniq');
                $table->index(['award_type'], 'ra_award_type_idx');
                $table->index(['presented_at'], 'ra_presented_at_idx');
            });
        }

        // Add foreign keys conditionally to avoid failures when referenced tables are missing
        try {
            if (Schema::hasTable('recognition_awards') && Schema::hasTable('recognition_events')) {
                Schema::table('recognition_awards', function (Blueprint $table) {
                    $table->foreign('recognition_event_id')->references('id')->on('recognition_events')->onDelete('cascade');
                });
            }
        } catch (Throwable $e) {}
        try {
            if (Schema::hasTable('recognition_awards') && Schema::hasTable('users')) {
                Schema::table('recognition_awards', function (Blueprint $table) {
                    $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
                });
            }
        } catch (Throwable $e) {}
    }

    public function down(): void
    {
        if (Schema::hasTable('recognition_awards')) {
            // Drop indexes/unique by explicit names safely
            try { Schema::table('recognition_awards', function (Blueprint $table) { $table->dropUnique('ra_event_user_type_uniq'); }); } catch (Throwable $e) {}
            try { Schema::table('recognition_awards', function (Blueprint $table) { $table->dropIndex('ra_award_type_idx'); }); } catch (Throwable $e) {}
            try { Schema::table('recognition_awards', function (Blueprint $table) { $table->dropIndex('ra_presented_at_idx'); }); } catch (Throwable $e) {}
        }
        Schema::dropIfExists('recognition_awards');
    }
};