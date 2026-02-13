<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add security fields to users table (main auth table)
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'password_changed_at')) {
                $table->timestamp('password_changed_at')->nullable()->after('password');
            }
            if (!Schema::hasColumn('users', 'force_password_change')) {
                $table->boolean('force_password_change')->default(false)->after('password_changed_at');
            }
            if (!Schema::hasColumn('users', 'two_factor_enabled')) {
                $table->boolean('two_factor_enabled')->default(false)->after('force_password_change');
            }
            if (!Schema::hasColumn('users', 'two_factor_method')) {
                $table->string('two_factor_method')->nullable()->after('two_factor_enabled');
            }
            if (!Schema::hasColumn('users', 'two_factor_code')) {
                $table->string('two_factor_code')->nullable()->after('two_factor_method');
            }
            if (!Schema::hasColumn('users', 'two_factor_expires_at')) {
                $table->timestamp('two_factor_expires_at')->nullable()->after('two_factor_code');
            }
            if (!Schema::hasColumn('users', 'failed_login_attempts')) {
                $table->integer('failed_login_attempts')->default(0)->after('two_factor_expires_at');
            }
            if (!Schema::hasColumn('users', 'locked_until')) {
                $table->timestamp('locked_until')->nullable()->after('failed_login_attempts');
            }
            if (!Schema::hasColumn('users', 'last_login_ip')) {
                $table->string('last_login_ip')->nullable()->after('locked_until');
            }
        });

        // Password history table (references users table)
        Schema::create('cms_password_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('password');
            $table->timestamp('created_at');

            $table->index(['user_id', 'created_at']);
        });

        // Login attempts tracking (references users table)
        Schema::create('cms_login_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->string('email');
            $table->string('ip_address');
            $table->string('user_agent')->nullable();
            $table->boolean('successful')->default(false);
            $table->string('failure_reason')->nullable();
            $table->timestamp('attempted_at');

            $table->index(['email', 'attempted_at']);
            $table->index(['ip_address', 'attempted_at']);
            $table->index(['user_id', 'attempted_at']);
        });

        // Security audit log
        Schema::create('cms_security_audit_log', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('company_id')->constrained('cms_companies')->onDelete('cascade');
            $table->string('event_type');
            $table->string('ip_address');
            $table->string('user_agent')->nullable();
            $table->text('description');
            $table->json('metadata')->nullable();
            $table->enum('severity', ['info', 'warning', 'critical'])->default('info');
            $table->timestamp('created_at');

            $table->index(['company_id', 'created_at']);
            $table->index(['user_id', 'created_at']);
            $table->index(['event_type', 'created_at']);
            $table->index(['severity', 'created_at']);
        });

        // Suspicious activity alerts
        Schema::create('cms_suspicious_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->foreignId('company_id')->constrained('cms_companies')->onDelete('cascade');
            $table->string('activity_type');
            $table->string('ip_address');
            $table->text('description');
            $table->json('details')->nullable();
            $table->enum('status', ['pending', 'reviewed', 'resolved', 'false_positive'])->default('pending');
            $table->timestamp('detected_at');
            $table->timestamp('reviewed_at')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->onDelete('set null');

            $table->index(['company_id', 'status', 'detected_at']);
            $table->index(['user_id', 'detected_at']);
        });

        // Add security settings to companies
        Schema::table('cms_companies', function (Blueprint $table) {
            if (!Schema::hasColumn('cms_companies', 'security_settings')) {
                $table->json('security_settings')->nullable()->after('settings');
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cms_suspicious_activities');
        Schema::dropIfExists('cms_security_audit_log');
        Schema::dropIfExists('cms_login_attempts');
        Schema::dropIfExists('cms_password_history');

        Schema::table('cms_users', function (Blueprint $table) {
            $table->dropColumn([
                'password_changed_at',
                'force_password_change',
                'two_factor_enabled',
                'two_factor_method',
                'two_factor_code',
                'two_factor_expires_at',
                'failed_login_attempts',
                'locked_until',
                'last_login_at',
                'last_login_ip',
            ]);
        });

        Schema::table('cms_companies', function (Blueprint $table) {
            $table->dropColumn('security_settings');
        });
    }
};
