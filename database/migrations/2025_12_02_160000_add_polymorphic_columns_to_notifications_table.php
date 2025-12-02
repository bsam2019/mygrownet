<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * This migration adds the polymorphic columns required by Laravel's
     * Notifiable trait and migrates existing user_id data.
     */
    public function up(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            // Add polymorphic columns after id
            $table->string('notifiable_type')->after('id')->default('App\\Models\\User');
            $table->unsignedBigInteger('notifiable_id')->after('notifiable_type')->nullable();
            
            // Add index for polymorphic relationship
            $table->index(['notifiable_type', 'notifiable_id']);
        });

        // Migrate existing user_id data to notifiable_id
        DB::table('notifications')
            ->whereNotNull('user_id')
            ->update([
                'notifiable_id' => DB::raw('user_id'),
                'notifiable_type' => 'App\\Models\\User',
            ]);

        // Now make notifiable_id not nullable and drop user_id
        Schema::table('notifications', function (Blueprint $table) {
            // Drop the old user_id foreign key and column
            $table->dropForeign(['user_id']);
            $table->dropIndex(['user_id', 'read_at']);
            $table->dropIndex(['user_id', 'category']);
            $table->dropColumn('user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            // Re-add user_id column
            $table->unsignedBigInteger('user_id')->after('id')->nullable();
        });

        // Migrate data back
        DB::table('notifications')
            ->where('notifiable_type', 'App\\Models\\User')
            ->update([
                'user_id' => DB::raw('notifiable_id'),
            ]);

        Schema::table('notifications', function (Blueprint $table) {
            // Add back foreign key
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            
            // Add back indexes
            $table->index(['user_id', 'read_at']);
            $table->index(['user_id', 'category']);
            
            // Drop polymorphic columns
            $table->dropIndex(['notifiable_type', 'notifiable_id']);
            $table->dropColumn(['notifiable_type', 'notifiable_id']);
        });
    }
};
