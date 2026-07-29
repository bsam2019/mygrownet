<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\QuickInvoice\Services;

use App\Domain\QuickInvoice\Services\TemplateBuilderService;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;

final class TemplateBuilderServiceTest extends TestCase
{
    private TemplateBuilderService $service;

    protected function setUp(): void
    {
        $this->service = new TemplateBuilderService();
    }

    #[Test]
    public function validate_layout_with_valid_layout_returns_no_errors(): void
    {
        $layout = [
            'version' => '1.0',
            'blocks' => [
                ['id' => 'h1', 'type' => 'header'],
                ['id' => 't1', 'type' => 'items-table'],
            ],
        ];
        $this->assertSame([], $this->service->validateLayout($layout));
    }

    #[Test]
    public function validate_layout_missing_version_returns_error(): void
    {
        $errors = $this->service->validateLayout(['blocks' => []]);
        $this->assertContains('Layout version is required', $errors);
    }

    #[Test]
    public function validate_layout_missing_blocks_returns_error(): void
    {
        $errors = $this->service->validateLayout(['version' => '1.0']);
        $this->assertContains('Layout must contain blocks array', $errors);
    }

    #[Test]
    public function validate_layout_blocks_missing_id_returns_error(): void
    {
        $layout = ['version' => '1.0', 'blocks' => [['type' => 'header']]];
        $errors = $this->service->validateLayout($layout);
        $this->assertContains('Block at index 0 missing id', $errors);
    }

    #[Test]
    public function validate_layout_blocks_missing_type_returns_error(): void
    {
        $layout = ['version' => '1.0', 'blocks' => [['id' => 'b1']]];
        $errors = $this->service->validateLayout($layout);
        $this->assertContains('Block at index 0 missing type', $errors);
    }

    #[Test]
    public function validate_field_config_with_all_required_enabled_returns_no_errors(): void
    {
        $config = [
            'invoice_number' => ['enabled' => true],
            'invoice_date' => ['enabled' => true],
            'customer_name' => ['enabled' => true],
            'items_table' => ['enabled' => true],
            'total' => ['enabled' => true],
        ];
        $this->assertSame([], $this->service->validateFieldConfig($config));
    }

    #[Test]
    public function validate_field_config_missing_required_returns_errors(): void
    {
        $config = [
            'invoice_number' => ['enabled' => true],
            'invoice_date' => ['enabled' => false],
            'customer_name' => ['enabled' => true],
        ];
        $errors = $this->service->validateFieldConfig($config);
        $this->assertNotEmpty($errors);
    }

    #[Test]
    public function validate_field_config_disabled_invoice_date_returns_error(): void
    {
        $config = [
            'invoice_number' => ['enabled' => true],
            'invoice_date' => ['enabled' => true],
            'customer_name' => ['enabled' => true],
            'items_table' => ['enabled' => true],
            'total' => ['enabled' => false],
        ];
        $errors = $this->service->validateFieldConfig($config);
        $this->assertContains("Required field 'total' must be enabled", $errors);
    }

    #[Test]
    public function generate_default_layout_has_all_expected_blocks(): void
    {
        $layout = $this->service->generateDefaultLayout();
        $this->assertSame('1.0', $layout['version']);
        $this->assertCount(5, $layout['blocks']);

        $types = array_map(fn($b) => $b['type'], $layout['blocks']);
        $this->assertContains('header', $types);
        $this->assertContains('invoice-meta', $types);
        $this->assertContains('customer-details', $types);
        $this->assertContains('items-table', $types);
        $this->assertContains('totals', $types);
    }

    #[Test]
    public function generate_default_layout_blocks_have_unique_ids(): void
    {
        $layout = $this->service->generateDefaultLayout();
        $ids = array_map(fn($b) => $b['id'], $layout['blocks']);
        $this->assertCount(5, array_unique($ids));
    }

    #[Test]
    public function generate_default_field_config_has_required_fields(): void
    {
        $config = $this->service->generateDefaultFieldConfig();
        $this->assertArrayHasKey('invoice_number', $config);
        $this->assertArrayHasKey('items_table', $config);
        $this->assertArrayHasKey('total', $config);
        $this->assertTrue($config['invoice_number']['enabled']);
        $this->assertTrue($config['invoice_number']['required']);
    }

    #[Test]
    public function generate_default_field_config_has_optional_disabled_fields(): void
    {
        $config = $this->service->generateDefaultFieldConfig();
        $this->assertFalse($config['po_number']['enabled']);
        $this->assertFalse($config['discount']['enabled']);
        $this->assertFalse($config['shipping']['enabled']);
    }
}
