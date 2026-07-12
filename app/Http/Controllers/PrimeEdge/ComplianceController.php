<?php

namespace App\Http\Controllers\PrimeEdge;

use App\Http\Controllers\Controller;
use App\Domain\PrimeEdge\Repositories\ComplianceTaskRepositoryInterface;
use App\Domain\PrimeEdge\ValueObjects\ClientId;
use App\Application\PrimeEdge\DTOs\ComplianceTaskDTO;
use Inertia\Inertia;

class ComplianceController extends Controller
{
    public function __construct(
        private ComplianceTaskRepositoryInterface $taskRepository,
    ) {}

    public function index()
    {
        $clientId = auth()->guard('primeedge')->id();
        $tasks = array_map(
            fn($t) => ComplianceTaskDTO::fromEntity($t),
            $this->taskRepository->findByClientId(ClientId::fromString($clientId))
        );

        return Inertia::render('PrimeEdge/Compliance/Index', [
            'tasks' => $tasks,
        ]);
    }
}
