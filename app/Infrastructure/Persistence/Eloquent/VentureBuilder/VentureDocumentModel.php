<?php

namespace App\Infrastructure\Persistence\Eloquent\VentureBuilder;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class VentureDocumentModel extends Model
{
    use SoftDeletes;

    protected $table = 'venture_documents';

    protected $fillable = [
        'venture_id',
        'title',
        'description',
        'type',
        'file_path',
        'file_name',
        'file_type',
        'file_size',
        'visibility',
        'is_confidential',
        'download_count',
        'uploaded_by',
    ];

    protected $casts = [
        'file_size' => 'integer',
        'is_confidential' => 'boolean',
        'download_count' => 'integer',
    ];

    public function venture(): BelongsTo
    {
        return $this->belongsTo(VentureModel::class, 'venture_id');
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    // Scopes
    public function scopePublic($query)
    {
        return $query->where('visibility', 'public');
    }

    public function scopeForInvestors($query)
    {
        return $query->whereIn('visibility', ['public', 'investors_only']);
    }

    public function scopeForShareholders($query)
    {
        return $query->whereIn('visibility', ['public', 'investors_only', 'shareholders_only']);
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    // Helpers
    public function isPublic(): bool
    {
        return $this->visibility === 'public';
    }

    public function canBeAccessedBy(string $userRole): bool
    {
        return match($this->visibility) {
            'public' => true,
            'investors_only' => in_array($userRole, ['investor', 'shareholder', 'admin']),
            'shareholders_only' => in_array($userRole, ['shareholder', 'admin']),
            'admin_only' => $userRole === 'admin',
            default => false,
        };
    }

    public function getFileSizeFormatted(): string
    {
        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }
}
