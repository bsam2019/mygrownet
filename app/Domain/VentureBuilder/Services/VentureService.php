<?php

namespace App\Domain\VentureBuilder\Services;

use App\Domain\VentureBuilder\Repositories\VentureRepositoryInterface;
use App\Domain\VentureBuilder\ValueObjects\VentureStatus;
use App\Events\VentureBuilder\VentureFundingCompleted;
use App\Events\VentureBuilder\VentureStatusChanged;
use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;

class VentureService
{
    private const ALLOWED_TRANSITIONS = [
        'draft' => ['review', 'cancelled'],
        'review' => ['approved', 'draft', 'cancelled'],
        'approved' => ['funding', 'cancelled'],
        'funding' => ['funded', 'cancelled'],
        'funded' => ['active', 'cancelled'],
        'active' => ['completed'],
    ];

    public function __construct(
        private readonly VentureRepositoryInterface $ventureRepository,
    ) {}

    public function transitionStatus(int $ventureId, string $newStatus): array
    {
        $venture = $this->ventureRepository->findById($ventureId);
        if (!$venture) {
            throw new \InvalidArgumentException('Venture not found.');
        }

        $oldStatus = $venture->status->value();

        if (!in_array($newStatus, self::ALLOWED_TRANSITIONS[$oldStatus] ?? [])) {
            throw new \InvalidArgumentException(
                "Cannot transition venture from '{$oldStatus}' to '{$newStatus}'."
            );
        }

        $this->ventureRepository->updateStatus($ventureId, $newStatus);

        Event::dispatch(new VentureStatusChanged(
            ventureId: $ventureId,
            oldStatus: $oldStatus,
            newStatus: $newStatus,
            ventureTitle: $venture->title,
            ventureSlug: $venture->slug,
        ));

        AuditLog::logEvent(
            'venture_status_changed',
            "Venture#$ventureId",
            Auth::id(),
            ['status' => $oldStatus],
            ['status' => $newStatus],
            null,
            null,
            [
                'venture_title' => $venture->title,
                'venture_slug' => $venture->slug,
            ]
        );

        $updated = $this->ventureRepository->findById($ventureId);
        return $updated ? $updated->toArray() : [];
    }

    public function calculateShares(float $amount, array $ventureData): float
    {
        $sharePrice = $ventureData['share_price'] ?? null;
        if ($sharePrice && $sharePrice > 0) {
            return floor($amount / $sharePrice);
        }

        return (int) floor($amount / 100);
    }

    public function registerCompany(
        int $ventureId,
        string $companyName,
        string $registrationNumber,
        ?float $mygrowNetEquityPercentage = null
    ): array {
        $venture = $this->ventureRepository->findById($ventureId);
        if (!$venture) {
            throw new \InvalidArgumentException('Venture not found.');
        }

        if ($venture->status->value() !== 'funded') {
            throw new \InvalidArgumentException('Only funded ventures can register a company.');
        }

        $this->ventureRepository->updateStatus($ventureId, 'active');

        AuditLog::logEvent(
            'venture_company_registered',
            "Venture#$ventureId",
            Auth::id(),
            null,
            ['company_name' => $companyName, 'registration_number' => $registrationNumber],
            null,
            $registrationNumber,
            [
                'company_name' => $companyName,
                'venture_title' => $venture->title,
            ]
        );

        $updated = $this->ventureRepository->findById($ventureId);
        return $updated ? $updated->toArray() : [];
    }

    public function checkFundingComplete(int $ventureId): bool
    {
        $venture = $this->ventureRepository->findById($ventureId);
        if (!$venture) {
            return false;
        }

        $totalRaised = $venture->totalRaised ?? 0;
        $fundingTarget = $venture->fundingTarget ?? 0;

        if ($totalRaised >= $fundingTarget && $venture->status->value() === 'funding') {
            $this->ventureRepository->updateStatus($ventureId, 'funded');

            Event::dispatch(new VentureFundingCompleted(
                ventureId: $ventureId,
                ventureTitle: $venture->title,
                totalRaised: $totalRaised,
                fundingTarget: $fundingTarget,
            ));

            AuditLog::logEvent(
                'venture_funded',
                "Venture#$ventureId",
                null,
                ['status' => 'funding'],
                ['status' => 'funded'],
                $totalRaised,
                null,
                [
                    'venture_title' => $venture->title,
                    'total_raised' => $totalRaised,
                    'funding_target' => $fundingTarget,
                ]
            );

            return true;
        }

        return false;
    }
}
