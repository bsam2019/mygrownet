<?php

namespace App\Http\Controllers\PrimeEdge;

use App\Http\Controllers\Controller;
use App\Application\PrimeEdge\UseCases\GenerateDashboardUseCase;
use Inertia\Inertia;

class DashboardController extends Controller
{
    public function __construct(
        private GenerateDashboardUseCase $dashboardUseCase,
    ) {}

    public function index()
    {
        $clientId = auth()->guard('primeedge')->id();
        $dashboard = $this->dashboardUseCase->execute($clientId);

        return Inertia::render('PrimeEdge/Dashboard', [
            'stats' => [
                'activeEngagements' => count($dashboard->activeEngagements),
                'pendingInquiries' => $dashboard->pendingInquiries,
                'upcomingDeadlines' => count($dashboard->upcomingTasks) + $dashboard->overdueTasks,
                'unpaidInvoices' => count(array_filter($dashboard->recentInvoices, fn($i) => $i->status !== 'paid')),
            ],
            'recentInvoices' => array_map(fn($i) => [
                'id' => $i->id,
                'number' => $i->number,
                'status' => $i->status,
                'total' => $i->total,
                'createdAt' => $i->createdAt,
            ], array_slice($dashboard->recentInvoices, 0, 5)),
            'upcomingAppointments' => array_map(fn($a) => [
                'id' => $a->id,
                'title' => $a->title,
                'scheduledAt' => $a->scheduledAt,
                'durationMinutes' => $a->durationMinutes,
                'status' => $a->status,
            ], array_slice($dashboard->upcomingAppointments, 0, 5)),
        ]);
    }
}