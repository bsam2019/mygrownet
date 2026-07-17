<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sa_transfers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sa_company_id')->constrained('sa_companies')->cascadeOnDelete();
            $table->string('transfer_number');
            $table->foreignId('from_warehouse_id')->constrained('sa_warehouses')->cascadeOnDelete();
            $table->foreignId('to_warehouse_id')->constrained('sa_warehouses')->cascadeOnDelete();
            $table->enum('status', ['draft', 'in_transit', 'received', 'cancelled'])->default('draft');
            $table->foreignId('transferred_by')->nullable()->constrained('sa_users')->nullOnDelete();
            $table->foreignId('received_by')->nullable()->constrained('sa_users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('sa_transfer_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sa_transfer_id')->constrained('sa_transfers')->cascadeOnDelete();
            $table->foreignId('sa_item_id')->constrained('sa_items')->cascadeOnDelete();
            $table->decimal('quantity', 12, 2)->default(0);
            $table->decimal('unit_cost', 14, 2)->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sa_transfer_items');
        Schema::dropIfExists('sa_transfers');
    }
};
