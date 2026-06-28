<?php

namespace App\Infrastructure\Persistence\Eloquent\CMS;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentVersionModel extends Model
{
    protected $table = 'cms_document_versions';

    protected $fillable = [
        'document_id',
        'version_number',
        'file_path',
        'file_name',
        'file_size',
        'uploaded_by',
        'version_notes',
    ];

    protected $casts = [
        'version_number' => 'integer',
        'file_size' => 'integer',
    ];

    public function document(): BelongsTo
    {
        return $this->belongsTo(DocumentModel::class, 'document_id');
    }

    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
