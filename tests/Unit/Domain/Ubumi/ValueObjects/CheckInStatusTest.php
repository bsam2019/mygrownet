<?php

namespace Tests\Unit\Domain\Ubumi\ValueObjects;

use App\Domain\Ubumi\ValueObjects\CheckInStatus;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;

class CheckInStatusTest extends TestCase
{
    #[Test]
    public function well_case_has_correct_value()
    {
        $this->assertEquals('well', CheckInStatus::WELL->value);
    }

    #[Test]
    public function unwell_case_has_correct_value()
    {
        $this->assertEquals('unwell', CheckInStatus::UNWELL->value);
    }

    #[Test]
    public function need_assistance_case_has_correct_value()
    {
        $this->assertEquals('need_assistance', CheckInStatus::NEED_ASSISTANCE->value);
    }

    #[Test]
    public function from_returns_correct_case()
    {
        $this->assertSame(CheckInStatus::WELL, CheckInStatus::from('well'));
        $this->assertSame(CheckInStatus::UNWELL, CheckInStatus::from('unwell'));
        $this->assertSame(CheckInStatus::NEED_ASSISTANCE, CheckInStatus::from('need_assistance'));
    }

    #[Test]
    public function from_throws_for_invalid_value()
    {
        $this->expectException(\ValueError::class);
        CheckInStatus::from('invalid');
    }

    #[Test]
    public function tryFrom_returns_null_for_invalid_value()
    {
        $this->assertNull(CheckInStatus::tryFrom('invalid'));
    }

    #[Test]
    public function well_label_is_correct()
    {
        $this->assertEquals('I am well', CheckInStatus::WELL->label());
    }

    #[Test]
    public function unwell_label_is_correct()
    {
        $this->assertEquals('I am not feeling well', CheckInStatus::UNWELL->label());
    }

    #[Test]
    public function need_assistance_label_is_correct()
    {
        $this->assertEquals('I need assistance', CheckInStatus::NEED_ASSISTANCE->label());
    }

    #[Test]
    public function emoji_returns_correct_for_each_case()
    {
        $this->assertEquals('😊', CheckInStatus::WELL->emoji());
        $this->assertEquals('😐', CheckInStatus::UNWELL->emoji());
        $this->assertEquals('🆘', CheckInStatus::NEED_ASSISTANCE->emoji());
    }

    #[Test]
    public function color_returns_correct_for_each_case()
    {
        $this->assertEquals('green', CheckInStatus::WELL->color());
        $this->assertEquals('amber', CheckInStatus::UNWELL->color());
        $this->assertEquals('red', CheckInStatus::NEED_ASSISTANCE->color());
    }

    #[Test]
    public function well_does_not_require_alert()
    {
        $this->assertFalse(CheckInStatus::WELL->requiresAlert());
    }

    #[Test]
    public function unwell_requires_alert()
    {
        $this->assertTrue(CheckInStatus::UNWELL->requiresAlert());
    }

    #[Test]
    public function need_assistance_requires_alert()
    {
        $this->assertTrue(CheckInStatus::NEED_ASSISTANCE->requiresAlert());
    }
}
