<?php

namespace Tests\Unit\GrowBuilder;

use App\Domain\GrowBuilder\Entities\Template;
use App\Domain\GrowBuilder\ValueObjects\TemplateCategory;
use App\Domain\GrowBuilder\ValueObjects\TemplateId;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

class TemplateEntityTest extends TestCase
{
    public function test_create_with_minimal_params(): void
    {
        $template = Template::create(
            name: 'Business Pro',
            slug: 'business-pro',
            category: TemplateCategory::business(),
            structureJson: [['type' => 'hero']],
        );

        $this->assertNull($template->getId());
        $this->assertEquals('Business Pro', $template->getName());
        $this->assertEquals('business-pro', $template->getSlug());
        $this->assertTrue($template->getCategory()->equals(TemplateCategory::business()));
        $this->assertEquals([['type' => 'hero']], $template->getStructureJson());
        $this->assertNull($template->getDescription());
        $this->assertNull($template->getPreviewImage());
        $this->assertNull($template->getThumbnail());
        $this->assertFalse($template->isPremium());
        $this->assertEquals(0, $template->getPrice());
        $this->assertTrue($template->isActive());
        $this->assertEquals(0, $template->getUsageCount());
        $this->assertTrue($template->isFree());
    }

    public function test_create_with_premium(): void
    {
        $template = Template::create(
            name: 'Premium Shop',
            slug: 'premium-shop',
            category: TemplateCategory::shop(),
            structureJson: [],
            description: 'A premium shop template',
            isPremium: true,
            price: 5000,
        );

        $this->assertTrue($template->isPremium());
        $this->assertEquals(5000, $template->getPrice());
        $this->assertFalse($template->isFree());
        $this->assertEquals('A premium shop template', $template->getDescription());
    }

    public function test_reconstitute_with_id(): void
    {
        $now = new DateTimeImmutable();
        $template = Template::reconstitute(
            id: TemplateId::fromInt(1),
            name: 'Restaurant',
            slug: 'restaurant',
            category: TemplateCategory::restaurant(),
            description: 'A restaurant template',
            previewImage: 'preview.jpg',
            thumbnail: 'thumb.jpg',
            structureJson: [['type' => 'menu']],
            defaultStyles: ['primary' => '#ff0000'],
            isPremium: true,
            price: 3000,
            isActive: false,
            usageCount: 15,
            createdAt: $now,
            updatedAt: $now,
        );

        $this->assertEquals(1, $template->getId()->value());
        $this->assertEquals('Restaurant', $template->getName());
        $this->assertEquals('preview.jpg', $template->getPreviewImage());
        $this->assertEquals('thumb.jpg', $template->getThumbnail());
        $this->assertFalse($template->isActive());
        $this->assertEquals(15, $template->getUsageCount());
    }

    public function test_increment_usage(): void
    {
        $template = Template::create('Test', 'test', TemplateCategory::portfolio(), []);
        $template->incrementUsage();
        $this->assertEquals(1, $template->getUsageCount());
        $template->incrementUsage();
        $template->incrementUsage();
        $this->assertEquals(3, $template->getUsageCount());
    }

    public function test_activate_and_deactivate(): void
    {
        $template = Template::create('Test', 'test', TemplateCategory::portfolio(), []);
        $template->deactivate();
        $this->assertFalse($template->isActive());

        $template->activate();
        $this->assertTrue($template->isActive());
    }

    public function test_update_pricing(): void
    {
        $template = Template::create('Test', 'test', TemplateCategory::portfolio(), []);
        $template->updatePricing(isPremium: true, price: 9999);
        $this->assertTrue($template->isPremium());
        $this->assertEquals(9999, $template->getPrice());
    }

    public function test_set_preview_image_and_thumbnail(): void
    {
        $template = Template::create('Test', 'test', TemplateCategory::portfolio(), []);
        $template->setPreviewImage('new-preview.jpg');
        $template->setThumbnail('new-thumb.jpg');
        $this->assertEquals('new-preview.jpg', $template->getPreviewImage());
        $this->assertEquals('new-thumb.jpg', $template->getThumbnail());

        $template->setPreviewImage(null);
        $this->assertNull($template->getPreviewImage());
    }

    public function test_update_structure(): void
    {
        $template = Template::create('Test', 'test', TemplateCategory::portfolio(), ['old']);
        $template->updateStructure(['new', 'structure']);
        $this->assertEquals(['new', 'structure'], $template->getStructureJson());
    }

    public function test_update_default_styles(): void
    {
        $template = Template::create('Test', 'test', TemplateCategory::portfolio(), []);
        $template->updateDefaultStyles(['font' => 'Roboto']);
        $this->assertEquals(['font' => 'Roboto'], $template->getDefaultStyles());
    }

    public function test_free_if_not_premium(): void
    {
        $template = Template::create('Free', 'free', TemplateCategory::business(), []);
        $this->assertTrue($template->isFree());
    }

    public function test_free_if_premium_with_zero_price(): void
    {
        $template = Template::create('Free Premium', 'free-premium', TemplateCategory::business(), [], isPremium: true, price: 0);
        $this->assertTrue($template->isFree());
    }
}
