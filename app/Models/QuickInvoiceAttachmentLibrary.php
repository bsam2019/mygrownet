<?php

namespace App\Models;

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

    /**
     * Get the user that owns the attachment
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get formatted file size
     */
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

    /**
     * Check if attachment is an image
     */
    public function isImage(): bool
    {
        return in_array($this->type, ['image/jpeg', 'image/jpg', 'image/png']);
    }

    /**
     * Check if attachment is a PDF
     */
    public function isPdf(): bool
    {
        return $this->type === 'application/pdf';
    }
}
