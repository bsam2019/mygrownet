<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('payment_logs');
    }

    public function down(): void
    {
        // Re-create is intentionally not implemented.
        // The original payment_logs table was replaced by payment_transactions.
        // Restore from backup if rollback is needed.
    }
};
