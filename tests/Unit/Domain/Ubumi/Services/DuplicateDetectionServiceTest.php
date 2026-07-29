<?php

namespace Tests\Unit\Domain\Ubumi\Services;

use App\Domain\Ubumi\Services\DuplicateDetectionService;
use App\Domain\Ubumi\Entities\Person;
use App\Domain\Ubumi\ValueObjects\PersonName;
use App\Domain\Ubumi\ValueObjects\ApproximateAge;
use App\Domain\Ubumi\ValueObjects\Slug;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;

class DuplicateDetectionServiceTest extends TestCase
{
    private DuplicateDetectionService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new DuplicateDetectionService();
    }

    private function createPerson(string $name, ?int $age = null, ?string $gender = null, ?string $photoUrl = null): Person
    {
        $approxAge = $age !== null ? ApproximateAge::fromInt($age) : null;
        return Person::create(
            'family-1',
            PersonName::fromString($name),
            Slug::fromString($name),
            1,
            $photoUrl,
            null,
            $approxAge,
            $gender
        );
    }

    #[Test]
    public function identical_persons_have_high_similarity()
    {
        $person1 = $this->createPerson('Alice Banda', 30, 'female');
        $person2 = $this->createPerson('Alice Banda', 30, 'female');

        $score = $this->service->calculateSimilarity($person1, $person2);

        $this->assertEqualsWithDelta(0.8, $score, 0.2);
    }

    #[Test]
    public function completely_different_persons_have_low_similarity()
    {
        $person1 = $this->createPerson('Alice Banda', 30, 'female');
        $person2 = $this->createPerson('John Smith', 45, 'male');

        $score = $this->service->calculateSimilarity($person1, $person2);

        $this->assertLessThan(0.5, $score);
    }

    #[Test]
    public function same_name_different_age_reduces_score()
    {
        $person1 = $this->createPerson('Alice Banda', 30, 'female');
        $person2 = $this->createPerson('Alice Banda', 50, 'female');

        $score = $this->service->calculateSimilarity($person1, $person2);

        $this->assertLessThan(0.8, $score);
        $this->assertGreaterThan(0.3, $score);
    }

    #[Test]
    public function similar_names_have_moderate_similarity()
    {
        $person1 = $this->createPerson('Alicia Banda', 30, 'female');
        $person2 = $this->createPerson('Alice Banda', 30, 'female');

        $score = $this->service->calculateSimilarity($person1, $person2);

        $this->assertGreaterThan(0.3, $score);
    }

    #[Test]
    public function persons_without_age_still_get_name_score()
    {
        $person1 = $this->createPerson('Alice Banda', null, 'female');
        $person2 = $this->createPerson('Alice Banda', null, 'female');

        $score = $this->service->calculateSimilarity($person1, $person2);

        $this->assertGreaterThan(0.3, $score);
        $this->assertLessThan(0.6, $score);
    }

    #[Test]
    public function different_gender_does_not_add_gender_score()
    {
        $person1 = $this->createPerson('Alice Banda', 30, 'female');
        $person2 = $this->createPerson('Alice Banda', 30, 'male');

        $score = $this->service->calculateSimilarity($person1, $person2);

        $this->assertGreaterThan(0, $score);
    }

    #[Test]
    public function isPotentialDuplicate_returns_true_above_threshold()
    {
        $this->assertTrue($this->service->isPotentialDuplicate(0.6));
        $this->assertTrue($this->service->isPotentialDuplicate(0.9));
    }

    #[Test]
    public function isPotentialDuplicate_returns_false_below_threshold()
    {
        $this->assertFalse($this->service->isPotentialDuplicate(0.59));
        $this->assertFalse($this->service->isPotentialDuplicate(0.0));
    }

    #[Test]
    public function getConfidenceLevel_returns_very_likely_above_80()
    {
        $this->assertEquals('very_likely', $this->service->getConfidenceLevel(0.8));
        $this->assertEquals('very_likely', $this->service->getConfidenceLevel(1.0));
    }

    #[Test]
    public function getConfidenceLevel_returns_possible_between_60_and_80()
    {
        $this->assertEquals('possibly', $this->service->getConfidenceLevel(0.6));
        $this->assertEquals('possibly', $this->service->getConfidenceLevel(0.79));
    }

    #[Test]
    public function getConfidenceLevel_returns_unlikely_below_60()
    {
        $this->assertEquals('unlikely', $this->service->getConfidenceLevel(0.0));
        $this->assertEquals('unlikely', $this->service->getConfidenceLevel(0.59));
    }
}
