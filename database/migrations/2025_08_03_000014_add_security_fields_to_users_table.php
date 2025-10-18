<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_blocked')->default(false);
            $table->string('block_reason')->nullable();
            $table->timestamp('blocked_at')->nullable();
            $table->foreignId('blocked_by')->nullable()->constrained('users');
            $table->boolean('requires_id_verification')->default(true);
            $table->boolean('is_id_verified')->default(false);
            $table->timestamp('id_verified_at')->nullable();
            $table->integer('failed_login_attempts')->default(0);
            $table->timestamp('last_failed_login_at')->nullable();
            $table->json('security_flags')->nullable(); // Array of security concerns
            $table->decimal('risk_score', 5, 2)->default(0.00); // 0-100 risk assessment
            $table->timestamp('risk_assessed_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'is_blocked',
                'block_reason',
                'blocked_at',
                'blocked_by',
                'requires_id_verification',
                'is_id_verified',
                'id_verified_at',
                'failed_login_attempts',
                'last_failed_login_at',
                'security_flags',
                'risk_score',
                'risk_assessed_at'
            ]);
        });
    }
};