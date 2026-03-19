<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('agency_client_services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agency_id')->constrained('agencies')->onDelete('cascade');
            $table->foreignId('client_id')->constrained('agency_clients')->onDelete('cascade');
            $table->enum('service_type', [
                'website', 'hosting', 'domain_management', 'seo', 
                'maintenance', 'ads', 'redesign', 'content_updates', 'other'
            ]);
            $table->string('service_name');
            $table->foreignId('linked_site_id')->nullable()->constrained('growbuilder_sites')->onDelete('set null');
            $table->enum('billing_model', ['monthly', 'quarterly', 'annual', 'one_time']);
            $table->decimal('unit_price', 10, 2);
            $table->unsignedInteger('quantity')->default(1);
            $table->date('start_date')->nullable();
            $table->date('renewal_date')->nullable();
            $table->enum('status', ['active', 'paused', 'cancelled'])->default('active');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index(['agency_id', 'client_id']);
            $table->index(['agency_id', 'status']);
            $table->index('renewal_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agency_client_services');
    }
};
