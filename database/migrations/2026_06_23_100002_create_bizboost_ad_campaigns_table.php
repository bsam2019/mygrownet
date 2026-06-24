<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bizboost_ad_campaigns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('business_id')->nullable();
            $table->string('name');
            $table->string('objective', 64)->nullable();
            $table->decimal('client_budget', 14, 4);
            $table->decimal('meta_budget', 14, 4);
            $table->decimal('platform_markup', 14, 4);
            $table->string('meta_campaign_id', 255)->nullable()->index();
            $table->string('meta_ad_set_id', 255)->nullable();
            $table->string('meta_ad_id', 255)->nullable();
            $table->string('status', 32)->default('draft');
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->integer('duration_days')->default(7);
            $table->json('targeting')->nullable();
            $table->json('creatives')->nullable();
            $table->json('insights')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();
            $table->index(['user_id', 'status']);
            $table->index('meta_campaign_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bizboost_ad_campaigns');
    }
};
