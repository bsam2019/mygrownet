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
        if (!Schema::hasTable('cms_companies')) {
            Schema::create('cms_companies', function (Blueprint $table) {
                        $table->id();
                        $table->string('name');
                        $table->string('industry_type', 100)->nullable();
                        $table->string('business_registration_number', 100)->nullable();
                        $table->string('tax_number', 100)->nullable();
                        $table->text('address')->nullable();
                        $table->string('city', 100)->nullable();
                        $table->string('country', 100)->default('Zambia');
                        $table->string('phone', 50)->nullable();
                        $table->string('email')->nullable();
                        $table->string('website')->nullable();
                        $table->string('logo_path')->nullable();
                        $table->text('invoice_footer')->nullable();
                        $table->enum('status', ['active', 'suspended'])->default('active');
                        $table->json('settings')->nullable();
                        $table->timestamps();
            
                        $table->index('status');
                        $table->index('industry_type');
                    });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cms_companies');
    }
};
