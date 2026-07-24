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
        Schema::table('withdrawal_requests', function (Blueprint $table) {
            // Add VBIF-specific fields for withdrawal management
            if (!Schema::hasColumn('withdrawal_requests', 'investment_id')) {
                $table->foreignId('investment_id')->nullable()->after('user_id')->constrained()->nullOnDelete();
            }
            if (!Schema::hasColumn('withdrawal_requests', 'type')) {
                $table->enum('type', ['full', 'partial', 'emergency'])->default('partial')->after('amount');
            }
            if (!Schema::hasColumn('withdrawal_requests', 'penalty_amount')) {
                $table->decimal('penalty_amount', 15, 2)->default(0)->after('fee');
            }
            if (!Schema::hasColumn('withdrawal_requests', 'requested_at')) {
                $table->timestamp('requested_at')->useCurrent()->after('reference_number');
            }
            if (!Schema::hasColumn('withdrawal_requests', 'approved_at')) {
                $table->timestamp('approved_at')->nullable()->after('requested_at');
            }
            if (!Schema::hasColumn('withdrawal_requests', 'admin_notes')) {
                $table->text('admin_notes')->nullable()->after('rejection_reason');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('withdrawal_requests', function (Blueprint $table) {
            $columns = [
                'investment_id',
                'type',
                'penalty_amount',
                'requested_at',
                'approved_at',
                'admin_notes'
            ];
            
            foreach ($columns as $column) {
                if (Schema::hasColumn('withdrawal_requests', $column)) {
                    if ($column === 'investment_id') {
                        $table->dropForeign(['investment_id']);
                    }
                    $table->dropColumn($column);
                }
            }
        });
    }
};
