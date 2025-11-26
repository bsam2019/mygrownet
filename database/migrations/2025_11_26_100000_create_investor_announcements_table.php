<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('investor_announcements')) {
            Schema::create('investor_announcements', function (Blueprint $table) {
                $table->id();
                $table->string('title');
                $table->text('content');
                $table->text('summary')->nullable();
                $table->enum('type', ['general', 'financial', 'dividend', 'meeting', 'urgent', 'milestone'])->default('general');
                $table->enum('priority', ['low', 'normal', 'high', 'urgent'])->default('normal');
                $table->boolean('is_pinned')->default(false);
                $table->boolean('send_email')->default(false);
                $table->timestamp('published_at')->nullable();
                $table->timestamp('expires_at')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->timestamps();
                
                $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
                $table->index(['published_at', 'expires_at']);
                $table->index('type');
                $table->index('priority');
            });
        }

        // Track which investors have read which announcements
        if (!Schema::hasTable('investor_announcement_reads')) {
            Schema::create('investor_announcement_reads', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('announcement_id');
                $table->unsignedBigInteger('investor_account_id');
                $table->timestamp('read_at');
                
                $table->foreign('announcement_id')->references('id')->on('investor_announcements')->onDelete('cascade');
                $table->foreign('investor_account_id')->references('id')->on('investor_accounts')->onDelete('cascade');
                $table->unique(['announcement_id', 'investor_account_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('investor_announcement_reads');
        Schema::dropIfExists('investor_announcements');
    }
};
