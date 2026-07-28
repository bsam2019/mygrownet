<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('growfinance_auto_journal_mappings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained('users')->cascadeOnDelete();
            $table->string('event_type');
            $table->string('debit_account_code');
            $table->string('credit_account_code');
            $table->string('description_template')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique(['business_id', 'event_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('growfinance_auto_journal_mappings');
    }
};
