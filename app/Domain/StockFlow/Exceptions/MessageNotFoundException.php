<?php

declare(strict_types=1);

namespace App\Domain\StockFlow\Exceptions;

class MessageNotFoundException extends StockFlowException
{
    public function __construct(int $messageId)
    {
        parent::__construct(
            message: "Message #{$messageId} not found.",
            errorCode: 'MESSAGE_NOT_FOUND',
            context: ['message_id' => $messageId],
        );
    }
}
