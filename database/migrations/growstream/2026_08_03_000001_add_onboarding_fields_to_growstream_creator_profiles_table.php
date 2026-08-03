<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('growstream_creator_profiles', function (Blueprint $table) {
            $table->string('channel_name', 100)->nullable()->after('display_name');
            $table->enum('status', ['pending', 'approved', 'rejected', 'suspended'])
                ->default('pending')
                ->after('is_active');
            $table->text('rejected_reason')->nullable()->after('status');
            $table->string('suspension_reason')->nullable()->after('rejected_reason');
            $table->timestamp('suspended_at')->nullable()->after('suspension_reason');
        });
    }

    public function down(): void
    {
        Schema::table('growstream_creator_profiles', function (Blueprint $table) {
            $table->dropColumn([
                'channel_name',
                'status',
                'rejected_reason',
                'suspension_reason',
                'suspended_at',
            ]);
        });
    }
};
