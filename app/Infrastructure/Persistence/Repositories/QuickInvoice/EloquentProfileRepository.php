<?php

declare(strict_types=1);

namespace App\Infrastructure\Persistence\Repositories\QuickInvoice;

use App\Domain\QuickInvoice\Entities\Profile;
use App\Domain\QuickInvoice\Repositories\ProfileRepositoryInterface;
use App\Models\QuickInvoice\QuickInvoiceProfile;

class EloquentProfileRepository implements ProfileRepositoryInterface
{
    public function findById(int $id): ?Profile
    {
        $model = QuickInvoiceProfile::find($id);
        return $model ? Profile::reconstitute($model->toArray()) : null;
    }

    public function findByUser(int $userId): ?Profile
    {
        $model = QuickInvoiceProfile::where('user_id', $userId)->first();
        return $model ? Profile::reconstitute($model->toArray()) : null;
    }

    public function save(Profile $profile): Profile
    {
        $data = $profile->toArray();
        $id = $data['id'] ?? null;
        unset($data['id'], $data['created_at'], $data['updated_at']);

        if ($id && QuickInvoiceProfile::find($id)) {
            QuickInvoiceProfile::where('id', $id)->update($data);
            return $this->findById($id);
        }

        $data['user_id'] = $profile->userId;
        $model = QuickInvoiceProfile::updateOrCreate(
            ['user_id' => $profile->userId],
            $data
        );
        return Profile::reconstitute($model->toArray());
    }

    public function delete(int $id): bool
    {
        return QuickInvoiceProfile::destroy($id) > 0;
    }
}