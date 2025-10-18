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
        // Check if employees table exists first
        if (!Schema::hasTable('employees')) {
            return; // Skip if table doesn't exist yet
        }
        
        Schema::table('employees', function (Blueprint $table) {
            // Check if user_id column exists before adding it
            if (!Schema::hasColumn('employees', 'user_id')) {
                $table->foreignId('user_id')->nullable()->after('id')->constrained()->onDelete('set null');
                $table->unique('user_id');
            }
            
            // Check if application_id column exists before adding it
            if (!Schema::hasColumn('employees', 'application_id')) {
                $table->foreignId('application_id')->nullable()->after('user_id')->constrained('job_applications')->onDelete('set null');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropForeign(['application_id']);
            $table->dropUnique(['user_id']);
            $table->dropColumn(['user_id', 'application_id']);
        });
    }
};
