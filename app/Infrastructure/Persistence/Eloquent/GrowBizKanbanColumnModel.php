<?php

namespace App\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GrowBizKanbanColumnModel extends Model
{
    protected $table = 'growbiz_kanban_columns';

    protected $fillable = [
        'project_id',
        'name',
        'color',
        'sort_order',
        'wip_limit',
        'is_done_column',
    ];

    protected $casts = [
        'project_id' => 'integer',
        'sort_order' => 'integer',
        'wip_limit' => 'integer',
        'is_done_column' => 'boolean',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(GrowBizProjectModel::class, 'project_id');
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(GrowBizTaskModel::class, 'kanban_column_id')->orderBy('kanban_order');
    }

    public function getTaskCountAttribute(): int
    {
        return $this->tasks()->count();
    }

    public function isAtWipLimit(): bool
    {
        if (!$this->wip_limit) return false;
        return $this->tasks()->count() >= $this->wip_limit;
    }
}
