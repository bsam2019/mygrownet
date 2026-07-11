<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sa_expiry_checks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sa_company_id')->constrained('sa_companies')->cascadeOnDelete();
            $table->foreignId('sa_audit_id')->nullable()->constrained('sa_audits')->nullOnDelete();
            $table->string('title');
            $table->date('check_date');
            $table->text('notes')->nullable();
            $table->decimal('total_system_value', 16, 2)->default(0);
            $table->decimal('total_physical_value', 16, 2)->default(0);
            $table->decimal('total_missing_value', 16, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('sa_expiry_check_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sa_expiry_check_id')->constrained('sa_expiry_checks')->cascadeOnDelete();
            $table->foreignId('sa_item_id')->nullable()->constrained('sa_items')->nullOnDelete();
            $table->string('item_name');
            $table->decimal('unit_price', 14, 2)->default(0);
            $table->decimal('system_qty', 12, 2)->default(0);
            $table->decimal('physical_qty', 12, 2)->default(0);
            $table->decimal('system_value', 16, 2)->default(0);
            $table->decimal('physical_value', 16, 2)->default(0);
            $table->decimal('missing_qty', 12, 2)->default(0);
            $table->decimal('missing_value', 16, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sa_expiry_check_items');
        Schema::dropIfExists('sa_expiry_checks');
    }
};
