<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\GrowStart\Entities;

use App\Domain\GrowStart\Entities\Stage;
use App\Domain\GrowStart\ValueObjects\StageSlug;
use PHPUnit\Framework\TestCase;

final class StageTest extends TestCase
{
    public function test_can_create_stage(): void
    {
        $stage = Stage::create('Idea Validation', StageSlug::idea(), 1, 'Validate your business idea', 'lightbulb', '#FF6B35', 7);

        $this->assertEquals(0, $stage->getId());
        $this->assertEquals('Idea Validation', $stage->getName());
        $this->assertTrue($stage->getSlug()->equals(StageSlug::idea()));
        $this->assertEquals('Validate your business idea', $stage->getDescription());
        $this->assertEquals(1, $stage->getOrder());
        $this->assertEquals('lightbulb', $stage->getIcon());
        $this->assertEquals('#FF6B35', $stage->getColor());
        $this->assertEquals(7, $stage->getEstimatedDays());
        $this->assertTrue($stage->isActive());
    }

    public function test_can_create_stage_with_minimal_args(): void
    {
        $stage = Stage::create('Growth', StageSlug::growth(), 8);
        $this->assertEquals('Growth', $stage->getName());
        $this->assertNull($stage->getDescription());
        $this->assertNull($stage->getIcon());
        $this->assertNull($stage->getColor());
        $this->assertEquals(7, $stage->getEstimatedDays());
    }

    public function test_can_reconstitute_stage(): void
    {
        $stage = Stage::reconstitute(5, 'Launch', StageSlug::launch(), 'Go to market', 5, 'rocket', '#00B4D8', 14, true);

        $this->assertEquals(5, $stage->getId());
        $this->assertEquals('Launch', $stage->getName());
        $this->assertTrue($stage->getSlug()->equals(StageSlug::launch()));
        $this->assertEquals(14, $stage->getEstimatedDays());
        $this->assertTrue($stage->isActive());
    }

    public function test_reconstitute_can_set_inactive(): void
    {
        $stage = Stage::reconstitute(3, 'Old', StageSlug::accounting(), null, 6, null, null, 7, false);
        $this->assertFalse($stage->isActive());
    }

    public function test_is_first_returns_true_for_order_one(): void
    {
        $stage = Stage::create('Idea', StageSlug::idea(), 1);
        $this->assertTrue($stage->isFirst());
    }

    public function test_is_first_returns_false_for_other_orders(): void
    {
        $stage = Stage::create('Validation', StageSlug::validation(), 2);
        $this->assertFalse($stage->isFirst());
    }

    public function test_is_last_returns_true_for_order_eight(): void
    {
        $stage = Stage::create('Growth', StageSlug::growth(), 8);
        $this->assertTrue($stage->isLast());
    }

    public function test_is_last_returns_false_for_other_orders(): void
    {
        $stage = Stage::create('Planning', StageSlug::planning(), 3);
        $this->assertFalse($stage->isLast());
    }

    public function test_to_array_returns_expected_structure(): void
    {
        $stage = Stage::create('Marketing', StageSlug::marketing(), 7, 'Promote your business', 'megaphone', '#FF4D6D', 10);
        $result = $stage->toArray();

        $this->assertEquals(0, $result['id']);
        $this->assertEquals('Marketing', $result['name']);
        $this->assertEquals('marketing', $result['slug']);
        $this->assertEquals('Promote your business', $result['description']);
        $this->assertEquals(7, $result['order']);
        $this->assertEquals('megaphone', $result['icon']);
        $this->assertEquals('#FF4D6D', $result['color']);
        $this->assertEquals(10, $result['estimated_days']);
        $this->assertTrue($result['is_active']);
    }
}
