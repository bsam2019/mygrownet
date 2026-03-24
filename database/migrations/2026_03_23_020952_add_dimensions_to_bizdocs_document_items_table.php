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
        Schema::table('bizdocs_document_items', function (Blueprint $table) {
            $table->string('dimensions')->nullable()->after('description');
            $table->decimal('dimensions_value', 10, 4)->default(1)->after('dimensions');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bizdocs_document_items', function (Blueprint $table) {
            $table->dropColumn(['dimensions', 'dimensions_value']);
        });
    }
};
