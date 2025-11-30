<?php

namespace App\Domain\Employee\Services;

use App\Domain\Employee\ValueObjects\EmployeeId;
use App\Models\EmployeePerformanceReview;
use Illuminate\Support\Collection;

class PerformanceReviewService
{
    public function getReviewsForEmployee(EmployeeId $employeeId, array $filters = []): Collection
    {
        $query = EmployeePerformanceReview::forEmployee($employeeId->value())
            ->with('reviewer');

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['type'])) {
            $query->where('review_type', $filters['type']);
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    public function getPendingReviews(EmployeeId $employeeId): Collection
    {
        return EmployeePerformanceReview::forEmployee($employeeId->value())
            ->pending()
            ->with('reviewer')
            ->orderBy('due_date')
            ->get();
    }

    public function getReviewStats(EmployeeId $employeeId): array
    {
        $reviews = EmployeePerformanceReview::forEmployee($employeeId->value())->get();

        return [
            'total' => $reviews->count(),
            'completed' => $reviews->where('status', 'completed')->count(),
            'pending' => $reviews->whereIn('status', ['draft', 'submitted', 'in_review'])->count(),
            'average_rating' => $reviews->where('status', 'completed')->avg('overall_rating'),
            'latest_rating' => $reviews->where('status', 'completed')
                ->sortByDesc('completed_at')
                ->first()?->overall_rating,
        ];
    }

    public function submitSelfAssessment(int $reviewId, array $data): EmployeePerformanceReview
    {
        $review = EmployeePerformanceReview::findOrFail($reviewId);

        $review->update([
            'ratings' => $data['ratings'] ?? $review->ratings,
            'strengths' => $data['strengths'] ?? null,
            'improvements' => $data['improvements'] ?? null,
            'employee_comments' => $data['employee_comments'] ?? null,
            'status' => 'submitted',
            'submitted_at' => now(),
        ]);

        return $review;
    }

    public function getRatingTrends(EmployeeId $employeeId, int $limit = 8): array
    {
        $reviews = EmployeePerformanceReview::forEmployee($employeeId->value())
            ->completed()
            ->whereNotNull('overall_rating')
            ->orderBy('completed_at', 'desc')
            ->limit($limit)
            ->get(['review_period', 'overall_rating', 'completed_at']);

        return [
            'labels' => $reviews->pluck('review_period')->reverse()->values()->toArray(),
            'data' => $reviews->pluck('overall_rating')->reverse()->values()->toArray(),
        ];
    }
}
