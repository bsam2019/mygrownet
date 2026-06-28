<?php

namespace App\Infrastructure\Persistence\Eloquent;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ModuleAccessLogModel extends Model
{
    protected $table = 'module_access_logs';
    
    public $timestamps = false;
    
    protected $fillable = [
        'user_id',
        'module_id',
        'action',
        'route',
        'metadata',
        'accessed_at',
    ];
    
    protected $casts = [
        'metadata' => 'array',
        'accessed_at' => 'datetime',
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
