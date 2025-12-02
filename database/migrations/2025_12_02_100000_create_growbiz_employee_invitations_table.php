<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('growbiz_employee_invitations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('growbiz_employees')->onDelete('cascade');
            $table->foreignId('manager_id')->constrained('users')->onDelete('cascade');
            $table->string('email')->nullable();
            $table->string('token', 64)->unique();
            $table->string('code', 8)->unique();
            $table->enum('type', ['email', 'code'])->default('email');
            $table->enum('status', ['pending', 'accepted', 'expired', 'revoked'])->default('pending');
            $table->timestamp('expires_at');
            $table->timestamp('accepted_at')->nullable();
            $table->foreignId('accepted_by_user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();

            $table->index(['employee_id', 'status']);
            $table->index(['manager_id', 'status']);
            $table->index('token');
            $table->index('code');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('growbiz_employee_invitations');
    }
};
