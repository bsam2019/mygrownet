<?php

namespace App\Domain\Core\Models;

use Illuminate\Database\Eloquent\Model;

class PlatformSetting extends Model
{
    protected $table = 'platform_settings';

    protected $fillable = ['key', 'value', 'type'];

    protected function casts(): array
    {
        return [
            'value' => 'string',
        ];
    }

    public function getTypedValue(): mixed
    {
        return match ($this->type) {
            'json' => json_decode($this->value, true),
            'boolean' => filter_var($this->value, FILTER_VALIDATE_BOOLEAN),
            'integer' => (int) $this->value,
            default => $this->value,
        };
    }
}
