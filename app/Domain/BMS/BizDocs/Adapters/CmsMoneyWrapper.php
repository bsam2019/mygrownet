<?php

namespace App\Domain\CMS\BizDocs\Adapters;

class CmsMoneyWrapper
{
    public function __construct(private float $value) {}

    /** Returns amount in cents (BizDocs stores money in cents) */
    public function amount(): int { return (int)round($this->value * 100); }

    public function formatted(): string { return number_format($this->value, 2); }

    public function __toString(): string { return $this->formatted(); }
}
