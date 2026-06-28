<?php

namespace App\Infrastructure\Persistence\Eloquent\CMS;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentSignatureModel extends Model
{
    protected $table = 'cms_document_signatures';

    protected $fillable = [
        'document_id',
        'signer_id',
        'signer_name',
        'signer_email',
        'signature_data',
        'signature_type',
        'signed_at',
        'ip_address',
    ];

    protected $casts = [
        'signed_at' => 'datetime',
    ];

    public function document(): BelongsTo
    {
        return $this->belongsTo(DocumentModel::class, 'document_id');
    }

    public function signer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'signer_id');
    }
}
