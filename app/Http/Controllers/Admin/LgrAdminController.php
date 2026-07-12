<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Infrastructure\Persistence\Eloquent\LoyaltyReward\LgrCycleModel;
use App\Infrastructure\Persistence\Eloquent\LoyaltyReward\LgrPoolModel;
use App\Infrastructure\Persistence\Eloquent\LoyaltyReward\LgrQualificationModel;
use App\Infrastructure\Persistence\Eloquent\LoyaltyReward\LgrPayoutModel;
use App\Infrastructure\Persistence\Eloquent\LoyaltyReward\LgrActivityModel;
use App\Infrastructure\Persistence\Eloquent\LoyaltyReward\LgrSettingsModel;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class LgrAdminController extends Controller
{
    public function index(): Response
    {
        $stats = [
            'total_qualified' => LgrQualificationModel::where('fully_qualified', true)->count(),
            'active_cycles' => LgrCycleModel::where('status', 'active')->count(),
            'completed_cycles' => LgrCycleModel::where('status', 'completed')->count(),
            'total_paid_out' => LgrPayoutModel::where('status', 'credited')->sum('lgc_amount'),
            'current_pool_balance' => LgrPoolModel::latest('pool_date')->first()?->available_for_distribution ?? 0,
        ];
        
        // Recent cycles
        $recentCycles = LgrCycleModel::with('user:id,name,email')
            ->latest()
            ->take(10)
            ->get()
            ->map(function ($cycle) {
                return [
                    'id' => $cycle->id,
                    'user' => [
                        'id' => $cycle->user->id,
                        'name' => $cycle->user->name,
                        'email' => $cycle->user->email,
                    ],
                    'status' => $cycle->status,
                    'start_date' => $cycle->start_date,
                    'end_date' => $cycle->end_date,
                    'active_days' => $cycle->active_days,
                    'total_earned_lgc' => $cycle->total_earned_lgc,
                    'completion_rate' => ($cycle->active_days / 70) * 100,
                ];
            });
        
        // Pool history (last 30 days)
        $poolHistory = LgrPoolModel::where('pool_date', '>=', Carbon::now()->subDays(30))
            ->orderBy('pool_date')
            ->get()
            ->map(function ($pool) {
                return [
                    'date' => $pool->pool_date,
                    'contributions' => $pool->contributions,
                    'allocations' => $pool->allocations,
                    'available' => $pool->available_for_distribution,
                ];
            });
        
        return Inertia::render('Admin/LGR/Dashboard', [
            'stats' => $stats,
            'recentCycles' => $recentCycles,
            'poolHistory' => $poolHistory,
        ]);
    }
    
    public function cycles(Request $request): Response
    {
        $query = LgrCycleModel::with('user:id,name,email');
        
        if ($request->status) {
            $query->where('status', $request->status);
        }
        
        if ($request->search) {
            $query->whereHas('user', function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('email', 'like', "%{$request->search}%");
            });
        }
        
        $cycles = $query->latest()
            ->paginate(20)
            ->through(function ($cycle) {
                return [
                    'id' => $cycle->id,
                    'user' => [
                        'id' => $cycle->user->id,
                        'name' => $cycle->user->name,
                        'email' => $cycle->user->email,
                    ],
                    'status' => $cycle->status,
                    'start_date' => $cycle->start_date,
                    'end_date' => $cycle->end_date,
                    'active_days' => $cycle->active_days,
                    'total_earned_lgc' => $cycle->total_earned_lgc,
                    'daily_rate' => $cycle->daily_rate,
                    'completion_rate' => ($cycle->active_days / 70) * 100,
                    'created_at' => $cycle->created_at,
                ];
            });
        
        return Inertia::render('Admin/LGR/Cycles', [
            'cycles' => $cycles,
            'filters' => $request->only(['status', 'search']),
        ]);
    }
    
    public function qualifications(Request $request): Response
    {
        $query = LgrQualificationModel::with('user:id,name,email');
        
        if ($request->has('qualified')) {
            $query->where('fully_qualified', $request->qualified === 'true');
        }
        
        if ($request->search) {
            $query->whereHas('user', function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('email', 'like', "%{$request->search}%");
            });
        }
        
        $qualifications = $query->latest('updated_at')
            ->paginate(20)
            ->through(function ($qual) {
                return [
                    'id' => $qual->id,
                    'user' => [
                        'id' => $qual->user->id,
                        'name' => $qual->user->name,
                        'email' => $qual->user->email,
                    ],
                    'starter_package_completed' => $qual->starter_package_completed,
                    'training_completed' => $qual->training_completed,
                    'first_level_members' => $qual->first_level_members,
                    'network_requirement_met' => $qual->network_requirement_met,
                    'activities_completed' => $qual->activities_completed,
                    'activity_requirement_met' => $qual->activity_requirement_met,
                    'fully_qualified' => $qual->fully_qualified,
                    'fully_qualified_at' => $qual->fully_qualified_at,
                ];
            });
        
        return Inertia::render('Admin/LGR/Qualifications', [
            'qualifications' => $qualifications,
            'filters' => $request->only(['qualified', 'search']),
        ]);
    }
    
    public function pool(): Response
    {
        $currentPool = LgrPoolModel::latest('pool_date')->first();
        
        $poolHistory = LgrPoolModel::orderBy('pool_date', 'desc')
            ->take(30)
            ->get()
            ->map(function ($pool) {
                return [
                    'id' => $pool->id,
                    'pool_date' => $pool->pool_date,
                    'opening_balance' => $pool->opening_balance,
                    'contributions' => $pool->contributions,
                    'allocations' => $pool->allocations,
                    'closing_balance' => $pool->closing_balance,
                    'reserve_amount' => $pool->reserve_amount,
                    'available_for_distribution' => $pool->available_for_distribution,
                ];
            });
        
        $settings = LgrSettingsModel::all()->mapWithKeys(function ($setting) {
            return [$setting->key => $setting->value];
        });
        
        return Inertia::render('Admin/LGR/Pool', [
            'currentPool' => $currentPool,
            'poolHistory' => $poolHistory,
            'settings' => $settings,
        ]);
    }
    
    public function activities(Request $request): Response
    {
        $query = LgrActivityModel::with(['user:id,name,email', 'cycle']);
        
        if ($request->activity_type) {
            $query->where('activity_type', $request->activity_type);
        }
        
        if ($request->date) {
            $query->whereDate('activity_date', $request->date);
        }
        
        if ($request->search) {
            $query->whereHas('user', function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('email', 'like', "%{$request->search}%");
            });
        }
        
        $activities = $query->latest('activity_date')
            ->paginate(50)
            ->through(function ($activity) {
                return [
                    'id' => $activity->id,
                    'user' => [
                        'id' => $activity->user->id,
                        'name' => $activity->user->name,
                        'email' => $activity->user->email,
                    ],
                    'activity_type' => $activity->activity_type,
                    'activity_description' => $activity->activity_description,
                    'activity_date' => $activity->activity_date,
                    'lgc_earned' => $activity->lgc_earned,
                    'verified' => $activity->verified,
                    'created_at' => $activity->created_at,
                ];
            });
        
        return Inertia::render('Admin/LGR/Activities', [
            'activities' => $activities,
            'filters' => $request->only(['activity_type', 'date', 'search']),
        ]);
    }
    
    public function settings(): Response
    {
        $settings = LgrSettingsModel::all()->map(function ($setting) {
            return [
                'id' => $setting->id,
                'key' => $setting->key,
                'value' => $setting->value,
                'type' => $setting->type,
                'description' => $setting->description,
            ];
        });
        
        return Inertia::render('Admin/LGR/Settings', [
            'settings' => $settings,
        ]);
    }
    
    public function updateSettings(Request $request)
    {
        $validated = $request->validate([
            'settings' => 'required|array',
            'settings.*.key' => 'required|string',
            'settings.*.value' => 'required',
        ]);
        
        foreach ($validated['settings'] as $setting) {
            LgrSettingsModel::where('key', $setting['key'])
                ->update(['value' => $setting['value']]);
        }
        
        return back()->with('success', 'LGR settings updated successfully');
    }
}

