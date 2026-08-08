<?php

namespace App\Domain\Storage\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;

class StorageMigrationService
{
    protected string $mode;
    protected string $primaryDisk;
    protected ?string $secondaryDisk;

    public function __construct()
    {
        // Wasabi is the sole storage provider (DigitalOcean Spaces discontinued).
        $this->mode = config('storage.migration_mode', 'wasabi_only');
        $this->primaryDisk = 'wasabi';
        $this->secondaryDisk = null;
    }

    /**
     * Store file with dual-write support
     */
    public function store(UploadedFile $file, string $path, array $options = []): string
    {
        // Default options
        $options = array_merge([
            'visibility' => 'public',
        ], $options);

        // Store on primary disk
        $storedPath = Storage::disk($this->primaryDisk)->putFile($path, $file, $options);

        // Also store on secondary disk if in dual-write mode
        if ($this->mode === 'dual_write' && $this->secondaryDisk) {
            try {
                Storage::disk($this->secondaryDisk)->putFileAs(
                    dirname($storedPath),
                    $file,
                    basename($storedPath),
                    $options
                );
            } catch (\Exception $e) {
                // Log but don't fail - secondary write is best-effort
                Log::warning("Secondary disk write failed: {$e->getMessage()}", [
                    'disk' => $this->secondaryDisk,
                    'path' => $storedPath,
                ]);
            }
        }

        return $storedPath;
    }

    /**
     * Store file content directly
     */
    public function put(string $path, mixed $contents, array $options = []): bool
    {
        $options = array_merge([
            'visibility' => 'public',
        ], $options);

        // Store on primary disk
        $result = Storage::disk($this->primaryDisk)->put($path, $contents, $options);

        // Also store on secondary disk if in dual-write mode
        if ($this->mode === 'dual_write' && $this->secondaryDisk) {
            try {
                Storage::disk($this->secondaryDisk)->put($path, $contents, $options);
            } catch (\Exception $e) {
                Log::warning("Secondary disk write failed: {$e->getMessage()}", [
                    'disk' => $this->secondaryDisk,
                    'path' => $path,
                ]);
            }
        }

        return $result;
    }

    /**
     * Get file URL with fallback support
     */
    public function url(string $path): string
    {
        // Try primary disk
        if (Storage::disk($this->primaryDisk)->exists($path)) {
            return Storage::disk($this->primaryDisk)->url($path);
        }

        // Fallback to secondary disk in dual-write mode
        if ($this->mode === 'dual_write' && $this->secondaryDisk) {
            if (Storage::disk($this->secondaryDisk)->exists($path)) {
                return Storage::disk($this->secondaryDisk)->url($path);
            }
        }

        // Fallback to old disk if migrating to wasabi
        if ($this->mode === 'wasabi_only' && Storage::disk('wasabi')->exists($path)) {
            return Storage::disk('wasabi')->url($path);
        }

        throw new \RuntimeException("File not found: {$path}");
    }

    /**
     * Get file contents
     */
    public function get(string $path): ?string
    {
        // Try primary disk
        if (Storage::disk($this->primaryDisk)->exists($path)) {
            return Storage::disk($this->primaryDisk)->get($path);
        }

        // Fallback to secondary disk in dual-write mode
        if ($this->mode === 'dual_write' && $this->secondaryDisk) {
            if (Storage::disk($this->secondaryDisk)->exists($path)) {
                return Storage::disk($this->secondaryDisk)->get($path);
            }
        }

        // Fallback to old disk if migrating
        if ($this->mode === 'wasabi_only' && Storage::disk('wasabi')->exists($path)) {
            return Storage::disk('wasabi')->get($path);
        }

        return null;
    }

    /**
     * Check if file exists on the primary disk
     */
    public function exists(string $path): bool
    {
        return Storage::disk($this->primaryDisk)->exists($path);
    }

    /**
     * Delete file from the primary disk
     */
    public function delete(string $path): bool
    {
        return Storage::disk($this->primaryDisk)->delete($path);
    }

    /**
     * Get current migration mode
     */
    public function getMode(): string
    {
        return $this->mode;
    }

    /**
     * Get primary disk name
     */
    public function getPrimaryDisk(): string
    {
        return $this->primaryDisk;
    }

    /**
     * Get secondary disk name
     */
    public function getSecondaryDisk(): ?string
    {
        return $this->secondaryDisk;
    }

    /**
     * Copy file from source disk to destination disk
     */
    public function copy(string $path, string $sourceDisk, string $destinationDisk): bool
    {
        try {
            $contents = Storage::disk($sourceDisk)->get($path);
            return Storage::disk($destinationDisk)->put($path, $contents, ['visibility' => 'public']);
        } catch (\Exception $e) {
            Log::error("File copy failed: {$e->getMessage()}", [
                'path' => $path,
                'source' => $sourceDisk,
                'destination' => $destinationDisk,
            ]);
            return false;
        }
    }

    /**
     * Get file size
     */
    public function size(string $path): int
    {
        if (Storage::disk($this->primaryDisk)->exists($path)) {
            return Storage::disk($this->primaryDisk)->size($path);
        }

        if ($this->secondaryDisk && Storage::disk($this->secondaryDisk)->exists($path)) {
            return Storage::disk($this->secondaryDisk)->size($path);
        }

        throw new \RuntimeException("File not found: {$path}");
    }

    /**
     * Test connection to both disks
     */
    public function testConnections(): array
    {
        $results = [];

        // Test primary disk
        try {
            Storage::disk($this->primaryDisk)->put('test-connection.txt', 'test');
            Storage::disk($this->primaryDisk)->delete('test-connection.txt');
            $results[$this->primaryDisk] = ['status' => 'success', 'message' => 'Connection successful'];
        } catch (\Exception $e) {
            $results[$this->primaryDisk] = ['status' => 'error', 'message' => $e->getMessage()];
        }

        // Test secondary disk if in dual-write mode
        if ($this->secondaryDisk) {
            try {
                Storage::disk($this->secondaryDisk)->put('test-connection.txt', 'test');
                Storage::disk($this->secondaryDisk)->delete('test-connection.txt');
                $results[$this->secondaryDisk] = ['status' => 'success', 'message' => 'Connection successful'];
            } catch (\Exception $e) {
                $results[$this->secondaryDisk] = ['status' => 'error', 'message' => $e->getMessage()];
            }
        }

        return $results;
    }
}
