<?php

namespace App\Domain\ProfitSharing\ValueObjects;

use InvalidArgumentException;

class Quarter
{
    private function __construct(
        private readonly int $year,
        private readonly int $quarter
    ) {
        if ($quarter < 1 || $quarter > 4) {
            throw new InvalidArgumentException('Quarter must be between 1 and 4');
        }
        
        if ($year < 2020 || $year > 2100) {
            throw new InvalidArgumentException('Invalid year');
        }
    }

    public static function create(int $year, int $quarter): self
    {
        return new self($year, $quarter);
    }

    public static function current(): self
    {
        $currentMonth = (int) date('n');
        $currentQuarter = (int) ceil($currentMonth / 3);
        return new self((int) date('Y'), $currentQuarter);
    }

    public function year(): int
    {
        return $this->year;
    }

    public function quarter(): int
    {
        return $this->quarter;
    }

    public function label(): string
    {
        return "Q{$this->quarter} {$this->year}";
    }

    public function startDate(): \DateTimeImmutable
    {
        $month = ($this->quarter - 1) * 3 + 1;
        return new \DateTimeImmutable("{$this->year}-{$month}-01");
    }

    public function endDate(): \DateTimeImmutable
    {
        $month = $this->quarter * 3;
        $lastDay = date('t', mktime(0, 0, 0, $month, 1, $this->year));
        return new \DateTimeImmutable("{$this->year}-{$month}-{$lastDay} 23:59:59");
    }
}
