<?php

namespace App\Models\QuickInvoice;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuickInvoiceAttachmentLibrary extends Model
{
    protected $table = 'quick_invoice_attachment_library';

    protected $fillable = [
        'user_id',
        'name',
        'original_filename',
        'path',
        'type',
        'size',
        'description',
    ];

    protected $casts = [
        'size' => 'integer',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getFormattedSizeAttribute(): string
    {
        $bytes = $this->size;

        if ($bytes < 1024) {
            return $bytes . ' B';
        }

        if ($bytes < 1024 * 1024) {
            return round($bytes / 1024, 1) . ' KB';
        }

        return round($bytes / (1024 * 1024), 1) . ' MB';
    }

    public function isImage(): bool
    {
        return in_array($this->type, ['image/jpeg', 'image/jpg', 'image/png']);
    }

    public function isPdf(): bool
    {
        return $this->type === 'application/pdf';
    }
}
