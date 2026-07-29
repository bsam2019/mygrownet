<?php

namespace Tests\Unit\Domain\Ubumi\Services;

use App\Domain\Ubumi\Services\SlugGeneratorService;
use App\Domain\Ubumi\Repositories\FamilyRepositoryInterface;
use App\Domain\Ubumi\Repositories\PersonRepositoryInterface;
use App\Domain\Ubumi\ValueObjects\FamilyId;
use App\Domain\Ubumi\ValueObjects\PersonId;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;

class SlugGeneratorServiceTest extends TestCase
{
    private FamilyRepositoryInterface $familyRepo;
    private PersonRepositoryInterface $personRepo;
    private SlugGeneratorService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->familyRepo = $this->createMock(FamilyRepositoryInterface::class);
        $this->personRepo = $this->createMock(PersonRepositoryInterface::class);
        $this->service = new SlugGeneratorService($this->familyRepo, $this->personRepo);
    }

    #[Test]
    public function generateFamilySlug_uses_base_slug_when_available()
    {
        $this->familyRepo->method('slugExists')->willReturn(false);

        $slug = $this->service->generateFamilySlug('My Family');

        $this->assertEquals('my-family', $slug->value());
    }

    #[Test]
    public function generateFamilySlug_appends_suffix_when_slug_taken()
    {
        $this->familyRepo
            ->method('slugExists')
            ->willReturnMap([
                ['my-family', null, true],
                ['my-family-1', null, false],
            ]);

        $slug = $this->service->generateFamilySlug('My Family');

        $this->assertEquals('my-family-1', $slug->value());
    }

    #[Test]
    public function generateFamilySlug_appends_incrementing_suffix()
    {
        $this->familyRepo
            ->method('slugExists')
            ->willReturnMap([
                ['my-family', null, true],
                ['my-family-1', null, true],
                ['my-family-2', null, false],
            ]);

        $slug = $this->service->generateFamilySlug('My Family');

        $this->assertEquals('my-family-2', $slug->value());
    }

    #[Test]
    public function generateFamilySlug_excludes_specified_id()
    {
        $excludeId = FamilyId::generate();
        $this->familyRepo
            ->expects($this->once())
            ->method('slugExists')
            ->with('my-family', $this->equalTo($excludeId))
            ->willReturn(false);

        $this->service->generateFamilySlug('My Family', $excludeId);
    }

    #[Test]
    public function generatePersonSlug_uses_base_slug_when_available()
    {
        $this->personRepo->method('slugExistsInFamily')->willReturn(false);

        $slug = $this->service->generatePersonSlug('family-1', 'Alice');

        $this->assertEquals('alice', $slug->value());
    }

    #[Test]
    public function generatePersonSlug_appends_suffix_when_slug_taken()
    {
        $this->personRepo
            ->method('slugExistsInFamily')
            ->willReturnMap([
                ['family-1', 'alice', null, true],
                ['family-1', 'alice-1', null, false],
            ]);

        $slug = $this->service->generatePersonSlug('family-1', 'Alice');

        $this->assertEquals('alice-1', $slug->value());
    }

    #[Test]
    public function generatePersonSlug_appends_incrementing_suffix()
    {
        $this->personRepo
            ->method('slugExistsInFamily')
            ->willReturnMap([
                ['family-1', 'alice', null, true],
                ['family-1', 'alice-1', null, true],
                ['family-1', 'alice-2', null, false],
            ]);

        $slug = $this->service->generatePersonSlug('family-1', 'Alice');

        $this->assertEquals('alice-2', $slug->value());
    }

    #[Test]
    public function generatePersonSlug_excludes_specified_id()
    {
        $excludeId = PersonId::generate();
        $this->personRepo
            ->expects($this->once())
            ->method('slugExistsInFamily')
            ->with('family-1', 'alice', $this->equalTo($excludeId))
            ->willReturn(false);

        $this->service->generatePersonSlug('family-1', 'Alice', $excludeId);
    }
}
