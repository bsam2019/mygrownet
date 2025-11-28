<?php

namespace App\Domain\Investor\Services;

use App\Models\ShareTransferRequest;
use App\Models\InvestorAccount;
use Illuminate\Support\Collection;

/**
 * Share Transfer Service for Private Limited Company
 * 
 * Handles share transfer requests that require board approval.
 * This is NOT a marketplace - all transfers go through formal approval process.
 */
class ShareTransferService
{
    /**
     * Create a new share transfer request (draft)
     */
    public function createTransferRequest(
        InvestorAccount $seller,
        float $sharesPercentage,
        float $proposedPrice,
        string $transferType,
        string $reasonForSale,
        ?int $proposedBuyerId = null,
        ?string $proposedBuyerName = null,
        ?string $proposedBuyerEmail = null
    ): ShareTransferRequest {
        // Validate seller has enough shares
        if ($sharesPercentage > $seller->equity_percentage) {
            throw new \InvalidArgumentException('Cannot transfer more shares than owned.');
        }

        return ShareTransferRequest::create([
            'seller_investor_id' => $seller->id,
            'proposed_buyer_id' => $proposedBuyerId,
            'proposed_buyer_name' => $proposedBuyerName,
            'proposed_buyer_email' => $proposedBuyerEmail,
            'shares_percentage' => $sharesPercentage,
            'proposed_price' => $proposedPrice,
            'transfer_type' => $transferType,
            'status' => 'draft',
            'reason_for_sale' => $reasonForSale,
            'required_documents' => $this->getRequiredDocuments($transferType),
        ]);
    }

    /**
     * Submit transfer request for board review
     */
    public function submitForReview(ShareTransferRequest $request): void
    {
        if (!$request->canBeSubmitted()) {
            throw new \InvalidArgumentException('Request cannot be submitted in current state.');
        }

        $request->submit();
    }

    /**
     * Board approves the transfer
     */
    public function approveTransfer(
        ShareTransferRequest $request,
        int $reviewerId,
        ?float $approvedPrice = null,
        ?string $boardNotes = null
    ): void {
        $request->update([
            'status' => 'board_approved',
            'approved_price' => $approvedPrice ?? $request->proposed_price,
            'board_notes' => $boardNotes,
            'reviewed_by' => $reviewerId,
            'reviewed_at' => now(),
        ]);
    }

    /**
     * Board rejects the transfer
     */
    public function rejectTransfer(
        ShareTransferRequest $request,
        int $reviewerId,
        string $rejectionReason
    ): void {
        $request->update([
            'status' => 'board_rejected',
            'rejection_reason' => $rejectionReason,
            'reviewed_by' => $reviewerId,
            'reviewed_at' => now(),
        ]);
    }

    /**
     * Complete the transfer (after all paperwork is done)
     */
    public function completeTransfer(ShareTransferRequest $request): void
    {
        if (!$request->isApproved()) {
            throw new \InvalidArgumentException('Transfer must be approved before completion.');
        }

        $request->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);

        // Update seller's equity
        $seller = $request->seller;
        $seller->decrement('equity_percentage', $request->shares_percentage);

        // Update buyer's equity if internal transfer
        if ($request->proposed_buyer_id) {
            $buyer = $request->proposedBuyer;
            $buyer->increment('equity_percentage', $request->shares_percentage);
        }
    }

    /**
     * Cancel a transfer request
     */
    public function cancelRequest(ShareTransferRequest $request): void
    {
        if (!$request->canBeCancelled()) {
            throw new \InvalidArgumentException('Request cannot be cancelled in current state.');
        }

        $request->update(['status' => 'cancelled']);
    }

    /**
     * Get transfer requests for an investor
     */
    public function getInvestorRequests(InvestorAccount $investor): Collection
    {
        return ShareTransferRequest::where('seller_investor_id', $investor->id)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get pending requests for board review
     */
    public function getPendingBoardRequests(): Collection
    {
        return ShareTransferRequest::pendingBoard()
            ->with(['seller', 'proposedBuyer'])
            ->orderBy('submitted_at', 'asc')
            ->get();
    }

    /**
     * Get required documents based on transfer type
     */
    private function getRequiredDocuments(string $transferType): array
    {
        $baseDocuments = [
            'share_transfer_form' => 'Share Transfer Form (Form SH-4)',
            'id_copy' => 'Copy of National ID',
        ];

        if ($transferType === 'external') {
            $baseDocuments['buyer_id'] = 'Buyer\'s National ID Copy';
            $baseDocuments['buyer_address_proof'] = 'Buyer\'s Proof of Address';
        }

        if ($transferType === 'buyback_request') {
            $baseDocuments['buyback_request_letter'] = 'Formal Buyback Request Letter';
        }

        return $baseDocuments;
    }
}
