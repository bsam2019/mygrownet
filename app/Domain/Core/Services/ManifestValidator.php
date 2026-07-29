<?php

namespace App\Domain\Core\Services;

use App\Domain\Core\ValueObjects\ModuleManifest;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class ManifestValidator
{
    private array $errors = [];

    public function __construct(
        private readonly LoggerInterface $logger = new NullLogger(),
    ) {}

    public function validate(ModuleManifest $manifest): bool
    {
        $this->errors = [];
        $this->validateRequired($manifest);
        $this->validateVersion($manifest);
        $this->validateContracts($manifest);
        $this->validateCapabilities($manifest);

        if (!empty($this->errors)) {
            $this->logger->warning("Manifest validation failed for '{$manifest->id}'", [
                'errors' => $this->errors,
                'manifest' => $manifest->toArray(),
            ]);
            return false;
        }

        return true;
    }

    public function errors(): array
    {
        return $this->errors;
    }

    private function validateRequired(ModuleManifest $manifest): void
    {
        if (empty($manifest->id)) {
            $this->errors[] = 'Module id is required';
        }
        if (empty($manifest->name)) {
            $this->errors[] = 'Module name is required';
        }
        if (empty($manifest->version)) {
            $this->errors[] = 'Module version is required';
        }
    }

    private function validateVersion(ModuleManifest $manifest): void
    {
        if ($manifest->minPlatformVersion && !preg_match('/^\d+\.\d+(\.\d+)?$/', $manifest->minPlatformVersion)) {
            $this->errors[] = "Invalid min_platform_version '{$manifest->minPlatformVersion}'";
        }
        if ($manifest->maxPlatformVersion && !preg_match('/^\d+\.\d+(\.x)?$/', $manifest->maxPlatformVersion)) {
            $this->errors[] = "Invalid max_platform_version '{$manifest->maxPlatformVersion}'";
        }
    }

    private function validateContracts(ModuleManifest $manifest): void
    {
        foreach ($manifest->contracts as $contract) {
            if (!interface_exists($contract)) {
                $this->errors[] = "Contract interface '{$contract}' does not exist";
            }
        }
    }

    private function validateCapabilities(ModuleManifest $manifest): void
    {
        foreach ($manifest->requiredCapabilities as $cap) {
            if (in_array($cap, $manifest->capabilities, true)) {
                $this->errors[] = "Module '{$manifest->id}' lists '{$cap}' as both a provider and consumer capability";
            }
        }
    }
}
