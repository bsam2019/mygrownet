<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('zamstay_bookings', function (Blueprint $table) {
            $table->foreignId('agent_id')->nullable()->constrained('zamstay_agents')->nullOnDelete()->after('property_id');
        });
    }

    public function down(): void
    {
        Schema::table('zamstay_bookings', function (Blueprint $table) {
            $table->dropForeign(['agent_id']);
            $table->dropColumn('agent_id');
        });
    }
};
