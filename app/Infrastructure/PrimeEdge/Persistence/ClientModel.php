<?php

namespace App\Infrastructure\PrimeEdge\Persistence;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class ClientModel extends Authenticatable
{
    use Notifiable;

    protected $table = 'prime_edge_clients';

    protected $fillable = [
        'id',
        'name',
        'email',
        'password',
        'phone',
        'business_type',
        'company_name',
        'status',
        'last_login_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'last_login_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function getAuthPassword()
    {
        return $this->password;
    }
}
