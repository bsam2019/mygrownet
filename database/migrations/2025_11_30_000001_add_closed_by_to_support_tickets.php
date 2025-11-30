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
        Schema::table('support_tickets', function (Blueprint $table) {
            // Add closed_by to track who closed the ticket
            $table->foreignId('closed_by')->nullable()->after('closed_at')->constrained('users')->nullOnDelete();
            
            // Add closure reason for context
            $table->string('closure_reason')->nullable()->after('closed_by');
        });

        // Also add to employee support tickets if it exists
        if (Schema::hasTable('employee_support_tickets')) {
            Schema::table('employee_support_tickets', function (Blueprint $table) {
                if (!Schema::hasColumn('employee_support_tickets', 'closed_by')) {
                    $table->foreignId('closed_by')->nullable()->after('closed_at')->constrained('users')->nullOnDelete();
                }
                if (!Schema::hasColumn('employee_support_tickets', 'closure_reason')) {
                    $table->string('closure_reason')->nullable()->after('closed_by');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('support_tickets', function (Blueprint $table) {
            $table->dropForeign(['closed_by']);
            $table->dropColumn(['closed_by', 'closure_reason']);
        });

        if (Schema::hasTable('employee_support_tickets')) {
            Schema::table('employee_support_tickets', function (Blueprint $table) {
                if (Schema::hasColumn('employee_support_tickets', 'closed_by')) {
                    $table->dropForeign(['closed_by']);
                    $table->dropColumn('closed_by');
                }
                if (Schema::hasColumn('employee_support_tickets', 'closure_reason')) {
                    $table->dropColumn('closure_reason');
                }
            });
        }
    }
};
