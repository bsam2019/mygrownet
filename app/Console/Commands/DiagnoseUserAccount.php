<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class DiagnoseUserAccount extends Command
{
    protected $signature = 'user:diagnose {email}';
    protected $description = 'Diagnose and fix user account issues';

    public function handle()
    {
        $email = $this->argument('email');
        
        $this->info("Diagnosing user account: {$email}");
        $this->newLine();
        
        // Find user
        $user = User::where('email', $email)->first();
        
        if (!$user) {
            $this->error("User not found with email: {$email}");
            return 1;
        }
        
        $this->info("✓ User found: {$user->name} (ID: {$user->id})");
        $this->newLine();
        
        // Check user data
        $this->info("Current User Data:");
        $this->table(
            ['Field', 'Value'],
            [
                ['ID', $user->id],
                ['Name', $user->name],
                ['Email', $user->email],
                ['Phone', $user->phone ?? 'NULL'],
                ['Status', $user->status ?? 'NULL'],
                ['Email Verified', $user->email_verified_at ? 'Yes' : 'No'],
                ['Created', $user->created_at],
                ['Updated', $user->updated_at],
            ]
        );
        $this->newLine();
        
        // Check if user can be updated
        $this->info("Testing database write...");
        try {
            DB::beginTransaction();
            
            $originalUpdatedAt = $user->updated_at;
            $user->touch();
            
            if ($user->updated_at->ne($originalUpdatedAt)) {
                $this->info("✓ Database write successful");
                DB::rollBack(); // Rollback the test
            } else {
                $this->error("✗ Database write failed - updated_at didn't change");
                DB::rollBack();
            }
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error("✗ Database write error: " . $e->getMessage());
        }
        $this->newLine();
        
        // Offer to reset password
        if ($this->confirm('Do you want to reset the password?')) {
            $newPassword = $this->secret('Enter new password');
            $confirmPassword = $this->secret('Confirm new password');
            
            if ($newPassword !== $confirmPassword) {
                $this->error('Passwords do not match!');
                return 1;
            }
            
            try {
                $user->password = Hash::make($newPassword);
                $user->save();
                
                $this->info("✓ Password updated successfully!");
                $this->info("User can now login with the new password.");
            } catch (\Exception $e) {
                $this->error("✗ Failed to update password: " . $e->getMessage());
                return 1;
            }
        }
        
        // Offer to update phone
        if ($this->confirm('Do you want to update the phone number?')) {
            $newPhone = $this->ask('Enter new phone number');
            
            try {
                $user->phone = $newPhone;
                $user->save();
                
                $this->info("✓ Phone number updated successfully!");
            } catch (\Exception $e) {
                $this->error("✗ Failed to update phone: " . $e->getMessage());
                return 1;
            }
        }
        
        $this->newLine();
        $this->info("Diagnosis complete!");
        
        return 0;
    }
}
