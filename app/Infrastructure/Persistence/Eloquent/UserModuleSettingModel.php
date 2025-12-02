<?php

namespace App\Infrastructure\Persistence\Eloquent;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserModuleSettingModel extends Model
{
    protected $table = 'user_module_settings';
    
    protected $fillable = [
        'user_id',
        'module_id',
        'settings',
        'notifications_enabled',
        'pinned',
        'sort_order',
    ];
    
    protected $casts = [
        'settings' => 'array',
        'notifications_enabled' => 'boolean',
        'pinned' => 'boolean',
        'sort_order' => 'integer',
    ];
    
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    
    public function module(): BelongsTo
    {
        return $this->belongsTo(ModuleModel::class, 'module_id');
    }
}
