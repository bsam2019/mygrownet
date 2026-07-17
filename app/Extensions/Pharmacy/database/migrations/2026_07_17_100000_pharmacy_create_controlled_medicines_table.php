<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('sa_controlled_medicines')) return;

        Schema::create('sa_controlled_medicines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sa_company_id')->constrained('sa_companies')->cascadeOnDelete();
            $table->foreignId('sa_item_id')->constrained('sa_items')->cascadeOnDelete();
            $table->foreignId('sa_lot_id')->nullable()->constrained('sa_lots')->nullOnDelete();
            $table->string('transaction_type');
            $table->decimal('quantity', 12, 2);
            $table->decimal('balance_after', 12, 2);
            $table->string('patient_name')->nullable();
            $table->string('patient_id_number')->nullable();
            $table->string('prescription_number')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('staff_user_id')->constrained('sa_users')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sa_controlled_medicines');
    }
};
