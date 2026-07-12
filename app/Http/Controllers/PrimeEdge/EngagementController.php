<?php

namespace App\Http\Controllers\PrimeEdge;

use App\Http\Controllers\Controller;
use App\Domain\PrimeEdge\Repositories\EngagementRepositoryInterface;
use App\Domain\PrimeEdge\ValueObjects\ClientId;
use App\Application\PrimeEdge\DTOs\EngagementDTO;
use Inertia\Inertia;

class EngagementController extends Controller
{
    public function __construct(
        private EngagementRepositoryInterface $engagementRepository,
    ) {}

    public function index()
    {
        $clientId = auth()->guard('primeedge')->id();
        $engagements = array_map(
            fn($e) => EngagementDTO::fromEntity($e),
            $this->engagementRepository->findByClientId(ClientId::fromString($clientId))
        );

        return Inertia::render('PrimeEdge/Engagements/Index', [
            'engagements' => $engagements,
        ]);
    }

    public function show(string $id)
    {
        $engagementId = \App\Domain\PrimeEdge\ValueObjects\EngagementId::fromString($id);
        $engagement = $this->engagementRepository->findById($engagementId);

        if (!$engagement || $engagement->clientId()->toString() !== auth()->guard('primeedge')->id()) {
            abort(404);
        }

        return Inertia::render('PrimeEdge/Engagements/Show', [
            'engagement' => EngagementDTO::fromEntity($engagement),
        ]);
    }
}
