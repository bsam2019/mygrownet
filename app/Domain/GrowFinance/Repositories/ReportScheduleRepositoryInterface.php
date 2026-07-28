<?php

declare(strict_types=1);

namespace App\Domain\GrowFinance\Repositories;

use App\Domain\GrowFinance\Entities\ReportSchedule;
use DateTimeImmutable;

interface ReportScheduleRepositoryInterface
{
    public function findById(int $id): ?ReportSchedule;

    /** @return ReportSchedule[] */
    public function findByBusiness(int $businessId): array;

    /** @return ReportSchedule[] */
    public function findDue(DateTimeImmutable $now): array;

    public function save(ReportSchedule $schedule): ReportSchedule;

    public function delete(int $id): void;
}
