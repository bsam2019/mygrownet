<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('wedding_guests', function (Blueprint $table) {
            $table->enum('rsvp_status', ['pending', 'attending', 'declined'])->default('pending')->after('invitation_sent');
            $table->integer('confirmed_guests')->default(0)->after('rsvp_status'); // How many are actually coming
            $table->text('dietary_restrictions')->nullable()->after('confirmed_guests');
            $table->text('rsvp_message')->nullable()->after('dietary_restrictions');
            $table->timestamp('rsvp_submitted_at')->nullable()->after('rsvp_message');
        });
    }

    public function down(): void
    {
        Schema::table('wedding_guests', function (Blueprint $table) {
            $table->dropColumn(['rsvp_status', 'confirmed_guests', 'dietary_restrictions', 'rsvp_message', 'rsvp_submitted_at']);
        });
    }
};
