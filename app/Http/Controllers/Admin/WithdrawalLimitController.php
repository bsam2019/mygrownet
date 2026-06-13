<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\VerificationLimitService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class WithdrawalLimitController extends Controller
{
    public function __construct(
        private VerificationLimitService $limitService
    ) {}

    public function index()
    {
        return Inertia::render('Admin/WithdrawalLimits', [
            'limits' => $this->limitService->getAllLimits(),
        ]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'limits' => 'required|array',
            'limits.*.daily_zmw' => 'required|numeric|min:0',
            'limits.*.daily_usd' => 'required|numeric|min:0',
            'limits.*.monthly_zmw' => 'required|numeric|min:0',
            'limits.*.monthly_usd' => 'required|numeric|min:0',
            'limits.*.single_zmw' => 'required|numeric|min:0',
            'limits.*.single_usd' => 'required|numeric|min:0',
        ]);

        $this->limitService->updateLimits($validated['limits']);

        return back()->with('success', 'Withdrawal limits updated successfully.');
    }
}
