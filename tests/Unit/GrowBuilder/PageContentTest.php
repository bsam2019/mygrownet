<?php

namespace Tests\Unit\GrowBuilder;

use App\Domain\GrowBuilder\ValueObjects\PageContent;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class PageContentTest extends TestCase
{
    public function test_empty(): void
    {
        $content = PageContent::empty();
        $this->assertTrue($content->isEmpty());
        $this->assertEquals(0, $content->count());
        $this->assertEquals([], $content->getSections());
    }

    public function test_from_array(): void
    {
        $sections = [
            ['type' => 'hero', 'content' => ['title' => 'Welcome']],
            ['type' => 'text', 'content' => ['body' => 'About us']],
        ];
        $content = PageContent::fromArray($sections);
        $this->assertEquals(2, $content->count());
        $this->assertFalse($content->isEmpty());
    }

    public function test_from_array_missing_type_throws(): void
    {
        $this->expectException(InvalidArgumentException::class);
        PageContent::fromArray([['content' => ['title' => 'No Type']]]);
    }

    public function test_from_json(): void
    {
        $json = '{"sections":[{"type":"hero","content":{"title":"Welcome"}}]}';
        $content = PageContent::fromJson($json);
        $this->assertEquals(1, $content->count());
        $this->assertEquals('hero', $content->getSection(0)['type']);
    }

    public function test_from_json_without_sections_wrapper(): void
    {
        $json = '[{"type":"hero","content":{"title":"Welcome"}}]';
        $content = PageContent::fromJson($json);
        $this->assertEquals(1, $content->count());
    }

    public function test_from_json_invalid_throws(): void
    {
        $this->expectException(InvalidArgumentException::class);
        PageContent::fromJson('not-json');
    }

    public function test_add_section_appends(): void
    {
        $content = PageContent::empty();
        $content = $content->addSection(['type' => 'hero', 'content' => ['title' => 'Hi']]);
        $this->assertEquals(1, $content->count());
        $this->assertEquals('hero', $content->getSection(0)['type']);
    }

    public function test_add_section_at_position(): void
    {
        $content = PageContent::fromArray([
            ['type' => 'header', 'content' => []],
            ['type' => 'footer', 'content' => []],
        ]);
        $content = $content->addSection(['type' => 'hero'], 1);
        $this->assertEquals(3, $content->count());
        $this->assertEquals('hero', $content->getSection(1)['type']);
    }

    public function test_add_section_without_type_throws(): void
    {
        $this->expectException(InvalidArgumentException::class);
        PageContent::empty()->addSection(['content' => ['title' => 'No Type']]);
    }

    public function test_update_section(): void
    {
        $content = PageContent::fromArray([
            ['type' => 'hero', 'content' => ['title' => 'Old']],
        ]);
        $content = $content->updateSection(0, ['content' => ['title' => 'New']]);
        $this->assertEquals('New', $content->getSection(0)['content']['title']);
    }

    public function test_update_section_nonexistent_throws(): void
    {
        $this->expectException(InvalidArgumentException::class);
        PageContent::empty()->updateSection(5, ['type' => 'hero']);
    }

    public function test_remove_section(): void
    {
        $content = PageContent::fromArray([
            ['type' => 'hero'],
            ['type' => 'footer'],
        ]);
        $content = $content->removeSection(0);
        $this->assertEquals(1, $content->count());
        $this->assertEquals('footer', $content->getSection(0)['type']);
    }

    public function test_remove_section_nonexistent_throws(): void
    {
        $this->expectException(InvalidArgumentException::class);
        PageContent::empty()->removeSection(99);
    }

    public function test_move_section(): void
    {
        $content = PageContent::fromArray([
            ['type' => 'a'],
            ['type' => 'b'],
            ['type' => 'c'],
        ]);
        $content = $content->moveSection(2, 0);
        $this->assertEquals('c', $content->getSection(0)['type']);
        $this->assertEquals('a', $content->getSection(1)['type']);
        $this->assertEquals('b', $content->getSection(2)['type']);
    }

    public function test_move_section_nonexistent_throws(): void
    {
        $this->expectException(InvalidArgumentException::class);
        PageContent::empty()->moveSection(0, 1);
    }

    public function test_to_array(): void
    {
        $content = PageContent::fromArray([['type' => 'hero']]);
        $arr = $content->toArray();
        $this->assertArrayHasKey('sections', $arr);
        $this->assertCount(1, $arr['sections']);
    }

    public function test_to_json(): void
    {
        $content = PageContent::fromArray([['type' => 'hero']]);
        $json = $content->toJson();
        $decoded = json_decode($json, true);
        $this->assertCount(1, $decoded['sections']);
    }

    public function test_returns_null_for_nonexistent_section(): void
    {
        $content = PageContent::empty();
        $this->assertNull($content->getSection(999));
    }
}
