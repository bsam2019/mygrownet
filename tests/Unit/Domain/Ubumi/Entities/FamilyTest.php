<?php

namespace Tests\Unit\Domain\Ubumi\Entities;

use App\Domain\Ubumi\Entities\Family;
use App\Domain\Ubumi\ValueObjects\FamilyId;
use App\Domain\Ubumi\ValueObjects\FamilyName;
use App\Domain\Ubumi\ValueObjects\Slug;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;

class FamilyTest extends TestCase
{
    private FamilyName $name;
    private Slug $slug;

    protected function setUp(): void
    {
        parent::setUp();
        $this->name = FamilyName::fromString('Smith');
        $this->slug = Slug::fromString('smith');
    }

    #[Test]
    public function create_generates_id_and_sets_createdAt()
    {
        $before = new DateTimeImmutable();
        $family = Family::create($this->name, $this->slug, 1);
        $after = new DateTimeImmutable();

        $this->assertInstanceOf(Family::class, $family);
        $this->assertInstanceOf(FamilyId::class, $family->getId());
        $this->assertTrue($family->getCreatedAt() >= $before && $family->getCreatedAt() <= $after);
        $this->assertNull($family->getUpdatedAt());
    }

    #[Test]
    public function create_sets_all_fields()
    {
        $family = Family::create($this->name, $this->slug, 42);

        $this->assertTrue($this->name->equals($family->getName()));
        $this->assertEquals($this->slug->value(), $family->getSlug()->value());
        $this->assertEquals(42, $family->getAdminUserId());
    }

    #[Test]
    public function reconstitute_restores_from_stored_data()
    {
        $id = FamilyId::generate();
        $createdAt = new DateTimeImmutable('2026-01-01');
        $updatedAt = new DateTimeImmutable('2026-01-15');

        $family = Family::reconstitute($id, $this->name, $this->slug, 42, $createdAt, $updatedAt);

        $this->assertTrue($id->equals($family->getId()));
        $this->assertTrue($this->name->equals($family->getName()));
        $this->assertEquals($this->slug->value(), $family->getSlug()->value());
        $this->assertEquals(42, $family->getAdminUserId());
        $this->assertEquals($createdAt, $family->getCreatedAt());
        $this->assertEquals($updatedAt, $family->getUpdatedAt());
    }

    #[Test]
    public function reconstitute_with_null_updatedAt()
    {
        $id = FamilyId::generate();
        $createdAt = new DateTimeImmutable('2026-01-01');

        $family = Family::reconstitute($id, $this->name, $this->slug, 42, $createdAt, null);

        $this->assertNull($family->getUpdatedAt());
    }

    #[Test]
    public function changeName_updates_name_and_slug()
    {
        $family = Family::create($this->name, $this->slug, 1);
        $newName = FamilyName::fromString('Jones');
        $newSlug = Slug::fromString('jones');

        $family->changeName($newName, $newSlug);

        $this->assertTrue($newName->equals($family->getName()));
        $this->assertEquals($newSlug->value(), $family->getSlug()->value());
        $this->assertNotNull($family->getUpdatedAt());
    }

    #[Test]
    public function changeAdmin_updates_admin_user()
    {
        $family = Family::create($this->name, $this->slug, 1);
        $family->changeAdmin(99);

        $this->assertEquals(99, $family->getAdminUserId());
        $this->assertNotNull($family->getUpdatedAt());
    }

    #[Test]
    public function isAdmin_returns_true_for_admin_user()
    {
        $family = Family::create($this->name, $this->slug, 42);
        $this->assertTrue($family->isAdmin(42));
    }

    #[Test]
    public function isAdmin_returns_false_for_non_admin_user()
    {
        $family = Family::create($this->name, $this->slug, 42);
        $this->assertFalse($family->isAdmin(1));
    }
}
