<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sa_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sa_company_id')->constrained('sa_companies')->cascadeOnDelete();
            $table->foreignId('sa_department_id')->nullable()->constrained('sa_departments')->nullOnDelete();
            $table->foreignId('sa_bin_id')->nullable()->constrained('sa_bins')->nullOnDelete();
            $table->string('name');
            $table->string('sku')->nullable();
            $table->text('description')->nullable();
            $table->decimal('unit_price', 14, 2)->default(0);
            $table->string('unit', 50)->nullable();
            $table->decimal('system_quantity', 12, 2)->default(0);
            $table->decimal('reorder_level', 12, 2)->nullable();
            $table->string('category')->nullable();
            $table->boolean('is_expirable')->default(false);
            $table->date('expiry_date')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['sa_company_id', 'sa_bin_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sa_items');
    }
};
