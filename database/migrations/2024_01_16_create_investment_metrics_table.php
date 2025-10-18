<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('investment_metrics', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->decimal('total_value', 15, 2)->default(0);
            $table->integer('total_count')->default(0);
            $table->decimal('average_roi', 8, 2)->default(0);
            $table->integer('active_investors')->default(0);
            $table->decimal('success_rate', 5, 2)->default(0);
            $table->timestamps();

            $table->unique('date');
        });
    }

    public function down()
    {
        Schema::dropIfExists('investment_metrics');
    }
};
