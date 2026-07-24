<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('growbuilder_sites', function (Blueprint $table) {
            $table->string('whatsapp_number')->nullable();
            $table->text('whatsapp_default_message')->nullable();
            $table->boolean('whatsapp_floating_button')->default(false);
            $table->string('whatsapp_floating_position')->default('bottom-right');
        });
    }

    public function down(): void
    {
        Schema::table('growbuilder_sites', function (Blueprint $table) {
            $table->dropColumn([
                'whatsapp_number',
                'whatsapp_default_message',
                'whatsapp_floating_button',
                'whatsapp_floating_position',
            ]);
        });
    }
};
