<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('wedding_events', function (Blueprint $table) {
            $table->string('slug')->nullable()->unique()->after('id');
            $table->string('access_code')->nullable()->after('notes');
        });
    }

    public function down(): void
    {
        Schema::table('wedding_events', function (Blueprint $table) {
            $table->dropColumn(['slug', 'access_code']);
        });
    }
};
