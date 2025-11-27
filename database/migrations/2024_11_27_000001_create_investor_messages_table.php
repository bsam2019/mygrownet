<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('investor_messages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('investor_account_id');
            $table->string('subject');
            $table->text('content');
            $table->enum('direction', ['inbound', 'outbound'])->default('inbound'); // inbound = from investor, outbound = from admin
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->boolean('is_read')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->unsignedBigInteger('admin_user_id')->nullable(); // for outbound messages
            $table->timestamps();

            $table->foreign('investor_account_id')->references('id')->on('investor_accounts')->onDelete('cascade');
            $table->foreign('parent_id')->references('id')->on('investor_messages')->onDelete('set null');
            $table->index(['investor_account_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('investor_messages');
    }
};
