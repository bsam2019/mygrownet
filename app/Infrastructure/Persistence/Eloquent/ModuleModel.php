<?php

namespace App\Infrastructure\Persistence\Eloquent;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ModuleModel extends Model
{
    protected $table = 'modules';
    
    public $incrementing = false;
    
    protected $keyType = 'string';
    
    protected $fillable = [
        'id',
        'name',
        'slug',
        'category',
        'description',
        'icon',
        'color',
        'thumbnail',
        'account_types',
        'required_roles',
        'min_user_level',
        'routes',
        'pwa_config',
        'features',
        'subscription_tiers',
        'requires_subscription',
        'version',
        'status',
    ];
    
    protected $casts = [
        'account_types' => 'array',
        'required_roles' => 'array',
        'routes' => 'array',
        'pwa_config' => 'array',
        'features' => 'array',
        'subscription_tiers' => 'array',
        'requires_subscription' => 'boolean',
        'min_user_level' => 'integer',
    ];
    
    public function subscriptions(): HasMany
    {
        return $this->hasMany(ModuleSubscriptionModel::class, 'module_id');
    }
    
    public function accessLogs(): HasMany
    {
        return $this->hasMany(ModuleAccessLogModel::class, 'module_id');
    }
}
