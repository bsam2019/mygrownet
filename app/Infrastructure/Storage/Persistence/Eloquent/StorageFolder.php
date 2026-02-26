<?php

namespace App\Infrastructure\Storage\Persistence\Eloquent;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class StorageFolder extends Model
{
    use HasUuids;

    protected $fillable = [
        'id',
        'user_id',
        'parent_id',
        'name',
        'path_cache',
    ];

    protected $casts = [
        'user_id' => 'integer',
    ];

    public $incrementing = false;
    protected $keyType = 'string';

    /**
     * Generate a new UUID for the model.
     */
    public function newUniqueId(): string
    {
        return (string) \Illuminate\Support\Str::uuid();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(StorageFolder::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(StorageFolder::class, 'parent_id');
    }

    public function files(): HasMany
    {
        return $this->hasMany(StorageFile::class, 'folder_id');
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeRoot($query)
    {
        return $query->whereNull('parent_id');
    }

    public function scopeInFolder($query, ?string $parentId)
    {
        return $query->where('parent_id', $parentId);
    }

    public function isRoot(): bool
    {
        return $this->parent_id === null;
    }

    public function hasChildren(): bool
    {
        return $this->children()->exists();
    }

    public function hasFiles(): bool
    {
        return $this->files()->where('is_deleted', false)->exists();
    }

    public function isEmpty(): bool
    {
        return !$this->hasChildren() && !$this->hasFiles();
    }
}
