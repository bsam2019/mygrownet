<?php

declare(strict_types=1);

namespace App\Domain\QuickInvoice\Services;

use App\Domain\QuickInvoice\Entities\Profile;
use App\Domain\QuickInvoice\Repositories\ProfileRepositoryInterface;

class ProfileService
{
    public function __construct(
        private readonly ProfileRepositoryInterface $profileRepository
    ) {}

    public function getProfile(int $userId): ?Profile
    {
        return $this->profileRepository->findByUser($userId);
    }

    public function saveProfile(int $userId, array $data): Profile
    {
        $existing = $this->profileRepository->findByUser($userId);

        $mergedData = array_merge(
            $existing ? $existing->toArray() : ['user_id' => $userId],
            $data,
            ['user_id' => $userId]
        );

        $profile = Profile::reconstitute($mergedData);
        return $this->profileRepository->save($profile);
    }

    public function generateDocumentNumber(int $userId, string $type): ?string
    {
        $profile = $this->profileRepository->findByUser($userId);

        if (!$profile) {
            return null;
        }

        $number = $profile->generateDocumentNumber($type);

        $updated = $profile->withIncrementedNumber($type);
        $this->profileRepository->save($updated);

        return $number;
    }

    public function calculateCompletion(Profile $profile): int
    {
        $fields = ['name', 'address', 'phone', 'email', 'logo'];
        $completed = 0;

        foreach ($fields as $field) {
            $value = match ($field) {
                'name' => $profile->name,
                'address' => $profile->address,
                'phone' => $profile->phone,
                'email' => $profile->email,
                'logo' => $profile->logo,
                default => null,
            };
            if (!empty($value)) {
                $completed++;
            }
        }

        return (int) (($completed / count($fields)) * 100);
    }

    public function profileToArray(?Profile $profile): ?array
    {
        if (!$profile) {
            return null;
        }

        return [
            'name' => $profile->name,
            'email' => $profile->email,
            'logo' => $profile->logo,
            'completion_percentage' => $this->calculateCompletion($profile),
        ];
    }
}