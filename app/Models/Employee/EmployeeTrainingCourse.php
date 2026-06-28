<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EmployeeTrainingCourse extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'category',
        'type',
        'duration_hours',
        'provider',
        'url',
        'is_mandatory',
        'is_active',
        'skills',
    ];

    protected $casts = [
        'skills' => 'array',
        'is_mandatory' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function enrollments(): HasMany
    {
        return $this->hasMany(EmployeeCourseEnrollment::class, 'course_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeMandatory($query)
    {
        return $query->where('is_mandatory', true);
    }

    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }
}
