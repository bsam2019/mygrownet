<?php

namespace App\Http\Controllers\GrowFinance;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class SubscriptionController extends Controller
{
    public function settings(Request $request)
    {
        return Inertia::render('GrowFinance/Settings/Subscription', [
            'walletBalance' => 0,
            'currentTier' => 'free',
        ]);
    }

    public function purchase(Request $request)
    {
        return back()->with('success', 'Subscription features are now free.');
    }

    public function upgrade(Request $request)
    {
        return Inertia::render('GrowFinance/Upgrade', [
            'currentTier' => 'free',
            'tiers' => [],
        ]);
    }

    public function checkout(Request $request)
    {
        return Inertia::render('GrowFinance/Checkout', [
            'tier' => 'free',
            'tierName' => 'Free',
            'billing' => 'monthly',
            'price' => 0,
            'currency' => 'ZMW',
            'features' => [],
            'limits' => [],
        ]);
    }

    public function subscribe(Request $request)
    {
        return redirect()->route('growfinance.dashboard')
            ->with('success', 'All features are now available for free.');
    }

    public function usage(Request $request)
    {
        return response()->json([
            'tier' => 'free',
            'transactions' => 0,
            'invoices' => 0,
            'customers' => 0,
            'vendors' => 0,
            'storage' => 0,
        ]);
    }

    public function cancel(Request $request)
    {
        return redirect()->route('growfinance.dashboard')
            ->with('success', 'Subscription cancelled.');
    }
}
