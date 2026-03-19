<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AgencyClientPayment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'invoice_id',
        'amount',
        'payment_date',
        'payment_method',
        'reference',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'payment_date' => 'date',
    ];

    protected static function booted()
    {
        // Update invoice payment status after payment is created/updated/deleted
        static::saved(function ($payment) {
            $payment->invoice->updatePaymentStatus();
        });

        static::deleted(function ($payment) {
            $payment->invoice->updatePaymentStatus();
        });
    }

    /**
     * Get the invoice
     */
    public function invoice()
    {
        return $this->belongsTo(AgencyClientInvoice::class, 'invoice_id');
    }
}
