<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Admin\PlatformAdminMetricsService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AdminDashboardController extends Controller
{
    public function __construct(
        protected PlatformAdminMetricsService $metricsService
    ) {}

    public function index(Request $request)
    {
        // Check if user is admin
        if (!auth()->user() || !auth()->user()->is_admin) {
            abort(403, 'Unauthorized. Administrator access required.');
        }

        $data = $this->metricsService->getDashboardData();

        return Inertia::render('Admin/Dashboard/Index', $data);
    }
}

