<?php

namespace App\Infrastructure\BizDocs\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentPaymentModel extends Model
{
    protected $table = 'bizdocs_document_payments';

    protected $fillable = [
        'document_id',
        'payment_date',
        'amount',
        'payment_method',
        'reference_number',
        'notes',
    ];

    protected $casts = [
        'payment_date' => 'date',
        'amount' => 'decimal:2',
    ];

    public function document(): BelongsTo
    {
        return $this->belongsTo(DocumentModel::class, 'document_id');
    }
}
