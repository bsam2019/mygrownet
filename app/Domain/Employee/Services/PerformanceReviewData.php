<?php

namespace App\Domain\Employee\Services;

class PerformanceReviewData
{
    public function __construct(
        public readonly array $data = []
    ) {}
}