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
        Schema::create('agency_clients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agency_id')->constrained('agencies')->onDelete('cascade');
            $table->string('client_code')->unique(); // e.g., AG001-CL001
            $table->enum('client_type', [
                'individual', 'business', 'institution', 'church', 'school', 'ngo', 'government'
            ])->default('business');
            
            // Basic Information
            $table->string('client_name');
            $table->string('company_name')->nullable();
            $table->string('email');
            $table->string('phone');
            $table->string('alternative_phone')->nullable();
            
            // Address
            $table->text('address')->nullable();
            $table->string('city')->nullable();
            $table->string('country')->default('ZM');
            
            // Status & Lifecycle
            $table->enum('status', ['lead', 'active', 'suspended', 'cancelled', 'archived'])->default('lead');
            $table->enum('onboarding_status', ['new', 'in_progress', 'completed'])->default('new');
            
            // Notes
            $table->text('notes')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index(['agency_id', 'status']);
            $table->index(['agency_id', 'email']);
            $table->index('client_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('agency_clients');
    }
};
