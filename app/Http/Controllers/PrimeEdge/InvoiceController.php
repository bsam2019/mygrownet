<?php

namespace App\Http\Controllers\PrimeEdge;

use App\Http\Controllers\Controller;
use App\Domain\PrimeEdge\Repositories\InvoiceRepositoryInterface;
use App\Domain\PrimeEdge\ValueObjects\ClientId;
use App\Application\PrimeEdge\DTOs\InvoiceDTO;
use Inertia\Inertia;

class InvoiceController extends Controller
{
    public function __construct(
        private InvoiceRepositoryInterface $invoiceRepository,
    ) {}

    public function index()
    {
        $clientId = auth()->guard('primeedge')->id();
        $invoices = array_map(
            fn($i) => InvoiceDTO::fromEntity($i),
            $this->invoiceRepository->findByClientId(ClientId::fromString($clientId))
        );

        return Inertia::render('PrimeEdge/Invoices/Index', [
            'invoices' => $invoices,
        ]);
    }

    public function show(string $id)
    {
        $invoiceId = \App\Domain\PrimeEdge\ValueObjects\InvoiceId::fromString($id);
        $invoice = $this->invoiceRepository->findById($invoiceId);

        if (!$invoice || $invoice->clientId()->toString() !== auth()->guard('primeedge')->id()) {
            abort(404);
        }

        return Inertia::render('PrimeEdge/Invoices/Show', [
            'invoice' => InvoiceDTO::fromEntity($invoice),
        ]);
    }
}
