<?php

namespace App\Application\PrimeEdge\DTOs;

class DashboardDTO
{
    public function __construct(
        public readonly array $activeEngagements,
        public readonly array $upcomingTasks,
        public readonly array $recentInvoices,
        public readonly array $upcomingAppointments,
        public readonly int $overdueTasks,
        public readonly int $pendingInquiries,
    ) {}
}
