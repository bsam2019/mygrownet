<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bizboost_omnichannel_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('business_id')->nullable();
            $table->string('channel_type', 32);
            $table->string('recipient_phone', 50)->nullable();
            $table->decimal('client_amount_charged', 14, 4)->default(0);
            $table->decimal('vendor_actual_cost', 14, 4)->default(0);
            $table->decimal('net_platform_profit', 14, 4)->default(0);
            $table->string('delivery_status', 32)->default('sent');
            $table->text('error_message')->nullable();
            $table->string('reference', 100)->nullable()->index();
            $table->json('meta')->nullable();
            $table->timestamps();
            $table->index(['user_id', 'created_at']);
            $table->index('channel_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bizboost_omnichannel_logs');
    }
};
