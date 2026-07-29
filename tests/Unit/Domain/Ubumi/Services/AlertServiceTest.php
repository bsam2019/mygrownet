<?php

namespace Tests\Unit\Domain\Ubumi\Services;

use App\Domain\Ubumi\Entities\CheckIn;
use App\Domain\Ubumi\Entities\Family;
use App\Domain\Ubumi\Entities\Person;
use App\Domain\Ubumi\Services\AlertService;
use App\Domain\Ubumi\Repositories\FamilyRepositoryInterface;
use App\Domain\Ubumi\Repositories\PersonRepositoryInterface;
use App\Domain\Ubumi\ValueObjects\CheckInStatus;
use App\Domain\Ubumi\ValueObjects\PersonId;
use App\Domain\Ubumi\ValueObjects\PersonName;
use App\Domain\Ubumi\ValueObjects\FamilyName;
use App\Domain\Ubumi\ValueObjects\Slug;
use App\Domain\Ubumi\ValueObjects\FamilyId;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Tests\TestCase;
use PHPUnit\Framework\Attributes\Test;

class AlertServiceTest extends TestCase
{
    private FamilyRepositoryInterface $familyRepo;
    private PersonRepositoryInterface $personRepo;
    private AlertService $service;
    private string $validFamilyUuid = '550e8400-e29b-41d4-a716-446655440000';

    protected function setUp(): void
    {
        parent::setUp();

        if (!Schema::hasTable('users')) {
            Schema::create('users', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('email');
                $table->timestamps();
            });
        }

        $this->familyRepo = $this->createMock(FamilyRepositoryInterface::class);
        $this->personRepo = $this->createMock(PersonRepositoryInterface::class);
        $this->service = new AlertService($this->familyRepo, $this->personRepo);
    }

    #[Test]
    public function processCheckIn_does_nothing_when_status_is_well()
    {
        $checkIn = CheckIn::create(PersonId::generate(), CheckInStatus::WELL);

        $this->personRepo->expects($this->never())->method('findById');
        DB::shouldReceive('table')->never();

        $this->service->processCheckIn($checkIn);
    }

    #[Test]
    public function processCheckIn_does_nothing_when_person_not_found()
    {
        $personId = PersonId::generate();
        $checkIn = CheckIn::create($personId, CheckInStatus::UNWELL);

        $this->personRepo->expects($this->once())->method('findById')->with($this->equalTo($personId))->willReturn(null);
        DB::shouldReceive('table')->never();

        $this->service->processCheckIn($checkIn);
    }

    #[Test]
    public function processCheckIn_does_nothing_when_family_not_found()
    {
        $checkIn = CheckIn::create(PersonId::generate(), CheckInStatus::UNWELL);
        $person = Person::create($this->validFamilyUuid, PersonName::fromString('Alice'), Slug::fromString('alice'), 1);

        $this->personRepo->expects($this->once())->method('findById')->willReturn($person);
        $this->familyRepo->expects($this->once())->method('findById')->willReturn(null);
        DB::shouldReceive('table')->never();

        $this->service->processCheckIn($checkIn);
    }

    #[Test]
    public function processCheckIn_creates_alert_for_unwell()
    {
        $checkIn = CheckIn::create(PersonId::generate(), CheckInStatus::UNWELL, 'Headache');
        list($person, $family) = $this->createPersonAndFamily();

        $this->personRepo->expects($this->once())->method('findById')->willReturn($person);
        $this->familyRepo->expects($this->once())->method('findById')->willReturn($family);

        $alertBuilder = $this->createStub(Builder::class);
        $alertBuilder->method('insertGetId')->willReturn('alert-uuid-123');

        $cgBuilder = $this->createStub(Builder::class);
        $cgBuilder->method('where')->willReturnSelf();
        $cgBuilder->method('get')->willReturn(collect([]));

        DB::shouldReceive('table')
            ->andReturnUsing(function ($table) use ($alertBuilder, $cgBuilder) {
                return match ($table) {
                    'ubumi_alerts' => $alertBuilder,
                    'ubumi_caregivers' => $cgBuilder,
                    default => throw new \InvalidArgumentException("Unexpected table: $table"),
                };
            });

        $this->service->processCheckIn($checkIn);
    }

    #[Test]
    public function getPendingAlertsByFamily_returns_mapped_alerts()
    {
        $builder = $this->createStub(Builder::class);
        $builder->method('where')->willReturnSelf();
        $builder->method('orderBy')->willReturnSelf();
        $builder->method('get')->willReturn(collect([(object) ['id' => 'a1', 'message' => 'M1', 'created_at' => now()]]));

        DB::shouldReceive('table')->with('ubumi_alerts')->once()->andReturn($builder);

        $this->assertCount(1, $this->service->getPendingAlertsByFamily(FamilyId::generate()));
    }

    #[Test]
    public function acknowledgeAlert_updates_alert_status()
    {
        $builder = $this->createStub(Builder::class);
        $builder->method('where')->with('id', 'alert-id')->willReturnSelf();
        $builder->method('update')->willReturn(1);

        DB::shouldReceive('table')->with('ubumi_alerts')->once()->andReturn($builder);

        $this->service->acknowledgeAlert('alert-id', 42);
    }

    #[Test]
    public function checkMissedCheckIns_skips_when_no_settings()
    {
        $settingsBuilder = $this->createStub(Builder::class);
        $settingsBuilder->method('where')->with('reminders_enabled', true)->willReturnSelf();
        $settingsBuilder->method('get')->willReturn(collect([]));

        DB::shouldReceive('table')
            ->andReturnUsing(function ($table) use ($settingsBuilder) {
                return match ($table) {
                    'ubumi_check_in_settings' => $settingsBuilder,
                    default => throw new \InvalidArgumentException("Unexpected table: $table"),
                };
            });

        $this->service->checkMissedCheckIns();
    }

    #[Test]
    public function checkMissedCheckIns_skips_when_no_last_checkin()
    {
        $settingsBuilder = $this->createStub(Builder::class);
        $settingsBuilder->method('where')->with('reminders_enabled', true)->willReturnSelf();
        $settingsBuilder->method('get')->willReturn(collect([
            (object) ['person_id' => 'p1', 'missed_threshold_hours' => 24],
        ]));

        $checkInBuilder = $this->createStub(Builder::class);
        $checkInBuilder->method('where')->willReturnSelf();
        $checkInBuilder->method('orderBy')->willReturnSelf();
        $checkInBuilder->method('first')->willReturn(null);

        DB::shouldReceive('table')->andReturnUsing(function ($table) use ($settingsBuilder, $checkInBuilder) {
            return match ($table) {
                'ubumi_check_in_settings' => $settingsBuilder,
                'ubumi_check_ins' => $checkInBuilder,
                default => throw new \InvalidArgumentException("Unexpected table: $table"),
            };
        });

        $this->service->checkMissedCheckIns();
    }

    #[Test]
    public function checkMissedCheckIns_skips_when_alert_exists()
    {
        $settingsBuilder = $this->createStub(Builder::class);
        $settingsBuilder->method('where')->with('reminders_enabled', true)->willReturnSelf();
        $settingsBuilder->method('get')->willReturn(collect([
            (object) ['person_id' => 'p1', 'missed_threshold_hours' => 24],
        ]));

        $checkInBuilder = $this->createStub(Builder::class);
        $checkInBuilder->method('where')->willReturnSelf();
        $checkInBuilder->method('orderBy')->willReturnSelf();
        $checkInBuilder->method('first')->willReturn(
            (object) ['checked_in_at' => now()->subHours(48)]
        );

        $alertBuilder = $this->createStub(Builder::class);
        $alertBuilder->method('where')->willReturnSelf();
        $alertBuilder->method('exists')->willReturn(true);

        DB::shouldReceive('table')->andReturnUsing(function ($table) use ($settingsBuilder, $checkInBuilder, $alertBuilder) {
            return match ($table) {
                'ubumi_check_in_settings' => $settingsBuilder,
                'ubumi_check_ins' => $checkInBuilder,
                'ubumi_alerts' => $alertBuilder,
                default => throw new \InvalidArgumentException("Unexpected table: $table"),
            };
        });

        $this->service->checkMissedCheckIns();
    }

    #[Test]
    public function checkMissedCheckIns_creates_alert_for_missed()
    {
        $settingsBuilder = $this->createStub(Builder::class);
        $settingsBuilder->method('where')->with('reminders_enabled', true)->willReturnSelf();
        $settingsBuilder->method('get')->willReturn(collect([
            (object) ['person_id' => 'p1', 'missed_threshold_hours' => 24],
        ]));

        $checkInBuilder = $this->createStub(Builder::class);
        $checkInBuilder->method('where')->willReturnSelf();
        $checkInBuilder->method('orderBy')->willReturnSelf();
        $checkInBuilder->method('first')->willReturn(
            (object) ['checked_in_at' => now()->subHours(48)]
        );

        $alertBuilder = $this->createStub(Builder::class);
        $alertBuilder->method('where')->willReturnSelf();
        $alertBuilder->method('exists')->willReturn(false);
        $alertBuilder->method('insert')->willReturn(true);

        $cgBuilder = $this->createStub(Builder::class);
        $cgBuilder->method('where')->willReturnSelf();
        $cgBuilder->method('get')->willReturn(collect([]));

        DB::shouldReceive('table')->andReturnUsing(function ($table) use (
            $settingsBuilder, $checkInBuilder, $alertBuilder, $cgBuilder
        ) {
            return match ($table) {
                'ubumi_check_in_settings' => $settingsBuilder,
                'ubumi_check_ins' => $checkInBuilder,
                'ubumi_alerts' => $alertBuilder,
                'ubumi_caregivers' => $cgBuilder,
                default => throw new \InvalidArgumentException("Unexpected table: $table"),
            };
        });

        list($person, $family) = $this->createPersonAndFamily();
        $this->personRepo->method('findById')->willReturn($person);
        $this->familyRepo->method('findById')->willReturn($family);

        $this->service->checkMissedCheckIns();
    }

    private function createPersonAndFamily(): array
    {
        $person = Person::create(
            $this->validFamilyUuid, PersonName::fromString('Alice'), Slug::fromString('alice'), 1
        );
        $family = Family::reconstitute(
            FamilyId::fromString($this->validFamilyUuid),
            FamilyName::fromString('Test'),
            Slug::fromString('test'),
            42,
            new \DateTimeImmutable(),
            null
        );
        return [$person, $family];
    }
}
