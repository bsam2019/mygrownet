<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // inventory_* tables may reference each other via FKs. Disable FK checks
        // so all tables can be dropped regardless of inter-table constraints.
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        try {
            Schema::dropIfExists('inventory_alerts');
            Schema::dropIfExists('stock_movements');
            Schema::dropIfExists('inventory_items');
            Schema::dropIfExists('inventory_categories');
        } finally {
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
        }
    }

    public function down(): void
    {
    }
};
