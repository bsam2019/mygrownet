<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('growfinance_group_consolidations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('group_id');
            $table->unsignedBigInteger('business_id');
            $table->string('period', 7);
            $table->json('consolidated_data');
            $table->string('functional_currency', 3);
            $table->string('reporting_currency', 3);
            $table->decimal('exchange_rate', 15, 6);
            $table->json('elimination_entries')->nullable();
            $table->string('status')->default('draft');
            $table->timestamp('consolidated_at')->nullable();
            $table->timestamps();

            $table->foreign('group_id')->references('id')->on('growfinance_org_groups')->onDelete('cascade');
            $table->index(['business_id', 'period']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('growfinance_group_consolidations');
    }
};
