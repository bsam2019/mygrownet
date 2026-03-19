<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Infrastructure\GrowBuilder\Models\Scopes\AgencyScope;
use Carbon\Carbon;

class AgencyClientInvoice extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'agency_id',
        'client_id',
        'invoice_number',
        'invoice_date',
        'due_date',
        'subtotal',
        'tax',
        'total',
        'currency',
        'payment_status',
        'notes',
    ];

    protected $casts = [
        'invoice_date' => 'date',
        'due_date' => 'date',
        'subtotal' => 'decimal:2',
        'tax' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    protected static function booted()
    {
        static::addGlobalScope(new AgencyScope());
        
        static::creating(function ($model) {
            if (auth()->check() && auth()->user()->currentAgency) {
                $model->agency_id = auth()->user()->currentAgency->id;
            }
            
            // Auto-generate invoice number if not set
            if (empty($model->invoice_number)) {
                $model->invoice_number = static::generateInvoiceNumber($model->agency_id);
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
     * Get invoice items
     */
    public function items()
    {
        return $this->hasMany(AgencyClientInvoiceItem::class, 'invoice_id');
    }

    /**
     * Get payments
     */
    public function payments()
    {
        return $this->hasMany(AgencyClientPayment::class, 'invoice_id');
    }

    /**
     * Calculate total paid amount
     */
    public function getTotalPaidAttribute(): float
    {
        return $this->payments()->sum('amount');
    }

    /**
     * Calculate remaining balance
     */
    public function getBalanceAttribute(): float
    {
        return $this->total - $this->total_paid;
    }

    /**
     * Check if invoice is fully paid
     */
    public function isPaid(): bool
    {
        return $this->payment_status === 'paid' || $this->balance <= 0;
    }

    /**
     * Check if invoice is overdue
     */
    public function isOverdue(): bool
    {
        return $this->payment_status !== 'paid' 
            && $this->payment_status !== 'cancelled'
            && $this->due_date->lt(Carbon::now());
    }

    /**
     * Mark invoice as sent
     */
    public function markAsSent(): void
    {
        $this->update(['payment_status' => 'sent']);
    }

    /**
     * Mark invoice as paid
     */
    public function markAsPaid(): void
    {
        $this->update(['payment_status' => 'paid']);
    }

    /**
     * Update payment status based on payments
     */
    public function updatePaymentStatus(): void
    {
        $totalPaid = $this->total_paid;
        
        if ($totalPaid >= $this->total) {
            $this->payment_status = 'paid';
        } elseif ($totalPaid > 0) {
            $this->payment_status = 'partial';
        } elseif ($this->isOverdue()) {
            $this->payment_status = 'overdue';
        }
        
        $this->save();
    }

    /**
     * Generate unique invoice number
     */
    public static function generateInvoiceNumber(?int $agencyId = null): string
    {
        $agencyId = $agencyId ?? auth()->user()->currentAgency->id;
        $year = date('Y');
        $month = date('m');
        
        // Get last invoice number for this agency this month
        $lastInvoice = static::where('agency_id', $agencyId)
            ->where('invoice_number', 'like', "INV-{$year}{$month}-%")
            ->orderBy('invoice_number', 'desc')
            ->first();
        
        if ($lastInvoice) {
            // Extract sequence number and increment
            $parts = explode('-', $lastInvoice->invoice_number);
            $sequence = intval(end($parts)) + 1;
        } else {
            $sequence = 1;
        }
        
        return sprintf('INV-%s%s-%04d', $year, $month, $sequence);
    }

    /**
     * Scope for unpaid invoices
     */
    public function scopeUnpaid($query)
    {
        return $query->whereIn('payment_status', ['draft', 'sent', 'partial', 'overdue']);
    }

    /**
     * Scope for overdue invoices
     */
    public function scopeOverdue($query)
    {
        return $query->whereIn('payment_status', ['sent', 'partial', 'overdue'])
            ->where('due_date', '<', Carbon::now());
    }

    /**
     * Scope for paid invoices
     */
    public function scopePaid($query)
    {
        return $query->where('payment_status', 'paid');
    }
}
