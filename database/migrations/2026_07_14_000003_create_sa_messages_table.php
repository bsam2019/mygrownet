<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('sa_messages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sa_company_id');
            $table->unsignedBigInteger('sender_id');
            $table->unsignedBigInteger('recipient_id');
            $table->string('subject');
            $table->text('body');
            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->timestamps();

            $table->foreign('sa_company_id')->references('id')->on('sa_companies')->cascadeOnDelete();
            $table->foreign('sender_id')->references('id')->on('sa_users')->cascadeOnDelete();
            $table->foreign('recipient_id')->references('id')->on('sa_users')->cascadeOnDelete();
            $table->foreign('parent_id')->references('id')->on('sa_messages')->cascadeOnDelete();
            $table->index(['recipient_id', 'is_read']);
            $table->index(['sender_id', 'created_at']);
            $table->index(['recipient_id', 'created_at']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('sa_messages');
    }
};
