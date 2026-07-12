<?php

namespace App\Application\PrimeEdge\UseCases;

use App\Application\PrimeEdge\DTOs\DashboardDTO;
use App\Application\PrimeEdge\DTOs\EngagementDTO;
use App\Application\PrimeEdge\DTOs\ComplianceTaskDTO;
use App\Application\PrimeEdge\DTOs\InvoiceDTO;
use App\Domain\PrimeEdge\ValueObjects\ClientId;
use App\Domain\PrimeEdge\Repositories\EngagementRepositoryInterface;
use App\Domain\PrimeEdge\Repositories\ComplianceTaskRepositoryInterface;
use App\Domain\PrimeEdge\Repositories\InvoiceRepositoryInterface;
use App\Domain\PrimeEdge\Repositories\AppointmentRepositoryInterface;
use App\Domain\PrimeEdge\Repositories\InquiryRepositoryInterface;

class GenerateDashboardUseCase
{
    public function __construct(
        private EngagementRepositoryInterface $engagementRepository,
        private ComplianceTaskRepositoryInterface $complianceTaskRepository,
        private InvoiceRepositoryInterface $invoiceRepository,
        private AppointmentRepositoryInterface $appointmentRepository,
        private InquiryRepositoryInterface $inquiryRepository,
    ) {}

    public function execute(string $clientId): DashboardDTO
    {
        $clientIdVo = ClientId::fromString($clientId);

        $engagements = array_map(
            fn($e) => EngagementDTO::fromEntity($e),
            $this->engagementRepository->findByClientId($clientIdVo)
        );

        $tasks = array_map(
            fn($t) => ComplianceTaskDTO::fromEntity($t),
            $this->complianceTaskRepository->findByClientId($clientIdVo)
        );

        $invoices = array_map(
            fn($i) => InvoiceDTO::fromEntity($i),
            $this->invoiceRepository->findByClientId($clientIdVo)
        );

        $appointments = $this->appointmentRepository->findByClientId($clientIdVo);

        $overdueTasks = count(array_filter($tasks, fn($t) => $t->isOverdue));
        $pendingInquiries = count($this->inquiryRepository->findByClientId($clientIdVo));

        return new DashboardDTO(
            activeEngagements: array_filter($engagements, fn($e) => in_array($e->status, ['pending', 'in_progress'])),
            upcomingTasks: array_filter($tasks, fn($t) => !$t->isOverdue && $t->status === 'pending'),
            recentInvoices: array_slice($invoices, 0, 5),
            upcomingAppointments: $appointments,
            overdueTasks: $overdueTasks,
            pendingInquiries: $pendingInquiries,
        );
    }
}
