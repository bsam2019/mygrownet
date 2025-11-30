<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employee_support_tickets', function (Blueprint $table) {
            if (!Schema::hasColumn('employee_support_tickets', 'satisfaction_rating')) {
                $table->tinyInteger('satisfaction_rating')->nullable()->after('closure_reason');
            }
            if (!Schema::hasColumn('employee_support_tickets', 'rating_feedback')) {
                $table->text('rating_feedback')->nullable()->after('satisfaction_rating');
            }
            if (!Schema::hasColumn('employee_support_tickets', 'rated_at')) {
                $table->timestamp('rated_at')->nullable()->after('rating_feedback');
            }
        });
    }

    public function down(): void
    {
        Schema::table('employee_support_tickets', function (Blueprint $table) {
            $table->dropColumn(['satisfaction_rating', 'rating_feedback', 'rated_at']);
        });
    }
};
