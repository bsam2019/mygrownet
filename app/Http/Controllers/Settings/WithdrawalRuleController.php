<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class WithdrawalRuleController extends Controller
{
    public function updateLockInPeriod(Request $request)
    {
        // Update system-wide lock-in period settings
    }

    public function updatePenaltyRates(Request $request)
    {
        // Update early withdrawal penalty rates
    }

    public function updateWithdrawalLimits(Request $request)
    {
        // Update partial withdrawal limits
    }

    public function getWithdrawalPolicyConfig()
    {
        // Get current withdrawal policy configuration
    }

    public function toggleEmergencyWithdrawals(Request $request)
    {
        // Enable/disable emergency withdrawal feature
    }
}