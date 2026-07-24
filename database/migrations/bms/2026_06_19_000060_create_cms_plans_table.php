<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cms_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('cms_companies')->cascadeOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('cms_plans')->cascadeOnDelete();
            $table->string('type'); // strategic, business, operational, schedule
            $table->string('title');
            $table->text('description')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->string('status')->default('draft'); // draft, active, completed, archived
            $table->unsignedSmallInteger('sort_order')->default(0);
            $table->json('metadata')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cms_plans');
    }
};
