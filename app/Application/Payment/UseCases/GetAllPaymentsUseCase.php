<?php

namespace App\Application\Payment\UseCases;

use App\Infrastructure\Persistence\Eloquent\Payment\MemberPaymentModel;

class GetAllPaymentsUseCase
{
    public function execute(?string $status = null, int $perPage = 20): mixed
    {
        $query = MemberPaymentModel::with(['user', 'verifiedBy'])
            ->orderBy('created_at', 'desc');

        if ($status) {
            $query->where('status', $status);
        }

        return $query->paginate($perPage);
    }
}
