<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Package;
use App\Models\Subscription;
use App\Models\User;
use Inertia\Inertia;
use Inertia\Response;

class StarterKitController extends Controller
{
    /**
     * Display starter kit assignments
     */
    public function index(): Response
    {
        $starterKit = Package::where('slug', 'starter-kit-associate')->first();
        
        $assignments = Subscription::with(['user', 'package'])
            ->whereHas('package', function ($query) {
                $query->where('slug', 'starter-kit-associate');
            })
            ->latest()
            ->paginate(20);

        $stats = [
            'total_assigned' => Subscription::whereHas('package', function ($query) {
                $query->where('slug', 'starter-kit-associate');
            })->count(),
            'total_members' => User::count(),
            'assignment_rate' => User::count() > 0 
                ? round((Subscription::whereHas('package', function ($query) {
                    $query->where('slug', 'starter-kit-associate');
                })->count() / User::count()) * 100, 2)
                : 0,
        ];

        return Inertia::render('Admin/StarterKits/Index', [
            'starterKit' => $starterKit,
            'assignments' => $assignments,
            'stats' => $stats,
        ]);
    }
}
