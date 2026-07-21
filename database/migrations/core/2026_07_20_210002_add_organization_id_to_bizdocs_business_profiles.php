<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bizdocs_business_profiles', function (Blueprint $table) {
            $table->foreignId('organization_id')
                ->nullable()
                ->after('user_id')
                ->constrained('organizations')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('bizdocs_business_profiles', function (Blueprint $table) {
            $table->dropForeign(['organization_id']);
            $table->dropColumn('organization_id');
        });
    }
};
