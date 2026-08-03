<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('growstream_videos', function (Blueprint $table) {
            $table->enum('moderation_status', ['pending_review', 'approved', 'rejected'])
                ->default('approved')
                ->after('upload_status');
            $table->text('moderation_reason')->nullable()->after('moderation_status');
            $table->timestamp('reviewed_at')->nullable()->after('moderation_reason');
            $table->foreignId('reviewed_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete()
                ->after('reviewed_at');

            $table->index('moderation_status');
        });
    }

    public function down(): void
    {
        Schema::table('growstream_videos', function (Blueprint $table) {
            $table->dropConstrainedForeignId('reviewed_by');
            $table->dropColumn([
                'moderation_status',
                'moderation_reason',
                'reviewed_at',
            ]);
        });
    }
};
