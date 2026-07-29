<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\QuickInvoice\Entities;

use App\Domain\QuickInvoice\Entities\Template;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;

final class TemplateTest extends TestCase
{
    #[Test]
    public function reconstitute_sets_all_fields(): void
    {
        $template = Template::reconstitute([
            'id' => 1,
            'user_id' => 42,
            'name' => 'My Template',
            'description' => 'A custom template',
            'base_template' => 'modern',
            'primary_color' => '#ff0000',
            'secondary_color' => '#00ff00',
            'accent_color' => '#0000ff',
            'font_family' => 'Arial',
            'heading_font' => 'Helvetica',
            'header_style' => 'centered',
            'layout_style' => 'standard',
            'show_logo' => true,
            'show_business_details' => true,
            'logo_position' => 'left',
            'logo_size' => 100,
            'border_radius' => 8,
            'border_style' => 'solid',
            'spacing' => 20,
            'table_style' => 'striped',
            'custom_css' => ['.test {}'],
            'section_visibility' => ['header' => true],
            'field_labels' => ['total' => 'Grand Total'],
            'layout_json' => ['version' => '1.0', 'blocks' => []],
            'field_config' => ['items_table' => ['enabled' => true]],
            'logo_url' => 'logos/my-logo.png',
            'version' => 3,
            'category' => 'invoice',
            'tags' => ['professional', 'dark'],
            'is_public' => true,
            'usage_count' => 15,
            'last_used_at' => '2026-07-28 12:00:00',
            'created_at' => '2026-01-01 00:00:00',
            'updated_at' => '2026-07-01 00:00:00',
        ]);

        $this->assertSame(1, $template->id);
        $this->assertSame(42, $template->userId);
        $this->assertSame('My Template', $template->name);
        $this->assertSame('#ff0000', $template->primaryColor);
        $this->assertSame('left', $template->logoPosition);
        $this->assertSame(8, $template->borderRadius);
        $this->assertSame('striped', $tableStyle = $template->tableStyle);
        $this->assertSame(3, $template->version);
        $this->assertTrue($template->isPublic);
        $this->assertSame(15, $template->usageCount);
    }

    #[Test]
    public function reconstitute_with_minimal_data(): void
    {
        $template = Template::reconstitute(['user_id' => 1, 'name' => 'Minimal']);
        $this->assertNull($template->id);
        $this->assertSame(1, $template->userId);
        $this->assertSame('Minimal', $template->name);
        $this->assertNull($template->description);
        $this->assertNull($template->primaryColor);
    }

    #[Test]
    public function reconstitute_parses_date_fields(): void
    {
        $template = Template::reconstitute([
            'user_id' => 1,
            'name' => 'Test',
            'created_at' => '2026-06-15 10:30:00',
            'updated_at' => '2026-07-20 14:45:00',
            'last_used_at' => '2026-07-28 08:00:00',
        ]);
        $this->assertNotNull($template->createdAt);
        $this->assertSame('2026-06-15 10:30:00', $template->createdAt->format('Y-m-d H:i:s'));
        $this->assertSame('2026-07-20 14:45:00', $template->updatedAt->format('Y-m-d H:i:s'));
        $this->assertSame('2026-07-28 08:00:00', $template->lastUsedAt->format('Y-m-d H:i:s'));
    }

    #[Test]
    public function reconstitute_handles_null_dates(): void
    {
        $template = Template::reconstitute([
            'user_id' => 1,
            'name' => 'No dates',
        ]);
        $this->assertNull($template->createdAt);
        $this->assertNull($template->updatedAt);
        $this->assertNull($template->lastUsedAt);
    }

    #[Test]
    public function to_array_returns_all_fields(): void
    {
        $data = [
            'id' => 5,
            'user_id' => 10,
            'name' => 'Full Template',
            'description' => 'Desc',
            'base_template' => 'classic',
            'primary_color' => '#111',
            'is_public' => true,
            'usage_count' => 3,
            'created_at' => '2026-01-01 00:00:00',
            'updated_at' => null,
        ];
        $template = Template::reconstitute($data);
        $arr = $template->toArray();

        $this->assertSame(5, $arr['id']);
        $this->assertSame(10, $arr['user_id']);
        $this->assertSame('Full Template', $arr['name']);
        $this->assertTrue($arr['is_public']);
        $this->assertSame('2026-01-01 00:00:00', $arr['created_at']);
        $this->assertNull($arr['updated_at']);
    }

    #[Test]
    public function tags_default_to_null_when_not_set(): void
    {
        $template = Template::reconstitute(['user_id' => 1, 'name' => 'No Tags']);
        $this->assertNull($template->tags);
    }

    #[Test]
    public function tags_are_preserved(): void
    {
        $template = Template::reconstitute([
            'user_id' => 1,
            'name' => 'Tagged',
            'tags' => ['sale', 'invoice'],
        ]);
        $this->assertSame(['sale', 'invoice'], $template->tags);
    }
}
