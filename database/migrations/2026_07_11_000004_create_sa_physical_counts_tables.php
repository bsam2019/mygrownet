<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sa_physical_counts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sa_company_id')->constrained('sa_companies')->cascadeOnDelete();
            $table->string('title');
            $table->date('count_date');
            $table->enum('status', ['draft', 'in_progress', 'completed', 'verified'])->default('draft');
            $table->foreignId('counted_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('sa_count_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sa_physical_count_id')->constrained('sa_physical_counts')->cascadeOnDelete();
            $table->foreignId('sa_item_id')->constrained('sa_items')->cascadeOnDelete();
            $table->foreignId('sa_bin_id')->nullable()->constrained('sa_bins')->nullOnDelete();
            $table->decimal('system_quantity', 12, 2)->default(0);
            $table->decimal('physical_quantity', 12, 2)->default(0);
            $table->decimal('variance', 12, 2)->default(0);
            $table->decimal('unit_price', 14, 2)->default(0);
            $table->decimal('variance_value', 14, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['sa_physical_count_id', 'sa_item_id'], 'sa_count_item_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sa_count_items');
        Schema::dropIfExists('sa_physical_counts');
    }
};
