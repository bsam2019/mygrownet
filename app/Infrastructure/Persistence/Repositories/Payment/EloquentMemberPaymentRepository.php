<?php

namespace App\Infrastructure\Persistence\Repositories\Payment;

use App\Domain\Payment\Entities\MemberPayment;
use App\Domain\Payment\Repositories\MemberPaymentRepositoryInterface;
use App\Domain\Payment\ValueObjects\PaymentAmount;
use App\Domain\Payment\ValueObjects\PaymentMethod;
use App\Domain\Payment\ValueObjects\PaymentStatus;
use App\Domain\Payment\ValueObjects\PaymentType;
use App\Infrastructure\Persistence\Eloquent\Payment\MemberPaymentModel;
use DateTimeImmutable;

class EloquentMemberPaymentRepository implements MemberPaymentRepositoryInterface
{
    public function save(MemberPayment $payment): MemberPayment
    {
        $model = $payment->id() > 0
            ? MemberPaymentModel::findOrFail($payment->id())
            : new MemberPaymentModel();

        $model->fill([
            'user_id' => $payment->userId(),
            'amount' => $payment->amount()->value(),
            'payment_method' => $payment->paymentMethod()->value,
            'payment_reference' => $payment->paymentReference(),
            'phone_number' => $payment->phoneNumber(),
            'account_name' => $payment->accountName(),
            'payment_type' => $payment->paymentType()->value,
            'notes' => $payment->notes(),
            'status' => $payment->status()->value,
            'admin_notes' => $payment->adminNotes(),
            'verified_by' => $payment->verifiedBy(),
            'verified_at' => $payment->verifiedAt()?->format('Y-m-d H:i:s'),
        ]);

        $model->save();

        return $this->toDomainEntity($model);
    }

    public function findById(int $id): ?MemberPayment
    {
        $model = MemberPaymentModel::find($id);
        return $model ? $this->toDomainEntity($model) : null;
    }

    public function findByUserId(int $userId): array
    {
        $models = MemberPaymentModel::where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->get();

        return $models->map(fn($model) => $this->toDomainEntity($model))->all();
    }

    public function findByReference(string $reference): ?MemberPayment
    {
        $model = MemberPaymentModel::where('payment_reference', $reference)->first();
        return $model ? $this->toDomainEntity($model) : null;
    }

    public function findPendingPayments(): array
    {
        return $this->findByStatus('pending');
    }

    public function findByStatus(string $status): array
    {
        $models = MemberPaymentModel::where('status', $status)
            ->orderBy('created_at', 'desc')
            ->get();

        return $models->map(fn($model) => $this->toDomainEntity($model))->all();
    }

    private function toDomainEntity(MemberPaymentModel $model): MemberPayment
    {
        return new MemberPayment(
            id: $model->id,
            userId: $model->user_id,
            amount: PaymentAmount::fromFloat((float) $model->amount),
            paymentMethod: PaymentMethod::fromString($model->payment_method),
            paymentReference: $model->payment_reference,
            phoneNumber: $model->phone_number,
            accountName: $model->account_name,
            paymentType: PaymentType::fromString($model->payment_type),
            notes: $model->notes,
            status: PaymentStatus::from($model->status),
            adminNotes: $model->admin_notes,
            verifiedBy: $model->verified_by,
            verifiedAt: $model->verified_at ? new DateTimeImmutable($model->verified_at->format('Y-m-d H:i:s')) : null,
            createdAt: new DateTimeImmutable($model->created_at->format('Y-m-d H:i:s')),
            updatedAt: new DateTimeImmutable($model->updated_at->format('Y-m-d H:i:s'))
        );
    }
}
