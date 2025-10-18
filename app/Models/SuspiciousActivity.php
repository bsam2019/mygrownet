<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SuspiciousActivity extends Model
{
    use HasFactory;

    const SEVERITY_LOW = 'low';
    const SEVERITY_MEDIUM = 'medium';
    const SEVERITY_HIGH = 'high';
    const SEVERITY_CRITICAL = 'critical';

    const TYPE_DUPLICATE_ACCOUNT = 'duplicate_account';
    const TYPE_RAPID_INVESTMENTS = 'rapid_investments';
    const TYPE_UNUSUAL_WITHDRAWAL = 'unusual_withdrawal';
    const TYPE_SUSPICIOUS_LOGIN = 'suspicious_login';
    const TYPE_MULTIPLE_DEVICES = 'multiple_devices';
    const TYPE_GEOGRAPHIC_ANOMALY = 'geographic_anomaly';

    const RESOLUTION_BLOCKED = 'blocked';
    const RESOLUTION_WARNED = 'warned';
    const RESOLUTION_CLEARED = 'cleared';
    const RESOLUTION_MONITORING = 'monitoring';

    protected $fillable = [
        'user_id',
        'activity_type',
        'severity',
        'ip_address',
        'user_agent',
        'activity_data',
        'detection_rules',
        'is_resolved',
        'resolution_action',
        'admin_notes',
        'resolved_at',
        'resolved_by',
    ];

    protected $casts = [
        'activity_data' => 'array',
        'detection_rules' => 'array',
        'is_resolved' => 'boolean',
        'resolved_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function resolvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    /**
     * Scope for unresolved activities
     */
    public function scopeUnresolved($query)
    {
        return $query->where('is_resolved', false);
    }

    /**
     * Scope for high priority activities
     */
    public function scopeHighPriority($query)
    {
        return $query->whereIn('severity', [self::SEVERITY_HIGH, self::SEVERITY_CRITICAL]);
    }

    /**
     * Scope for specific activity type
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('activity_type', $type);
    }

    /**
     * Mark as resolved
     */
    public function resolve(string $action, ?string $notes = null, ?int $resolvedBy = null): void
    {
        $this->update([
            'is_resolved' => true,
            'resolution_action' => $action,
            'admin_notes' => $notes,
            'resolved_at' => now(),
            'resolved_by' => $resolvedBy,
        ]);
    }

    /**
     * Check if this is a critical activity
     */
    public function isCritical(): bool
    {
        return $this->severity === self::SEVERITY_CRITICAL;
    }

    /**
     * Get severity color for UI
     */
    public function getSeverityColor(): string
    {
        return match ($this->severity) {
            self::SEVERITY_LOW => 'green',
            self::SEVERITY_MEDIUM => 'yellow',
            self::SEVERITY_HIGH => 'orange',
            self::SEVERITY_CRITICAL => 'red',
            default => 'gray',
        };
    }
}