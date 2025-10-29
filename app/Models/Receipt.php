<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Receipt extends Model
{
    use HasFactory;

    protected $fillable = [
        'receipt_number',
        'user_id',
        'type',
        'amount',
        'payment_method',
        'transaction_reference',
        'description',
        'pdf_path',
        'metadata',
        'emailed',
        'emailed_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'metadata' => 'array',
        'emailed' => 'boolean',
        'emailed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the user that owns the receipt
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope for specific receipt types
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope for emailed receipts
     */
    public function scopeEmailed($query)
    {
        return $query->where('emailed', true);
    }

    /**
     * Scope for not emailed receipts
     */
    public function scopeNotEmailed($query)
    {
        return $query->where('emailed', false);
    }

    /**
     * Get formatted payment method
     */
    public function getFormattedPaymentMethodAttribute(): string
    {
        return match($this->payment_method) {
            'mobile_money' => 'Mobile Money',
            'bank_transfer' => 'Bank Transfer',
            'wallet' => 'MyGrowNet Wallet',
            default => ucfirst(str_replace('_', ' ', $this->payment_method)),
        };
    }

    /**
     * Get formatted type
     */
    public function getFormattedTypeAttribute(): string
    {
        return match($this->type) {
            'payment' => 'Registration Payment',
            'starter_kit' => 'Starter Kit Purchase',
            'workshop' => 'Workshop Registration',
            'subscription' => 'Subscription Payment',
            'shop_purchase' => 'Shop Purchase',
            'wallet' => 'Wallet Transaction',
            default => ucfirst(str_replace('_', ' ', $this->type)),
        };
    }

    /**
     * Check if PDF file exists
     */
    public function pdfExists(): bool
    {
        return $this->pdf_path && file_exists($this->pdf_path);
    }

    /**
     * Get PDF URL for download
     */
    public function getPdfUrlAttribute(): ?string
    {
        if (!$this->pdfExists()) {
            return null;
        }

        return route('receipts.download', $this->id);
    }
}
