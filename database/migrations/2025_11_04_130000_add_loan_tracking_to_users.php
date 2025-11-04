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
            $table->decimal('loan_balance', 10, 2)->default(0)->after('bonus_balance');
            $table->decimal('total_loan_issued', 10, 2)->default(0)->after('loan_balance');
            $table->decimal('total_loan_repaid', 10, 2)->default(0)->after('total_loan_issued');
            $table->timestamp('loan_issued_at')->nullable()->after('total_loan_repaid');
            $table->foreignId('loan_issued_by')->nullable()->constrained('users')->after('loan_issued_at');
            $table->text('loan_notes')->nullable()->after('loan_issued_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['loan_issued_by']);
            $table->dropColumn([
                'loan_balance',
                'total_loan_issued',
                'total_loan_repaid',
                'loan_issued_at',
                'loan_issued_by',
                'loan_notes',
            ]);
        });
    }
};
