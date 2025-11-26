<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // For MySQL, we need to modify the ENUM to add new values for unlisted guest RSVPs
        DB::statement("ALTER TABLE wedding_guests MODIFY COLUMN rsvp_status ENUM('pending', 'attending', 'declined', 'inquiry', 'attending_pending', 'declined_pending') DEFAULT 'pending'");
    }

    public function down(): void
    {
        // First update any new status records to 'pending'
        DB::table('wedding_guests')->whereIn('rsvp_status', ['inquiry', 'attending_pending', 'declined_pending'])->update(['rsvp_status' => 'pending']);
        
        // Then revert the ENUM
        DB::statement("ALTER TABLE wedding_guests MODIFY COLUMN rsvp_status ENUM('pending', 'attending', 'declined') DEFAULT 'pending'");
    }
};
