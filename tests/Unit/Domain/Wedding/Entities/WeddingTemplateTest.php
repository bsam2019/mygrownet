<?php

namespace Tests\Unit\Domain\Wedding\Entities;

use App\Domain\Wedding\Entities\WeddingTemplate;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class WeddingTemplateTest extends TestCase
{
    public function test_create(): void
    {
        $template = WeddingTemplate::create(
            name: 'Classic Elegance',
            slug: 'classic-elegance',
            settings: ['colors' => ['primary' => '#fff'], 'fonts' => ['body' => 'Georgia'], 'layout' => ['type' => 'single']],
            description: 'A timeless classic design',
            previewImage: 'classic.jpg',
            isActive: true,
            isPremium: true
        );

        $this->assertNull($template->getId());
        $this->assertEquals('Classic Elegance', $template->getName());
        $this->assertEquals('classic-elegance', $template->getSlug());
        $this->assertEquals('A timeless classic design', $template->getDescription());
        $this->assertEquals('classic.jpg', $template->getPreviewImage());
        $this->assertTrue($template->isActive());
        $this->assertTrue($template->isPremium());
    }

    public function test_create_defaults(): void
    {
        $template = WeddingTemplate::create(
            name: 'Minimal',
            slug: 'minimal',
            settings: []
        );

        $this->assertNull($template->getDescription());
        $this->assertNull($template->getPreviewImage());
        $this->assertTrue($template->isActive());
        $this->assertFalse($template->isPremium());
    }

    public function test_get_settings_helpers(): void
    {
        $settings = [
            'colors' => ['primary' => '#ff0000'],
            'fonts' => ['body' => 'Arial'],
            'layout' => ['type' => 'multi-page'],
        ];

        $template = WeddingTemplate::create('Modern', 'modern', $settings);

        $this->assertEquals(['primary' => '#ff0000'], $template->getColors());
        $this->assertEquals(['body' => 'Arial'], $template->getFonts());
        $this->assertEquals(['type' => 'multi-page'], $template->getLayout());
    }

    public function test_get_settings_helpers_return_empty_when_missing(): void
    {
        $template = WeddingTemplate::create('Empty', 'empty', []);

        $this->assertEquals([], $template->getColors());
        $this->assertEquals([], $template->getFonts());
        $this->assertEquals([], $template->getLayout());
    }

    public function test_from_array(): void
    {
        $now = new DateTimeImmutable();
        $data = [
            'id' => 3,
            'name' => 'Rustic',
            'slug' => 'rustic',
            'description' => 'Rustic countryside theme',
            'preview_image' => 'rustic.jpg',
            'settings' => json_encode(['colors' => ['primary' => '#8B4513']]),
            'is_active' => false,
            'is_premium' => false,
            'created_at' => $now->format('Y-m-d H:i:s'),
            'updated_at' => $now->format('Y-m-d H:i:s'),
        ];

        $template = WeddingTemplate::fromArray($data);

        $this->assertEquals(3, $template->getId());
        $this->assertEquals('Rustic', $template->getName());
        $this->assertEquals('rustic', $template->getSlug());
        $this->assertEquals('Rustic countryside theme', $template->getDescription());
        $this->assertEquals('rustic.jpg', $template->getPreviewImage());
        $this->assertFalse($template->isActive());
        $this->assertFalse($template->isPremium());
        $this->assertEquals(['primary' => '#8B4513'], $template->getColors());
        $this->assertNotNull($template->toArray()['created_at']);
    }

    public function test_from_array_with_array_settings(): void
    {
        $data = [
            'name' => 'Test',
            'slug' => 'test',
            'settings' => ['colors' => ['primary' => '#000']],
        ];

        $template = WeddingTemplate::fromArray($data);
        $this->assertEquals(['colors' => ['primary' => '#000']], $template->getSettings());
    }

    public function test_to_array(): void
    {
        $template = WeddingTemplate::create(
            name: 'Beach',
            slug: 'beach',
            settings: ['colors' => ['primary' => '#00f']],
            description: 'Beach wedding',
            isPremium: true
        );

        $result = $template->toArray();

        $this->assertNull($result['id']);
        $this->assertEquals('Beach', $result['name']);
        $this->assertEquals('beach', $result['slug']);
        $this->assertEquals('Beach wedding', $result['description']);
        $this->assertEquals(['colors' => ['primary' => '#00f']], $result['settings']);
        $this->assertTrue($result['is_premium']);
        $this->assertTrue($result['is_active']);
    }
}
