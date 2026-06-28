<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\MatrixPosition;

class InitializeMatrixPositions extends Command
{
    protected $signature = 'matrix:initialize';
    protected $description = 'Initialize matrix positions for existing users';

    public function handle()
    {
        $this->info('Initializing matrix positions for existing users...');
        
        // Get all active users without matrix positions
        $usersWithoutMatrix = User::whereDoesntHave('matrixPositions')
            ->where('status', 'active')
            ->get();
            
        $this->info("Found {$usersWithoutMatrix->count()} users without matrix positions");
        
        foreach ($usersWithoutMatrix as $user) {
            $this->createMatrixPosition($user);
            $this->info("Created matrix position for user: {$user->name} (ID: {$user->id})");
        }
        
        $this->info('Matrix initialization completed!');
    }
    
    private function createMatrixPosition(User $user): void
    {
        // Check if user already has a matrix position
        if ($user->getMatrixPosition()) {
            return;
        }
        
        // Find sponsor (referrer)
        $sponsor = $user->referrer;
        
        if (!$sponsor) {
            // Create root position for users without referrer
            MatrixPosition::create([
                'user_id' => $user->id,
                'sponsor_id' => null,
                'level' => 0, // Root level
                'position' => 1,
                'is_active' => true,
                'placed_at' => now(),
            ]);
            return;
        }
        
        // Find next available position under sponsor
        $directChildren = MatrixPosition::where('sponsor_id', $sponsor->id)
            ->where('level', 1)
            ->count();
            
        if ($directChildren < 3) {
            // Direct placement
            MatrixPosition::create([
                'user_id' => $user->id,
                'sponsor_id' => $sponsor->id,
                'level' => 1,
                'position' => $directChildren + 1,
                'is_active' => true,
                'placed_at' => now(),
            ]);
        } else {
            // Spillover placement - find next available position
            $this->findSpilloverPosition($user, $sponsor);
        }
    }
    
    private function findSpilloverPosition(User $user, User $sponsor): void
    {
        // Simple spillover logic - place under first available position
        $availableSponsors = MatrixPosition::where('sponsor_id', $sponsor->id)
            ->where('level', 1)
            ->with('user')
            ->get();
            
        foreach ($availableSponsors as $position) {
            $childrenCount = MatrixPosition::where('sponsor_id', $position->user_id)
                ->where('level', 2)
                ->count();
                
            if ($childrenCount < 3) {
                MatrixPosition::create([
                    'user_id' => $user->id,
                    'sponsor_id' => $position->user_id,
                    'level' => 2,
                    'position' => $childrenCount + 1,
                    'is_active' => true,
                    'placed_at' => now(),
                ]);
                return;
            }
        }
        
        // If no level 2 positions available, create level 3 or fallback
        MatrixPosition::create([
            'user_id' => $user->id,
            'sponsor_id' => $sponsor->id,
            'level' => 1,
            'position' => 4, // This will be adjusted by the system
            'is_active' => true,
            'placed_at' => now(),
        ]);
    }
}