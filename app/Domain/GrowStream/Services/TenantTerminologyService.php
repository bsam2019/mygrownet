<?php

namespace App\Domain\GrowStream\Services;

class TenantTerminologyService
{
    private const CATEGORY_MAPS = [
        'education' => [
            'category' => 'education',
            'category_name' => 'Education & Academies',
            'audience_label' => 'Students',
            'enrollment_action' => 'Enroll',
            'content_unit_label' => 'Course',
            'content_unit_plural' => 'Courses',
            'completion_metric' => 'Course Completion',
            'show_revenue' => true,
            'show_publishing_destination' => true,
            'allow_self_serve' => true,
            'default_content_model' => 'course',
        ],
        'business' => [
            'category' => 'business',
            'category_name' => 'Corporate & Internal Training',
            'audience_label' => 'Employees',
            'enrollment_action' => 'Assign',
            'content_unit_label' => 'Training Module',
            'content_unit_plural' => 'Training Modules',
            'completion_metric' => 'Compliance Completion',
            'show_revenue' => false,
            'show_publishing_destination' => false,
            'allow_self_serve' => false,
            'default_content_model' => 'course',
        ],
        'media' => [
            'category' => 'media',
            'category_name' => 'Media & Entertainment Studios',
            'audience_label' => 'Viewers',
            'enrollment_action' => 'Subscribe',
            'content_unit_label' => 'Series',
            'content_unit_plural' => 'Series & Films',
            'completion_metric' => 'Watch Time',
            'show_revenue' => true,
            'show_publishing_destination' => true,
            'allow_self_serve' => true,
            'default_content_model' => 'series',
        ],
        'creator' => [
            'category' => 'creator',
            'category_name' => 'Independent Content Creator',
            'audience_label' => 'Subscribers',
            'enrollment_action' => 'Follow',
            'content_unit_label' => 'Series',
            'content_unit_plural' => 'Series & Playlists',
            'completion_metric' => 'Watch Time',
            'show_revenue' => true,
            'show_publishing_destination' => true,
            'allow_self_serve' => true,
            'default_content_model' => 'series',
        ],
    ];

    public function getMap(?string $category): array
    {
        $cat = strtolower($category ?? 'education');
        return self::CATEGORY_MAPS[$cat] ?? self::CATEGORY_MAPS['education'];
    }

    public function getAllCategories(): array
    {
        return array_values(self::CATEGORY_MAPS);
    }
}
