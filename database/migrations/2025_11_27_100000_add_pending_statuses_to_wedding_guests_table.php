<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Check if we're using SQLite
        if (DB::connection()->getDriverName() === 'sqlite') {
            // SQLite doesn't support MODIFY COLUMN or ENUM
            // The column should already exist as TEXT from the original migration
            // No changes needed for SQLite as it stores enum values as text anyway
            return;
        }

        // Add attending_pending and declined_pending statuses for unlisted guest RSVPs
        DB::statement("ALTER TABLE wedding_guests MODIFY COLUMN rsvp_status ENUM('pending', 'attending', 'declined', 'inquiry', 'attending_pending', 'declined_pending') DEFAULT 'pending'");
    }

    public function down(): void
    {
        // Check if we're using SQLite
        if (DB::connection()->getDriverName() === 'sqlite') {
            // No changes needed for SQLite
            return;
        }

        // First update any pending status records to 'pending'
        DB::table('wedding_guests')->whereIn('rsvp_status', ['attending_pending', 'declined_pending'])->update(['rsvp_status' => 'pending']);
        
        // Then revert the ENUM
        DB::statement("ALTER TABLE wedding_guests MODIFY COLUMN rsvp_status ENUM('pending', 'attending', 'declined', 'inquiry') DEFAULT 'pending'");
    }
};
