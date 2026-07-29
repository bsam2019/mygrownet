<?php

namespace Tests\Unit\Domain\Core;

use App\Domain\Core\Services\ManifestValidator;
use App\Domain\Core\ValueObjects\ModuleManifest;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class ManifestValidatorTest extends TestCase
{
    private ManifestValidator $validator;

    protected function setUp(): void
    {
        $this->validator = new ManifestValidator();
    }

    #[Test]
    public function validates_valid_manifest()
    {
        $manifest = new ModuleManifest(
            id: 'stockflow',
            name: 'StockFlow',
            version: '1.0.0',
            category: 'business',
            contracts: [],
            maxPlatformVersion: '99.0',
        );

        $this->assertTrue($this->validator->validate($manifest));
        $this->assertEmpty($this->validator->errors());
    }

    #[Test]
    public function fails_on_empty_id()
    {
        $manifest = new ModuleManifest(
            id: '',
            name: 'Test',
            version: '1.0.0',
            category: 'core',
        );

        $this->assertFalse($this->validator->validate($manifest));
        $this->assertContains('Module id is required', $this->validator->errors());
    }

    #[Test]
    public function fails_on_empty_name()
    {
        $manifest = new ModuleManifest(
            id: 'test',
            name: '',
            version: '1.0.0',
            category: 'core',
        );

        $this->assertFalse($this->validator->validate($manifest));
        $this->assertContains('Module name is required', $this->validator->errors());
    }

    #[Test]
    public function fails_on_empty_version()
    {
        $manifest = new ModuleManifest(
            id: 'test',
            name: 'Test',
            version: '',
            category: 'core',
        );

        $this->assertFalse($this->validator->validate($manifest));
        $this->assertContains('Module version is required', $this->validator->errors());
    }

    #[Test]
    public function fails_on_invalid_min_platform_version()
    {
        $manifest = new ModuleManifest(
            id: 'test',
            name: 'Test',
            version: '1.0.0',
            category: 'core',
            minPlatformVersion: 'abc',
        );

        $this->assertFalse($this->validator->validate($manifest));
        $this->assertContains("Invalid min_platform_version 'abc'", $this->validator->errors());
    }

    #[Test]
    public function fails_on_invalid_max_platform_version()
    {
        $manifest = new ModuleManifest(
            id: 'test',
            name: 'Test',
            version: '1.0.0',
            category: 'core',
            maxPlatformVersion: 'bad',
        );

        $this->assertFalse($this->validator->validate($manifest));
        $this->assertContains("Invalid max_platform_version 'bad'", $this->validator->errors());
    }

    #[Test]
    public function accepts_valid_version_formats()
    {
        $manifest = new ModuleManifest(
            id: 'test',
            name: 'Test',
            version: '1.0.0',
            category: 'core',
            minPlatformVersion: '2.0',
            maxPlatformVersion: '3.0',
        );

        $this->assertTrue($this->validator->validate($manifest));
    }

    #[Test]
    public function fails_when_module_self_references_capability()
    {
        $manifest = new ModuleManifest(
            id: 'test',
            name: 'Test',
            version: '1.0.0',
            category: 'core',
            capabilities: ['storage'],
            requiredCapabilities: ['storage'],
        );

        $this->assertFalse($this->validator->validate($manifest));
        $this->assertContains(
            "Module 'test' lists 'storage' as both a provider and consumer capability",
            $this->validator->errors(),
        );
    }

    #[Test]
    public function detects_non_existent_contracts()
    {
        $manifest = new ModuleManifest(
            id: 'test',
            name: 'Test',
            version: '1.0.0',
            category: 'core',
            contracts: ['App\Contracts\NonExistentContract'],
        );

        $this->assertFalse($this->validator->validate($manifest));
        $this->assertContains(
            "Contract interface 'App\Contracts\NonExistentContract' does not exist",
            $this->validator->errors(),
        );
    }

    #[Test]
    public function accumulates_multiple_errors()
    {
        $manifest = new ModuleManifest(
            id: '',
            name: '',
            version: '',
            category: 'core',
            maxPlatformVersion: '9.9',
        );

        $this->assertFalse($this->validator->validate($manifest));
        $this->assertCount(3, $this->validator->errors());
    }
}
