<?php

declare(strict_types=1);

namespace App\Domain\GrowBiz\Services;

use App\Domain\GrowBiz\Entities\Employee;
use App\Domain\GrowBiz\Entities\EmployeeInvitation;
use App\Domain\GrowBiz\Exceptions\EmployeeNotFoundException;
use App\Domain\GrowBiz\Exceptions\OperationFailedException;
use App\Domain\GrowBiz\Repositories\EmployeeRepositoryInterface;
use App\Domain\GrowBiz\ValueObjects\EmployeeId;
use App\Domain\GrowBiz\ValueObjects\InvitationCode;
use App\Domain\GrowBiz\ValueObjects\InvitationToken;
use App\Infrastructure\Persistence\Eloquent\GrowBizEmployeeInvitationModel;
use App\Models\User;
use App\Notifications\GrowBiz\EmployeeInvitationNotification;
use DateTimeImmutable;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Throwable;

class EmployeeInvitationService
{
    public function __construct(
        private EmployeeRepositoryInterface $employeeRepository
    ) {}

    public function sendEmailInvitation(int $employeeId, int $managerId, string $email): EmployeeInvitation
    {
        try {
            $employee = $this->employeeRepository->findById(EmployeeId::fromInt($employeeId));
            if (!$employee) {
                throw new EmployeeNotFoundException($employeeId);
            }

            $this->revokePendingInvitations($employeeId);
            $invitation = EmployeeInvitation::createEmailInvitation($employeeId, $managerId, $email);
            $saved = $this->saveInvitation($invitation);

            $manager = User::find($managerId);
            $this->sendInvitationEmail($saved, $employee, $manager);

            Log::info('Email invitation sent', ['employee_id' => $employeeId, 'email' => $email]);
            return $saved;
        } catch (EmployeeNotFoundException $e) {
            throw $e;
        } catch (Throwable $e) {
            Log::error('Failed to send email invitation', ['employee_id' => $employeeId, 'error' => $e->getMessage()]);
            throw new OperationFailedException('send email invitation', $e->getMessage());
        }
    }

    public function generateCodeInvitation(int $employeeId, int $managerId): EmployeeInvitation
    {
        try {
            $employee = $this->employeeRepository->findById(EmployeeId::fromInt($employeeId));
            if (!$employee) {
                throw new EmployeeNotFoundException($employeeId);
            }

            $this->revokePendingInvitations($employeeId);
            $invitation = EmployeeInvitation::createCodeInvitation($employeeId, $managerId);
            $saved = $this->saveInvitation($invitation);

            Log::info('Code invitation generated', ['employee_id' => $employeeId, 'code' => $saved->code()->value()]);
            return $saved;
        } catch (EmployeeNotFoundException $e) {
            throw $e;
        } catch (Throwable $e) {
            Log::error('Failed to generate code invitation', ['employee_id' => $employeeId, 'error' => $e->getMessage()]);
            throw new OperationFailedException('generate code invitation', $e->getMessage());
        }
    }

    public function acceptByToken(string $token, int $userId): Employee
    {
        try {
            $model = GrowBizEmployeeInvitationModel::byToken($token)->pending()->first();
            if (!$model) {
                throw new \DomainException('Invalid or expired invitation');
            }

            $invitation = $this->modelToEntity($model);
            $invitation->accept($userId);

            $model->update([
                'status' => 'accepted',
                'accepted_at' => now(),
                'accepted_by_user_id' => $userId,
            ]);

            $employee = $this->employeeRepository->findById(EmployeeId::fromInt($invitation->employeeId()));
            if ($employee) {
                $employee->linkToUser($userId);
                $this->employeeRepository->save($employee);
            }

            // Clear setup cache so user is recognized as employee
            \Illuminate\Support\Facades\Cache::forget("growbiz_needs_setup_{$userId}");
            \Illuminate\Support\Facades\Cache::forget("growbiz_context_{$userId}");

            Log::info('Invitation accepted via token', ['employee_id' => $invitation->employeeId(), 'user_id' => $userId]);
            return $employee;
        } catch (\DomainException $e) {
            throw $e;
        } catch (Throwable $e) {
            Log::error('Failed to accept invitation by token', ['error' => $e->getMessage()]);
            throw new OperationFailedException('accept invitation', $e->getMessage());
        }
    }

    public function acceptByCode(string $code, int $userId): Employee
    {
        try {
            $normalizedCode = strtoupper(trim($code));
            Log::info('Looking up invitation code', ['code' => $normalizedCode, 'user_id' => $userId]);
            
            $model = GrowBizEmployeeInvitationModel::byCode($normalizedCode)->pending()->first();
            
            if (!$model) {
                // Check if code exists but is not pending
                $anyModel = GrowBizEmployeeInvitationModel::where('code', $normalizedCode)->first();
                if ($anyModel) {
                    Log::warning('Invitation code found but not pending', [
                        'code' => $normalizedCode,
                        'status' => $anyModel->status,
                        'expires_at' => $anyModel->expires_at,
                    ]);
                    if ($anyModel->status === 'accepted') {
                        throw new \DomainException('This invitation code has already been used');
                    }
                    if ($anyModel->status === 'revoked') {
                        throw new \DomainException('This invitation code has been revoked');
                    }
                    if ($anyModel->expires_at < now()) {
                        throw new \DomainException('This invitation code has expired');
                    }
                }
                throw new \DomainException('Invalid or expired invitation code');
            }

            $invitation = $this->modelToEntity($model);
            $invitation->accept($userId);

            $model->update([
                'status' => 'accepted',
                'accepted_at' => now(),
                'accepted_by_user_id' => $userId,
            ]);

            $employee = $this->employeeRepository->findById(EmployeeId::fromInt($invitation->employeeId()));
            if ($employee) {
                $employee->linkToUser($userId);
                $this->employeeRepository->save($employee);
            }

            // Clear setup cache so user is recognized as employee
            \Illuminate\Support\Facades\Cache::forget("growbiz_needs_setup_{$userId}");
            \Illuminate\Support\Facades\Cache::forget("growbiz_context_{$userId}");

            Log::info('Invitation accepted via code', ['employee_id' => $invitation->employeeId(), 'user_id' => $userId]);
            return $employee;
        } catch (\DomainException $e) {
            throw $e;
        } catch (Throwable $e) {
            Log::error('Failed to accept invitation by code', ['code' => $code, 'error' => $e->getMessage()]);
            throw new OperationFailedException('accept invitation', $e->getMessage());
        }
    }

    public function getInvitationByToken(string $token): ?array
    {
        $model = GrowBizEmployeeInvitationModel::byToken($token)->with(['employee', 'manager'])->first();
        if (!$model) {
            return null;
        }

        return [
            'invitation' => $this->modelToEntity($model)->toArray(),
            'employee_name' => $model->employee?->first_name . ' ' . $model->employee?->last_name,
            'business_name' => $model->manager?->name ?? 'Business',
            'position' => $model->employee?->position,
            'is_valid' => $model->status === 'pending' && $model->expires_at > now(),
        ];
    }

    public function getPendingInvitation(int $employeeId): ?EmployeeInvitation
    {
        $model = GrowBizEmployeeInvitationModel::where('employee_id', $employeeId)->pending()->first();
        return $model ? $this->modelToEntity($model) : null;
    }

    public function revokeInvitation(int $invitationId): void
    {
        GrowBizEmployeeInvitationModel::where('id', $invitationId)
            ->where('status', 'pending')
            ->update(['status' => 'revoked']);
    }

    private function revokePendingInvitations(int $employeeId): void
    {
        GrowBizEmployeeInvitationModel::where('employee_id', $employeeId)
            ->where('status', 'pending')
            ->update(['status' => 'revoked']);
    }

    private function saveInvitation(EmployeeInvitation $invitation): EmployeeInvitation
    {
        $model = GrowBizEmployeeInvitationModel::create([
            'employee_id' => $invitation->employeeId(),
            'manager_id' => $invitation->managerId(),
            'email' => $invitation->email(),
            'token' => $invitation->token()->value(),
            'code' => $invitation->code()->value(),
            'type' => $invitation->type(),
            'status' => $invitation->status(),
            'expires_at' => $invitation->expiresAt(),
        ]);

        return $this->modelToEntity($model);
    }

    private function sendInvitationEmail(EmployeeInvitation $invitation, Employee $employee, ?User $manager): void
    {
        if (!$invitation->email()) {
            return;
        }

        $notification = new EmployeeInvitationNotification(
            employeeName: $employee->getName(),
            businessName: $manager?->name ?? 'Business',
            position: $employee->position(),
            invitationToken: $invitation->token()->value(),
            expiresAt: $invitation->expiresAt()
        );

        Notification::route('mail', $invitation->email())->notify($notification);
    }

    private function modelToEntity(GrowBizEmployeeInvitationModel $model): EmployeeInvitation
    {
        return EmployeeInvitation::reconstitute(
            id: $model->id,
            employeeId: $model->employee_id,
            managerId: $model->manager_id,
            email: $model->email,
            token: InvitationToken::fromString($model->token),
            code: InvitationCode::fromString($model->code),
            type: $model->type,
            status: $model->status,
            expiresAt: new DateTimeImmutable($model->expires_at->toDateTimeString()),
            acceptedAt: $model->accepted_at ? new DateTimeImmutable($model->accepted_at->toDateTimeString()) : null,
            acceptedByUserId: $model->accepted_by_user_id,
            createdAt: new DateTimeImmutable($model->created_at->toDateTimeString()),
            updatedAt: new DateTimeImmutable($model->updated_at->toDateTimeString())
        );
    }
}
