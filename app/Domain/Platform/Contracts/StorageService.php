<?php

namespace App\Domain\Platform\Contracts;

interface StorageService
{
    public function upload(string $path, $content, array $options = []): string;
    public function delete(string $path): bool;
    public function url(string $path): string;
    public function temporaryUrl(string $path, \DateTimeInterface $expiration): string;
}
