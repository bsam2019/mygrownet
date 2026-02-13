<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cms_payments', function (Blueprint $table) {
            $table->boolean('is_voided')->default(false)->after('notes');
            $table->string('void_reason')->nullable()->after('is_voided');
            $table->timestamp('voided_at')->nullable()->after('void_reason');
            $table->unsignedBigInteger('voided_by')->nullable()->after('voided_at');
            
            $table->foreign('voided_by')->references('id')->on('cms_users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('cms_payments', function (Blueprint $table) {
            $table->dropForeign(['voided_by']);
            $table->dropColumn(['is_voided', 'void_reason', 'voided_at', 'voided_by']);
        });
    }
};
