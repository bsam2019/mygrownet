<?php

namespace App\Infrastructure\Persistence\Eloquent\CMS;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentShareModel extends Model
{
    protected $table = 'cms_document_shares';

    protected $fillable = [
        'document_id',
        'shared_with_type',
        'shared_with_id',
        'shared_by',
        'permission',
        'expiry_date',
    ];

    protected $casts = [
        'shared_with_id' => 'integer',
        'expiry_date' => 'date',
    ];

    public function document(): BelongsTo
    {
        return $this->belongsTo(DocumentModel::class, 'document_id');
    }

    public function sharedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'shared_by');
    }
}
