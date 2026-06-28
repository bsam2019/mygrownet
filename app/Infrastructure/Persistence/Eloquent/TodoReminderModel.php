<?php

namespace App\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TodoReminderModel extends Model
{
    protected $table = 'todo_reminders';

    protected $fillable = [
        'todo_id',
        'remind_at',
        'channel',
        'sent_at',
    ];

    protected $casts = [
        'todo_id' => 'integer',
        'remind_at' => 'datetime',
        'sent_at' => 'datetime',
    ];

    public function todo(): BelongsTo
    {
        return $this->belongsTo(PersonalTodoModel::class, 'todo_id');
    }

    public function scopePending($query)
    {
        return $query->whereNull('sent_at')
                     ->where('remind_at', '<=', now());
    }

    public function scopeUpcoming($query)
    {
        return $query->whereNull('sent_at')
                     ->where('remind_at', '>', now());
    }
}
