<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('growstream_watch_history', function (Blueprint $table) {
            $table->integer('watch_duration')->default(0)->after('duration');
        });

        DB::table('growstream_watch_history')->update(['watch_duration' => DB::raw('current_position')]);
    }

    public function down(): void
    {
        Schema::table('growstream_watch_history', function (Blueprint $table) {
            $table->dropColumn('watch_duration');
        });
    }
};
