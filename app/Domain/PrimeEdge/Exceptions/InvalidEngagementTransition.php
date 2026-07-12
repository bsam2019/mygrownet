<?php

namespace App\Domain\PrimeEdge\Exceptions;

class InvalidEngagementTransition extends PrimeEdgeException
{
    public function __construct(string $fromStatus, string $toStatus)
    {
        parent::__construct(
            message: "Cannot transition engagement from {$fromStatus} to {$toStatus}",
            errorCode: 'INVALID_ENGAGEMENT_TRANSITION',
            context: ['from' => $fromStatus, 'to' => $toStatus]
        );
    }
}
