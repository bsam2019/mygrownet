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
        Schema::create('cms_measurement_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('cms_companies')->onDelete('cascade');
            $table->foreignId('measurement_id')->constrained('cms_measurement_records')->onDelete('cascade');
            $table->string('location_name');
            $table->enum('type', ['sliding_window', 'casement_window', 'sliding_door', 'hinged_door', 'other']);
            
            // Measurements (in mm)
            $table->decimal('width_top', 10, 2);
            $table->decimal('width_middle', 10, 2);
            $table->decimal('width_bottom', 10, 2);
            $table->decimal('height_left', 10, 2);
            $table->decimal('height_right', 10, 2);
            
            // Calculated fields (will be computed in model)
            $table->decimal('final_width', 10, 2)->nullable();
            $table->decimal('final_height', 10, 2)->nullable();
            $table->decimal('area', 10, 4)->nullable(); // in m²
            
            $table->integer('quantity')->default(1);
            $table->decimal('total_area', 10, 4)->nullable(); // in m²
            
            $table->text('notes')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index(['company_id', 'measurement_id']);
            $table->index('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cms_measurement_items');
    }
};
