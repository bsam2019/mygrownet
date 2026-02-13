<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('cms_customer_contacts')) {
            Schema::create('cms_customer_contacts', function (Blueprint $table) {
                $table->id();
                $table->foreignId('company_id')->constrained('cms_companies')->onDelete('cascade');
                $table->foreignId('customer_id')->constrained('cms_customers')->onDelete('cascade');
                
                $table->string('name');
                $table->string('title')->nullable(); // e.g., Manager, Director, etc.
                $table->string('email')->nullable();
                $table->string('phone')->nullable();
                $table->string('mobile')->nullable();
                $table->boolean('is_primary')->default(false);
                $table->text('notes')->nullable();
                
                $table->timestamps();

                $table->index(['company_id', 'customer_id']);
                $table->index('is_primary');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('cms_customer_contacts');
    }
};
