<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Make author_id nullable and add user_id for admin support agents
     * who don't have employee records.
     */
    public function up(): void
    {
        Schema::table('employee_support_ticket_comments', function (Blueprint $table) {
            // Make author_id nullable for support agents without employee records
            $table->unsignedBigInteger('author_id')->nullable()->change();
            
            // Add user_id for admin/support users who don't have employee records
            if (!Schema::hasColumn('employee_support_ticket_comments', 'user_id')) {
                $table->foreignId('user_id')->nullable()->after('author_id')->constrained('users')->nullOnDelete();
            }
            
            // Add author_name for display purposes when no employee record exists
            if (!Schema::hasColumn('employee_support_ticket_comments', 'author_name')) {
                $table->string('author_name')->nullable()->after('author_type');
            }
        });
    }

    public function down(): void
    {
        Schema::table('employee_support_ticket_comments', function (Blueprint $table) {
            if (Schema::hasColumn('employee_support_ticket_comments', 'user_id')) {
                $table->dropForeign(['user_id']);
                $table->dropColumn('user_id');
            }
            
            if (Schema::hasColumn('employee_support_ticket_comments', 'author_name')) {
                $table->dropColumn('author_name');
            }
        });
    }
};
