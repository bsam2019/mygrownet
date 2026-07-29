<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\Storage\ValueObjects;

use App\Domain\Storage\ValueObjects\FileSize;
use App\Domain\Storage\ValueObjects\StorageQuota;
use PHPUnit\Framework\TestCase;

final class StorageQuotaTest extends TestCase
{
    private FileSize $oneGb;
    private FileSize $fiveHundredMb;
    private FileSize $twoHundredMb;

    protected function setUp(): void
    {
        $this->oneGb = FileSize::fromGigabytes(1);
        $this->fiveHundredMb = FileSize::fromMegabytes(500);
        $this->twoHundredMb = FileSize::fromMegabytes(200);
    }

    public function test_can_create_quota(): void
    {
        $quota = StorageQuota::create($this->oneGb, $this->twoHundredMb);
        $this->assertInstanceOf(StorageQuota::class, $quota);
    }

    public function test_can_accommodate_file_within_limit(): void
    {
        $quota = StorageQuota::create($this->oneGb, $this->twoHundredMb);
        $this->assertTrue($quota->canAccommodate($this->fiveHundredMb));
    }

    public function test_cannot_accommodate_file_exceeding_limit(): void
    {
        $quota = StorageQuota::create($this->fiveHundredMb, $this->twoHundredMb);
        $this->assertFalse($quota->canAccommodate($this->fiveHundredMb));
    }

    public function test_get_remaining_returns_correct_size(): void
    {
        $quota = StorageQuota::create($this->oneGb, $this->twoHundredMb);
        $remaining = $quota->getRemaining();
        $expected = $this->oneGb->toBytes() - $this->twoHundredMb->toBytes();
        $this->assertEquals($expected, $remaining->toBytes());
    }

    public function test_get_remaining_never_negative(): void
    {
        $quota = StorageQuota::create($this->twoHundredMb, $this->oneGb);
        $remaining = $quota->getRemaining();
        $this->assertEquals(0, $remaining->toBytes());
    }

    public function test_get_percent_used_returns_correct_value(): void
    {
        $limit = FileSize::fromMegabytes(500);
        $used = FileSize::fromMegabytes(250);
        $quota = StorageQuota::create($limit, $used);
        $this->assertEquals(50.0, $quota->getPercentUsed());
    }

    public function test_get_percent_used_returns_zero_when_limit_is_zero(): void
    {
        $zero = FileSize::fromBytes(0);
        $quota = StorageQuota::create($zero, $zero);
        $this->assertEquals(0, $quota->getPercentUsed());
    }

    public function test_is_near_limit_returns_true_when_above_threshold(): void
    {
        $limit = FileSize::fromMegabytes(100);
        $used = FileSize::fromMegabytes(85);
        $quota = StorageQuota::create($limit, $used);
        $this->assertTrue($quota->isNearLimit());
    }

    public function test_is_near_limit_returns_false_when_below_threshold(): void
    {
        $quota = StorageQuota::create($this->oneGb, $this->twoHundredMb);
        $this->assertFalse($quota->isNearLimit());
    }

    public function test_is_near_limit_uses_custom_threshold(): void
    {
        $quota = StorageQuota::create($this->oneGb, $this->twoHundredMb);
        $this->assertTrue($quota->isNearLimit(10.0));
    }

    public function test_is_exceeded_returns_true_when_over_limit(): void
    {
        $quota = StorageQuota::create($this->twoHundredMb, $this->oneGb);
        $this->assertTrue($quota->isExceeded());
    }

    public function test_is_exceeded_returns_false_when_under_limit(): void
    {
        $quota = StorageQuota::create($this->oneGb, $this->twoHundredMb);
        $this->assertFalse($quota->isExceeded());
    }

    public function test_get_limit_and_get_used(): void
    {
        $quota = StorageQuota::create($this->oneGb, $this->twoHundredMb);
        $this->assertTrue($quota->getLimit()->equals($this->oneGb));
        $this->assertTrue($quota->getUsed()->equals($this->twoHundredMb));
    }

    public function test_exactly_at_limit_can_accommodate(): void
    {
        $limit = FileSize::fromMegabytes(100);
        $used = FileSize::fromMegabytes(60);
        $file = FileSize::fromMegabytes(40);
        $quota = StorageQuota::create($limit, $used);
        $this->assertTrue($quota->canAccommodate($file));
    }
}
