<?php

namespace App\Http\Controllers\PrimeEdge;

use App\Http\Controllers\Controller;
use App\Application\PrimeEdge\UseCases\SubmitInquiryUseCase;
use App\Domain\PrimeEdge\Repositories\InquiryRepositoryInterface;
use App\Domain\PrimeEdge\ValueObjects\ClientId;
use App\Application\PrimeEdge\DTOs\InquiryDTO;
use Illuminate\Http\Request;
use Inertia\Inertia;

class InquiryController extends Controller
{
    public function __construct(
        private SubmitInquiryUseCase $submitInquiryUseCase,
        private InquiryRepositoryInterface $inquiryRepository,
    ) {}

    public function index()
    {
        $clientId = auth()->guard('primeedge')->id();
        $inquiries = array_map(
            fn($i) => InquiryDTO::fromEntity($i),
            $this->inquiryRepository->findByClientId(ClientId::fromString($clientId))
        );

        return Inertia::render('PrimeEdge/Inquiries/Index', [
            'inquiries' => $inquiries,
        ]);
    }

    public function create()
    {
        $services = [];
        foreach (config('modules.primeedge.services', []) as $categoryKey => $category) {
            foreach ($category['items'] ?? [] as $item) {
                $services[] = [
                    'id' => $item['type'],
                    'name' => $item['label'],
                ];
            }
        }

        return Inertia::render('PrimeEdge/Inquiries/Create', [
            'services' => $services,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'service_id' => ['nullable', 'string', 'max:100'],
            'description' => ['required', 'string', 'max:5000'],
            'preferred_contact' => ['nullable', 'string', 'in:email,phone'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $this->submitInquiryUseCase->execute(
            clientId: auth()->guard('primeedge')->id(),
            serviceDescription: $validated['description'],
            preferredServiceType: $validated['service_id'] ?? null,
            notes: $validated['notes'] ?? null,
        );

        return redirect()->route('primeedge.inquiries.index')
            ->with('success', 'Your inquiry has been submitted. We will get back to you shortly.');
    }
}
