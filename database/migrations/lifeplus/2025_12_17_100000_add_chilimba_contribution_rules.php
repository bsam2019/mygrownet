<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add contribution rules to groups
        Schema::table('lifeplus_chilimba_groups', function (Blueprint $table) {
            // Rename contribution_amount to min_contribution
            $table->renameColumn('contribution_amount', 'min_contribution');
        });

        Schema::table('lifeplus_chilimba_groups', function (Blueprint $table) {
            // Add new fields for contribution rules
            $table->decimal('max_contribution', 10, 2)->nullable()->after('min_contribution');
            $table->decimal('initial_contribution', 10, 2)->nullable()->after('max_contribution');
            $table->decimal('teacher_penalty', 10, 2)->default(0)->after('initial_contribution');
            $table->text('penalty_rules')->nullable()->after('teacher_penalty');
        });

        // Add penalty tracking to contributions
        Schema::table('lifeplus_chilimba_contributions', function (Blueprint $table) {
            $table->boolean('is_initial')->default(false)->after('amount');
            $table->decimal('penalty_amount', 10, 2)->default(0)->after('is_initial');
            $table->string('penalty_reason')->nullable()->after('penalty_amount');
        });
    }

    public function down(): void
    {
        Schema::table('lifeplus_chilimba_contributions', function (Blueprint $table) {
            $table->dropColumn(['is_initial', 'penalty_amount', 'penalty_reason']);
        });

        Schema::table('lifeplus_chilimba_groups', function (Blueprint $table) {
            $table->dropColumn(['max_contribution', 'initial_contribution', 'teacher_penalty', 'penalty_rules']);
        });

        Schema::table('lifeplus_chilimba_groups', function (Blueprint $table) {
            $table->renameColumn('min_contribution', 'contribution_amount');
        });
    }
};
