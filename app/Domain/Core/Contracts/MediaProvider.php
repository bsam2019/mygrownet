<?php

namespace App\Domain\Core\Contracts;

interface MediaProvider extends ProviderContract
{
    public function upload(string $path, string $contents, array $options = []): string;

    public function getUrl(string $path): string;

    public function delete(string $path): bool;

    public function exists(string $path): bool;
}
