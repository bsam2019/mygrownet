<?php

namespace App\Domain\Core\Contracts;

class AiGenerationResult
{
    public function __construct(
        public bool $success,
        public string $content,
        public int $tokensUsed = 0,
        public ?string $errorMessage = null
    ) {}
}

interface AiProviderInterface
{
    /**
     * Generate text from prompt.
     */
    public function generateText(string $prompt, array $options = []): AiGenerationResult;

    /**
     * Parse raw text into structured schema JSON.
     */
    public function extractStructuredData(string $text, array $schema): array;

    /**
     * Unique name identifier of the AI provider (e.g. 'gemini', 'openai', 'claude', 'deepseek', 'mock').
     */
    public function getProviderName(): string;
}
