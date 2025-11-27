<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DemoWeddingSeeder extends Seeder
{
    /**
     * Create the demo wedding event for Kaoma & Mubanga.
     * This is required for the RSVP functionality to work on the demo wedding website.
     */
    public function run(): void
    {
        // Check if demo wedding already exists
        $exists = DB::table('wedding_events')->where('id', 1)->exists();
        
        if ($exists) {
            $this->command->info('Demo wedding event already exists (ID: 1)');
            return;
        }

        // Get or create a system user for the demo wedding
        $systemUser = DB::table('users')->where('email', 'admin@mygrownet.com')->first();
        
        if (!$systemUser) {
            // Use the first admin user if system user doesn't exist
            $systemUser = DB::table('users')
                ->join('model_has_roles', 'users.id', '=', 'model_has_roles.model_id')
                ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
                ->where('roles.name', 'admin')
                ->select('users.*')
                ->first();
        }

        if (!$systemUser) {
            // Use the first user as fallback
            $systemUser = DB::table('users')->first();
        }

        if (!$systemUser) {
            $this->command->error('No users found in database. Please create a user first.');
            return;
        }

        // Insert the demo wedding event with ID 1
        DB::table('wedding_events')->insert([
            'id' => 1,
            'user_id' => $systemUser->id,
            'partner_name' => 'Mubanga',
            'wedding_date' => '2025-12-06',
            'budget' => 75000.00,
            'guest_count' => 150,
            'venue_name' => '3Sixty Convention Centre',
            'venue_location' => 'Twin Palm Road, Ibex Hill, Lusaka',
            'status' => 'planning',
            'slug' => 'kaoma-and-mubanga-dec-2025',
            'access_code' => 'KAOMAMUBANGA2025',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->command->info('Demo wedding event created successfully (ID: 1)');
        $this->command->info('Couple: Kaoma & Mubanga');
        $this->command->info('Date: December 6, 2025');
        $this->command->info('Venue: 3Sixty Convention Centre, Lusaka');
    }
}
