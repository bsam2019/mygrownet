<?php

namespace App\Domain\PrimeEdge\Exceptions;

class EngagementNotFoundException extends PrimeEdgeException
{
    public function __construct(string $engagementId = '')
    {
        parent::__construct(
            message: "Engagement not found: {$engagementId}",
            errorCode: 'ENGAGEMENT_NOT_FOUND',
            context: ['engagement_id' => $engagementId]
        );
    }
}
