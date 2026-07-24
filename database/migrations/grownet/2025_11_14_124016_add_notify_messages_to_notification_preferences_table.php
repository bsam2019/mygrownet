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
        Schema::table('notification_preferences', function (Blueprint $table) {
            $table->boolean('notify_messages')->default(true)->after('notify_marketing');
        });
        
        // Update existing records to enable messages by default
        DB::table('notification_preferences')->update(['notify_messages' => true]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('notification_preferences', function (Blueprint $table) {
            $table->dropColumn('notify_messages');
        });
    }
};
