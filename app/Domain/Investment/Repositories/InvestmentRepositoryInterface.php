<?php

namespace App\Domain\Investment\Repositories;

interface InvestmentRepositoryInterface
{
    public function findByUser(int $userId): array;
    public function findById(int $id);
    public function save(array $data);
}