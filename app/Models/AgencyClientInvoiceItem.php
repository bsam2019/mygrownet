<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AgencyClientInvoiceItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_id',
        'service_id',
        'description',
        'quantity',
        'amount',
        'total',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'amount' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    /**
     * Get the invoice
     */
    public function invoice()
    {
        return $this->belongsTo(AgencyClientInvoice::class, 'invoice_id');
    }

    /**
     * Get the service
     */
    public function service()
    {
        return $this->belongsTo(AgencyClientService::class, 'service_id');
    }

    /**
     * Calculate total automatically
     */
    protected static function booted()
    {
        static::saving(function ($item) {
            $item->total = $item->quantity * $item->amount;
        });
    }
}
