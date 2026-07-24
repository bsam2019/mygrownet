<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('gift_settings', function (Blueprint $table) {
            $table->id();
            $table->integer('max_gifts_per_month')->default(5);
            $table->integer('max_gift_amount_per_month')->default(5000);
            $table->integer('min_wallet_balance_to_gift')->default(1000);
            $table->boolean('gift_feature_enabled')->default(true);
            $table->decimal('gift_fee_percentage', 5, 2)->default(0);
            $table->timestamps();
        });

        // Insert default settings
        DB::table('gift_settings')->insert([
            'max_gifts_per_month' => 5,
            'max_gift_amount_per_month' => 5000,
            'min_wallet_balance_to_gift' => 1000,
            'gift_feature_enabled' => true,
            'gift_fee_percentage' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('gift_settings');
    }
};
