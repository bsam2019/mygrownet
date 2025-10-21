<?php

namespace App\Http\Controllers\MyGrowNet;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class StarterKitController extends Controller
{
    /**
     * Display member's starter kit information
     */
    public function show(Request $request): Response
    {
        $user = $request->user();
        
        // Get starter kit subscription
        $starterKit = \App\Models\Subscription::with('package')
            ->where('user_id', $user->id)
            ->whereHas('package', function ($query) {
                $query->where('slug', 'starter-kit-associate');
            })
            ->first();
        
        $starterKitInfo = null;
        if ($starterKit) {
            $starterKitInfo = [
                'received' => true,
                'package_name' => $starterKit->package->name,
                'package_description' => $starterKit->package->description,
                'received_date' => $starterKit->created_at->format('M j, Y'),
                'features' => $starterKit->package->features,
                'status' => $starterKit->status,
                'amount' => $starterKit->amount,
                'start_date' => $starterKit->start_date?->format('M j, Y'),
                'end_date' => $starterKit->end_date?->format('M j, Y'),
            ];
        }
        
        // Get initial points awarded
        $initialPoints = \App\Models\PointTransaction::where('user_id', $user->id)
            ->where('source', 'registration')
            ->first();
        
        return Inertia::render('MyGrowNet/StarterKit', [
            'starterKit' => $starterKitInfo,
            'initialPoints' => $initialPoints ? [
                'lp_amount' => $initialPoints->lp_amount,
                'map_amount' => $initialPoints->map_amount,
                'awarded_at' => $initialPoints->created_at->format('M j, Y'),
            ] : null,
            'user' => [
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'joined_at' => $user->created_at->format('M j, Y'),
            ],
        ]);
    }
}
