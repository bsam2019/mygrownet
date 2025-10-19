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
        Schema::table('achievements', function (Blueprint $table) {
            // Add monetary_reward if it doesn't exist
            if (!Schema::hasColumn('achievements', 'monetary_reward')) {
                $table->decimal('monetary_reward', 10, 2)->default(0)->after('points');
            }
            
            // Add tier_requirement if it doesn't exist
            if (!Schema::hasColumn('achievements', 'tier_requirement')) {
                $table->string('tier_requirement')->nullable()->after('monetary_reward');
            }
            
            // Add triggers_celebration if it doesn't exist
            if (!Schema::hasColumn('achievements', 'triggers_celebration')) {
                $table->boolean('triggers_celebration')->default(false)->after('tier_requirement');
            }
            
            // Add celebration_message if it doesn't exist
            if (!Schema::hasColumn('achievements', 'celebration_message')) {
                $table->string('celebration_message')->nullable()->after('triggers_celebration');
            }
            
            // Add difficulty_level if it doesn't exist
            if (!Schema::hasColumn('achievements', 'difficulty_level')) {
                $table->enum('difficulty_level', ['easy', 'medium', 'hard', 'legendary'])->default('medium')->after('celebration_message');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('achievements', function (Blueprint $table) {
            $columns = ['monetary_reward', 'tier_requirement', 'triggers_celebration', 'celebration_message', 'difficulty_level'];
            
            foreach ($columns as $column) {
                if (Schema::hasColumn('achievements', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
