<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sa_stock_levels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sa_company_id')->constrained('sa_companies')->cascadeOnDelete();
            $table->foreignId('sa_item_id')->constrained('sa_items')->cascadeOnDelete();
            $table->decimal('qty_on_hand', 12, 2)->default(0);
            $table->decimal('value_on_hand', 14, 2)->default(0);
            $table->timestamp('last_movement_at')->nullable();
            $table->timestamps();

            $table->unique(['sa_company_id', 'sa_item_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sa_stock_levels');
    }
};
