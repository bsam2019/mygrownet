<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\GrowStart\Entities;

use App\Domain\GrowStart\Entities\Task;
use PHPUnit\Framework\TestCase;

final class TaskTest extends TestCase
{
    public function test_can_create_task(): void
    {
        $task = Task::create(
            stageId: 1,
            title: 'Write business plan',
            industryId: null,
            countryId: null,
            description: 'Create a detailed business plan',
            instructions: 'Use the template provided',
            externalLink: 'https://example.com/template',
            estimatedHours: 3,
            order: 1,
            isRequired: true,
            isPremium: false,
        );

        $this->assertEquals(0, $task->getId());
        $this->assertEquals(1, $task->getStageId());
        $this->assertNull($task->getIndustryId());
        $this->assertNull($task->getCountryId());
        $this->assertEquals('Write business plan', $task->getTitle());
        $this->assertEquals('Create a detailed business plan', $task->getDescription());
        $this->assertEquals('Use the template provided', $task->getInstructions());
        $this->assertEquals('https://example.com/template', $task->getExternalLink());
        $this->assertEquals(3, $task->getEstimatedHours());
        $this->assertEquals(1, $task->getOrder());
        $this->assertTrue($task->isRequired());
        $this->assertFalse($task->isPremium());
    }

    public function test_can_create_task_with_defaults(): void
    {
        $task = Task::create(stageId: 1, title: 'Simple task');

        $this->assertEquals(1, $task->getEstimatedHours());
        $this->assertEquals(0, $task->getOrder());
        $this->assertTrue($task->isRequired());
        $this->assertFalse($task->isPremium());
        $this->assertNull($task->getIndustryId());
        $this->assertNull($task->getCountryId());
        $this->assertNull($task->getDescription());
        $this->assertNull($task->getInstructions());
        $this->assertNull($task->getExternalLink());
    }

    public function test_can_reconstitute_task(): void
    {
        $task = Task::reconstitute(10, 2, 5, 1, 'Market research', 'Research the market', null, null, 5, 2, true, false);

        $this->assertEquals(10, $task->getId());
        $this->assertEquals(2, $task->getStageId());
        $this->assertEquals(5, $task->getIndustryId());
        $this->assertEquals(1, $task->getCountryId());
    }

    public function test_is_generic_when_no_industry_and_no_country(): void
    {
        $task = Task::create(stageId: 1, title: 'Generic');
        $this->assertTrue($task->isGeneric());
        $this->assertFalse($task->isIndustrySpecific());
        $this->assertFalse($task->isCountrySpecific());
    }

    public function test_is_industry_specific_when_industry_set(): void
    {
        $task = Task::create(stageId: 1, title: 'Industry specific', industryId: 3);
        $this->assertTrue($task->isIndustrySpecific());
        $this->assertFalse($task->isGeneric());
    }

    public function test_is_country_specific_when_country_set(): void
    {
        $task = Task::create(stageId: 1, title: 'Country specific', countryId: 2);
        $this->assertTrue($task->isCountrySpecific());
        $this->assertFalse($task->isGeneric());
    }

    public function test_applies_to_industry_when_null_or_matching(): void
    {
        $task = Task::create(stageId: 1, title: 'Task', industryId: 3);
        $this->assertTrue($task->appliesToIndustry(3));
        $this->assertFalse($task->appliesToIndustry(null));
        $this->assertFalse($task->appliesToIndustry(99));
    }

    public function test_applies_to_country_when_null_or_matching(): void
    {
        $task = Task::create(stageId: 1, title: 'Task', countryId: 2);
        $this->assertTrue($task->appliesToCountry(2));
        $this->assertFalse($task->appliesToCountry(null));
        $this->assertFalse($task->appliesToCountry(99));
    }

    public function test_generic_task_applies_to_all(): void
    {
        $task = Task::create(stageId: 1, title: 'Generic');
        $this->assertTrue($task->appliesToIndustry(5));
        $this->assertTrue($task->appliesToCountry(3));
    }

    public function test_to_array_returns_expected_structure(): void
    {
        $task = Task::create(
            stageId: 2,
            title: 'Register company',
            industryId: 5,
            countryId: 1,
            description: 'Register with PACRA',
            instructions: 'Step by step guide',
            externalLink: 'https://pacra.zm',
            estimatedHours: 4,
            order: 3,
            isRequired: true,
            isPremium: true,
        );
        $result = $task->toArray();

        $this->assertEquals(2, $result['stage_id']);
        $this->assertEquals('Register company', $result['title']);
        $this->assertEquals(5, $result['industry_id']);
        $this->assertEquals(1, $result['country_id']);
        $this->assertEquals('Register with PACRA', $result['description']);
        $this->assertEquals('Step by step guide', $result['instructions']);
        $this->assertEquals('https://pacra.zm', $result['external_link']);
        $this->assertEquals(4, $result['estimated_hours']);
        $this->assertEquals(3, $result['order']);
        $this->assertTrue($result['is_required']);
        $this->assertTrue($result['is_premium']);
    }
}
