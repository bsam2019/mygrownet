<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            if (!Schema::hasColumn('transactions', 'processed_at')) {
                $table->timestamp('processed_at')->nullable();
            }
            if (!Schema::hasColumn('transactions', 'processed_by')) {
                $table->foreignId('processed_by')->nullable()->constrained('users')->onDelete('set null');
            }
        });
    }

    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropForeign(['processed_by']);
            $table->dropColumn(['processed_at', 'processed_by']);
        });
    }
}; 