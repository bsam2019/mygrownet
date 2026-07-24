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
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('has_premium_template_access')->default(false)->after('email_verified_at');
            $table->timestamp('premium_access_granted_at')->nullable()->after('has_premium_template_access');
            $table->unsignedBigInteger('premium_access_granted_by')->nullable()->after('premium_access_granted_at');
            $table->text('premium_access_notes')->nullable()->after('premium_access_granted_by');
            
            $table->foreign('premium_access_granted_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['premium_access_granted_by']);
            $table->dropColumn([
                'has_premium_template_access',
                'premium_access_granted_at',
                'premium_access_granted_by',
                'premium_access_notes',
            ]);
        });
    }
};
