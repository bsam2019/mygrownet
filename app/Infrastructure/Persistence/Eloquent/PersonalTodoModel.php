<?php

namespace App\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PersonalTodoModel extends Model
{
    use SoftDeletes;

    protected $table = 'personal_todos';

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'due_date',
        'due_time',
        'priority',
        'status',
        'category',
        'tags',
        'is_recurring',
        'recurrence_pattern',
        'parent_id',
        'sort_order',
        'completed_at',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'due_date' => 'date',
        'tags' => 'array',
        'is_recurring' => 'boolean',
        'parent_id' => 'integer',
        'sort_order' => 'integer',
        'completed_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function subtasks(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('sort_order');
    }

    public function reminders(): HasMany
    {
        return $this->hasMany(TodoReminderModel::class, 'todo_id');
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeWithStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopePending($query)
    {
        return $query->whereIn('status', ['pending', 'in_progress']);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeOverdue($query)
    {
        return $query->where('due_date', '<', now()->startOfDay())
                     ->whereNotIn('status', ['completed']);
    }

    public function scopeDueToday($query)
    {
        return $query->whereDate('due_date', now()->toDateString());
    }

    public function scopeDueThisWeek($query)
    {
        return $query->whereBetween('due_date', [
            now()->startOfDay(),
            now()->addDays(7)->endOfDay()
        ]);
    }

    public function scopeParentTodos($query)
    {
        return $query->whereNull('parent_id');
    }

    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    public function scopeByPriority($query, string $priority)
    {
        return $query->where('priority', $priority);
    }
}
