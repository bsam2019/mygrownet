<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('growfinance_org_groups', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('parent_org_id')->nullable();
            $table->unsignedBigInteger('child_org_id');
            $table->string('relationship_type')->default('subsidiary');
            $table->json('consolidation_settings')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('parent_org_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('child_org_id')->references('id')->on('users')->onDelete('cascade');
            $table->unique(['parent_org_id', 'child_org_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('growfinance_org_groups');
    }
};
