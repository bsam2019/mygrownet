<?php

namespace Tests\Unit\Domain\Transaction\Repositories;

use App\Domain\Transaction\Enums\TransactionStatus;
use App\Domain\Transaction\Enums\TransactionType;
use App\Domain\Transaction\Repositories\TransactionRepositoryInterface;
use App\Domain\Transaction\ValueObjects\TransactionSource;
use App\Domain\GrowNet\Wallet\ValueObjects\Money;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class TransactionRepositoryInterfaceTest extends TestCase
{
    private function createSource(string $code): TransactionSource
    {
        $reflection = new \ReflectionClass(TransactionSource::class);
        $instance = $reflection->newInstanceWithoutConstructor();
        $prop = $reflection->getProperty('source');
        $prop->setAccessible(true);
        $prop->setValue($instance, $code);
        return $instance;
    }

    private function createMockRepository(): TransactionRepositoryInterface
    {
        return $this->createMock(TransactionRepositoryInterface::class);
    }

    #[Test]
    public function create_accepts_valid_data_structure(): void
    {
        $repo = $this->createMockRepository();

        $data = [
            'user_id' => 1,
            'type' => TransactionType::DEPOSIT,
            'source' => $this->createSource('growmart'),
            'amount' => Money::fromKwacha(100.00),
            'status' => TransactionStatus::PENDING,
            'reference' => 'REF-001',
            'description' => 'Test transaction',
            'metadata' => ['key' => 'value'],
        ];

        $repo->expects($this->once())
            ->method('create')
            ->with($data)
            ->willReturn(1);

        $result = $repo->create($data);
        $this->assertSame(1, $result);
    }

    #[Test]
    public function create_works_without_optional_metadata(): void
    {
        $repo = $this->createMockRepository();

        $data = [
            'user_id' => 1,
            'type' => TransactionType::WITHDRAWAL,
            'source' => $this->createSource('growfinance'),
            'amount' => Money::zero(),
            'status' => TransactionStatus::COMPLETED,
            'reference' => 'REF-002',
            'description' => 'No metadata',
        ];

        $repo->expects($this->once())
            ->method('create')
            ->with($data)
            ->willReturn(2);

        $result = $repo->create($data);
        $this->assertSame(2, $result);
    }

    #[Test]
    public function findByReference_returns_object_or_null(): void
    {
        $repo = $this->createMockRepository();

        $repo->expects($this->exactly(2))
            ->method('findByReference')
            ->willReturnMap([
                ['REF-001', (object) ['id' => 1, 'reference' => 'REF-001']],
                ['NONEXISTENT', null],
            ]);

        $found = $repo->findByReference('REF-001');
        $this->assertIsObject($found);
        $this->assertSame('REF-001', $found->reference);

        $notFound = $repo->findByReference('NONEXISTENT');
        $this->assertNull($notFound);
    }

    #[Test]
    public function existsByReference_returns_boolean(): void
    {
        $repo = $this->createMockRepository();

        $repo->expects($this->exactly(2))
            ->method('existsByReference')
            ->willReturnMap([
                ['REF-001', true],
                ['NONEXISTENT', false],
            ]);

        $this->assertTrue($repo->existsByReference('REF-001'));
        $this->assertFalse($repo->existsByReference('NONEXISTENT'));
    }

    #[Test]
    public function getUserTransactions_accepts_filters(): void
    {
        $repo = $this->createMockRepository();
        $user = $this->createMock(\App\Models\User::class);

        $filters = [
            'type' => TransactionType::DEPOSIT,
            'source' => $this->createSource('growmart'),
            'status' => TransactionStatus::COMPLETED,
            'from_date' => new \DateTimeImmutable('2026-01-01'),
            'to_date' => new \DateTimeImmutable('2026-12-31'),
            'limit' => 10,
            'offset' => 0,
        ];

        $repo->expects($this->once())
            ->method('getUserTransactions')
            ->with($user, $filters)
            ->willReturn([]);

        $result = $repo->getUserTransactions($user, $filters);
        $this->assertIsArray($result);
    }

    #[Test]
    public function getUserTransactions_works_with_empty_filters(): void
    {
        $repo = $this->createMockRepository();
        $user = $this->createMock(\App\Models\User::class);

        $repo->expects($this->once())
            ->method('getUserTransactions')
            ->with($user, [])
            ->willReturn([]);

        $result = $repo->getUserTransactions($user);
        $this->assertIsArray($result);
    }

    #[Test]
    public function getTotalCredits_returns_money(): void
    {
        $repo = $this->createMockRepository();
        $user = $this->createMock(\App\Models\User::class);
        $source = $this->createSource('growmart');

        $money = Money::fromKwacha(500.00);

        $repo->expects($this->exactly(2))
            ->method('getTotalCredits')
            ->willReturnMap([
                [$user, null, $money],
                [$user, $source, $money],
            ]);

        $result = $repo->getTotalCredits($user);
        $this->assertInstanceOf(Money::class, $result);

        $resultWithSource = $repo->getTotalCredits($user, $source);
        $this->assertInstanceOf(Money::class, $resultWithSource);
    }

    #[Test]
    public function getTotalDebits_returns_money(): void
    {
        $repo = $this->createMockRepository();
        $user = $this->createMock(\App\Models\User::class);

        $money = Money::fromKwacha(200.00);

        $repo->expects($this->once())
            ->method('getTotalDebits')
            ->with($user, null)
            ->willReturn($money);

        $result = $repo->getTotalDebits($user);
        $this->assertInstanceOf(Money::class, $result);
    }

    #[Test]
    public function updateStatus_returns_boolean(): void
    {
        $repo = $this->createMockRepository();

        $repo->expects($this->exactly(2))
            ->method('updateStatus')
            ->willReturnMap([
                [1, TransactionStatus::COMPLETED, true],
                [999, TransactionStatus::CANCELLED, false],
            ]);

        $this->assertTrue($repo->updateStatus(1, TransactionStatus::COMPLETED));
        $this->assertFalse($repo->updateStatus(999, TransactionStatus::CANCELLED));
    }

    #[Test]
    public function getBySource_accepts_filters(): void
    {
        $repo = $this->createMockRepository();

        $source = $this->createSource('growfinance');
        $filters = [
            'status' => TransactionStatus::COMPLETED,
            'limit' => 5,
        ];

        $repo->expects($this->once())
            ->method('getBySource')
            ->with($source, $filters)
            ->willReturn([]);

        $result = $repo->getBySource($source, $filters);
        $this->assertIsArray($result);
    }

    #[Test]
    public function getBySource_works_with_empty_filters(): void
    {
        $repo = $this->createMockRepository();

        $source = $this->createSource('growmart');

        $repo->expects($this->once())
            ->method('getBySource')
            ->with($source, [])
            ->willReturn([]);

        $result = $repo->getBySource($source);
        $this->assertIsArray($result);
    }

    #[Test]
    public function interface_has_expected_methods(): void
    {
        $reflection = new \ReflectionClass(TransactionRepositoryInterface::class);
        $methods = array_map(fn(\ReflectionMethod $m) => $m->getName(), $reflection->getMethods());

        $expected = [
            'create',
            'findByReference',
            'existsByReference',
            'getUserTransactions',
            'getTotalCredits',
            'getTotalDebits',
            'updateStatus',
            'getBySource',
        ];

        foreach ($expected as $method) {
            $this->assertContains($method, $methods, "Interface should declare {$method}()");
        }
    }
}
