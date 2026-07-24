<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('sa_notifications', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sa_company_id');
            $table->unsignedBigInteger('sa_user_id');
            $table->string('type', 50); // sale.completed, po.received, stock.low, cash.discrepancy, audit.finalized, etc.
            $table->string('title');
            $table->text('message')->nullable();
            $table->string('action_url', 500)->nullable();
            $table->string('action_text', 100)->nullable();
            $table->json('data')->nullable();
            $table->string('priority', 20)->default('normal'); // low, normal, high, urgent
            $table->timestamp('read_at')->nullable();
            $table->timestamps();

            $table->foreign('sa_company_id')->references('id')->on('sa_companies')->cascadeOnDelete();
            $table->foreign('sa_user_id')->references('id')->on('sa_users')->cascadeOnDelete();
            $table->index(['sa_user_id', 'read_at']);
            $table->index(['sa_company_id', 'type']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('sa_notifications');
    }
};
