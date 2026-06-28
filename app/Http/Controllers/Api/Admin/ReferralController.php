<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Services\ReferralService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReferralController extends Controller
{
    protected $referralService;

    public function __construct(ReferralService $referralService)
    {
        $this->referralService = $referralService;
        $this->middleware('admin');
    }

    public function stats()
    {
        $stats = $this->referralService->getAdminReferralStats();

        return response()->json($stats);
    }

    public function topReferrers()
    {
        $topReferrers = $this->referralService->getTopReferrers();

        return response()->json($topReferrers);
    }

    public function monthlyStats()
    {
        $monthlyStats = $this->referralService->getMonthlyReferralStats();

        return response()->json($monthlyStats);
    }

    public function processCommissions()
    {
        $result = $this->referralService->processPendingCommissions();

        return response()->json([
            'message' => 'Commissions processed successfully',
            'processed_count' => $result['processed_count'],
            'total_amount' => $result['total_amount']
        ]);
    }
} 