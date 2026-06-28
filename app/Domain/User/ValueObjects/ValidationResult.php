<?php

namespace App\Domain\User\ValueObjects;

class ValidationResult
{
    private array $errors;
    private array $validatedData;
    
    public function __construct(array $errors, array $validatedData)
    {
        $this->errors = $errors;
        $this->validatedData = $validatedData;
    }
    
    public function isValid(): bool
    {
        return empty($this->errors);
    }
    
    public function getErrors(): array
    {
        return $this->errors;
    }
    
    public function getValidatedData(): array
    {
        return $this->validatedData;
    }
    
    public function getErrorsAsString(): string
    {
        return implode(', ', $this->errors);
    }
    
    public function hasError(string $errorMessage): bool
    {
        return in_array($errorMessage, $this->errors);
    }
}