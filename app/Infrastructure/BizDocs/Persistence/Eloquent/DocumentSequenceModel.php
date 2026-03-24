<?php

namespace App\Infrastructure\BizDocs\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentSequenceModel extends Model
{
    protected $table = 'bizdocs_document_sequences';

    protected $fillable = [
        'business_id',
        'document_type',
        'year',
        'last_number',
        'prefix',
        'padding',
        'include_year',
    ];

    protected $casts = [
        'year' => 'integer',
        'last_number' => 'integer',
        'padding' => 'integer',
        'include_year' => 'boolean',
    ];

    public function business(): BelongsTo
    {
        return $this->belongsTo(BusinessProfileModel::class, 'business_id');
    }
}
