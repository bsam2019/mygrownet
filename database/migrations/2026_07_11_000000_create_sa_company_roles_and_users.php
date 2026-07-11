<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Company Roles - tenant-defined roles with permissions
        Schema::create('sa_company_roles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sa_company_id')->constrained('sa_companies')->cascadeOnDelete();
            $table->string('name'); // e.g., "Store Manager", "Floor Supervisor", "Cashier"
            $table->string('slug'); // e.g., "store_manager", "floor_supervisor", "cashier"
            $table->text('description')->nullable();
            $table->json('permissions')->default('[]'); // array of permission strings
            $table->boolean('is_system')->default(false); // protected roles (owner, admin)
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['sa_company_id', 'slug']);
            $table->index('sa_company_id');
        });

        // Company Users - employees assigned to company with a role
        Schema::create('sa_company_users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sa_company_id')->constrained('sa_companies')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('sa_company_role_id')->nullable()->constrained('sa_company_roles')->nullOnDelete();
            $table->string('status')->default('active'); // pending, active, suspended, removed
            $table->timestamp('invited_at')->nullable();
            $table->timestamp('joined_at')->nullable();
            $table->timestamp('removed_at')->nullable();
            $table->text('removal_reason')->nullable();
            $table->timestamps();

            $table->unique(['sa_company_id', 'user_id']);
            $table->index(['sa_company_id', 'status']);
            $table->index('user_id');
        });

        // Seed default system roles for new companies
        // This will be done via CompanyRoleService::seedDefaultRoles()
    }

    public function down(): void
    {
        Schema::dropIfExists('sa_company_users');
        Schema::dropIfExists('sa_company_roles');
    }
};