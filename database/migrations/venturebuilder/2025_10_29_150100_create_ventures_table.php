<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ventures', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('venture_categories')->onDelete('restrict');
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description');
            $table->text('business_model')->nullable();
            $table->string('featured_image')->nullable();
            
            // Financial Details
            $table->decimal('funding_target', 15, 2);
            $table->decimal('minimum_investment', 15, 2)->default(1000.00);
            $table->decimal('maximum_investment', 15, 2)->nullable();
            $table->decimal('total_raised', 15, 2)->default(0);
            $table->integer('investor_count')->default(0);
            
            // Timeline
            $table->date('funding_start_date')->nullable();
            $table->date('funding_end_date')->nullable();
            $table->date('expected_launch_date')->nullable();
            $table->date('actual_launch_date')->nullable();
            
            // Status
            $table->enum('status', [
                'draft',
                'review',
                'approved',
                'funding',
                'funded',
                'active',
                'completed',
                'cancelled'
            ])->default('draft');
            
            // Company Details (after formation)
            $table->string('company_name')->nullable();
            $table->string('company_registration_number')->nullable();
            $table->date('company_formation_date')->nullable();
            $table->decimal('mygrownet_equity_percentage', 5, 2)->nullable();
            
            // Projections
            $table->text('revenue_projections')->nullable(); // JSON
            $table->text('risk_factors')->nullable();
            $table->integer('expected_roi_months')->nullable();
            
            // Metadata
            $table->boolean('is_featured')->default(false);
            $table->integer('views_count')->default(0);
            $table->foreignId('created_by')->constrained('users')->onDelete('restrict');
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('approved_at')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['status', 'is_featured']);
            $table->index('funding_end_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ventures');
    }
};
