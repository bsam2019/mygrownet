<?php

namespace App\Domain\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FeatureFlag extends Model
{
    protected $fillable = [
        'name', 'application_id', 'enabled', 'rules',
    ];

    protected function casts(): array
    {
        return [
            'enabled' => 'boolean',
            'rules' => 'array',
        ];
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }
}
