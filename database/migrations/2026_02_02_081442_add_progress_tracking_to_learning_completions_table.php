<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('learning_completions', function (Blueprint $table) {
            $table->integer('current_page')->default(0)->after('time_spent_seconds');
            $table->timestamp('last_accessed_at')->nullable()->after('current_page');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('learning_completions', function (Blueprint $table) {
            $table->dropColumn(['current_page', 'last_accessed_at']);
        });
    }
};
