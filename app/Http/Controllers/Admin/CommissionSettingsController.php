<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\CommissionSettingsService;
use App\Models\ReferralCommission;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CommissionSettingsController extends Controller
{
    public function __construct(
        private readonly CommissionSettingsService $commissionSettings
    ) {}

    /**
     * Display commission settings page
     */
    public function index(): Response
    {
        $settings = $this->commissionSettings->getAllSettings();
        
        // Get commission statistics
        $stats = $this->getCommissionStats();
        
        // Get recent commissions for preview
        $recentCommissions = ReferralCommission::with(['referrer:id,name,email,has_starter_kit', 'referee:id,name'])
            ->latest()
            ->take(10)
            ->get()
            ->map(fn($c) => [
                'id' => $c->id,
                'referrer' => [
                    'name' => $c->referrer?->name,
                    'has_kit' => $c->referrer?->has_starter_kit,
                ],
                'referee' => $c->referee?->name,
                'level' => $c->level,
                'package_amount' => $c->package_amount,
                'commission_base_amount' => $c->commission_base_amount,
                'amount' => $c->amount,
                'non_kit_multiplier' => $c->non_kit_multiplier,
                'referrer_has_kit' => $c->referrer_has_kit,
                'status' => $c->status,
                'created_at' => $c->created_at->format('Y-m-d H:i'),
            ]);

        return Inertia::render('Admin/CommissionSettings', [
            'settings' => $settings,
            'stats' => $stats,
            'recentCommissions' => $recentCommissions,
            'levelNames' => ReferralCommission::LEVEL_NAMES,
        ]);
    }

    /**
     * Update commission settings
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'base_percentage' => 'required|numeric|min:1|max:100',
            'non_kit_multiplier_percentage' => 'required|numeric|min:0|max:100',
            'level_rates' => 'required|array',
            'level_rates.1' => 'required|numeric|min:0|max:50',
            'level_rates.2' => 'required|numeric|min:0|max:50',
            'level_rates.3' => 'required|numeric|min:0|max:50',
            'level_rates.4' => 'required|numeric|min:0|max:50',
            'level_rates.5' => 'required|numeric|min:0|max:50',
            'level_rates.6' => 'required|numeric|min:0|max:50',
            'level_rates.7' => 'required|numeric|min:0|max:50',
            'enabled' => 'required|boolean',
        ]);

        // Validate total payout doesn't exceed 100%
        $totalPayout = array_sum($validated['level_rates']);
        if ($totalPayout > 100) {
            return back()->withErrors([
                'level_rates' => 'Total commission rates cannot exceed 100%. Current total: ' . $totalPayout . '%'
            ]);
        }

        $this->commissionSettings->updateSettings($validated);

        return back()->with('success', 'Commission settings updated successfully');
    }

    /**
     * Preview commission calculation
     */
    public function preview(Request $request)
    {
        $validated = $request->validate([
            'purchase_amount' => 'required|numeric|min:1',
            'referrer_has_kit' => 'required|boolean',
        ]);

        $calculations = [];
        for ($level = 1; $level <= 7; $level++) {
            $calculations[$level] = $this->commissionSettings->calculateCommission(
                $validated['purchase_amount'],
                $level,
                $validated['referrer_has_kit']
            );
        }

        $totalPayout = array_sum(array_column($calculations, 'commission_amount'));
        $effectivePayoutPercentage = ($totalPayout / $validated['purchase_amount']) * 100;

        return response()->json([
            'calculations' => $calculations,
            'total_payout' => round($totalPayout, 2),
            'effective_payout_percentage' => round($effectivePayoutPercentage, 2),
            'base_amount' => $this->commissionSettings->calculateBaseAmount($validated['purchase_amount']),
        ]);
    }

    /**
     * Get commission statistics
     */
    private function getCommissionStats(): array
    {
        $thisMonth = now()->startOfMonth();
        $lastMonth = now()->subMonth()->startOfMonth();

        return [
            'total_paid_this_month' => ReferralCommission::where('status', 'paid')
                ->where('created_at', '>=', $thisMonth)
                ->sum('amount'),
            'total_paid_last_month' => ReferralCommission::where('status', 'paid')
                ->whereBetween('created_at', [$lastMonth, $thisMonth])
                ->sum('amount'),
            'commissions_this_month' => ReferralCommission::where('created_at', '>=', $thisMonth)->count(),
            'pending_commissions' => ReferralCommission::where('status', 'pending')->count(),
            'non_kit_commissions_this_month' => ReferralCommission::where('created_at', '>=', $thisMonth)
                ->where('referrer_has_kit', false)
                ->count(),
            'average_commission' => ReferralCommission::where('status', 'paid')
                ->where('created_at', '>=', $thisMonth)
                ->avg('amount') ?? 0,
        ];
    }
}
