<?php

namespace App\Infrastructure\Persistence\Eloquent\StockFlow;

use Illuminate\Foundation\Auth\User as Authenticatable;

class SaUserModel extends Authenticatable
{
    protected $table = 'sa_users';
    protected $guarded = ['id'];
    protected $hidden = ['password', 'remember_token'];
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];
}
