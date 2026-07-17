<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sa_sale_items', function (Blueprint $table) {
            $table->foreignId('sa_lot_id')->nullable()->after('sa_item_id')->constrained('sa_lots')->nullOnDelete();
        });

        Schema::table('sa_purchase_order_items', function (Blueprint $table) {
            $table->foreignId('sa_lot_id')->nullable()->after('sa_item_id')->constrained('sa_lots')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('sa_sale_items', function (Blueprint $table) {
            $table->dropForeign(['sa_lot_id']);
            $table->dropColumn('sa_lot_id');
        });

        Schema::table('sa_purchase_order_items', function (Blueprint $table) {
            $table->dropForeign(['sa_lot_id']);
            $table->dropColumn('sa_lot_id');
        });
    }
};
