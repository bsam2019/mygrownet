<?php

namespace App\Infrastructure\Persistence\Eloquent\CMS;

use Illuminate\Database\Eloquent\Model;

class PublicHolidayModel extends Model
{
    protected $table = 'cms_public_holidays';

    protected $fillable = [
        'company_id',
        'name',
        'date',
        'is_recurring',
        'description',
    ];

    protected $casts = [
        'date' => 'date',
        'is_recurring' => 'boolean',
    ];

    public function company()
    {
        return $this->belongsTo(CompanyModel::class, 'company_id');
    }

    /**
     * Check if a given date is a public holiday
     */
    public static function isPublicHoliday(int $companyId, string $date): bool
    {
        return self::where('company_id', $companyId)
            ->where('date', $date)
            ->exists();
    }

    /**
     * Get all public holidays for a date range
     */
    public static function getHolidaysInRange(int $companyId, string $startDate, string $endDate): array
    {
        return self::where('company_id', $companyId)
            ->whereBetween('date', [$startDate, $endDate])
            ->pluck('date')
            ->toArray();
    }
}
