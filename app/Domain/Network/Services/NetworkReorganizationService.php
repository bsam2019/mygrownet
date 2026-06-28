<?php

namespace App\Domain\Network\Services;

use App\Models\User;
use Illuminate\Support\Facades\DB;

class NetworkReorganizationService
{
    /**
     * Move a user to a new referrer, updating all network relationships
     */
    public function moveUserToNewReferrer(User $user, User $newReferrer): void
    {
        DB::transaction(function () use ($user, $newReferrer) {
            // Update the user's referrer
            $user->referrer_id = $newReferrer->id;
            $user->save();
            
            // Update user_networks table
            $this->updateUserNetworkRecord($user, $newReferrer);
            
            // Update network path
            $user->updateNetworkPath();
            
            // Update all downlines' network records
            $this->updateDownlineNetworkRecords($user, $newReferrer);
        });
    }
    
    /**
     * Move a user and their entire tree to a new referrer
     */
    public function moveUserTreeToNewReferrer(User $user, User $newReferrer): void
    {
        DB::transaction(function () use ($user, $newReferrer) {
            // Move the user
            $this->moveUserToNewReferrer($user, $newReferrer);
            
            // Get all downlines recursively
            $downlines = $this->getAllDownlines($user);
            
            // Update each downline's network record under the new top referrer
            foreach ($downlines as $downline) {
                $this->updateDownlineNetworkPath($downline, $newReferrer);
            }
        });
    }
    
    /**
     * Check if a move would violate 3x7 matrix rules
     */
    public function canMoveToReferrer(User $newReferrer): array
    {
        $directDownlineCount = User::where('referrer_id', $newReferrer->id)->count();
        
        if ($directDownlineCount >= 3) {
            return [
                'allowed' => false,
                'reason' => 'Referrer already has 3 direct downlines (3x7 matrix limit)',
                'current_count' => $directDownlineCount
            ];
        }
        
        return [
            'allowed' => true,
            'current_count' => $directDownlineCount
        ];
    }
    
    /**
     * Get network tree structure for a user
     */
    public function getNetworkTree(User $user, int $maxDepth = 3): array
    {
        return $this->buildTreeRecursive($user, 1, $maxDepth);
    }
    
    private function buildTreeRecursive(User $user, int $currentDepth, int $maxDepth): array
    {
        $node = [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'level' => $currentDepth,
            'children' => []
        ];
        
        if ($currentDepth < $maxDepth) {
            $downlines = User::where('referrer_id', $user->id)->get();
            foreach ($downlines as $downline) {
                $node['children'][] = $this->buildTreeRecursive($downline, $currentDepth + 1, $maxDepth);
            }
        }
        
        return $node;
    }
    
    private function updateUserNetworkRecord(User $user, User $newReferrer): void
    {
        // Get new referrer's path
        $referrerNetworkRecord = DB::table('user_networks')
            ->where('user_id', $newReferrer->id)
            ->first();
        
        $referrerPath = $referrerNetworkRecord ? $referrerNetworkRecord->path : (string)$newReferrer->id;
        $newPath = $referrerPath . '.' . $user->id;
        
        // Delete old record
        DB::table('user_networks')
            ->where('user_id', $user->id)
            ->delete();
        
        // Insert new record
        DB::table('user_networks')->insert([
            'user_id' => $user->id,
            'referrer_id' => $newReferrer->id,
            'level' => 1,
            'path' => $newPath,
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }
    
    private function updateDownlineNetworkRecords(User $user, User $topReferrer): void
    {
        $downlines = User::where('referrer_id', $user->id)->get();
        
        foreach ($downlines as $downline) {
            $this->updateDownlineNetworkPath($downline, $topReferrer);
            
            // Recursively update their downlines
            $this->updateDownlineNetworkRecords($downline, $topReferrer);
        }
    }
    
    private function updateDownlineNetworkPath(User $user, User $topReferrer): void
    {
        $user->updateNetworkPath();
        
        // Update the network record to reflect new top referrer
        $userNetworkRecord = DB::table('user_networks')
            ->where('user_id', $user->id)
            ->first();
        
        if ($userNetworkRecord) {
            // Calculate new level based on path depth
            $pathParts = explode('.', $userNetworkRecord->path);
            $newLevel = count($pathParts) - 1; // Subtract 1 because path includes the user
            
            DB::table('user_networks')
                ->where('user_id', $user->id)
                ->where('referrer_id', $topReferrer->id)
                ->update([
                    'level' => $newLevel,
                    'updated_at' => now()
                ]);
        }
    }
    
    private function getAllDownlines(User $user): array
    {
        $downlines = [];
        $directDownlines = User::where('referrer_id', $user->id)->get();
        
        foreach ($directDownlines as $downline) {
            $downlines[] = $downline;
            $downlines = array_merge($downlines, $this->getAllDownlines($downline));
        }
        
        return $downlines;
    }
}
