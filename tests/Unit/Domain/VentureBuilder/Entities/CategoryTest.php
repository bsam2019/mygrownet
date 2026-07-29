<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\VentureBuilder\Entities;

use App\Domain\VentureBuilder\Entities\Category;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class CategoryTest extends TestCase
{
    #[Test]
    public function can_be_created_with_minimal_data(): void
    {
        $category = new Category(
            name: 'Technology',
            slug: 'technology',
        );

        $this->assertSame('Technology', $category->name);
        $this->assertSame('technology', $category->slug);
        $this->assertNull($category->id);
    }

    #[Test]
    public function can_be_reconstituted_from_array(): void
    {
        $data = [
            'id' => 3,
            'name' => 'Agriculture',
            'slug' => 'agriculture',
            'description' => 'Farming ventures',
            'icon' => 'leaf',
            'sort_order' => 1,
            'is_active' => true,
        ];

        $category = Category::reconstitute($data);

        $this->assertSame(3, $category->id);
        $this->assertSame('Agriculture', $category->name);
        $this->assertSame('Farming ventures', $category->description);
        $this->assertSame('leaf', $category->icon);
        $this->assertSame(1, $category->sortOrder);
        $this->assertTrue($category->isActive);
    }

    #[Test]
    public function to_array_returns_all_fields(): void
    {
        $category = new Category(
            name: 'Tech',
            slug: 'tech',
            id: 3,
            description: 'Tech ventures',
            createdAt: new DateTimeImmutable('2026-01-01 00:00:00'),
        );

        $arr = $category->toArray();

        $this->assertSame(3, $arr['id']);
        $this->assertSame('Tech', $arr['name']);
        $this->assertSame('tech', $arr['slug']);
        $this->assertSame('Tech ventures', $arr['description']);
        $this->assertSame('2026-01-01 00:00:00', $arr['created_at']);
    }
}
