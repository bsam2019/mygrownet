<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('organizations', function (Blueprint $table) {
            $table->string('logo_path')->nullable()->after('language');
            $table->text('address')->nullable()->after('logo_path');
            $table->string('phone')->nullable()->after('address');
            $table->string('email')->nullable()->after('phone');
            $table->string('website')->nullable()->after('email');
            $table->string('tax_number')->nullable()->after('website');
            $table->string('registration_number')->nullable()->after('tax_number');
        });
    }

    public function down(): void
    {
        Schema::table('organizations', function (Blueprint $table) {
            $table->dropColumn([
                'logo_path',
                'address',
                'phone',
                'email',
                'website',
                'tax_number',
                'registration_number',
            ]);
        });
    }
};