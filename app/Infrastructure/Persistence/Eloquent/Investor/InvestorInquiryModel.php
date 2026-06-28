<?php

namespace App\Infrastructure\Persistence\Eloquent\Investor;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * Investor Inquiry Eloquent Model
 * 
 * Data layer representation of investor inquiries
 */
class InvestorInquiryModel extends Model
{
    use HasFactory;

    protected $table = 'investor_inquiries';

    protected $fillable = [
        'name',
        'email',
        'phone',
        'investment_range',
        'message',
        'status',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
