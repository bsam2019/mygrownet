<?php

namespace App\Infrastructure\Persistence\Eloquent\Announcement;

use Illuminate\Database\Eloquent\Model;

class AnnouncementReadModel extends Model
{
    protected $table = 'announcement_reads';

    protected $fillable = [
        'announcement_id',
        'user_id',
        'read_at',
    ];

    protected $casts = [
        'read_at' => 'datetime',
    ];

    public $timestamps = false;
}
