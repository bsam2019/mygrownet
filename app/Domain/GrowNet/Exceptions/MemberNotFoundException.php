<?php

declare(strict_types=1);

namespace App\Domain\GrowNet\Exceptions;

class MemberNotFoundException extends GrowNetException
{
    public function __construct(int $memberId, ?\Throwable $previous = null)
    {
        parent::__construct("Member not found: {$memberId}", 404, $previous);
    }
}
