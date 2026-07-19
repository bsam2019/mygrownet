<?php

namespace App\Infrastructure\Persistence\Eloquent\CMS;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class PlanLinkModel extends Model
{
    protected $table = 'plan_links';

    protected $fillable = [
        'plan_objective_id',
        'linkable_type',
        'linkable_id',
        'metric_field',
        'auto_sync',
        'label',
    ];

    protected $casts = [
        'auto_sync' => 'boolean',
    ];

    public function objective(): BelongsTo
    {
        return $this->belongsTo(PlanObjectiveModel::class, 'plan_objective_id');
    }

    public function linkable(): MorphTo
    {
        return $this->morphTo();
    }

    public function syncValue(): ?float
    {
        if (!$this->linkable || !$this->metric_field) {
            return null;
        }

        $value = data_get($this->linkable, $this->metric_field);

        if (is_numeric($value)) {
            $this->objective->update(['current_value' => (float) $value]);
            return (float) $value;
        }

        return null;
    }
}
