<?php

namespace App\Domain\Messaging\ValueObjects;

use InvalidArgumentException;

final class MessageContent
{
    private const MAX_SUBJECT_LENGTH = 255;
    private const MAX_BODY_LENGTH = 10000;

    private function __construct(
        private string $subject,
        private string $body
    ) {
        $this->validate();
    }

    public static function create(string $subject, string $body): self
    {
        return new self(trim($subject), trim($body));
    }

    private function validate(): void
    {
        if (empty($this->subject)) {
            throw new InvalidArgumentException('Message subject cannot be empty');
        }

        if (strlen($this->subject) > self::MAX_SUBJECT_LENGTH) {
            throw new InvalidArgumentException(
                sprintf('Message subject cannot exceed %d characters', self::MAX_SUBJECT_LENGTH)
            );
        }

        if (empty($this->body)) {
            throw new InvalidArgumentException('Message body cannot be empty');
        }

        if (strlen($this->body) > self::MAX_BODY_LENGTH) {
            throw new InvalidArgumentException(
                sprintf('Message body cannot exceed %d characters', self::MAX_BODY_LENGTH)
            );
        }
    }

    public function subject(): string
    {
        return $this->subject;
    }

    public function body(): string
    {
        return $this->body;
    }

    public function preview(int $length = 100): string
    {
        if (strlen($this->body) <= $length) {
            return $this->body;
        }

        return substr($this->body, 0, $length) . '...';
    }
}
