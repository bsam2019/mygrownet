<?php

namespace App\Domain\Core\Infrastructure;

use App\Domain\Core\Contracts\MediaProvider;
use Illuminate\Support\Facades\Storage;

class MediaProviderImpl implements MediaProvider
{
    private string $disk;

    public function __construct(string $disk = 'public')
    {
        $this->disk = $disk;
    }

    public function capability(): string
    {
        return 'media';
    }

    public function upload(string $path, string $contents, array $options = []): string
    {
        Storage::disk($this->disk)->put($path, $contents, $options);
        return $path;
    }

    public function getUrl(string $path): string
    {
        return Storage::disk($this->disk)->url($path);
    }

    public function delete(string $path): bool
    {
        return Storage::disk($this->disk)->delete($path);
    }

    public function exists(string $path): bool
    {
        return Storage::disk($this->disk)->exists($path);
    }
}
