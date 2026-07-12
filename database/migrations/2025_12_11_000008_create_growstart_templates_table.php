<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('growstart_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('category', ['business_plan', 'financial', 'marketing', 'legal', 'operations']);
            $table->string('file_path');
            $table->string('file_type')->nullable();
            $table->foreignId('industry_id')->nullable()->constrained('growstart_industries')->nullOnDelete();
            $table->foreignId('country_id')->nullable()->constrained('growstart_countries')->nullOnDelete();
            $table->boolean('is_premium')->default(false);
            $table->integer('download_count')->default(0);
            $table->timestamps();

            $table->index(['category', 'country_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('growstart_templates');
    }
};
