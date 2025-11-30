<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employee_support_ticket_comments', function (Blueprint $table) {
            // Add author_id if it doesn't exist (rename employee_id or add new)
            if (!Schema::hasColumn('employee_support_ticket_comments', 'author_id')) {
                // If employee_id exists, rename it
                if (Schema::hasColumn('employee_support_ticket_comments', 'employee_id')) {
                    $table->renameColumn('employee_id', 'author_id');
                } else {
                    $table->foreignId('author_id')->nullable()->after('ticket_id')->constrained('employees')->onDelete('set null');
                }
            }

            // Add author_type column
            if (!Schema::hasColumn('employee_support_ticket_comments', 'author_type')) {
                $table->enum('author_type', ['employee', 'support'])->default('employee')->after('author_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('employee_support_ticket_comments', function (Blueprint $table) {
            if (Schema::hasColumn('employee_support_ticket_comments', 'author_type')) {
                $table->dropColumn('author_type');
            }
            
            // Rename back if needed
            if (Schema::hasColumn('employee_support_ticket_comments', 'author_id')) {
                $table->renameColumn('author_id', 'employee_id');
            }
        });
    }
};
