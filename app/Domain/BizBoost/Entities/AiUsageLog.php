<?php

namespace App\Domain\BizBoost\Entities;

class AiUsageLog
{
    public function __construct(
        public readonly ?int $id,
        public readonly int $businessId,
        public readonly ?int $userId,
        public readonly string $contentType,
        public readonly ?string $model,
        public readonly ?int $inputTokens,
        public readonly ?int $outputTokens,
        public readonly int $creditsUsed,
        public readonly ?array $requestParams,
        public readonly ?string $prompt,
        public readonly ?string $response,
        public readonly bool $wasSuccessful,
        public readonly ?string $errorMessage,
        public readonly ?string $createdAt,
        public readonly ?string $updatedAt,
    ) {}

    public static function reconstitute(array $data): self
    {
        return new self(
            id: isset($data['id']) ? (int) $data['id'] : null,
            businessId: (int) $data['business_id'],
            userId: isset($data['user_id']) ? (int) $data['user_id'] : null,
            contentType: $data['content_type'],
            model: $data['model'] ?? null,
            inputTokens: isset($data['input_tokens']) ? (int) $data['input_tokens'] : null,
            outputTokens: isset($data['output_tokens']) ? (int) $data['output_tokens'] : null,
            creditsUsed: (int) ($data['credits_used'] ?? 0),
            requestParams: $data['request_params'] ?? null,
            prompt: $data['prompt'] ?? null,
            response: $data['response'] ?? null,
            wasSuccessful: (bool) ($data['was_successful'] ?? true),
            errorMessage: $data['error_message'] ?? null,
            createdAt: $data['created_at'] ?? null,
            updatedAt: $data['updated_at'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'business_id' => $this->businessId,
            'user_id' => $this->userId,
            'content_type' => $this->contentType,
            'model' => $this->model,
            'input_tokens' => $this->inputTokens,
            'output_tokens' => $this->outputTokens,
            'credits_used' => $this->creditsUsed,
            'request_params' => $this->requestParams,
            'prompt' => $this->prompt,
            'response' => $this->response,
            'was_successful' => $this->wasSuccessful,
            'error_message' => $this->errorMessage,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];
    }
}