<?php

namespace App\Infrastructure\BizDocs\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class DocumentModel extends Model
{
    use SoftDeletes;

    protected $table = 'bizdocs_documents';

    protected $fillable = [
        'business_id',
        'customer_id',
        'template_id',
        'document_type',
        'document_number',
        'issue_date',
        'due_date',
        'validity_date',
        'subtotal',
        'tax_total',
        'discount_total',
        'grand_total',
        'currency',
        'status',
        'notes',
        'terms',
        'payment_instructions',
        'pdf_path',
        'discount_type',
        'discount_value',
        'collect_tax',
    ];

    protected $casts = [
        'issue_date' => 'date',
        'due_date' => 'date',
        'validity_date' => 'date',
        'subtotal' => 'decimal:2',
        'tax_total' => 'decimal:2',
        'discount_total' => 'decimal:2',
        'grand_total' => 'decimal:2',
        'discount_value' => 'decimal:2',
        'collect_tax' => 'boolean',
    ];

    public function business(): BelongsTo
    {
        return $this->belongsTo(BusinessProfileModel::class, 'business_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(CustomerModel::class, 'customer_id');
    }

    public function template(): BelongsTo
    {
        return $this->belongsTo(DocumentTemplateModel::class, 'template_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(DocumentItemModel::class, 'document_id')->orderBy('sort_order');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(DocumentPaymentModel::class, 'document_id');
    }
}
