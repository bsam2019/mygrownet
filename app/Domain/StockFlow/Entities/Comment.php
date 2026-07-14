<?php

declare(strict_types=1);

namespace App\Domain\StockFlow\Entities;

use App\Domain\StockFlow\ValueObjects\CommentId;
use App\Domain\StockFlow\ValueObjects\CompanyId;
use App\Domain\StockFlow\ValueObjects\UserId;
use DateTimeImmutable;
use Illuminate\Contracts\Support\Arrayable;

class Comment implements Arrayable
{
    private function __construct(
        private CommentId $id,
        private CompanyId $companyId,
        private string $commentableType,
        private int $commentableId,
        private UserId $userId,
        private string $body,
        private DateTimeImmutable $createdAt,
        private DateTimeImmutable $updatedAt,
    ) {}

    public static function create(
        CompanyId $companyId,
        string $commentableType,
        int $commentableId,
        UserId $userId,
        string $body,
    ): self {
        return new self(
            id: CommentId::generate(),
            companyId: $companyId,
            commentableType: $commentableType,
            commentableId: $commentableId,
            userId: $userId,
            body: $body,
            createdAt: new DateTimeImmutable(),
            updatedAt: new DateTimeImmutable(),
        );
    }

    public static function reconstitute(
        CommentId $id,
        CompanyId $companyId,
        string $commentableType,
        int $commentableId,
        UserId $userId,
        string $body,
        DateTimeImmutable $createdAt,
        DateTimeImmutable $updatedAt,
    ): self {
        return new self($id, $companyId, $commentableType, $commentableId, $userId, $body, $createdAt, $updatedAt);
    }

    public function updateBody(string $body): void
    {
        $this->body = $body;
        $this->updatedAt = new DateTimeImmutable();
    }

    public function id(): int { return $this->id->toInt(); }
    public function getId(): CommentId { return $this->id; }
    public function getCompanyId(): CompanyId { return $this->companyId; }
    public function getCommentableType(): string { return $this->commentableType; }
    public function getCommentableId(): int { return $this->commentableId; }
    public function getUserId(): UserId { return $this->userId; }
    public function getUserIdValue(): int { return $this->userId->toInt(); }
    public function getBody(): string { return $this->body; }

    public function toArray(): array
    {
        return [
            'id' => $this->id->toInt(),
            'sa_company_id' => $this->companyId->toInt(),
            'commentable_type' => $this->commentableType,
            'commentable_id' => $this->commentableId,
            'sa_user_id' => $this->userId->toInt(),
            'body' => $this->body,
            'created_at' => $this->createdAt->format('Y-m-d H:i:s'),
            'updated_at' => $this->updatedAt->format('Y-m-d H:i:s'),
        ];
    }
}
