<?php

namespace App\Domain\Platform\Contracts;

interface AIService
{
    public function predict(string $model, array $features): array;
    public function classify(string $text, array $categories): string;
    public function embed(string $text): array;
}
