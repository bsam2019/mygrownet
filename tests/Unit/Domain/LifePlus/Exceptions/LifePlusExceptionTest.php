<?php

namespace Tests\Unit\Domain\LifePlus\Exceptions;

use App\Domain\LifePlus\Exceptions\LifePlusException;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class LifePlusExceptionTest extends TestCase
{
    #[Test]
    public function notFound_creates_exception_with_message()
    {
        $exception = LifePlusException::notFound('Task');

        $this->assertInstanceOf(\RuntimeException::class, $exception);
        $this->assertSame('Task not found.', $exception->getMessage());
    }

    #[Test]
    public function notOwned_creates_exception_with_message()
    {
        $exception = LifePlusException::notOwned();

        $this->assertSame('You do not own this resource.', $exception->getMessage());
    }

    #[Test]
    public function limitReached_creates_exception_with_message()
    {
        $exception = LifePlusException::limitReached('tasks');

        $this->assertSame('You have reached the limit for tasks.', $exception->getMessage());
    }
}
