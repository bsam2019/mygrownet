<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('cms_companies')) {
            if (!Schema::hasColumn('cms_companies', 'password_expiry_days')) {
                Schema::table('cms_companies', function (Blueprint $table) {
                    $table->integer('password_expiry_days')->default(90)->after('settings');
                });
            }
            if (!Schema::hasColumn('cms_companies', 'max_login_attempts')) {
                Schema::table('cms_companies', function (Blueprint $table) {
                    $table->integer('max_login_attempts')->default(5)->after('password_expiry_days');
                });
            }
            if (!Schema::hasColumn('cms_companies', 'lockout_duration_minutes')) {
                Schema::table('cms_companies', function (Blueprint $table) {
                    $table->integer('lockout_duration_minutes')->default(30)->after('max_login_attempts');
                });
            }
            if (!Schema::hasColumn('cms_companies', 'session_timeout_minutes')) {
                Schema::table('cms_companies', function (Blueprint $table) {
                    $table->integer('session_timeout_minutes')->default(120)->after('lockout_duration_minutes');
                });
            }
            if (!Schema::hasColumn('cms_companies', 'require_2fa')) {
                Schema::table('cms_companies', function (Blueprint $table) {
                    $table->boolean('require_2fa')->default(false)->after('session_timeout_minutes');
                });
            }
            if (!Schema::hasColumn('cms_companies', 'password_min_length')) {
                Schema::table('cms_companies', function (Blueprint $table) {
                    $table->integer('password_min_length')->default(8)->after('require_2fa');
                });
            }
            if (!Schema::hasColumn('cms_companies', 'enable_security_alerts')) {
                Schema::table('cms_companies', function (Blueprint $table) {
                    $table->boolean('enable_security_alerts')->default(true)->after('password_min_length');
                });
            }
        }

        // Add two_factor_secret to users table
        if (Schema::hasTable('users')) {
            if (!Schema::hasColumn('users', 'two_factor_secret')) {
                Schema::table('users', function (Blueprint $table) {
                    $table->string('two_factor_secret')->nullable()->after('two_factor_expires_at');
                });
            }
        }

        // Add review_notes to suspicious activities
        if (Schema::hasTable('cms_suspicious_activities')) {
            if (!Schema::hasColumn('cms_suspicious_activities', 'review_notes')) {
                Schema::table('cms_suspicious_activities', function (Blueprint $table) {
                    $table->text('review_notes')->nullable()->after('reviewed_by');
                });
            }
            if (!Schema::hasColumn('cms_suspicious_activities', 'severity')) {
                Schema::table('cms_suspicious_activities', function (Blueprint $table) {
                    $table->enum('severity', ['low', 'medium', 'high'])->default('medium')->after('details');
                });
            }
        }
    }

    public function down(): void
    {
        Schema::table('cms_companies', function (Blueprint $table) {
            $table->dropColumn([
                'password_expiry_days',
                'max_login_attempts',
                'lockout_duration_minutes',
                'session_timeout_minutes',
                'require_2fa',
                'password_min_length',
                'enable_security_alerts',
            ]);
        });

        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'two_factor_secret')) {
                $table->dropColumn('two_factor_secret');
            }
        });

        Schema::table('cms_suspicious_activities', function (Blueprint $table) {
            if (Schema::hasColumn('cms_suspicious_activities', 'review_notes')) {
                $table->dropColumn('review_notes');
            }
            if (Schema::hasColumn('cms_suspicious_activities', 'severity')) {
                $table->dropColumn('severity');
            }
        });
    }
};
