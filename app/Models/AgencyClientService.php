<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Infrastructure\GrowBuilder\Models\Scopes\AgencyScope;
use Carbon\Carbon;

class AgencyClientService extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'agency_id',
        'client_id',
        'service_type',
        'service_name',
        'linked_site_id',
        'billing_model',
        'unit_price',
        'quantity',
        'start_date',
        'renewal_date',
        'status',
        'notes',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'quantity' => 'integer',
        'start_date' => 'date',
        'renewal_date' => 'date',
    ];

    protected static function booted()
    {
        static::addGlobalScope(new AgencyScope());
        
        static::creating(function ($model) {
            if (auth()->check() && auth()->user()->currentAgency) {
                $model->agency_id = auth()->user()->currentAgency->id;
            }
        });
    }

    /**
     * Get the agency
     */
    public function agency()
    {
        return $this->belongsTo(Agency::class);
    }

    /**
     * Get the client
     */
    public function client()
    {
        return $this->belongsTo(AgencyClient::class, 'client_id');
    }

    /**
     * Get the linked site
     */
    public function site()
    {
        return $this->belongsTo(\App\Infrastructure\GrowBuilder\Models\GrowBuilderSite::class, 'linked_site_id');
    }

    /**
     * Get invoice items for this service
     */
    public function invoiceItems()
    {
        return $this->hasMany(AgencyClientInvoiceItem::class, 'service_id');
    }

    /**
     * Calculate total price
     */
    public function getTotalPriceAttribute(): float
    {
        return $this->unit_price * $this->quantity;
    }

    /**
     * Check if service is due for renewal
     */
    public function isDueForRenewal(int $daysAhead = 7): bool
    {
        if (!$this->renewal_date || $this->status !== 'active') {
            return false;
        }

        return $this->renewal_date->lte(Carbon::now()->addDays($daysAhead));
    }

    /**
     * Check if service is overdue for renewal
     */
    public function isOverdue(): bool
    {
        if (!$this->renewal_date || $this->status !== 'active') {
            return false;
        }

        return $this->renewal_date->lt(Carbon::now());
    }

    /**
     * Calculate next renewal date based on billing model
     */
    public function calculateNextRenewalDate(): ?Carbon
    {
        if (!$this->renewal_date) {
            return null;
        }

        return match($this->billing_model) {
            'monthly' => $this->renewal_date->addMonth(),
            'quarterly' => $this->renewal_date->addMonths(3),
            'annual' => $this->renewal_date->addYear(),
            'one_time' => null,
            default => null,
        };
    }

    /**
     * Scope for active services
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope for services due for renewal
     */
    public function scopeDueForRenewal($query, int $daysAhead = 7)
    {
        return $query->where('status', 'active')
            ->whereNotNull('renewal_date')
            ->where('renewal_date', '<=', Carbon::now()->addDays($daysAhead));
    }

    /**
     * Scope for overdue services
     */
    public function scopeOverdue($query)
    {
        return $query->where('status', 'active')
            ->whereNotNull('renewal_date')
            ->where('renewal_date', '<', Carbon::now());
    }
}
