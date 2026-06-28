<?php

namespace App\Application\Network\UseCases;

use App\Domain\Network\Services\NetworkReorganizationService;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class MoveUserToNewReferrerUseCase
{
    public function __construct(
        private NetworkReorganizationService $networkService
    ) {}
    
    public function execute(int $userId, int $newReferrerId, bool $moveEntireTree = false): array
    {
        try {
            $user = User::findOrFail($userId);
            $newReferrer = User::findOrFail($newReferrerId);
            
            // Validate the move
            if ($user->id === $newReferrer->id) {
                return [
                    'success' => false,
                    'message' => 'Cannot move user to themselves'
                ];
            }
            
            // Check if new referrer is in the user's downline (would create circular reference)
            if ($this->isInDownline($user, $newReferrer)) {
                return [
                    'success' => false,
                    'message' => 'Cannot move user to their own downline (circular reference)'
                ];
            }
            
            // Check matrix constraints
            $canMove = $this->networkService->canMoveToReferrer($newReferrer);
            if (!$canMove['allowed']) {
                return [
                    'success' => false,
                    'message' => $canMove['reason']
                ];
            }
            
            // Perform the move
            if ($moveEntireTree) {
                $this->networkService->moveUserTreeToNewReferrer($user, $newReferrer);
            } else {
                $this->networkService->moveUserToNewReferrer($user, $newReferrer);
            }
            
            Log::info('User moved to new referrer', [
                'user_id' => $userId,
                'user_name' => $user->name,
                'new_referrer_id' => $newReferrerId,
                'new_referrer_name' => $newReferrer->name,
                'move_entire_tree' => $moveEntireTree
            ]);
            
            return [
                'success' => true,
                'message' => 'User successfully moved to new referrer',
                'data' => [
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name
                    ],
                    'new_referrer' => [
                        'id' => $newReferrer->id,
                        'name' => $newReferrer->name
                    ]
                ]
            ];
            
        } catch (\Exception $e) {
            Log::error('Failed to move user to new referrer', [
                'user_id' => $userId,
                'new_referrer_id' => $newReferrerId,
                'error' => $e->getMessage()
            ]);
            
            return [
                'success' => false,
                'message' => 'Failed to move user: ' . $e->getMessage()
            ];
        }
    }
    
    private function isInDownline(User $user, User $potentialDownline): bool
    {
        $downlines = User::where('referrer_id', $user->id)->get();
        
        foreach ($downlines as $downline) {
            if ($downline->id === $potentialDownline->id) {
                return true;
            }
            
            if ($this->isInDownline($downline, $potentialDownline)) {
                return true;
            }
        }
        
        return false;
    }
}
