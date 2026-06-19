<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cms_contracts', function (Blueprint $table) {
            if (!Schema::hasColumn('cms_contracts', 'signed_pdf_path')) {
                $table->string('signed_pdf_path')->nullable()->after('signed_at');
            }
            if (!Schema::hasColumn('cms_contracts', 'signing_token')) {
                $table->string('signing_token', 64)->nullable()->unique()->after('signed_pdf_path');
            }
            if (!Schema::hasColumn('cms_contracts', 'branch_id')) {
                $table->foreignId('branch_id')->nullable()->after('company_id')
                    ->constrained('cms_branches')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('cms_contracts', function (Blueprint $table) {
            $table->dropColumn(['signed_pdf_path', 'signing_token']);
            $table->dropForeign(['branch_id']);
            $table->dropColumn('branch_id');
        });
    }
};
