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
        Schema::create('cms_users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('cms_companies')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('role_id')->constrained('cms_roles');
            $table->string('employee_number', 50)->nullable();
            $table->enum('status', ['active', 'inactive', 'suspended'])->default('active');
            $table->timestamp('last_login_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'company_id'], 'unique_user_company');
            $table->index(['company_id', 'status'], 'idx_company_status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cms_users');
    }
};
