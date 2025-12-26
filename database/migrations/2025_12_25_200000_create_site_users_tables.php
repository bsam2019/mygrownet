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
        // Site Permissions (global, not per-site)
        Schema::create('site_permissions', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('slug', 100)->unique();
            $table->string('group_name', 50); // users, content, orders, settings, member
            $table->string('description', 255)->nullable();
            $table->timestamp('created_at')->nullable();
        });

        // Site Roles (per-site)
        Schema::create('site_roles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_id')->constrained('growbuilder_sites')->cascadeOnDelete();
            $table->string('name', 50);
            $table->string('slug', 50);
            $table->string('description', 255)->nullable();
            $table->boolean('is_system')->default(false);
            $table->unsignedInteger('level')->default(0);
            $table->timestamps();

            $table->unique(['site_id', 'slug']);
        });

        // Role-Permission Pivot
        Schema::create('site_role_permissions', function (Blueprint $table) {
            $table->foreignId('site_role_id')->constrained('site_roles')->cascadeOnDelete();
            $table->foreignId('site_permission_id')->constrained('site_permissions')->cascadeOnDelete();

            $table->primary(['site_role_id', 'site_permission_id']);
        });


        // Site Users (independent from MyGrowNet users)
        Schema::create('site_users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_id')->constrained('growbuilder_sites')->cascadeOnDelete();
            $table->foreignId('site_role_id')->nullable()->constrained('site_roles')->nullOnDelete();
            $table->string('name');
            $table->string('email');
            $table->string('password');
            $table->string('phone', 50)->nullable();
            $table->string('avatar')->nullable();
            $table->enum('status', ['active', 'inactive', 'pending', 'suspended'])->default('pending');
            $table->timestamp('email_verified_at')->nullable();
            $table->timestamp('phone_verified_at')->nullable();
            $table->timestamp('last_login_at')->nullable();
            $table->unsignedInteger('login_count')->default(0);
            $table->json('metadata')->nullable();
            $table->rememberToken();
            $table->timestamps();

            $table->unique(['site_id', 'email']);
            $table->index(['site_id', 'status']);
            $table->index('email');
        });

        // Site User Sessions (for multi-device tracking)
        Schema::create('site_user_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_user_id')->constrained('site_users')->cascadeOnDelete();
            $table->string('token')->unique();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('last_activity_at')->nullable();
            $table->timestamp('expires_at');
            $table->timestamp('created_at')->nullable();

            $table->index('token');
            $table->index('expires_at');
        });

        // Site User Password Resets
        Schema::create('site_user_password_resets', function (Blueprint $table) {
            $table->string('email');
            $table->foreignId('site_id')->constrained('growbuilder_sites')->cascadeOnDelete();
            $table->string('token');
            $table->timestamp('created_at')->nullable();

            $table->index(['site_id', 'email']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('site_user_password_resets');
        Schema::dropIfExists('site_user_sessions');
        Schema::dropIfExists('site_users');
        Schema::dropIfExists('site_role_permissions');
        Schema::dropIfExists('site_roles');
        Schema::dropIfExists('site_permissions');
    }
};
