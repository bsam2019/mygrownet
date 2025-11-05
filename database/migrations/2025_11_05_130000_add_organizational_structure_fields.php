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
        // Add organizational level to positions table
        Schema::table('positions', function (Blueprint $table) {
            $table->enum('organizational_level', [
                'c_level',           // CEO, COO, CFO, CTO, CGO
                'director',          // Department Directors
                'manager',           // Department Managers
                'team_lead',         // Team Leads
                'individual'         // Individual Contributors
            ])->default('individual')->after('level');
            
            $table->unsignedBigInteger('reports_to_position_id')->nullable()->after('organizational_level');
            $table->foreign('reports_to_position_id')->references('id')->on('positions')->onDelete('set null');
            
            $table->index('organizational_level');
            $table->index('reports_to_position_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('positions', function (Blueprint $table) {
            $table->dropForeign(['reports_to_position_id']);
            $table->dropIndex(['organizational_level']);
            $table->dropIndex(['reports_to_position_id']);
            $table->dropColumn(['organizational_level', 'reports_to_position_id']);
        });
    }
};
