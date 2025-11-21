<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('telegram_chat_id')->nullable()->after('phone');
            $table->boolean('telegram_notifications')->default(false)->after('telegram_chat_id');
            $table->timestamp('telegram_linked_at')->nullable()->after('telegram_notifications');
            $table->string('telegram_link_code', 8)->nullable()->after('telegram_linked_at');
            
            $table->index('telegram_chat_id');
            $table->index('telegram_link_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['telegram_chat_id']);
            $table->dropIndex(['telegram_link_code']);
            $table->dropColumn([
                'telegram_chat_id',
                'telegram_notifications',
                'telegram_linked_at',
                'telegram_link_code'
            ]);
        });
    }
};
