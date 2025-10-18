<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('matrix_positions', function (Blueprint $table) {
            // Add professional level name column
            if (!Schema::hasColumn('matrix_positions', 'professional_level_name')) {
                $table->string('professional_level_name', 50)->nullable()->after('level');
            }
            
            // Add index for better query performance
            $table->index(['level', 'is_active'], 'idx_level_active');
            $table->index('professional_level_name', 'idx_professional_level');
        });
        
        // Update existing records with professional level names
        $this->updateProfessionalLevelNames();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('matrix_positions', function (Blueprint $table) {
            $table->dropIndex('idx_level_active');
            $table->dropIndex('idx_professional_level');
            
            if (Schema::hasColumn('matrix_positions', 'professional_level_name')) {
                $table->dropColumn('professional_level_name');
            }
        });
    }
    
    /**
     * Update existing records with professional level names
     */
    protected function updateProfessionalLevelNames(): void
    {
        $levelNames = [
            1 => 'Associate',
            2 => 'Professional',
            3 => 'Senior',
            4 => 'Manager',
            5 => 'Director',
            6 => 'Executive',
            7 => 'Ambassador',
        ];
        
        foreach ($levelNames as $level => $name) {
            DB::table('matrix_positions')
                ->where('level', $level)
                ->update(['professional_level_name' => $name]);
        }
    }
};
