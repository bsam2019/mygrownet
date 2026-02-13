<?php

namespace App\Domain\CMS\Core\Services;

use Illuminate\Support\Facades\DB;

class AuditTrailService
{
    public function log(
        int $companyId,
        int $userId,
        string $entityType,
        int $entityId,
        string $action,
        ?array $oldValues = null,
        ?array $newValues = null,
        ?string $ipAddress = null
    ): void {
        DB::table('cms_audit_trail')->insert([
            'company_id' => $companyId,
            'user_id' => $userId,
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'action' => $action,
            'old_values' => $oldValues ? json_encode($oldValues) : null,
            'new_values' => $newValues ? json_encode($newValues) : null,
            'ip_address' => $ipAddress ?? request()->ip(),
            'created_at' => now(),
        ]);
    }

    public function getHistory(string $entityType, int $entityId, int $companyId): array
    {
        return DB::table('cms_audit_trail')
            ->where('company_id', $companyId)
            ->where('entity_type', $entityType)
            ->where('entity_id', $entityId)
            ->orderBy('created_at', 'desc')
            ->get()
            ->toArray();
    }

    public function getUserActivity(int $userId, int $companyId, int $limit = 50): array
    {
        return DB::table('cms_audit_trail')
            ->where('company_id', $companyId)
            ->where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get()
            ->toArray();
    }
}
