<?php

namespace App\Domain\PrimeEdge\Services;

use App\Domain\PrimeEdge\Entities\Invoice;
use App\Domain\PrimeEdge\Entities\InvoiceLineItem;
use App\Domain\PrimeEdge\ValueObjects\InvoiceId;
use App\Domain\PrimeEdge\ValueObjects\InvoiceNumber;
use App\Domain\PrimeEdge\ValueObjects\ClientId;
use App\Domain\PrimeEdge\ValueObjects\Money;
use App\Domain\PrimeEdge\Repositories\InvoiceRepositoryInterface;
use DateTimeImmutable;

class BillingService
{
    public function __construct(
        private InvoiceRepositoryInterface $invoiceRepository,
    ) {}

    public function createInvoice(
        ClientId $clientId,
        ?string $engagementId = null,
        ?string $notes = null,
    ): Invoice {
        $now = new DateTimeImmutable();
        $year = (int) $now->format('Y');
        $sequence = $this->invoiceRepository->nextSequenceNumber($year);

        $invoice = Invoice::create(
            id: $this->invoiceRepository->nextId(),
            clientId: $clientId,
            number: InvoiceNumber::generate($year, $sequence),
            engagementId: $engagementId,
            notes: $notes,
        );

        $this->invoiceRepository->save($invoice);

        return $invoice;
    }

    public function addLineItem(InvoiceId $invoiceId, string $description, Money $unitPrice, int $quantity = 1): Invoice
    {
        $invoice = $this->invoiceRepository->findById($invoiceId);
        if (!$invoice) {
            throw new \App\Domain\PrimeEdge\Exceptions\InvoicePaymentException("Invoice not found: {$invoiceId->toString()}");
        }

        $item = InvoiceLineItem::create($description, $unitPrice, $quantity);
        $invoice->addLineItem($item);
        $this->invoiceRepository->save($invoice);

        return $invoice;
    }

    public function sendInvoice(InvoiceId $invoiceId): Invoice
    {
        $invoice = $this->invoiceRepository->findById($invoiceId);
        if (!$invoice) {
            throw new \App\Domain\PrimeEdge\Exceptions\InvoicePaymentException("Invoice not found: {$invoiceId->toString()}");
        }

        $invoice->send();
        $this->invoiceRepository->save($invoice);

        return $invoice;
    }

    public function recordPayment(InvoiceId $invoiceId): Invoice
    {
        $invoice = $this->invoiceRepository->findById($invoiceId);
        if (!$invoice) {
            throw new \App\Domain\PrimeEdge\Exceptions\InvoicePaymentException("Invoice not found: {$invoiceId->toString()}");
        }

        $invoice->markAsPaid();
        $this->invoiceRepository->save($invoice);

        return $invoice;
    }

    public function processOverdueInvoices(): array
    {
        $overdue = $this->invoiceRepository->findOverdue();
        foreach ($overdue as $invoice) {
            $invoice->markAsOverdue();
            $this->invoiceRepository->save($invoice);
        }
        return $overdue;
    }
}
