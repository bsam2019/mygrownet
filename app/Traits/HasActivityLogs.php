<?php

namespace App\Traits;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasActivityLogs
{
    public function activities(): MorphMany
    {
        return $this->morphMany(ActivityLog::class, 'loggable');
    }

    public function logActivity(string $action, string $description = null): void
    {
        $this->activities()->create([
            'action' => $action,
            'description' => $description ?? "Performed {$action} on " . class_basename($this),
            'user_id' => auth()->id()
        ]);
    }
}
