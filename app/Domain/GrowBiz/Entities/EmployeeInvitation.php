<?php

declare(strict_types=1);

namespace App\Domain\GrowBiz\Entities;

use App\Domain\GrowBiz\ValueObjects\InvitationCode;
use App\Domain\GrowBiz\ValueObjects\InvitationToken;
use DateTimeImmutable;

class EmployeeInvitation
{
    private function __construct(
        private ?int $id,
        private int $employeeId,
        private int $managerId,
        private ?string $email,
        private InvitationToken $token,
        private InvitationCode $code,
        private string $type, // 'email' or 'code'
        private string $status, // 'pending', 'accepted', 'expired', 'revoked'
        private DateTimeImmutable $expiresAt,
        private ?DateTimeImmutable $acceptedAt,
        private ?int $acceptedByUserId,
        private DateTimeImmutable $createdAt,
        private DateTimeImmutable $updatedAt
    ) {}

    public static function createEmailInvitation(
        int $employeeId,
        int $managerId,
        string $email,
        int $expiryDays = 7
    ): self {
        return new self(
            id: null,
            employeeId: $employeeId,
            managerId: $managerId,
            email: $email,
            token: InvitationToken::generate(),
            code: InvitationCode::generate(),
            type: 'email',
            status: 'pending',
            expiresAt: new DateTimeImmutable("+{$expiryDays} days"),
            acceptedAt: null,
            acceptedByUserId: null,
            createdAt: new DateTimeImmutable(),
            updatedAt: new DateTimeImmutable()
        );
    }

    public static function createCodeInvitation(
        int $employeeId,
        int $managerId,
        int $expiryDays = 30
    ): self {
        return new self(
            id: null,
            employeeId: $employeeId,
            managerId: $managerId,
            email: null,
            token: InvitationToken::generate(),
            code: InvitationCode::generate(),
            type: 'code',
            status: 'pending',
            expiresAt: new DateTimeImmutable("+{$expiryDays} days"),
            acceptedAt: null,
            acceptedByUserId: null,
            createdAt: new DateTimeImmutable(),
            updatedAt: new DateTimeImmutable()
        );
    }

    public static function reconstitute(
        int $id,
        int $employeeId,
        int $managerId,
        ?string $email,
        InvitationToken $token,
        InvitationCode $code,
        string $type,
        string $status,
        DateTimeImmutable $expiresAt,
        ?DateTimeImmutable $acceptedAt,
        ?int $acceptedByUserId,
        DateTimeImmutable $createdAt,
        DateTimeImmutable $updatedAt
    ): self {
        return new self(
            $id, $employeeId, $managerId, $email, $token, $code,
            $type, $status, $expiresAt, $acceptedAt, $acceptedByUserId,
            $createdAt, $updatedAt
        );
    }

    public function accept(int $userId): void
    {
        if ($this->status !== 'pending') {
            throw new \DomainException('Invitation is no longer pending');
        }
        if ($this->isExpired()) {
            throw new \DomainException('Invitation has expired');
        }
        
        $this->status = 'accepted';
        $this->acceptedAt = new DateTimeImmutable();
        $this->acceptedByUserId = $userId;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function revoke(): void
    {
        if ($this->status !== 'pending') {
            throw new \DomainException('Can only revoke pending invitations');
        }
        
        $this->status = 'revoked';
        $this->updatedAt = new DateTimeImmutable();
    }

    public function isExpired(): bool
    {
        return $this->expiresAt < new DateTimeImmutable();
    }

    public function isPending(): bool
    {
        return $this->status === 'pending' && !$this->isExpired();
    }

    public function isAccepted(): bool
    {
        return $this->status === 'accepted';
    }

    // Getters
    public function id(): ?int { return $this->id; }
    public function employeeId(): int { return $this->employeeId; }
    public function managerId(): int { return $this->managerId; }
    public function email(): ?string { return $this->email; }
    public function token(): InvitationToken { return $this->token; }
    public function code(): InvitationCode { return $this->code; }
    public function type(): string { return $this->type; }
    public function status(): string { return $this->status; }
    public function expiresAt(): DateTimeImmutable { return $this->expiresAt; }
    public function acceptedAt(): ?DateTimeImmutable { return $this->acceptedAt; }
    public function acceptedByUserId(): ?int { return $this->acceptedByUserId; }
    public function createdAt(): DateTimeImmutable { return $this->createdAt; }
    public function updatedAt(): DateTimeImmutable { return $this->updatedAt; }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'employee_id' => $this->employeeId,
            'manager_id' => $this->managerId,
            'email' => $this->email,
            'token' => $this->token->value(),
            'code' => $this->code->value(),
            'type' => $this->type,
            'status' => $this->status,
            'expires_at' => $this->expiresAt->format('Y-m-d H:i:s'),
            'accepted_at' => $this->acceptedAt?->format('Y-m-d H:i:s'),
            'is_expired' => $this->isExpired(),
            'is_pending' => $this->isPending(),
            'created_at' => $this->createdAt->format('Y-m-d H:i:s'),
        ];
    }
}
