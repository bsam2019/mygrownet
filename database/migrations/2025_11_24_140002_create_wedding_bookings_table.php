<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wedding_bookings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wedding_event_id')->constrained()->onDelete('cascade');
            $table->foreignId('wedding_vendor_id')->constrained()->onDelete('cascade');
            $table->string('service_type');
            $table->date('service_date');
            $table->time('service_time')->nullable();
            $table->decimal('quoted_amount', 15, 2);
            $table->decimal('final_amount', 15, 2)->nullable();
            $table->decimal('deposit_amount', 15, 2)->default(0);
            $table->enum('status', [
                'pending', 'quoted', 'confirmed', 'deposit_paid', 
                'paid', 'completed', 'cancelled'
            ])->default('pending');
            $table->text('requirements')->nullable();
            $table->text('vendor_notes')->nullable();
            $table->json('contract_terms')->nullable();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamps();
            
            $table->index(['wedding_event_id', 'status']);
            $table->index(['wedding_vendor_id', 'status']);
            $table->index('service_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wedding_bookings');
    }
};