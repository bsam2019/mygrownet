<?php

namespace App\Http\Controllers\PrimeEdge;

use App\Http\Controllers\Controller;
use App\Domain\PrimeEdge\Repositories\ServiceRepositoryInterface;
use App\Domain\PrimeEdge\Entities\Service;
use Inertia\Inertia;

class ServiceController extends Controller
{
    public function __construct(
        private ServiceRepositoryInterface $serviceRepository,
    ) {}

    public function index()
    {
        $services = array_map(
            fn(Service $s) => [
                'id' => $s->id()->toString(),
                'name' => $s->name(),
                'description' => $s->description(),
                'is_active' => $s->isActive(),
            ],
            $this->serviceRepository->findActive()
        );

        return Inertia::render('PrimeEdge/Services/Index', [
            'services' => $services,
        ]);
    }
}
