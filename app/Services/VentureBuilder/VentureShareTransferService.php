<?php

namespace App\Services\VentureBuilder;

use App\Infrastructure\Persistence\Eloquent\VentureBuilder\VentureModel;
use App\Infrastructure\Persistence\Eloquent\VentureBuilder\VentureShareTransferModel;
use App\Infrastructure\Persistence\Eloquent\VentureBuilder\VentureShareholderModel;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class VentureShareTransferService
{
    public function requestTransfer(
        User $fromUser,
        VentureModel $venture,
        User $toUser,
        float $shares,
        ?float $pricePerShare = null,
        ?string $reason = null
    ): VentureShareTransferModel {
        $shareholder = VentureShareholderModel::where('venture_id', $venture->id)
            ->where('user_id', $fromUser->id)
            ->active()
            ->first();

        if (!$shareholder) {
            throw new \InvalidArgumentException('You are not an active shareholder in this venture.');
        }

        if ($shareholder->shares_owned < $shares) {
            throw new \InvalidArgumentException('Insufficient shares to transfer. You own ' . $shareholder->shares_owned . ' shares.');
        }

        $totalValue = $pricePerShare ? round($shares * $pricePerShare, 2) : null;

        $transfer = VentureShareTransferModel::create([
            'venture_id' => $venture->id,
            'from_user_id' => $fromUser->id,
            'to_user_id' => $toUser->id,
            'shares' => $shares,
            'price_per_share' => $pricePerShare,
            'total_value' => $totalValue,
            'status' => 'pending',
            'reason' => $reason,
        ]);

        AuditLog::logEvent(
            'venture_share_transfer_requested',
            $transfer,
            $fromUser->id,
            null,
            $transfer->toArray(),
            $totalValue,
            null,
            [
                'venture_title' => $venture->title,
                'from_user' => $fromUser->email,
                'to_user' => $toUser->email,
                'shares' => $shares,
            ]
        );

        return $transfer;
    }

    public function approveTransfer(VentureShareTransferModel $transfer): VentureShareTransferModel
    {
        if (!$transfer->isPending()) {
            throw new \InvalidArgumentException('Only pending transfers can be approved.');
        }

        DB::beginTransaction();
        try {
            $fromShareholder = VentureShareholderModel::where('venture_id', $transfer->venture_id)
                ->where('user_id', $transfer->from_user_id)
                ->active()
                ->firstOrFail();

            if ($fromShareholder->shares_owned < $transfer->shares) {
                throw new \InvalidArgumentException('Insufficient shares remaining in shareholder account.');
            }

            $toShareholder = VentureShareholderModel::where('venture_id', $transfer->venture_id)
                ->where('user_id', $transfer->to_user_id)
                ->active()
                ->first();

            $fromShareholder->decrement('shares_owned', $transfer->shares);
            $fromShareholder->decrement('total_investment', $transfer->total_value ?? 0);
            $fromShareholder->equity_percentage = $this->recalculateEquity($fromShareholder);
            $fromShareholder->save();

            if ($toShareholder) {
                $toShareholder->increment('shares_owned', $transfer->shares);
                $toShareholder->increment('total_investment', $transfer->total_value ?? 0);
                $toShareholder->equity_percentage = $this->recalculateEquity($toShareholder);
                $toShareholder->save();
            }

            $transfer->update([
                'status' => 'approved',
                'approved_by' => Auth::id(),
                'approved_at' => now(),
                'completed_at' => now(),
            ]);

            AuditLog::logEvent(
                'venture_share_transfer_completed',
                $transfer,
                Auth::id(),
                ['status' => 'pending'],
                $transfer->toArray(),
                $transfer->total_value,
                null,
                [
                    'venture_id' => $transfer->venture_id,
                    'from_user' => $transfer->from_user_id,
                    'to_user' => $transfer->to_user_id,
                    'shares' => $transfer->shares,
                ]
            );

            DB::commit();

            return $transfer->fresh();
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }

    public function rejectTransfer(VentureShareTransferModel $transfer, ?string $adminNotes = null): VentureShareTransferModel
    {
        if (!$transfer->isPending()) {
            throw new \InvalidArgumentException('Only pending transfers can be rejected.');
        }

        $transfer->update([
            'status' => 'rejected',
            'admin_notes' => $adminNotes,
            'approved_by' => Auth::id(),
            'approved_at' => now(),
        ]);

        return $transfer->fresh();
    }

    private function recalculateEquity(VentureShareholderModel $shareholder): float
    {
        $totalShares = VentureShareholderModel::where('venture_id', $shareholder->venture_id)
            ->active()
            ->sum('shares_owned');

        if ($totalShares <= 0) {
            return 0;
        }

        return min(100, ($shareholder->shares_owned / $totalShares) * 100);
    }
}
