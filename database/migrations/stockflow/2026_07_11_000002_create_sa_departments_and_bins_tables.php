<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sa_departments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sa_company_id')->constrained('sa_companies')->cascadeOnDelete();
            $table->string('name');
            $table->string('slug')->nullable();
            $table->text('description')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['sa_company_id', 'slug']);
        });

        Schema::create('sa_bins', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sa_company_id')->constrained('sa_companies')->cascadeOnDelete();
            $table->foreignId('sa_department_id')->constrained('sa_departments')->cascadeOnDelete();
            $table->string('name');
            $table->string('label')->nullable();
            $table->text('description')->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['sa_company_id', 'name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sa_bins');
        Schema::dropIfExists('sa_departments');
    }
};
