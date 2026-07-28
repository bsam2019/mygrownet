<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('growfinance_scenarios', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('business_id');
            $table->string('name');
            $table->json('parameters');
            $table->json('results');
            $table->timestamps();
            $table->foreign('business_id')->references('id')->on('users')->onDelete('cascade');
            $table->index('business_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('growfinance_scenarios');
    }
};
