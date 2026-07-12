<?php

namespace App\Services\VentureBuilder;

use App\Events\VentureBuilder\VentureFundingCompleted;
use App\Events\VentureBuilder\VentureStatusChanged;
use App\Infrastructure\Persistence\Eloquent\VentureBuilder\VentureModel;
use App\Infrastructure\Persistence\Eloquent\VentureBuilder\VentureShareholderModel;
use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;

class VentureService
{
    public function transitionStatus(VentureModel $venture, string $newStatus): VentureModel
    {
        $oldStatus = $venture->status;
        $allowedTransitions = [
            'draft' => ['review', 'cancelled'],
            'review' => ['approved', 'draft', 'cancelled'],
            'approved' => ['funding', 'cancelled'],
            'funding' => ['funded', 'cancelled'],
            'funded' => ['active', 'cancelled'],
            'active' => ['completed'],
        ];

        if (!in_array($newStatus, $allowedTransitions[$oldStatus] ?? [])) {
            throw new \InvalidArgumentException(
                "Cannot transition venture from '{$oldStatus}' to '{$newStatus}'."
            );
        }

        $venture->update(['status' => $newStatus]);

        Event::dispatch(new VentureStatusChanged($venture, $oldStatus, $newStatus));

        AuditLog::logEvent(
            'venture_status_changed',
            $venture,
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

        return $venture->fresh();
    }

    public function calculateShares(float $amount, VentureModel $venture): float
    {
        if ($venture->share_price && $venture->share_price > 0) {
            return floor($amount / $venture->share_price);
        }

        return (int) floor($amount / 100);
    }

    public function registerCompany(
        VentureModel $venture,
        string $companyName,
        string $registrationNumber,
        ?float $mygrownetEquityPercentage = null
    ): VentureModel {
        if ($venture->status !== 'funded') {
            throw new \InvalidArgumentException('Only funded ventures can register a company.');
        }

        $venture->update([
            'company_name' => $companyName,
            'company_registration_number' => $registrationNumber,
            'company_formation_date' => now(),
            'mygrownet_equity_percentage' => $mygrownetEquityPercentage ?? $venture->mygrownet_equity_percentage,
        ]);

        AuditLog::logEvent(
            'venture_company_registered',
            $venture,
            Auth::id(),
            null,
            $venture->toArray(),
            null,
            $registrationNumber,
            [
                'company_name' => $companyName,
                'venture_title' => $venture->title,
            ]
        );

        return $venture->fresh();
    }

    public function checkFundingComplete(VentureModel $venture): bool
    {
        if ($venture->total_raised >= $venture->funding_target && $venture->status === 'funding') {
            $venture->update(['status' => 'funded', 'funding_end_date' => now()]);

            Event::dispatch(new VentureFundingCompleted($venture));

            AuditLog::logEvent(
                'venture_funded',
                $venture,
                null,
                ['status' => 'funding'],
                ['status' => 'funded'],
                $venture->total_raised,
                null,
                [
                    'venture_title' => $venture->title,
                    'total_raised' => $venture->total_raised,
                    'funding_target' => $venture->funding_target,
                ]
            );

            return true;
        }

        return false;
    }
}
