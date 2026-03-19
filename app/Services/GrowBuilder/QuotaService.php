<?php

namespace App\Services\GrowBuilder;

use App\Models\Agency;

class QuotaService
{
    /**
     * Check if agency can create a new site
     */
    public function canCreateSite(Agency $agency): bool
    {
        return $agency->sites_used < $agency->site_limit;
    }

    /**
     * Check if agency can upload a file of given size
     */
    public function canUploadFile(Agency $agency, int $fileSizeMb): bool
    {
        $newTotal = $agency->storage_used_mb + $fileSizeMb;
        return $newTotal <= $agency->storage_limit_mb;
    }

    /**
     * Check if agency can invite a new team member
     */
    public function canInviteTeamMember(Agency $agency): bool
    {
        $currentMembers = $agency->users()->count();
        return $currentMembers < $agency->team_member_limit;
    }

    /**
     * Get storage warning level
     */
    public function getStorageWarningLevel(Agency $agency): string
    {
        if ($agency->storage_limit_mb == 0) {
            return 'normal';
        }

        $percentage = ($agency->storage_used_mb / $agency->storage_limit_mb) * 100;

        if ($percentage >= 100) return 'critical';
        if ($percentage >= 90) return 'danger';
        if ($percentage >= 80) return 'warning';
        return 'normal';
    }

    /**
     * Get sites warning level
     */
    public function getSitesWarningLevel(Agency $agency): string
    {
        if ($agency->site_limit == 0) {
            return 'normal';
        }

        $percentage = ($agency->sites_used / $agency->site_limit) * 100;

        if ($percentage >= 100) return 'critical';
        if ($percentage >= 90) return 'danger';
        if ($percentage >= 80) return 'warning';
        return 'normal';
    }

    /**
     * Get remaining storage in MB
     */
    public function getRemainingStorage(Agency $agency): int
    {
        return max(0, $agency->storage_limit_mb - $agency->storage_used_mb);
    }

    /**
     * Get remaining sites
     */
    public function getRemainingSites(Agency $agency): int
    {
        return max(0, $agency->site_limit - $agency->sites_used);
    }

    /**
     * Get remaining team member slots
     */
    public function getRemainingTeamSlots(Agency $agency): int
    {
        $currentMembers = $agency->users()->count();
        return max(0, $agency->team_member_limit - $currentMembers);
    }

    /**
     * Get quota summary for agency
     */
    public function getQuotaSummary(Agency $agency): array
    {
        return [
            'storage' => [
                'used' => $agency->storage_used_mb,
                'limit' => $agency->storage_limit_mb,
                'remaining' => $this->getRemainingStorage($agency),
                'percentage' => $agency->storage_percentage,
                'warning_level' => $this->getStorageWarningLevel($agency),
            ],
            'sites' => [
                'used' => $agency->sites_used,
                'limit' => $agency->site_limit,
                'remaining' => $this->getRemainingSites($agency),
                'percentage' => $agency->sites_percentage,
                'warning_level' => $this->getSitesWarningLevel($agency),
            ],
            'team_members' => [
                'used' => $agency->users()->count(),
                'limit' => $agency->team_member_limit,
                'remaining' => $this->getRemainingTeamSlots($agency),
            ],
        ];
    }
}
