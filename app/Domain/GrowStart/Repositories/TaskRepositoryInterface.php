<?php

declare(strict_types=1);

namespace App\Domain\GrowStart\Repositories;

use App\Domain\GrowStart\Entities\Task;
use App\Domain\GrowStart\Entities\UserTask;
use Illuminate\Support\Collection;

interface TaskRepositoryInterface
{
    public function findById(int $id): ?Task;
    
    public function findByStage(int $stageId, ?int $industryId = null, ?int $countryId = null): Collection;
    
    public function findForJourney(int $journeyId): Collection;
    
    public function findUserTask(int $journeyId, int $taskId): ?UserTask;
    
    public function findUserTasksByJourney(int $journeyId): Collection;
    
    public function findUserTasksByStage(int $journeyId, int $stageId): Collection;
    
    public function saveUserTask(UserTask $userTask): UserTask;
    
    public function countCompletedByJourney(int $journeyId): int;
    
    public function countTotalByJourney(int $journeyId): int;
    
    public function countCompletedByStage(int $journeyId, int $stageId): int;
    
    public function countTotalByStage(int $journeyId, int $stageId): int;
}
