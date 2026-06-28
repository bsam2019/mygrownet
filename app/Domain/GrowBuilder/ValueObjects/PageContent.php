<?php

declare(strict_types=1);

namespace App\Domain\GrowBuilder\ValueObjects;

final class PageContent
{
    private function __construct(private array $sections) {}

    public static function empty(): self
    {
        return new self([]);
    }

    public static function fromArray(array $sections): self
    {
        // Validate section structure
        foreach ($sections as $section) {
            if (!isset($section['type'])) {
                throw new \InvalidArgumentException('Each section must have a type');
            }
        }

        return new self($sections);
    }

    public static function fromJson(string $json): self
    {
        $data = json_decode($json, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \InvalidArgumentException('Invalid JSON content');
        }

        return self::fromArray($data['sections'] ?? $data);
    }

    public function addSection(array $section, ?int $position = null): self
    {
        if (!isset($section['type'])) {
            throw new \InvalidArgumentException('Section must have a type');
        }

        $sections = $this->sections;

        if ($position === null) {
            $sections[] = $section;
        } else {
            array_splice($sections, $position, 0, [$section]);
        }

        return new self($sections);
    }

    public function updateSection(int $index, array $section): self
    {
        if (!isset($this->sections[$index])) {
            throw new \InvalidArgumentException("Section at index {$index} does not exist");
        }

        $sections = $this->sections;
        $sections[$index] = array_merge($sections[$index], $section);

        return new self($sections);
    }

    public function removeSection(int $index): self
    {
        if (!isset($this->sections[$index])) {
            throw new \InvalidArgumentException("Section at index {$index} does not exist");
        }

        $sections = $this->sections;
        array_splice($sections, $index, 1);

        return new self($sections);
    }

    public function moveSection(int $fromIndex, int $toIndex): self
    {
        if (!isset($this->sections[$fromIndex])) {
            throw new \InvalidArgumentException("Section at index {$fromIndex} does not exist");
        }

        $sections = $this->sections;
        $section = $sections[$fromIndex];
        array_splice($sections, $fromIndex, 1);
        array_splice($sections, $toIndex, 0, [$section]);

        return new self($sections);
    }

    public function getSections(): array
    {
        return $this->sections;
    }

    public function getSection(int $index): ?array
    {
        return $this->sections[$index] ?? null;
    }

    public function count(): int
    {
        return count($this->sections);
    }

    public function isEmpty(): bool
    {
        return empty($this->sections);
    }

    public function toArray(): array
    {
        return ['sections' => $this->sections];
    }

    public function toJson(): string
    {
        return json_encode($this->toArray());
    }
}
