<?php

namespace Tests\Unit\Domain\Wedding\ValueObjects;

use App\Domain\Wedding\ValueObjects\VendorCategory;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class VendorCategoryTest extends TestCase
{
    public function test_venue_factory(): void
    {
        $category = VendorCategory::venue();
        $this->assertEquals('venue', $category->getValue());
        $this->assertEquals('Venues', $category->getLabel());
        $this->assertEquals('building-office', $category->getIcon());
        $this->assertTrue($category->isVenue());
        $this->assertFalse($category->isPhotography());
    }

    public function test_photography_factory(): void
    {
        $category = VendorCategory::photography();
        $this->assertEquals('photography', $category->getValue());
        $this->assertEquals('Photography', $category->getLabel());
        $this->assertEquals('camera', $category->getIcon());
        $this->assertTrue($category->isPhotography());
    }

    public function test_catering_factory(): void
    {
        $category = VendorCategory::catering();
        $this->assertEquals('catering', $category->getValue());
        $this->assertEquals('Catering', $category->getLabel());
        $this->assertTrue($category->isCatering());
    }

    public function test_decoration_factory(): void
    {
        $category = VendorCategory::decoration();
        $this->assertEquals('decoration', $category->getValue());
        $this->assertEquals('Decoration', $category->getLabel());
        $this->assertFalse($category->isVenue());
        $this->assertFalse($category->isPhotography());
        $this->assertFalse($category->isCatering());
    }

    public function test_music_factory(): void
    {
        $category = VendorCategory::music();
        $this->assertEquals('music', $category->getValue());
        $this->assertEquals('Music & Entertainment', $category->getLabel());
        $this->assertTrue($category->equals(VendorCategory::fromString('music')));
    }

    public function test_transport_factory(): void
    {
        $category = VendorCategory::transport();
        $this->assertEquals('transport', $category->getValue());
        $this->assertEquals('Transportation', $category->getLabel());
        $this->assertTrue($category->equals(VendorCategory::fromString('transport')));
    }

    public function test_flowers_factory(): void
    {
        $category = VendorCategory::flowers();
        $this->assertEquals('flowers', $category->getValue());
        $this->assertEquals('Flowers & Bouquets', $category->getLabel());
        $this->assertTrue($category->equals(VendorCategory::fromString('flowers')));
    }

    public function test_makeup_factory(): void
    {
        $category = VendorCategory::makeup();
        $this->assertEquals('makeup', $category->getValue());
        $this->assertEquals('Makeup & Beauty', $category->getLabel());
        $this->assertTrue($category->equals(VendorCategory::fromString('makeup')));
    }

    public function test_planning_factory(): void
    {
        $category = VendorCategory::planning();
        $this->assertEquals('planning', $category->getValue());
        $this->assertEquals('Wedding Planning', $category->getLabel());
        $this->assertEquals('clipboard-document-list', $category->getIcon());
        $this->assertTrue($category->equals(VendorCategory::fromString('planning')));
    }

    public function test_from_string(): void
    {
        $category = VendorCategory::fromString('catering');
        $this->assertTrue($category->isCatering());
        $this->assertEquals('catering', $category->getValue());
    }

    public function test_from_string_invalid_throws_exception(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        VendorCategory::fromString('invalid_category');
    }

    public function test_all_returns_all_categories(): void
    {
        $all = VendorCategory::all();
        $this->assertCount(9, $all);
        $this->assertEquals('venue', $all[0]->getValue());
        $this->assertEquals('planning', $all[8]->getValue());
    }

    public function test_equals(): void
    {
        $a = VendorCategory::venue();
        $b = VendorCategory::venue();
        $c = VendorCategory::photography();

        $this->assertTrue($a->equals($b));
        $this->assertFalse($a->equals($c));
    }

    public function test_to_array(): void
    {
        $category = VendorCategory::music();
        $result = $category->toArray();

        $this->assertEquals('music', $result['value']);
        $this->assertEquals('Music & Entertainment', $result['label']);
        $this->assertEquals('musical-note', $result['icon']);
    }
}
