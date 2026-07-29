<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\GrowFinance\ValueObjects;

use App\Domain\GrowFinance\ValueObjects\AssetStatus;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class AssetStatusTest extends TestCase
{
    #[Test]
    public function all_cases_have_correct_values()
    {
        $this->assertSame('active', AssetStatus::ACTIVE->value);
        $this->assertSame('disposed', AssetStatus::DISPOSED->value);
        $this->assertSame('fully_depreciated', AssetStatus::FULLY_DEPRECIATED->value);
    }

    #[Test]
    public function from_returns_correct_case()
    {
        $this->assertSame(AssetStatus::ACTIVE, AssetStatus::from('active'));
        $this->assertSame(AssetStatus::DISPOSED, AssetStatus::from('disposed'));
        $this->assertSame(AssetStatus::FULLY_DEPRECIATED, AssetStatus::from('fully_depreciated'));
    }

    #[Test]
    public function invalid_value_throws_value_error()
    {
        $this->expectException(\ValueError::class);
        AssetStatus::from('invalid');
    }

    #[Test]
    public function label_returns_correct_string()
    {
        $this->assertSame('Active', AssetStatus::ACTIVE->label());
        $this->assertSame('Disposed', AssetStatus::DISPOSED->label());
        $this->assertSame('Fully Depreciated', AssetStatus::FULLY_DEPRECIATED->label());
    }
}
