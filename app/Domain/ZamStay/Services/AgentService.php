<?php

namespace App\Domain\ZamStay\Services;

use App\Domain\ZamStay\Entities\Agent;
use App\Domain\ZamStay\Exceptions\ZamStayException;
use App\Domain\ZamStay\Repositories\AgentRepositoryInterface;
use App\Domain\ZamStay\Repositories\BookingRepositoryInterface;

class AgentService
{
    public function __construct(
        private readonly AgentRepositoryInterface $agentRepo,
        private readonly BookingRepositoryInterface $bookingRepo,
    ) {}

    public function findById(int $id): ?Agent
    {
        return $this->agentRepo->findById($id);
    }

    public function findByUser(int $userId): ?Agent
    {
        return $this->agentRepo->findByUser($userId);
    }

    public function findOrFailByUser(int $userId): Agent
    {
        $agent = $this->findByUser($userId);
        if (!$agent) {
            throw ZamStayException::notFound('Agent profile');
        }
        return $agent;
    }

    public function register(int $userId, array $data): Agent
    {
        if ($this->agentRepo->existsByUser($userId)) {
            throw ZamStayException::invalidOperation('You are already registered as an agent.');
        }

        $agent = Agent::reconstitute(array_merge($data, [
            'user_id' => $userId,
            'commission_rate' => 0,
            'is_approved' => false,
        ]));

        return $this->agentRepo->save($agent);
    }

    public function findAllApproved(): array
    {
        return $this->agentRepo->findAllApproved();
    }

    public function getDashboard(int $agentId): array
    {
        $agent = $this->agentRepo->findById($agentId);
        $bookings = $this->bookingRepo->findByAgent($agentId);

        return [
            'agent' => $agent,
            'bookings' => $bookings,
            'stats' => [
                'total_bookings' => count($bookings),
                'confirmed_bookings' => count(array_filter($bookings, fn($b) => $b->status === 'confirmed')),
                'pending_bookings' => count(array_filter($bookings, fn($b) => $b->status === 'pending')),
                'commission_rate' => $agent?->commissionRate ?? 0,
            ],
        ];
    }
}
