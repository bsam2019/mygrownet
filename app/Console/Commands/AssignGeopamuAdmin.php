<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class AssignGeopamuAdmin extends Command
{
    protected $signature = 'geopamu:assign-admin {email}';
    protected $description = 'Assign Geopamu admin permission to a user by email';

    public function handle()
    {
        $email = $this->argument('email');
        
        $user = User::where('email', $email)->first();
        
        if (!$user) {
            $this->error("User with email '{$email}' not found.");
            return 1;
        }

        // Give permission
        $user->givePermissionTo('manage-geopamu');
        
        $this->info("✓ Geopamu admin permission granted to {$user->name} ({$email})");
        $this->info("They can now access: /geopamu/admin/blog");
        $this->info("Note: This does NOT grant access to MyGrowNet admin dashboard.");
        
        return 0;
    }
}
