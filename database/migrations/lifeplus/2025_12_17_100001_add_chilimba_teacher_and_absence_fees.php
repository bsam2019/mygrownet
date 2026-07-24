<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('lifeplus_chilimba_groups', function (Blueprint $table) {
            // Rename teacher_penalty to teacher_contribution (it's not a penalty)
            $table->renameColumn('teacher_penalty', 'teacher_contribution');
        });

        Schema::table('lifeplus_chilimba_groups', function (Blueprint $table) {
            // Add absence penalty (separate from teacher contribution)
            $table->decimal('absence_penalty', 10, 2)->default(0)->after('teacher_contribution');
        });

        Schema::table('lifeplus_chilimba_contributions', function (Blueprint $table) {
            // Add teacher contribution tracking
            $table->decimal('teacher_amount', 10, 2)->default(0)->after('penalty_amount');
        });
    }

    public function down(): void
    {
        Schema::table('lifeplus_chilimba_contributions', function (Blueprint $table) {
            $table->dropColumn('teacher_amount');
        });

        Schema::table('lifeplus_chilimba_groups', function (Blueprint $table) {
            $table->dropColumn('absence_penalty');
        });

        Schema::table('lifeplus_chilimba_groups', function (Blueprint $table) {
            $table->renameColumn('teacher_contribution', 'teacher_penalty');
        });
    }
};
