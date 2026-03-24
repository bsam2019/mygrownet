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
        Schema::table('bizdocs_document_sequences', function (Blueprint $table) {
            $table->boolean('include_year')->default(false)->after('padding');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bizdocs_document_sequences', function (Blueprint $table) {
            $table->dropColumn('include_year');
        });
    }
};
