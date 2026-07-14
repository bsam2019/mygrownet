<?php

declare(strict_types=1);

namespace App\Domain\StockFlow\Exceptions;

class CommentNotFoundException extends StockFlowException
{
    public function __construct(int $commentId)
    {
        parent::__construct(
            message: "Comment #{$commentId} not found.",
            errorCode: 'COMMENT_NOT_FOUND',
            context: ['comment_id' => $commentId],
        );
    }
}
