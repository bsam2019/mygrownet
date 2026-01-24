<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ubumi_check_ins', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('person_id');
            $table->enum('status', ['well', 'unwell', 'need_assistance'])->default('well');
            $table->text('note')->nullable();
            $table->string('location')->nullable();
            $table->string('photo_url')->nullable();
            $table->timestamp('checked_in_at');
            $table->timestamps();

            $table->foreign('person_id')
                ->references('id')
                ->on('ubumi_persons')
                ->onDelete('cascade');

            $table->index(['person_id', 'checked_in_at']);
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ubumi_check_ins');
    }
};
