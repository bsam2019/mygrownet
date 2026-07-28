<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('growfinance_bank_connections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained('users')->cascadeOnDelete();
            $table->string('bank_name');
            $table->string('account_name');
            $table->string('account_number');
            $table->string('connection_type')->default('api');
            $table->string('status')->default('active');
            $table->timestamp('last_sync_at')->nullable();
            $table->text('credentials')->nullable();
            $table->timestamps();

            $table->unique(['business_id', 'account_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('growfinance_bank_connections');
    }
};
