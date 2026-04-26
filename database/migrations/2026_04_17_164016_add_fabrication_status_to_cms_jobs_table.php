<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // SQLite doesn't support MODIFY COLUMN, so we use a raw approach
        // For MySQL/PostgreSQL this would be a column modification
        // We add a quotation_id FK to jobs for traceability
        Schema::table('cms_jobs', function (Blueprint $table) {
            $table->foreignId('quotation_id')->nullable()->after('customer_id')
                ->constrained('cms_quotations')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('cms_jobs', function (Blueprint $table) {
            $table->dropForeign(['quotation_id']);
            $table->dropColumn('quotation_id');
        });
    }
};
