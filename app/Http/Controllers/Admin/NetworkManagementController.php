<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\NetworkChangeHistory;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class NetworkManagementController extends Controller
{
    public function index()
    {
        return Inertia::render('Admin/NetworkManagement/Index');
    }
    
    public function searchUsers(Request $request)
    {
        $query = $request->input('query');
        
        $users = User::where('name', 'like', "%{$query}%")
            ->orWhere('email', 'like', "%{$query}%")
            ->orWhere('phone', 'like', "%{$query}%")
            ->limit(20)
            ->get(['id', 'name', 'email', 'phone', 'referrer_id']);
        
        return response()->json($users);
    }
    
    public function getUserNetwork(Request $request, int $userId)
    {
        $user = User::findOrFail($userId);
        $depth = $request->input('depth', 3);
        
        $tree = $this->buildNetworkTree($user, 1, $depth);
        
        $referrer = $user->referrer_id ? User::find($user->referrer_id) : null;
        
        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'referrer' => $referrer ? [
                    'id' => $referrer->id,
                    'name' => $referrer->name,
                    'email' => $referrer->email
                ] : null
            ],
            'tree' => $tree
        ]);
    }
    
    public function checkMove(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'new_referrer_id' => 'required|exists:users,id'
        ]);
        
        $newReferrer = User::findOrFail($request->new_referrer_id);
        $directDownlineCount = User::where('referrer_id', $newReferrer->id)->count();
        
        // Find the actual placement position using spillover logic
        $actualReferrerId = User::findMatrixPlacement($newReferrer->id);
        $actualReferrer = User::find($actualReferrerId);
        
        $message = '';
        if ($actualReferrerId === $newReferrer->id) {
            $message = "Will be placed as direct downline of {$newReferrer->name}";
        } else {
            $message = "Matrix full. Will be placed under {$actualReferrer->name} (in {$newReferrer->name}'s network)";
        }
        
        return response()->json([
            'allowed' => true,
            'current_count' => $directDownlineCount,
            'actual_referrer_id' => $actualReferrerId,
            'actual_referrer_name' => $actualReferrer->name,
            'message' => $message,
            'is_spillover' => $actualReferrerId !== $newReferrer->id
        ]);
    }
    
    public function moveUser(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'new_referrer_id' => 'required|exists:users,id',
            'move_entire_tree' => 'boolean'
        ]);
        
        try {
            $user = User::findOrFail($request->user_id);
            $targetReferrer = User::findOrFail($request->new_referrer_id);
            
            if ($user->id === $targetReferrer->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot move user to themselves'
                ], 422);
            }
            
            if ($this->isInDownline($user, $targetReferrer)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot move user to their own downline (circular reference)'
                ], 422);
            }
            
            // Find actual placement using spillover logic
            $actualReferrerId = User::findMatrixPlacement($targetReferrer->id);
            $actualReferrer = User::find($actualReferrerId);
            
            // Store old referrer for history
            $oldReferrerId = $user->referrer_id;
            $oldReferrer = $oldReferrerId ? User::find($oldReferrerId) : null;
            
            DB::transaction(function () use ($user, $actualReferrer, $targetReferrer, $oldReferrerId, $actualReferrerId, $request) {
                $user->referrer_id = $actualReferrer->id;
                $user->save();
                
                $this->updateUserNetworkRecord($user, $targetReferrer);
                $user->updateNetworkPath();
                
                // Log the change
                NetworkChangeHistory::create([
                    'user_id' => $user->id,
                    'old_referrer_id' => $oldReferrerId,
                    'new_referrer_id' => $actualReferrer->id,
                    'target_referrer_id' => $targetReferrer->id,
                    'performed_by' => auth()->id(),
                    'is_spillover' => $actualReferrerId !== $targetReferrer->id,
                    'reason' => $request->input('reason'),
                    'metadata' => [
                        'old_referrer_name' => $oldReferrerId ? User::find($oldReferrerId)->name : null,
                        'new_referrer_name' => $actualReferrer->name,
                        'target_referrer_name' => $targetReferrer->name,
                        'move_entire_tree' => $request->boolean('move_entire_tree', false),
                        'ip_address' => $request->ip(),
                        'user_agent' => $request->userAgent(),
                    ]
                ]);
            });
            
            $message = $actualReferrerId === $targetReferrer->id
                ? "User successfully moved as direct downline of {$targetReferrer->name}"
                : "User successfully placed under {$actualReferrer->name} (spillover in {$targetReferrer->name}'s network)";
            
            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => [
                    'user' => ['id' => $user->id, 'name' => $user->name],
                    'target_referrer' => ['id' => $targetReferrer->id, 'name' => $targetReferrer->name],
                    'actual_referrer' => ['id' => $actualReferrer->id, 'name' => $actualReferrer->name],
                    'is_spillover' => $actualReferrerId !== $targetReferrer->id
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to move user: ' . $e->getMessage()
            ], 500);
        }
    }
    
    public function getNetworkStats(int $userId)
    {
        $directDownlines = User::where('referrer_id', $userId)->count();
        $totalNetwork = DB::table('user_networks')->where('referrer_id', $userId)->count();
        
        return response()->json([
            'direct_downlines' => $directDownlines,
            'total_network' => $totalNetwork,
            'matrix_slots_available' => max(0, 3 - $directDownlines)
        ]);
    }
    
    /**
     * Get network change history
     */
    public function getHistory(Request $request)
    {
        $query = NetworkChangeHistory::with([
            'user:id,name,email',
            'oldReferrer:id,name,email',
            'newReferrer:id,name,email',
            'targetReferrer:id,name,email',
            'performedBy:id,name,email'
        ])->orderBy('created_at', 'desc');
        
        // Filter by user if provided
        if ($request->has('user_id')) {
            $query->where('user_id', $request->user_id);
        }
        
        // Filter by date range if provided
        if ($request->has('from_date')) {
            $query->whereDate('created_at', '>=', $request->from_date);
        }
        
        if ($request->has('to_date')) {
            $query->whereDate('created_at', '<=', $request->to_date);
        }
        
        $history = $query->paginate(50);
        
        return response()->json($history);
    }
    
    /**
     * Get user's network change history
     */
    public function getUserHistory(int $userId)
    {
        $history = NetworkChangeHistory::with([
            'oldReferrer:id,name,email',
            'newReferrer:id,name,email',
            'targetReferrer:id,name,email',
            'performedBy:id,name,email'
        ])
        ->where('user_id', $userId)
        ->orderBy('created_at', 'desc')
        ->limit(20)
        ->get();
        
        return response()->json($history);
    }
    
    private function buildNetworkTree(User $user, int $currentDepth, int $maxDepth): array
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
                $node['children'][] = $this->buildNetworkTree($downline, $currentDepth + 1, $maxDepth);
            }
        }
        
        return $node;
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
    
    private function updateUserNetworkRecord(User $user, User $newReferrer): void
    {
        $referrerNetworkRecord = DB::table('user_networks')
            ->where('user_id', $newReferrer->id)
            ->first();
        
        $referrerPath = $referrerNetworkRecord ? $referrerNetworkRecord->path : (string)$newReferrer->id;
        $newPath = $referrerPath . '.' . $user->id;
        
        DB::table('user_networks')->where('user_id', $user->id)->delete();
        
        DB::table('user_networks')->insert([
            'user_id' => $user->id,
            'referrer_id' => $newReferrer->id,
            'level' => 1,
            'path' => $newPath,
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }
}
