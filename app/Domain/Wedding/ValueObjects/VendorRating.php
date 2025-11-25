<?php

namespace App\Domain\Wedding\ValueObjects;

class VendorRating
{
    private function __construct(
        private float $rating,
        private int $reviewCount
    ) {
        if ($rating < 0 || $rating > 5) {
            throw new \InvalidArgumentException('Rating must be between 0 and 5');
        }
        
        if ($reviewCount < 0) {
            throw new \InvalidArgumentException('Review count cannot be negative');
        }
    }

    public static function zero(): self
    {
        return new self(0, 0);
    }

    public static function fromRating(float $rating, int $reviewCount): self
    {
        return new self($rating, $reviewCount);
    }

    public function addReview(int $newRating): self
    {
        if ($newRating < 1 || $newRating > 5) {
            throw new \InvalidArgumentException('New rating must be between 1 and 5');
        }

        $totalRating = ($this->rating * $this->reviewCount) + $newRating;
        $newReviewCount = $this->reviewCount + 1;
        $newAverageRating = $totalRating / $newReviewCount;

        return new self($newAverageRating, $newReviewCount);
    }

    public function getRating(): float
    {
        return $this->rating;
    }

    public function getReviewCount(): int
    {
        return $this->reviewCount;
    }

    public function getFormattedRating(): string
    {
        return number_format($this->rating, 1);
    }

    public function getStarRating(): int
    {
        return (int) round($this->rating);
    }

    public function hasReviews(): bool
    {
        return $this->reviewCount > 0;
    }

    public function isHighRated(): bool
    {
        return $this->rating >= 4.0 && $this->reviewCount >= 5;
    }

    public function getQualityLabel(): string
    {
        if (!$this->hasReviews()) {
            return 'No reviews';
        }

        return match (true) {
            $this->rating >= 4.5 => 'Excellent',
            $this->rating >= 4.0 => 'Very Good',
            $this->rating >= 3.5 => 'Good',
            $this->rating >= 3.0 => 'Average',
            default => 'Below Average'
        };
    }

    public function toArray(): array
    {
        return [
            'rating' => $this->rating,
            'review_count' => $this->reviewCount,
            'formatted_rating' => $this->getFormattedRating(),
            'star_rating' => $this->getStarRating(),
            'quality_label' => $this->getQualityLabel(),
            'has_reviews' => $this->hasReviews()
        ];
    }
}