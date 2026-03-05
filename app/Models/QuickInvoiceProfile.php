<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuickInvoiceProfile extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'address',
        'phone',
        'email',
        'logo',
        'signature',
        'prepared_by',
        'tax_number',
        'default_tax_rate',
        'default_discount_rate',
        'default_notes',
        'default_terms',
        // Numbering settings
        'invoice_prefix',
        'invoice_next_number',
        'invoice_number_padding',
        'quotation_prefix',
        'quotation_next_number',
        'quotation_number_padding',
        'receipt_prefix',
        'receipt_next_number',
        'receipt_number_padding',
        'delivery_note_prefix',
        'delivery_note_next_number',
        'delivery_note_number_padding',
        // Template preferences
        'default_template',
        'default_color',
    ];

    protected $casts = [
        'default_tax_rate' => 'float',
        'default_discount_rate' => 'float',
        'invoice_next_number' => 'integer',
        'invoice_number_padding' => 'integer',
        'quotation_next_number' => 'integer',
        'quotation_number_padding' => 'integer',
        'receipt_next_number' => 'integer',
        'receipt_number_padding' => 'integer',
        'delivery_note_next_number' => 'integer',
        'delivery_note_number_padding' => 'integer',
    ];

    /**
     * Generate next document number for the given type
     */
    public function generateDocumentNumber(string $type): string
    {
        $prefix = $this->{$type . '_prefix'} ?? strtoupper(substr($type, 0, 3));
        $nextNumber = $this->{$type . '_next_number'} ?? 1;
        $padding = $this->{$type . '_number_padding'} ?? 4;
        
        return $prefix . '-' . str_pad($nextNumber, $padding, '0', STR_PAD_LEFT);
    }

    /**
     * Increment document number for the given type
     */
    public function incrementDocumentNumber(string $type): void
    {
        $field = $type . '_next_number';
        $this->increment($field);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
