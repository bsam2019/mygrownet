<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Domain\Investor\Services\InvestorAnalyticsService;
use Inertia\Inertia;

class InvestorAnalyticsController extends Controller
{
    public function __construct(
        private readonly InvestorAnalyticsService $analyticsService
    ) {}

    public function index()
    {
        $analytics = $this->analyticsService->getAllAnalytics();

        return Inertia::render('Admin/Investor/Analytics/Index', $analytics);
    }
}
