<?php

namespace App\Infrastructure\Persistence\Eloquent\BMS;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GrowFinanceSyncConfigModel extends Model
{
    protected $table = 'cms_growfinance_sync_config';

    protected $fillable = [
        'company_id',
        'is_enabled',
        'growfinance_business_id',
        'auto_sync',
        'sync_invoices',
        'sync_expenses',
        'sync_payments',
    ];

    protected $casts = [
        'is_enabled' => 'boolean',
        'auto_sync' => 'boolean',
        'sync_invoices' => 'boolean',
        'sync_expenses' => 'boolean',
        'sync_payments' => 'boolean',
    ];

    public function company(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'company_id');
    }

    public function growfinanceBusiness(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'growfinance_business_id');
    }

    public function scopeEnabled($query)
    {
        return $query->where('is_enabled', true);
    }

    public function scopeForCompany($query, int $companyId)
    {
        return $query->where('company_id', $companyId);
    }

    public function isSyncEnabled(): bool
    {
        return $this->is_enabled && $this->auto_sync;
    }

    public function shouldSyncInvoices(): bool
    {
        return $this->isSyncEnabled() && $this->sync_invoices;
    }

    public function shouldSyncExpenses(): bool
    {
        return $this->isSyncEnabled() && $this->sync_expenses;
    }

    public function shouldSyncPayments(): bool
    {
        return $this->isSyncEnabled() && $this->sync_payments;
    }
}
