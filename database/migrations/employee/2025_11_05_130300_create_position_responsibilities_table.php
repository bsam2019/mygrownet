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
        Schema::create('position_responsibilities', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('position_id');
            $table->string('responsibility_title');
            $table->text('responsibility_description')->nullable();
            $table->enum('priority', ['critical', 'high', 'medium', 'low'])->default('medium');
            $table->enum('category', ['strategic', 'operational', 'administrative', 'technical'])->default('operational');
            $table->integer('display_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->foreign('position_id')->references('id')->on('positions')->onDelete('cascade');
            $table->index(['position_id', 'is_active']);
            $table->index('display_order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('position_responsibilities');
    }
};
