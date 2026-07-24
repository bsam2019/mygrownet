<?php

namespace App\Domain\VentureBuilder\Services;

use App\Domain\VentureBuilder\Repositories\ShareTransferRepositoryInterface;
use App\Domain\VentureBuilder\Repositories\ShareholderRepositoryInterface;
use App\Domain\VentureBuilder\Repositories\VentureRepositoryInterface;
use App\Domain\VentureBuilder\Entities\ShareTransfer;
use App\Domain\VentureBuilder\ValueObjects\TransferStatus;
use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class VentureShareTransferService
{
    public function __construct(
        private readonly ShareTransferRepositoryInterface $shareTransferRepository,
        private readonly ShareholderRepositoryInterface $shareholderRepository,
        private readonly VentureRepositoryInterface $ventureRepository,
    ) {}

    public function requestTransfer(
        int $fromUserId,
        int $ventureId,
        int $toUserId,
        float $shares,
        ?float $pricePerShare = null,
        ?string $reason = null
    ): array {
        $shareholder = $this->shareholderRepository->findActiveByUserAndVenture($fromUserId, $ventureId);
        if (!$shareholder) {
            throw new \InvalidArgumentException('You are not an active shareholder in this venture.');
        }

        if (($shareholder->sharesOwned ?? 0) < $shares) {
            throw new \InvalidArgumentException('Insufficient shares to transfer. You own ' . ($shareholder->sharesOwned ?? 0) . ' shares.');
        }

        $totalValue = $pricePerShare ? round($shares * $pricePerShare, 2) : null;

        $transfer = new ShareTransfer(
            ventureId: $ventureId,
            fromUserId: $fromUserId,
            toUserId: $toUserId,
            shares: $shares,
            status: TransferStatus::pending(),
            pricePerShare: $pricePerShare,
            totalValue: $totalValue,
            reason: $reason,
        );

        $saved = $this->shareTransferRepository->save($transfer);

        $venture = $this->ventureRepository->findById($ventureId);

        AuditLog::logEvent(
            'venture_share_transfer_requested',
            "ShareTransfer#{$saved->id}",
            $fromUserId,
            null,
            $saved->toArray(),
            $totalValue,
            null,
            [
                'venture_title' => $venture?->title ?? 'Unknown',
                'from_user' => $fromUserId,
                'to_user' => $toUserId,
                'shares' => $shares,
            ]
        );

        return $saved->toArray();
    }

    public function approveTransfer(int $transferId): array
    {
        $transfer = $this->shareTransferRepository->findById($transferId);
        if (!$transfer || !$transfer->isPending()) {
            throw new \InvalidArgumentException('Only pending transfers can be approved.');
        }

        return DB::transaction(function () use ($transfer, $transferId) {
            $fromShareholder = $this->shareholderRepository->findActiveByUserAndVenture(
                $transfer->fromUserId,
                $transfer->ventureId
            );

            if (!$fromShareholder) {
                throw new \InvalidArgumentException('Source shareholder not found.');
            }

            if (($fromShareholder->sharesOwned ?? 0) < $transfer->shares) {
                throw new \InvalidArgumentException('Insufficient shares remaining in shareholder account.');
            }

            $this->shareholderRepository->decrementShares($fromShareholder->id ?? 0, $transfer->shares);
            $this->shareholderRepository->decrementInvestment($fromShareholder->id ?? 0, $transfer->totalValue ?? 0);

            $totalShares = $this->shareholderRepository->getTotalSharesByVenture($transfer->ventureId);
            $newFromEquity = $totalShares > 0
                ? min(100, ((($fromShareholder->sharesOwned ?? 0) - $transfer->shares) / $totalShares) * 100)
                : 0;
            $this->shareholderRepository->updateEquity($fromShareholder->id ?? 0, $newFromEquity);

            $toShareholder = $this->shareholderRepository->findActiveByUserAndVenture(
                $transfer->toUserId,
                $transfer->ventureId
            );

            if ($toShareholder) {
                $this->shareholderRepository->incrementShares($toShareholder->id ?? 0, $transfer->shares);
                $this->shareholderRepository->incrementInvestment($toShareholder->id ?? 0, $transfer->totalValue ?? 0);
                $newToEquity = $totalShares > 0
                    ? min(100, ((($toShareholder->sharesOwned ?? 0) + $transfer->shares) / $totalShares) * 100)
                    : 0;
                $this->shareholderRepository->updateEquity($toShareholder->id ?? 0, $newToEquity);
            }

            $this->shareTransferRepository->updateStatus($transferId, 'approved', [
                'approved_by' => Auth::id(),
                'approved_at' => now(),
                'completed_at' => now(),
            ]);

            AuditLog::logEvent(
                'venture_share_transfer_completed',
                "ShareTransfer#$transferId",
                Auth::id(),
                ['status' => 'pending'],
                [],
                $transfer->totalValue,
                null,
                [
                    'venture_id' => $transfer->ventureId,
                    'from_user' => $transfer->fromUserId,
                    'to_user' => $transfer->toUserId,
                    'shares' => $transfer->shares,
                ]
            );

            $updated = $this->shareTransferRepository->findById($transferId);
            return $updated ? $updated->toArray() : [];
        });
    }

    public function rejectTransfer(int $transferId, ?string $adminNotes = null): array
    {
        $transfer = $this->shareTransferRepository->findById($transferId);
        if (!$transfer || !$transfer->isPending()) {
            throw new \InvalidArgumentException('Only pending transfers can be rejected.');
        }

        $this->shareTransferRepository->updateStatus($transferId, 'rejected', [
            'admin_notes' => $adminNotes,
            'approved_by' => Auth::id(),
            'approved_at' => now(),
        ]);

        $updated = $this->shareTransferRepository->findById($transferId);
        return $updated ? $updated->toArray() : [];
    }
}
