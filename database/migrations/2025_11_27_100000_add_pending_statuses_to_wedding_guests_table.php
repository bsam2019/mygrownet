<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Add attending_pending and declined_pending statuses for unlisted guest RSVPs
        DB::statement("ALTER TABLE wedding_guests MODIFY COLUMN rsvp_status ENUM('pending', 'attending', 'declined', 'inquiry', 'attending_pending', 'declined_pending') DEFAULT 'pending'");
    }

    public function down(): void
    {
        // First update any pending status records to 'pending'
        DB::table('wedding_guests')->whereIn('rsvp_status', ['attending_pending', 'declined_pending'])->update(['rsvp_status' => 'pending']);
        
        // Then revert the ENUM
        DB::statement("ALTER TABLE wedding_guests MODIFY COLUMN rsvp_status ENUM('pending', 'attending', 'declined', 'inquiry') DEFAULT 'pending'");
    }
};
