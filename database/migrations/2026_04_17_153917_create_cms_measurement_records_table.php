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
        Schema::create('cms_measurement_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('cms_companies')->onDelete('cascade');
            $table->foreignId('customer_id')->constrained('cms_customers')->onDelete('cascade');
            $table->string('measurement_number', 50);
            $table->string('project_name');
            $table->string('location')->nullable();
            $table->foreignId('measured_by')->nullable()->constrained('cms_users')->onDelete('set null');
            $table->date('measurement_date');
            $table->enum('status', ['draft', 'completed', 'quoted'])->default('draft');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('cms_users')->onDelete('set null');
            $table->timestamps();
            
            // Indexes
            $table->unique(['company_id', 'measurement_number']);
            $table->index(['company_id', 'customer_id']);
            $table->index(['company_id', 'status']);
            $table->index('measurement_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cms_measurement_records');
    }
};
