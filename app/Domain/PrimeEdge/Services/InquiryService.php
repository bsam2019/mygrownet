<?php

namespace App\Domain\PrimeEdge\Services;

use App\Domain\PrimeEdge\Entities\Inquiry;
use App\Domain\PrimeEdge\ValueObjects\InquiryId;
use App\Domain\PrimeEdge\ValueObjects\ClientId;
use App\Domain\PrimeEdge\ValueObjects\Money;
use App\Domain\PrimeEdge\Repositories\InquiryRepositoryInterface;

class InquiryService
{
    public function __construct(
        private InquiryRepositoryInterface $inquiryRepository,
    ) {}

    public function submit(
        ClientId $clientId,
        string $serviceDescription,
        ?string $preferredServiceType = null,
        ?string $notes = null,
    ): Inquiry {
        $inquiry = Inquiry::create(
            id: $this->inquiryRepository->nextId(),
            clientId: $clientId,
            serviceDescription: $serviceDescription,
            preferredServiceType: $preferredServiceType,
            notes: $notes,
        );

        $this->inquiryRepository->save($inquiry);

        return $inquiry;
    }

    public function sendQuote(InquiryId $inquiryId, Money $amount, string $notes = ''): Inquiry
    {
        $inquiry = $this->inquiryRepository->findById($inquiryId);
        if (!$inquiry) {
            throw new \App\Domain\PrimeEdge\Exceptions\InquiryNotFoundException($inquiryId->toString());
        }

        $inquiry->sendQuote($amount, $notes);
        $this->inquiryRepository->save($inquiry);

        return $inquiry;
    }

    public function acceptQuote(InquiryId $inquiryId): Inquiry
    {
        $inquiry = $this->inquiryRepository->findById($inquiryId);
        if (!$inquiry) {
            throw new \App\Domain\PrimeEdge\Exceptions\InquiryNotFoundException($inquiryId->toString());
        }

        $inquiry->accept();
        $this->inquiryRepository->save($inquiry);

        return $inquiry;
    }

    public function declineQuote(InquiryId $inquiryId, ?string $reason = null): Inquiry
    {
        $inquiry = $this->inquiryRepository->findById($inquiryId);
        if (!$inquiry) {
            throw new \App\Domain\PrimeEdge\Exceptions\InquiryNotFoundException($inquiryId->toString());
        }

        $inquiry->decline($reason);
        $this->inquiryRepository->save($inquiry);

        return $inquiry;
    }
}
