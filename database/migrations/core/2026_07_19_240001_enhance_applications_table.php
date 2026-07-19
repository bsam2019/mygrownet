<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->string('category')->default('shared')->after('slug'); // business | consumer | shared
            $table->string('access_model')->default('both')->after('category'); // customer | organization_members | both
            $table->string('context_support')->default('both')->after('access_model'); // personal | organization | both
            $table->boolean('requires_organization_context')->default(false)->after('context_support');
            $table->boolean('subscription_required')->default(true)->after('requires_organization_context');
            $table->string('lifecycle')->default('active')->after('subscription_required'); // active | legacy | retired
            $table->string('operational_status')->default('online')->after('lifecycle'); // online | maintenance | disabled
            $table->foreignId('replacement_app_id')->nullable()->constrained('applications')->after('operational_status');
            $table->date('migration_deadline')->nullable()->after('replacement_app_id');
            $table->boolean('is_visible')->default(true)->after('migration_deadline');
        });
    }

    public function down(): void
    {
        Schema::table('applications', function (Blueprint $table) {
            $table->dropColumn([
                'category', 'access_model', 'context_support',
                'requires_organization_context', 'subscription_required',
                'lifecycle', 'operational_status', 'replacement_app_id',
                'migration_deadline', 'is_visible',
            ]);
        });
    }
};
