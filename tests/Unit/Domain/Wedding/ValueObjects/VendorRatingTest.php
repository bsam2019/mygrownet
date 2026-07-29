<?php

namespace Tests\Unit\Domain\Wedding\ValueObjects;

use App\Domain\Wedding\ValueObjects\VendorRating;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class VendorRatingTest extends TestCase
{
    public function test_zero_returns_zero_rating(): void
    {
        $rating = VendorRating::zero();
        $this->assertEquals(0, $rating->getRating());
        $this->assertEquals(0, $rating->getReviewCount());
        $this->assertFalse($rating->hasReviews());
    }

    public function test_from_rating(): void
    {
        $rating = VendorRating::fromRating(4.5, 10);
        $this->assertEquals(4.5, $rating->getRating());
        $this->assertEquals(10, $rating->getReviewCount());
        $this->assertTrue($rating->hasReviews());
    }

    public function test_rating_below_zero_throws(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        VendorRating::fromRating(-1, 5);
    }

    public function test_rating_above_five_throws(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        VendorRating::fromRating(5.1, 5);
    }

    public function test_negative_review_count_throws(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        VendorRating::fromRating(4.0, -1);
    }

    public function test_add_review_updates_average(): void
    {
        $rating = VendorRating::fromRating(4.0, 2);
        $updated = $rating->addReview(5);

        $this->assertEquals(3, $updated->getReviewCount());
        $this->assertEqualsWithDelta(4.333, $updated->getRating(), 0.01);
    }

    public function test_add_review_with_invalid_rating_throws(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $rating = VendorRating::fromRating(4.0, 5);
        $rating->addReview(6);
    }

    public function test_add_review_with_zero_rating_throws(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $rating = VendorRating::fromRating(4.0, 5);
        $rating->addReview(0);
    }

    public function test_get_formatted_rating(): void
    {
        $rating = VendorRating::fromRating(4.5, 10);
        $this->assertEquals('4.5', $rating->getFormattedRating());
    }

    public function test_get_star_rating_rounds(): void
    {
        $this->assertEquals(5, VendorRating::fromRating(4.5, 10)->getStarRating());
        $this->assertEquals(4, VendorRating::fromRating(4.4, 10)->getStarRating());
        $this->assertEquals(3, VendorRating::fromRating(2.5, 10)->getStarRating());
    }

    public function test_is_high_rated(): void
    {
        $this->assertTrue(VendorRating::fromRating(4.0, 5)->isHighRated());
        $this->assertFalse(VendorRating::fromRating(3.9, 5)->isHighRated());
        $this->assertFalse(VendorRating::fromRating(4.0, 4)->isHighRated());
    }

    public function test_get_quality_label_no_reviews(): void
    {
        $rating = VendorRating::zero();
        $this->assertEquals('No reviews', $rating->getQualityLabel());
    }

    public function test_get_quality_label_excellent(): void
    {
        $rating = VendorRating::fromRating(4.5, 10);
        $this->assertEquals('Excellent', $rating->getQualityLabel());
    }

    public function test_get_quality_label_very_good(): void
    {
        $rating = VendorRating::fromRating(4.0, 10);
        $this->assertEquals('Very Good', $rating->getQualityLabel());
    }

    public function test_get_quality_label_good(): void
    {
        $rating = VendorRating::fromRating(3.5, 10);
        $this->assertEquals('Good', $rating->getQualityLabel());
    }

    public function test_get_quality_label_average(): void
    {
        $rating = VendorRating::fromRating(3.0, 10);
        $this->assertEquals('Average', $rating->getQualityLabel());
    }

    public function test_get_quality_label_below_average(): void
    {
        $rating = VendorRating::fromRating(2.9, 10);
        $this->assertEquals('Below Average', $rating->getQualityLabel());
    }

    public function test_to_array(): void
    {
        $rating = VendorRating::fromRating(4.0, 5);
        $result = $rating->toArray();

        $this->assertEquals(4.0, $result['rating']);
        $this->assertEquals(5, $result['review_count']);
        $this->assertEquals('4.0', $result['formatted_rating']);
        $this->assertEquals(4, $result['star_rating']);
        $this->assertEquals('Very Good', $result['quality_label']);
        $this->assertTrue($result['has_reviews']);
    }
}
