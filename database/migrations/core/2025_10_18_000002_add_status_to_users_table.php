<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Add status column if it doesn't exist
            if (!Schema::hasColumn('users', 'status')) {
                $table->enum('status', ['active', 'inactive', 'suspended', 'pending'])
                    ->default('active')
                    ->after('email_verified_at');
            }
            
            // Add last_login_at if it doesn't exist
            if (!Schema::hasColumn('users', 'last_login_at')) {
                $table->timestamp('last_login_at')->nullable()->after('status');
            }
        });
        
        // Set all existing users to active status
        DB::table('users')->whereNull('status')->update(['status' => 'active']);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'status')) {
                $table->dropColumn('status');
            }
            if (Schema::hasColumn('users', 'last_login_at')) {
                $table->dropColumn('last_login_at');
            }
        });
    }
};
