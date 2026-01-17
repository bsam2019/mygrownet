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
        $recentCycles = LgrCycleModel::with('user:id,name,email,starter_kit_tier')
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
                        'tier' => $cycle->user->starter_kit_tier,
                    ],
                    'tier' => $cycle->tier,
                    'status' => $cycle->status,
                    'start_date' => $cycle->start_date,
                    'end_date' => $cycle->end_date,
                    'active_days' => $cycle->active_days,
                    'total_earned_lgc' => $cycle->total_earned_lgc,
                    'daily_rate' => $cycle->daily_rate,
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
        $query = LgrCycleModel::with('user:id,name,email,starter_kit_tier');
        
        if ($request->status) {
            $query->where('status', $request->status);
        }
        
        if ($request->tier) {
            $query->where('tier', $request->tier);
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
                        'tier' => $cycle->user->starter_kit_tier,
                    ],
                    'tier' => $cycle->tier,
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
            'filters' => $request->only(['status', 'tier', 'search']),
        ]);
    }
    
    public function qualifications(Request $request): Response
    {
        $query = LgrQualificationModel::with('user:id,name,email,starter_kit_tier');
        
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
                        'tier' => $qual->user->starter_kit_tier,
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
        $query = LgrActivityModel::with(['user:id,name,email,starter_kit_tier', 'cycle']);
        
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
                        'tier' => $activity->user->starter_kit_tier,
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
        $allSettings = LgrSettingsModel::all();
        
        // Group settings by their group field
        $groupedSettings = [
            'general' => [],
            'withdrawal' => [],
            'transfer' => [],
            'awards' => [],
        ];
        
        foreach ($allSettings as $setting) {
            $group = $setting->group ?? 'general';
            if (!isset($groupedSettings[$group])) {
                $groupedSettings[$group] = [];
            }
            $groupedSettings[$group][] = [
                'id' => $setting->id,
                'key' => $setting->key,
                'value' => $this->castSettingValue($setting->value, $setting->type),
                'type' => $setting->type ?? 'string',
                'label' => $setting->label ?? $setting->key,
                'description' => $setting->description ?? '',
            ];
        }
        
        // Get current tier rates from domain entity (these are constants, but we'll make them configurable)
        $tierRates = [
            'lite' => [
                'daily_rate' => \App\Domain\LoyaltyReward\Entities\LgrCycle::TIER_DAILY_RATES['lite'] ?? 12.50,
                'max_earnings' => \App\Domain\LoyaltyReward\Entities\LgrCycle::TIER_MAX_EARNINGS['lite'] ?? 875.00,
                'cycle_days' => 70,
            ],
            'basic' => [
                'daily_rate' => \App\Domain\LoyaltyReward\Entities\LgrCycle::TIER_DAILY_RATES['basic'] ?? 25.00,
                'max_earnings' => \App\Domain\LoyaltyReward\Entities\LgrCycle::TIER_MAX_EARNINGS['basic'] ?? 1750.00,
                'cycle_days' => 70,
            ],
            'growth_plus' => [
                'daily_rate' => \App\Domain\LoyaltyReward\Entities\LgrCycle::TIER_DAILY_RATES['growth_plus'] ?? 37.50,
                'max_earnings' => \App\Domain\LoyaltyReward\Entities\LgrCycle::TIER_MAX_EARNINGS['growth_plus'] ?? 2625.00,
                'cycle_days' => 70,
            ],
            'pro' => [
                'daily_rate' => \App\Domain\LoyaltyReward\Entities\LgrCycle::TIER_DAILY_RATES['pro'] ?? 62.50,
                'max_earnings' => \App\Domain\LoyaltyReward\Entities\LgrCycle::TIER_MAX_EARNINGS['pro'] ?? 4375.00,
                'cycle_days' => 70,
            ],
        ];
        
        // Check for database overrides
        $dbTierRates = LgrSettingsModel::where('key', 'like', 'tier_%_daily_rate')
            ->orWhere('key', 'like', 'tier_%_max_earnings')
            ->orWhere('key', 'like', 'tier_%_cycle_days')
            ->get()
            ->keyBy('key');
        
        foreach (['lite', 'basic', 'growth_plus', 'pro'] as $tier) {
            if ($dbTierRates->has("tier_{$tier}_daily_rate")) {
                $tierRates[$tier]['daily_rate'] = (float) $dbTierRates->get("tier_{$tier}_daily_rate")->value;
            }
            if ($dbTierRates->has("tier_{$tier}_max_earnings")) {
                $tierRates[$tier]['max_earnings'] = (float) $dbTierRates->get("tier_{$tier}_max_earnings")->value;
            }
            if ($dbTierRates->has("tier_{$tier}_cycle_days")) {
                $tierRates[$tier]['cycle_days'] = (int) $dbTierRates->get("tier_{$tier}_cycle_days")->value;
            }
        }
        
        return Inertia::render('Admin/LGR/Settings', [
            'settings' => $groupedSettings,
            'tierRates' => $tierRates,
        ]);
    }
    
    private function castSettingValue($value, $type)
    {
        return match ($type) {
            'boolean' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            'integer' => (int) $value,
            'decimal' => (float) $value,
            default => $value,
        };
    }
    
    public function updateSettings(Request $request)
    {
        $validated = $request->validate([
            'settings' => 'nullable|array',
            'settings.*.key' => 'required|string',
            'settings.*.value' => 'required',
            'tierRates' => 'nullable|array',
            'tierRates.*.daily_rate' => 'nullable|numeric|min:0',
            'tierRates.*.max_earnings' => 'nullable|numeric|min:0',
            'tierRates.*.cycle_days' => 'nullable|integer|min:1|max:365',
        ]);
        
        // Update general settings
        if (!empty($validated['settings'])) {
            foreach ($validated['settings'] as $setting) {
                LgrSettingsModel::where('key', $setting['key'])
                    ->update(['value' => $setting['value']]);
            }
        }
        
        // Update tier rates
        if (!empty($validated['tierRates'])) {
            foreach ($validated['tierRates'] as $tier => $rates) {
                // Daily rate
                if (isset($rates['daily_rate'])) {
                    LgrSettingsModel::updateOrCreate(
                        ['key' => "tier_{$tier}_daily_rate"],
                        [
                            'value' => (string) $rates['daily_rate'],
                            'type' => 'decimal',
                            'group' => 'tier_rates',
                            'label' => ucfirst(str_replace('_', ' ', $tier)) . ' Daily Rate',
                            'description' => "Daily LGR rate for {$tier} tier members",
                        ]
                    );
                }
                
                // Max earnings
                if (isset($rates['max_earnings'])) {
                    LgrSettingsModel::updateOrCreate(
                        ['key' => "tier_{$tier}_max_earnings"],
                        [
                            'value' => (string) $rates['max_earnings'],
                            'type' => 'decimal',
                            'group' => 'tier_rates',
                            'label' => ucfirst(str_replace('_', ' ', $tier)) . ' Max Earnings',
                            'description' => "Maximum LGR earnings per cycle for {$tier} tier",
                        ]
                    );
                }
                
                // Cycle days
                if (isset($rates['cycle_days'])) {
                    LgrSettingsModel::updateOrCreate(
                        ['key' => "tier_{$tier}_cycle_days"],
                        [
                            'value' => (string) $rates['cycle_days'],
                            'type' => 'integer',
                            'group' => 'tier_rates',
                            'label' => ucfirst(str_replace('_', ' ', $tier)) . ' Cycle Days',
                            'description' => "LGR cycle duration in days for {$tier} tier",
                        ]
                    );
                }
            }
        }
        
        return back()->with('success', 'LGR settings updated successfully');
    }
}

