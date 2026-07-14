<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('sa_comments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sa_company_id');
            $table->morphs('commentable');
            $table->unsignedBigInteger('sa_user_id');
            $table->text('body');
            $table->timestamps();

            $table->foreign('sa_company_id')->references('id')->on('sa_companies')->cascadeOnDelete();
            $table->foreign('sa_user_id')->references('id')->on('sa_users')->cascadeOnDelete();
            $table->index(['sa_company_id', 'commentable_type', 'commentable_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('sa_comments');
    }
};
