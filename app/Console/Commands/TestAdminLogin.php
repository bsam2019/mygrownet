<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class TestAdminLogin extends Command
{
    protected $signature = 'admin:test-login {email=admin@vbif.com}';
    protected $description = 'Test admin login credentials and permissions';

    public function handle()
    {
        $email = $this->argument('email');
        
        $this->info("Testing login for: {$email}");
        $this->newLine();
        
        $user = User::where('email', $email)->first();
        
        if (!$user) {
            $this->error("User not found: {$email}");
            return 1;
        }
        
        $this->info("✓ User found: {$user->name}");
        
        // Check roles
        $roles = $user->roles->pluck('name')->toArray();
        $this->info("✓ Roles: " . implode(', ', $roles));
        
        // Check hasRole
        $hasAdmin = $user->hasRole('Administrator') || $user->hasRole('admin');
        if ($hasAdmin) {
            $this->info("✓ Has admin role: YES");
        } else {
            $this->error("✗ Has admin role: NO");
            return 1;
        }
        
        // Test password
        if ($this->confirm('Test password?', true)) {
            $password = $this->secret('Enter password to test');
            
            if (Hash::check($password, $user->password)) {
                $this->info("✓ Password is CORRECT");
            } else {
                $this->error("✗ Password is WRONG");
                
                if ($this->confirm('Reset password to vbif@2025!?')) {
                    $user->password = Hash::make('vbif@2025!');
                    $user->save();
                    $this->info("✓ Password reset to: vbif@2025!");
                }
            }
        }
        
        $this->newLine();
        $this->info("=== Login Credentials ===");
        $this->line("Email: {$email}");
        $this->line("Password: vbif@2025!");
        
        return 0;
    }
}
