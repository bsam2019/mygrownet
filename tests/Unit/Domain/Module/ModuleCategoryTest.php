<?php

namespace Tests\Unit\Domain\Module;

use App\Domain\Module\ValueObjects\ModuleCategory;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class ModuleCategoryTest extends TestCase
{
    #[Test]
    public function all_cases_have_correct_values()
    {
        $this->assertEquals('core', ModuleCategory::CORE->value);
        $this->assertEquals('personal', ModuleCategory::PERSONAL->value);
        $this->assertEquals('sme', ModuleCategory::SME->value);
        $this->assertEquals('enterprise', ModuleCategory::ENTERPRISE->value);
    }

    #[Test]
    public function fromString_returns_matching_case()
    {
        $this->assertSame(ModuleCategory::CORE, ModuleCategory::fromString('core'));
        $this->assertSame(ModuleCategory::PERSONAL, ModuleCategory::fromString('personal'));
        $this->assertSame(ModuleCategory::SME, ModuleCategory::fromString('sme'));
        $this->assertSame(ModuleCategory::ENTERPRISE, ModuleCategory::fromString('enterprise'));
    }

    #[Test]
    public function fromString_is_case_insensitive()
    {
        $this->assertSame(ModuleCategory::CORE, ModuleCategory::fromString('CORE'));
        $this->assertSame(ModuleCategory::SME, ModuleCategory::fromString('SME'));
    }

    #[Test]
    public function fromString_defaults_to_sme_for_unknown()
    {
        $this->assertSame(ModuleCategory::SME, ModuleCategory::fromString('unknown'));
    }

    #[Test]
    public function labels_are_readable()
    {
        $this->assertEquals('Core Platform', ModuleCategory::CORE->label());
        $this->assertEquals('Personal Apps', ModuleCategory::PERSONAL->label());
        $this->assertEquals('SME Business Tools', ModuleCategory::SME->label());
        $this->assertEquals('Enterprise Solutions', ModuleCategory::ENTERPRISE->label());
    }

    #[Test]
    public function descriptions_are_readable()
    {
        $this->assertEquals('Essential platform features', ModuleCategory::CORE->description());
        $this->assertEquals('Personal productivity and lifestyle apps', ModuleCategory::PERSONAL->description());
        $this->assertEquals('Small and medium enterprise business tools', ModuleCategory::SME->description());
        $this->assertEquals('Large-scale enterprise solutions', ModuleCategory::ENTERPRISE->description());
    }
}
