# MyGrowNet Storage - Implementation Plan

**Last Updated:** February 21, 2026
**Status:** Planning
**Version:** 1.0

## Implementation Overview

This document provides a detailed technical implementation plan for the MyGrowNet Storage module using **Domain-Driven Design (DDD)** principles. The module is a **GrowNet MLM product** (starter kit + level upgrades) with full commission and points integration.

### Key Implementation Principles

1. **Domain-Driven Design**: Strict separation of Domain, Application, Infrastructure, and Presentation layers
2. **GrowNet Product Integration**: Full integration with existing products, subscriptions, and MLM commission system
3. **S3-Compatible Storage**: Wasabi primary, but architecture supports any S3-compatible provider
4. **Security First**: Private buckets, signed URLs, user isolation
5. **Quota Enforcement**: Real-time quota checks with transaction safety

## Phase 1: MVP Implementation

### Timeline Estimate
- **Domain Layer (Entities, VOs, Services)**: 3 days
- **Database & Migrations**: 2 days
- **Infrastructure Layer (Repositories, S3)**: 3 days
- **Application Layer (Use Cases)**: 3 days
- **Presentation Layer (Controllers, API)**: 2 days
- **GrowNet Product Integration**: 2 days
- **Frontend Components**: 5 days
- **Testing & Bug Fixes**: 3 days
- **Documentation & Deployment**: 2 days
- **Total**: ~4 weeks (25 days)

### Implementation Order (DDD Approach)

1. **Domain Layer First** - Core business logic, no dependencies
2. **Infrastructure Layer** - Database, S3, external services
3. **Application Layer** - Use cases orchestrating domain logic
4. **Presentation Layer** - Controllers delegating to use cases
5. **Frontend** - Vue components consuming API

## Database Schema

### Migration Order

1. `create_storage_plans_table`
2. `create_user_storage_subscriptions_table`
3. `create_storage_folders_table`
4. `create_storage_files_table`
5. `create_storage_usage_table`
6. `create_storage_share_links_table` (Phase 2)
7. `create_storage_audit_logs_table` (Optional)

### Table Definitions

#### A) storage_plans
```php
Schema::create('storage_plans', function (Blueprint $table) {
    $table->id();
    $table->string('name')->unique(); // Lite, Basic, Growth, Pro
    $table->string('slug')->unique();
    $table->unsignedBigInteger('quota_bytes'); // 2GB, 20GB, 100GB, 500GB
    $table->unsignedBigInteger('max_file_size_bytes');
    $table->json('allowed_mime_types')->nullable();
    $table->boolean('allow_sharing')->default(false);
    $table->boolean('allow_public_profile_files')->default(false);
    $table->boolean('is_active')->default(true);
    $table->timestamps();
});
```

#### B) user_storage_subscriptions
```php
Schema::create('user_storage_subscriptions', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->onDelete('cascade');
    $table->foreignId('storage_plan_id')->constrained()->onDelete('restrict');
    $table->enum('status', ['active', 'paused', 'expired'])->default('active');
    $table->timestamp('start_at');
    $table->timestamp('end_at')->nullable();
    $table->enum('source', ['grownet', 'manual', 'promo'])->default('grownet');
    $table->timestamps();
    
    $table->index(['user_id', 'status']);
});
```


#### C) storage_folders
```php
Schema::create('storage_folders', function (Blueprint $table) {
    $table->uuid('id')->primary();
    $table->foreignId('user_id')->constrained()->onDelete('cascade');
    $table->uuid('parent_id')->nullable();
    $table->string('name');
    $table->string('path_cache')->nullable(); // /Documents/Projects
    $table->timestamps();
    
    $table->foreign('parent_id')->references('id')->on('storage_folders')->onDelete('cascade');
    $table->index(['user_id', 'parent_id']);
    $table->index('user_id');
});
```

#### D) storage_files
```php
Schema::create('storage_files', function (Blueprint $table) {
    $table->uuid('id')->primary();
    $table->foreignId('user_id')->constrained()->onDelete('cascade');
    $table->uuid('folder_id')->nullable();
    $table->string('original_name');
    $table->string('stored_name')->nullable();
    $table->string('extension', 10);
    $table->string('mime_type');
    $table->unsignedBigInteger('size_bytes');
    $table->string('s3_bucket');
    $table->string('s3_key')->unique();
    $table->string('checksum')->nullable(); // SHA-256
    $table->boolean('is_deleted')->default(false);
    $table->timestamps();
    $table->softDeletes();
    
    $table->foreign('folder_id')->references('id')->on('storage_folders')->onDelete('set null');
    $table->index(['user_id', 'folder_id']);
    $table->index('user_id');
    $table->index('s3_key');
});
```

#### E) storage_usage
```php
Schema::create('storage_usage', function (Blueprint $table) {
    $table->foreignId('user_id')->primary()->constrained()->onDelete('cascade');
    $table->unsignedBigInteger('used_bytes')->default(0);
    $table->unsignedInteger('files_count')->default(0);
    $table->timestamp('updated_at');
});
```

#### F) storage_share_links (Phase 2)
```php
Schema::create('storage_share_links', function (Blueprint $table) {
    $table->uuid('id')->primary();
    $table->foreignId('user_id')->constrained()->onDelete('cascade');
    $table->uuid('file_id')->nullable();
    $table->uuid('folder_id')->nullable();
    $table->string('token')->unique();
    $table->timestamp('expires_at')->nullable();
    $table->string('password_hash')->nullable();
    $table->unsignedInteger('max_downloads')->nullable();
    $table->unsignedInteger('downloads_count')->default(0);
    $table->timestamps();
    
    $table->foreign('file_id')->references('id')->on('storage_files')->onDelete('cascade');
    $table->foreign('folder_id')->references('id')->on('storage_folders')->onDelete('cascade');
    $table->index('token');
});
```

#### G) storage_audit_logs (Optional)
```php
Schema::create('storage_audit_logs', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->onDelete('cascade');
    $table->enum('action', ['upload', 'delete', 'rename', 'move', 'download', 'share_create']);
    $table->uuid('file_id')->nullable();
    $table->uuid('folder_id')->nullable();
    $table->string('ip_address', 45)->nullable();
    $table->text('user_agent')->nullable();
    $table->json('metadata')->nullable();
    $table->timestamp('created_at');
    
    $table->index(['user_id', 'created_at']);
    $table->index('action');
});
```

### Seeders

#### StoragePlanSeeder
```php
DB::table('storage_plans')->insert([
    [
        'name' => 'Starter (Lite)',
        'slug' => 'starter',
        'quota_bytes' => 2 * 1024 * 1024 * 1024, // 2 GB
        'max_file_size_bytes' => 25 * 1024 * 1024, // 25 MB
        'allowed_mime_types' => json_encode(['application/pdf', 'image/*', 'text/*']),
        'allow_sharing' => false,
    ],
    [
        'name' => 'Basic',
        'slug' => 'basic',
        'quota_bytes' => 20 * 1024 * 1024 * 1024, // 20 GB
        'max_file_size_bytes' => 100 * 1024 * 1024, // 100 MB
        'allowed_mime_types' => null,
        'allow_sharing' => true,
    ],
    // ... Growth and Pro plans
]);
```


## Backend Implementation

### Directory Structure (Domain-Driven)

```
app/
├── Domain/
│   └── Storage/
│       ├── Entities/
│       │   ├── StorageFile.php
│       │   ├── StorageFolder.php
│       │   └── StoragePlan.php
│       ├── ValueObjects/
│       │   ├── FileSize.php
│       │   ├── StorageQuota.php
│       │   └── S3Path.php
│       ├── Services/
│       │   ├── QuotaEnforcementService.php
│       │   └── FileValidationService.php
│       └── Repositories/
│           ├── StorageFileRepository.php
│           └── StorageFolderRepository.php
├── Application/
│   └── Storage/
│       ├── UseCases/
│       │   ├── UploadFileUseCase.php
│       │   ├── DeleteFileUseCase.php
│       │   └── GenerateDownloadUrlUseCase.php
│       └── DTOs/
│           ├── UploadInitDTO.php
│           └── FileMetadataDTO.php
├── Infrastructure/
│   └── Storage/
│       ├── Persistence/
│       │   ├── Eloquent/
│       │   │   ├── StorageFile.php (Model)
│       │   │   ├── StorageFolder.php (Model)
│       │   │   └── StoragePlan.php (Model)
│       │   └── Repositories/
│       │       └── EloquentStorageFileRepository.php
│       └── S3/
│           └── S3StorageService.php
└── Presentation/
    └── Http/
        └── Controllers/
            ├── StorageFileController.php
            ├── StorageFolderController.php
            └── StorageUsageController.php
```

### Core Services

#### 1. S3StorageService
```php
namespace App\Infrastructure\Storage\S3;

use Illuminate\Support\Facades\Storage;
use Aws\S3\S3Client;

class S3StorageService
{
    public function generatePresignedUploadUrl(
        string $s3Key,
        string $mimeType,
        int $expiresIn = 900
    ): string {
        $client = Storage::disk('s3')->getClient();
        $command = $client->getCommand('PutObject', [
            'Bucket' => config('filesystems.disks.s3.bucket'),
            'Key' => $s3Key,
            'ContentType' => $mimeType,
        ]);
        
        $request = $client->createPresignedRequest($command, "+{$expiresIn} seconds");
        return (string) $request->getUri();
    }
    
    public function generatePresignedDownloadUrl(
        string $s3Key,
        int $expiresIn = 900
    ): string {
        return Storage::disk('s3')->temporaryUrl($s3Key, now()->addSeconds($expiresIn));
    }
    
    public function fileExists(string $s3Key): bool
    {
        return Storage::disk('s3')->exists($s3Key);
    }
    
    public function getFileSize(string $s3Key): int
    {
        return Storage::disk('s3')->size($s3Key);
    }
    
    public function deleteFile(string $s3Key): bool
    {
        return Storage::disk('s3')->delete($s3Key);
    }
    
    public function generateS3Key(int $userId, string $filename): string
    {
        $uuid = Str::uuid();
        $sanitized = $this->sanitizeFilename($filename);
        return "storage/{$userId}/{$uuid}_{$sanitized}";
    }
    
    private function sanitizeFilename(string $filename): string
    {
        // Remove path traversal attempts and special characters
        $filename = basename($filename);
        return preg_replace('/[^a-zA-Z0-9._-]/', '_', $filename);
    }
}
```

#### 2. QuotaEnforcementService
```php
namespace App\Domain\Storage\Services;

use App\Infrastructure\Storage\Persistence\Eloquent\StorageUsage;
use App\Infrastructure\Storage\Persistence\Eloquent\UserStorageSubscription;

class QuotaEnforcementService
{
    public function canUpload(int $userId, int $fileSize): bool
    {
        $subscription = $this->getActiveSubscription($userId);
        $usage = StorageUsage::firstOrCreate(['user_id' => $userId]);
        
        $newTotal = $usage->used_bytes + $fileSize;
        return $newTotal <= $subscription->storagePlan->quota_bytes;
    }
    
    public function getRemainingQuota(int $userId): int
    {
        $subscription = $this->getActiveSubscription($userId);
        $usage = StorageUsage::firstOrCreate(['user_id' => $userId]);
        
        return max(0, $subscription->storagePlan->quota_bytes - $usage->used_bytes);
    }
    
    public function incrementUsage(int $userId, int $fileSize): void
    {
        DB::transaction(function () use ($userId, $fileSize) {
            StorageUsage::where('user_id', $userId)->increment('used_bytes', $fileSize);
            StorageUsage::where('user_id', $userId)->increment('files_count');
        });
    }
    
    public function decrementUsage(int $userId, int $fileSize): void
    {
        DB::transaction(function () use ($userId, $fileSize) {
            StorageUsage::where('user_id', $userId)->decrement('used_bytes', $fileSize);
            StorageUsage::where('user_id', $userId)->decrement('files_count');
        });
    }
    
    private function getActiveSubscription(int $userId): UserStorageSubscription
    {
        return UserStorageSubscription::where('user_id', $userId)
            ->where('status', 'active')
            ->with('storagePlan')
            ->firstOrFail();
    }
}
```


#### 3. FileValidationService
```php
namespace App\Domain\Storage\Services;

class FileValidationService
{
    public function validateUpload(
        string $filename,
        int $fileSize,
        string $mimeType,
        StoragePlan $plan
    ): array {
        $errors = [];
        
        // Check file size
        if ($fileSize > $plan->max_file_size_bytes) {
            $errors[] = "File exceeds maximum size of " . $this->formatBytes($plan->max_file_size_bytes);
        }
        
        // Check MIME type if restricted
        if ($plan->allowed_mime_types) {
            $allowed = json_decode($plan->allowed_mime_types, true);
            if (!$this->isMimeTypeAllowed($mimeType, $allowed)) {
                $errors[] = "File type not allowed";
            }
        }
        
        // Check filename
        if (!$this->isValidFilename($filename)) {
            $errors[] = "Invalid filename";
        }
        
        return $errors;
    }
    
    private function isMimeTypeAllowed(string $mimeType, array $allowed): bool
    {
        foreach ($allowed as $pattern) {
            if (fnmatch($pattern, $mimeType)) {
                return true;
            }
        }
        return false;
    }
    
    private function isValidFilename(string $filename): bool
    {
        // Reject path traversal attempts
        if (str_contains($filename, '..') || str_contains($filename, '/')) {
            return false;
        }
        return true;
    }
    
    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }
        return round($bytes, 2) . ' ' . $units[$i];
    }
}
```

### API Endpoints

#### Routes (routes/api.php)
```php
Route::middleware(['auth:sanctum'])->prefix('storage')->group(function () {
    // Folders
    Route::get('/folders', [StorageFolderController::class, 'index']);
    Route::post('/folders', [StorageFolderController::class, 'store']);
    Route::patch('/folders/{folder}', [StorageFolderController::class, 'update']);
    Route::post('/folders/{folder}/move', [StorageFolderController::class, 'move']);
    Route::delete('/folders/{folder}', [StorageFolderController::class, 'destroy']);
    
    // Files
    Route::post('/files/upload-init', [StorageFileController::class, 'uploadInit']);
    Route::post('/files/upload-complete', [StorageFileController::class, 'uploadComplete']);
    Route::get('/files/{file}/download', [StorageFileController::class, 'download']);
    Route::patch('/files/{file}', [StorageFileController::class, 'update']);
    Route::post('/files/{file}/move', [StorageFileController::class, 'move']);
    Route::delete('/files/{file}', [StorageFileController::class, 'destroy']);
    
    // Usage
    Route::get('/usage', [StorageUsageController::class, 'show']);
});
```

#### StorageFileController
```php
namespace App\Presentation\Http\Controllers;

use App\Infrastructure\Storage\S3\S3StorageService;
use App\Domain\Storage\Services\QuotaEnforcementService;
use App\Domain\Storage\Services\FileValidationService;
use Illuminate\Http\Request;

class StorageFileController extends Controller
{
    public function __construct(
        private S3StorageService $s3Service,
        private QuotaEnforcementService $quotaService,
        private FileValidationService $validationService
    ) {}
    
    public function uploadInit(Request $request)
    {
        $validated = $request->validate([
            'folder_id' => 'nullable|uuid|exists:storage_folders,id',
            'filename' => 'required|string|max:255',
            'size' => 'required|integer|min:1',
            'mime_type' => 'required|string',
        ]);
        
        $userId = auth()->id();
        $subscription = $this->getActiveSubscription($userId);
        
        // Validate file
        $errors = $this->validationService->validateUpload(
            $validated['filename'],
            $validated['size'],
            $validated['mime_type'],
            $subscription->storagePlan
        );
        
        if (!empty($errors)) {
            return response()->json(['errors' => $errors], 422);
        }
        
        // Check quota
        if (!$this->quotaService->canUpload($userId, $validated['size'])) {
            return response()->json([
                'error' => 'Storage quota exceeded'
            ], 422);
        }
        
        // Generate S3 key and presigned URL
        $s3Key = $this->s3Service->generateS3Key($userId, $validated['filename']);
        $uploadUrl = $this->s3Service->generatePresignedUploadUrl(
            $s3Key,
            $validated['mime_type']
        );
        
        // Create pending file record
        $file = StorageFile::create([
            'id' => Str::uuid(),
            'user_id' => $userId,
            'folder_id' => $validated['folder_id'],
            'original_name' => $validated['filename'],
            'extension' => pathinfo($validated['filename'], PATHINFO_EXTENSION),
            'mime_type' => $validated['mime_type'],
            'size_bytes' => $validated['size'],
            's3_bucket' => config('filesystems.disks.s3.bucket'),
            's3_key' => $s3Key,
        ]);
        
        return response()->json([
            'file_id' => $file->id,
            'upload_url' => $uploadUrl,
            's3_key' => $s3Key,
            'expires_in' => 900,
        ]);
    }
    
    public function uploadComplete(Request $request)
    {
        $validated = $request->validate([
            'file_id' => 'required|uuid|exists:storage_files,id',
            's3_key' => 'required|string',
            'checksum' => 'nullable|string',
        ]);
        
        $file = StorageFile::findOrFail($validated['file_id']);
        
        // Verify ownership
        if ($file->user_id !== auth()->id()) {
            abort(403);
        }
        
        // Verify S3 object exists
        if (!$this->s3Service->fileExists($validated['s3_key'])) {
            $file->delete();
            return response()->json(['error' => 'File not found in storage'], 422);
        }
        
        // Verify size matches
        $actualSize = $this->s3Service->getFileSize($validated['s3_key']);
        if ($actualSize !== $file->size_bytes) {
            $file->update(['size_bytes' => $actualSize]);
        }
        
        // Update usage
        $this->quotaService->incrementUsage($file->user_id, $file->size_bytes);
        
        // Update file record
        if (isset($validated['checksum'])) {
            $file->update(['checksum' => $validated['checksum']]);
        }
        
        return response()->json([
            'success' => true,
            'file' => $file,
        ]);
    }
    
    public function download(StorageFile $file)
    {
        // Verify ownership
        if ($file->user_id !== auth()->id()) {
            abort(403);
        }
        
        $downloadUrl = $this->s3Service->generatePresignedDownloadUrl($file->s3_key);
        
        return response()->json([
            'url' => $downloadUrl,
            'expires_in' => 900,
        ]);
    }
    
    public function destroy(StorageFile $file)
    {
        // Verify ownership
        if ($file->user_id !== auth()->id()) {
            abort(403);
        }
        
        // Delete from S3
        $this->s3Service->deleteFile($file->s3_key);
        
        // Update usage
        $this->quotaService->decrementUsage($file->user_id, $file->size_bytes);
        
        // Soft delete
        $file->delete();
        
        return response()->json(['success' => true]);
    }
}
```


#### StorageFolderController
```php
namespace App\Presentation\Http\Controllers;

use App\Infrastructure\Storage\Persistence\Eloquent\StorageFolder;
use Illuminate\Http\Request;

class StorageFolderController extends Controller
{
    public function index(Request $request)
    {
        $parentId = $request->query('parent_id');
        $userId = auth()->id();
        
        $folders = StorageFolder::where('user_id', $userId)
            ->where('parent_id', $parentId)
            ->orderBy('name')
            ->get();
        
        $files = StorageFile::where('user_id', $userId)
            ->where('folder_id', $parentId)
            ->where('is_deleted', false)
            ->orderBy('original_name')
            ->get();
        
        return response()->json([
            'folders' => $folders,
            'files' => $files,
        ]);
    }
    
    public function store(Request $request)
    {
        $validated = $request->validate([
            'parent_id' => 'nullable|uuid|exists:storage_folders,id',
            'name' => 'required|string|max:255',
        ]);
        
        $folder = StorageFolder::create([
            'id' => Str::uuid(),
            'user_id' => auth()->id(),
            'parent_id' => $validated['parent_id'],
            'name' => $validated['name'],
        ]);
        
        return response()->json($folder, 201);
    }
    
    public function update(Request $request, StorageFolder $folder)
    {
        if ($folder->user_id !== auth()->id()) {
            abort(403);
        }
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);
        
        $folder->update($validated);
        
        return response()->json($folder);
    }
    
    public function move(Request $request, StorageFolder $folder)
    {
        if ($folder->user_id !== auth()->id()) {
            abort(403);
        }
        
        $validated = $request->validate([
            'new_parent_id' => 'nullable|uuid|exists:storage_folders,id',
        ]);
        
        // Prevent moving folder into itself or its descendants
        if ($this->isDescendant($validated['new_parent_id'], $folder->id)) {
            return response()->json(['error' => 'Cannot move folder into itself'], 422);
        }
        
        $folder->update(['parent_id' => $validated['new_parent_id']]);
        
        return response()->json($folder);
    }
    
    public function destroy(StorageFolder $folder)
    {
        if ($folder->user_id !== auth()->id()) {
            abort(403);
        }
        
        // Check if folder is empty
        $hasChildren = StorageFolder::where('parent_id', $folder->id)->exists();
        $hasFiles = StorageFile::where('folder_id', $folder->id)
            ->where('is_deleted', false)
            ->exists();
        
        if ($hasChildren || $hasFiles) {
            return response()->json([
                'error' => 'Folder must be empty before deletion'
            ], 422);
        }
        
        $folder->delete();
        
        return response()->json(['success' => true]);
    }
    
    private function isDescendant(?string $potentialParentId, string $folderId): bool
    {
        if (!$potentialParentId) {
            return false;
        }
        
        if ($potentialParentId === $folderId) {
            return true;
        }
        
        $parent = StorageFolder::find($potentialParentId);
        if (!$parent || !$parent->parent_id) {
            return false;
        }
        
        return $this->isDescendant($parent->parent_id, $folderId);
    }
}
```

#### StorageUsageController
```php
namespace App\Presentation\Http\Controllers;

use App\Infrastructure\Storage\Persistence\Eloquent\StorageUsage;
use App\Infrastructure\Storage\Persistence\Eloquent\UserStorageSubscription;

class StorageUsageController extends Controller
{
    public function show()
    {
        $userId = auth()->id();
        
        $subscription = UserStorageSubscription::where('user_id', $userId)
            ->where('status', 'active')
            ->with('storagePlan')
            ->firstOrFail();
        
        $usage = StorageUsage::firstOrCreate(['user_id' => $userId]);
        
        $quotaBytes = $subscription->storagePlan->quota_bytes;
        $usedBytes = $usage->used_bytes;
        $percentUsed = $quotaBytes > 0 ? ($usedBytes / $quotaBytes) * 100 : 0;
        
        return response()->json([
            'used_bytes' => $usedBytes,
            'quota_bytes' => $quotaBytes,
            'remaining_bytes' => max(0, $quotaBytes - $usedBytes),
            'percent_used' => round($percentUsed, 2),
            'files_count' => $usage->files_count,
            'plan_name' => $subscription->storagePlan->name,
        ]);
    }
}
```


## Frontend Implementation

### Vue Components Structure

```
resources/js/
├── Pages/
│   └── Storage/
│       ├── Index.vue (Main file manager)
│       ├── Dashboard.vue (Usage overview)
│       └── Settings.vue (Storage settings)
├── Components/
│   └── Storage/
│       ├── FileList.vue
│       ├── FolderTree.vue
│       ├── UploadButton.vue
│       ├── UploadProgress.vue
│       ├── FileActions.vue
│       ├── MoveDialog.vue
│       ├── RenameDialog.vue
│       ├── UsageIndicator.vue
│       └── EmptyState.vue
└── Composables/
    └── useStorage.ts
```

### Key Components

#### 1. Storage Index Page (Main File Manager)
```vue
<!-- resources/js/Pages/Storage/Index.vue -->
<script setup lang="ts">
import { ref, computed, onMounted } from 'vue';
import { router } from '@inertiajs/vue3';
import FileList from '@/Components/Storage/FileList.vue';
import FolderTree from '@/Components/Storage/FolderTree.vue';
import UploadButton from '@/Components/Storage/UploadButton.vue';
import UsageIndicator from '@/Components/Storage/UsageIndicator.vue';
import { useStorage } from '@/Composables/useStorage';

interface Props {
    currentFolderId?: string;
}

const props = defineProps<Props>();

const { 
    folders, 
    files, 
    loading, 
    fetchContents,
    createFolder,
    deleteFile,
    deleteFolder,
    moveFile,
    renameFile
} = useStorage();

const breadcrumbs = ref<Array<{ id: string | null; name: string }>>([
    { id: null, name: 'My Storage' }
]);

onMounted(() => {
    fetchContents(props.currentFolderId);
});

const handleFolderClick = (folderId: string) => {
    router.visit(`/storage?folder=${folderId}`);
};

const handleCreateFolder = async () => {
    const name = prompt('Enter folder name:');
    if (name) {
        await createFolder(name, props.currentFolderId);
        await fetchContents(props.currentFolderId);
    }
};
</script>

<template>
    <AppLayout title="Storage">
        <div class="py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="flex items-center justify-between mb-6">
                    <h1 class="text-2xl font-semibold text-gray-900">My Storage</h1>
                    <div class="flex items-center gap-4">
                        <UsageIndicator />
                        <button
                            @click="handleCreateFolder"
                            class="btn-secondary"
                        >
                            <FolderPlusIcon class="h-5 w-5" aria-hidden="true" />
                            New Folder
                        </button>
                        <UploadButton :folder-id="currentFolderId" />
                    </div>
                </div>

                <!-- Breadcrumbs -->
                <nav class="flex mb-4" aria-label="Breadcrumb">
                    <ol class="flex items-center space-x-2">
                        <li v-for="(crumb, index) in breadcrumbs" :key="index">
                            <div class="flex items-center">
                                <ChevronRightIcon 
                                    v-if="index > 0" 
                                    class="h-4 w-4 text-gray-400 mx-2" 
                                    aria-hidden="true"
                                />
                                <a
                                    :href="crumb.id ? `/storage?folder=${crumb.id}` : '/storage'"
                                    class="text-sm text-gray-500 hover:text-gray-700"
                                >
                                    {{ crumb.name }}
                                </a>
                            </div>
                        </li>
                    </ol>
                </nav>

                <!-- File Manager -->
                <div class="bg-white rounded-lg shadow">
                    <FileList
                        :folders="folders"
                        :files="files"
                        :loading="loading"
                        @folder-click="handleFolderClick"
                        @file-delete="deleteFile"
                        @folder-delete="deleteFolder"
                        @file-rename="renameFile"
                        @file-move="moveFile"
                    />
                </div>
            </div>
        </div>
    </AppLayout>
</template>
```

#### 2. Upload Button Component
```vue
<!-- resources/js/Components/Storage/UploadButton.vue -->
<script setup lang="ts">
import { ref } from 'vue';
import { ArrowUpTrayIcon } from '@heroicons/vue/24/outline';
import UploadProgress from './UploadProgress.vue';
import { useStorageUpload } from '@/Composables/useStorageUpload';

interface Props {
    folderId?: string;
}

const props = defineProps<Props>();

const fileInput = ref<HTMLInputElement>();
const { uploads, uploadFile } = useStorageUpload();

const handleFileSelect = async (event: Event) => {
    const target = event.target as HTMLInputElement;
    const files = target.files;
    
    if (!files) return;
    
    for (const file of Array.from(files)) {
        await uploadFile(file, props.folderId);
    }
    
    // Reset input
    if (fileInput.value) {
        fileInput.value.value = '';
    }
};

const triggerFileInput = () => {
    fileInput.value?.click();
};
</script>

<template>
    <div>
        <input
            ref="fileInput"
            type="file"
            multiple
            class="hidden"
            @change="handleFileSelect"
        />
        
        <button
            @click="triggerFileInput"
            class="btn-primary"
            aria-label="Upload files"
        >
            <ArrowUpTrayIcon class="h-5 w-5" aria-hidden="true" />
            Upload
        </button>
        
        <!-- Upload Progress Modal -->
        <UploadProgress v-if="uploads.length > 0" :uploads="uploads" />
    </div>
</template>
```


#### 3. FileValidationService
```php
namespace App\Domain\Storage\Services;

class FileValidationService
{
    public function validateUpload(
        string $filename,
        int $fileSize,
        string $mimeType,
        StoragePlan $plan
    ): array {
        $errors = [];
        
        // Check file size
        if ($fileSize > $plan->max_file_size_bytes) {
            $errors[] = "File exceeds maximum size of " . $this->formatBytes($plan->max_file_size_bytes);
        }
        
        // Check MIME type if restricted
        if ($plan->allowed_mime_types) {
            $allowed = json_decode($plan->allowed_mime_types, true);
            if (!$this->isMimeTypeAllowed($mimeType, $allowed)) {
                $errors[] = "File type not allowed";
            }
        }
        
        // Check filename
        if (!$this->isValidFilename($filename)) {
            $errors[] = "Invalid filename";
        }
        
        return $errors;
    }
    
    private function isMimeTypeAllowed(string $mimeType, array $allowed): bool
    {
        foreach ($allowed as $pattern) {
            if (fnmatch($pattern, $mimeType)) {
                return true;
            }
        }
        return false;
    }
    
    private function isValidFilename(string $filename): bool
    {
        // Reject path traversal attempts
        if (str_contains($filename, '..') || str_contains($filename, '/')) {
            return false;
        }
        return true;
    }
    
    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $i = 0;
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }
        return round($bytes, 2) . ' ' . $units[$i];
    }
}
```

### Domain Layer Implementation

#### 1. Domain Entities

```php
namespace App\Domain\Storage\Entities;

use App\Domain\Storage\ValueObjects\FileSize;
use App\Domain\Storage\ValueObjects\S3Path;

class StorageFile
{
    private function __construct(
        private string $id,
        private int $userId,
        private ?string $folderId,
        private string $originalName,
        private string $extension,
        private string $mimeType,
        private FileSize $size,
        private S3Path $s3Path,
        private ?string $checksum = null
    ) {}
    
    public static function create(
        string $id,
        int $userId,
        ?string $folderId,
        string $originalName,
        string $mimeType,
        FileSize $size,
        S3Path $s3Path
    ): self {
        return new self(
            $id,
            $userId,
            $folderId,
            $originalName,
            pathinfo($originalName, PATHINFO_EXTENSION),
            $mimeType,
            $size,
            $s3Path
        );
    }
    
    public function rename(string $newName): void
    {
        $this->originalName = $newName;
        $this->extension = pathinfo($newName, PATHINFO_EXTENSION);
    }
    
    public function moveTo(?string $folderId): void
    {
        $this->folderId = $folderId;
    }
    
    public function setChecksum(string $checksum): void
    {
        $this->checksum = $checksum;
    }
    
    // Getters
    public function getId(): string { return $this->id; }
    public function getUserId(): int { return $this->userId; }
    public function getSize(): FileSize { return $this->size; }
    public function getS3Path(): S3Path { return $this->s3Path; }
}
```

#### 2. Value Objects

```php
namespace App\Domain\Storage\ValueObjects;

class FileSize
{
    private function __construct(private int $bytes)
    {
        if ($bytes < 0) {
            throw new \InvalidArgumentException('File size cannot be negative');
        }
    }
    
    public static function fromBytes(int $bytes): self
    {
        return new self($bytes);
    }
    
    public static function fromMegabytes(float $mb): self
    {
        return new self((int)($mb * 1024 * 1024));
    }
    
    public function toBytes(): int
    {
        return $this->bytes;
    }
    
    public function toMegabytes(): float
    {
        return $this->bytes / (1024 * 1024);
    }
    
    public function add(FileSize $other): self
    {
        return new self($this->bytes + $other->bytes);
    }
    
    public function isGreaterThan(FileSize $other): bool
    {
        return $this->bytes > $other->bytes;
    }
    
    public function format(): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = $this->bytes;
        $i = 0;
        
        while ($bytes >= 1024 && $i < count($units) - 1) {
            $bytes /= 1024;
            $i++;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }
}
```

```php
namespace App\Domain\Storage\ValueObjects;

class S3Path
{
    private function __construct(
        private string $bucket,
        private string $key
    ) {
        $this->validate();
    }
    
    public static function create(string $bucket, string $key): self
    {
        return new self($bucket, $key);
    }
    
    public static function forUser(int $userId, string $filename, string $bucket): self
    {
        $uuid = \Illuminate\Support\Str::uuid();
        $sanitized = self::sanitizeFilename($filename);
        $key = "storage/{$userId}/{$uuid}_{$sanitized}";
        
        return new self($bucket, $key);
    }
    
    private function validate(): void
    {
        if (empty($this->bucket)) {
            throw new \InvalidArgumentException('Bucket cannot be empty');
        }
        
        if (empty($this->key)) {
            throw new \InvalidArgumentException('Key cannot be empty');
        }
        
        // Prevent path traversal
        if (str_contains($this->key, '..')) {
            throw new \InvalidArgumentException('Invalid key: path traversal detected');
        }
    }
    
    private static function sanitizeFilename(string $filename): string
    {
        $filename = basename($filename);
        return preg_replace('/[^a-zA-Z0-9._-]/', '_', $filename);
    }
    
    public function getBucket(): string { return $this->bucket; }
    public function getKey(): string { return $this->key; }
    public function getFullPath(): string { return "{$this->bucket}/{$this->key}"; }
}
```

```php
namespace App\Domain\Storage\ValueObjects;

class StorageQuota
{
    private function __construct(
        private FileSize $limit,
        private FileSize $used
    ) {}
    
    public static function create(FileSize $limit, FileSize $used): self
    {
        return new self($limit, $used);
    }
    
    public function canAccommodate(FileSize $fileSize): bool
    {
        $newTotal = $this->used->add($fileSize);
        return !$newTotal->isGreaterThan($this->limit);
    }
    
    public function getRemaining(): FileSize
    {
        $remaining = $this->limit->toBytes() - $this->used->toBytes();
        return FileSize::fromBytes(max(0, $remaining));
    }
    
    public function getPercentUsed(): float
    {
        if ($this->limit->toBytes() === 0) {
            return 0;
        }
        
        return ($this->used->toBytes() / $this->limit->toBytes()) * 100;
    }
    
    public function isNearLimit(float $threshold = 80.0): bool
    {
        return $this->getPercentUsed() >= $threshold;
    }
}
```

#### 3. Domain Events

```php
namespace App\Domain\Storage\Events;

class FileUploaded
{
    public function __construct(
        public readonly string $fileId,
        public readonly int $userId,
        public readonly int $sizeBytes,
        public readonly string $mimeType,
        public readonly \DateTimeImmutable $occurredAt
    ) {}
}
```

```php
namespace App\Domain\Storage\Events;

class FileDeleted
{
    public function __construct(
        public readonly string $fileId,
        public readonly int $userId,
        public readonly int $sizeBytes,
        public readonly \DateTimeImmutable $occurredAt
    ) {}
}
```

```php
namespace App\Domain\Storage\Events;

class QuotaExceeded
{
    public function __construct(
        public readonly int $userId,
        public readonly int $attemptedSize,
        public readonly int $quotaLimit,
        public readonly int $currentUsage,
        public readonly \DateTimeImmutable $occurredAt
    ) {}
}
```


### Application Layer Implementation

#### 1. Use Cases

```php
namespace App\Application\Storage\UseCases;

use App\Domain\Storage\Entities\StorageFile;
use App\Domain\Storage\ValueObjects\FileSize;
use App\Domain\Storage\ValueObjects\S3Path;
use App\Domain\Storage\Services\QuotaEnforcementService;
use App\Domain\Storage\Services\FileValidationService;
use App\Infrastructure\Storage\S3\S3StorageService;
use App\Infrastructure\Storage\Repositories\StorageFileRepository;

class UploadFileUseCase
{
    public function __construct(
        private QuotaEnforcementService $quotaService,
        private FileValidationService $validationService,
        private S3StorageService $s3Service,
        private StorageFileRepository $fileRepository
    ) {}
    
    public function initiate(
        int $userId,
        ?string $folderId,
        string $filename,
        int $sizeBytes,
        string $mimeType
    ): array {
        // Get user's storage plan
        $subscription = $this->getActiveSubscription($userId);
        
        // Validate file
        $errors = $this->validationService->validateUpload(
            $filename,
            $sizeBytes,
            $mimeType,
            $subscription->storagePlan
        );
        
        if (!empty($errors)) {
            throw new \DomainException(implode(', ', $errors));
        }
        
        // Check quota
        $fileSize = FileSize::fromBytes($sizeBytes);
        if (!$this->quotaService->canUpload($userId, $fileSize)) {
            throw new \DomainException('Storage quota exceeded');
        }
        
        // Generate S3 path
        $s3Path = S3Path::forUser(
            $userId,
            $filename,
            config('filesystems.disks.s3.bucket')
        );
        
        // Create domain entity
        $file = StorageFile::create(
            \Illuminate\Support\Str::uuid(),
            $userId,
            $folderId,
            $filename,
            $mimeType,
            $fileSize,
            $s3Path
        );
        
        // Save to repository
        $this->fileRepository->save($file);
        
        // Generate presigned upload URL
        $uploadUrl = $this->s3Service->generatePresignedUploadUrl(
            $s3Path->getKey(),
            $mimeType
        );
        
        return [
            'file_id' => $file->getId(),
            'upload_url' => $uploadUrl,
            's3_key' => $s3Path->getKey(),
            'expires_in' => 900,
        ];
    }
    
    public function complete(string $fileId, int $userId): void
    {
        $file = $this->fileRepository->findById($fileId);
        
        if (!$file || $file->getUserId() !== $userId) {
            throw new \DomainException('File not found or access denied');
        }
        
        // Verify S3 object exists
        if (!$this->s3Service->fileExists($file->getS3Path()->getKey())) {
            $this->fileRepository->delete($file);
            throw new \DomainException('File not found in storage');
        }
        
        // Update usage
        $this->quotaService->incrementUsage($userId, $file->getSize());
        
        // Dispatch domain event
        event(new \App\Domain\Storage\Events\FileUploaded(
            $file->getId(),
            $userId,
            $file->getSize()->toBytes(),
            $file->getMimeType(),
            new \DateTimeImmutable()
        ));
    }
}
```

```php
namespace App\Application\Storage\UseCases;

use App\Domain\Storage\Services\QuotaEnforcementService;
use App\Infrastructure\Storage\S3\S3StorageService;
use App\Infrastructure\Storage\Repositories\StorageFileRepository;

class DeleteFileUseCase
{
    public function __construct(
        private QuotaEnforcementService $quotaService,
        private S3StorageService $s3Service,
        private StorageFileRepository $fileRepository
    ) {}
    
    public function execute(string $fileId, int $userId): void
    {
        $file = $this->fileRepository->findById($fileId);
        
        if (!$file || $file->getUserId() !== $userId) {
            throw new \DomainException('File not found or access denied');
        }
        
        // Delete from S3
        $this->s3Service->deleteFile($file->getS3Path()->getKey());
        
        // Update usage
        $this->quotaService->decrementUsage($userId, $file->getSize());
        
        // Delete from repository
        $this->fileRepository->delete($file);
        
        // Dispatch domain event
        event(new \App\Domain\Storage\Events\FileDeleted(
            $file->getId(),
            $userId,
            $file->getSize()->toBytes(),
            new \DateTimeImmutable()
        ));
    }
}
```

```php
namespace App\Application\Storage\UseCases;

use App\Infrastructure\Storage\S3\S3StorageService;
use App\Infrastructure\Storage\Repositories\StorageFileRepository;

class GenerateDownloadUrlUseCase
{
    public function __construct(
        private S3StorageService $s3Service,
        private StorageFileRepository $fileRepository
    ) {}
    
    public function execute(string $fileId, int $userId): array
    {
        $file = $this->fileRepository->findById($fileId);
        
        if (!$file || $file->getUserId() !== $userId) {
            throw new \DomainException('File not found or access denied');
        }
        
        $downloadUrl = $this->s3Service->generatePresignedDownloadUrl(
            $file->getS3Path()->getKey()
        );
        
        return [
            'url' => $downloadUrl,
            'expires_in' => 900,
            'filename' => $file->getOriginalName(),
        ];
    }
}
```

#### 2. DTOs

```php
namespace App\Application\Storage\DTOs;

class UploadInitDTO
{
    public function __construct(
        public readonly int $userId,
        public readonly ?string $folderId,
        public readonly string $filename,
        public readonly int $sizeBytes,
        public readonly string $mimeType
    ) {}
    
    public static function fromRequest(array $data, int $userId): self
    {
        return new self(
            $userId,
            $data['folder_id'] ?? null,
            $data['filename'],
            $data['size'],
            $data['mime_type']
        );
    }
}
```

```php
namespace App\Application\Storage\DTOs;

class FileMetadataDTO
{
    public function __construct(
        public readonly string $id,
        public readonly string $originalName,
        public readonly string $extension,
        public readonly string $mimeType,
        public readonly int $sizeBytes,
        public readonly string $formattedSize,
        public readonly ?string $folderId,
        public readonly \DateTimeInterface $createdAt,
        public readonly \DateTimeInterface $updatedAt
    ) {}
}
```

```php
namespace App\Application\Storage\DTOs;

class StorageUsageDTO
{
    public function __construct(
        public readonly int $usedBytes,
        public readonly int $quotaBytes,
        public readonly int $remainingBytes,
        public readonly float $percentUsed,
        public readonly int $filesCount,
        public readonly string $planName,
        public readonly bool $nearLimit
    ) {}
}
```


## GrowNet Product Integration

### Product Setup

#### 1. Add Storage Products to Database

```php
// database/seeders/StorageProductSeeder.php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\ProductCategory;

class StorageProductSeeder extends Seeder
{
    public function run()
    {
        $category = ProductCategory::firstOrCreate(
            ['slug' => 'storage'],
            ['name' => 'Cloud Storage', 'description' => 'Secure cloud storage solutions']
        );
        
        $products = [
            [
                'name' => 'MyGrowNet Storage - Starter',
                'slug' => 'storage-starter',
                'description' => '2GB secure cloud storage included in starter kit',
                'product_type' => 'storage',
                'price' => 0, // Bundled with starter kit
                'is_active' => true,
                'is_commission_eligible' => false, // Part of starter kit
                'metadata' => json_encode([
                    'quota_gb' => 2,
                    'max_file_size_mb' => 25,
                    'plan_slug' => 'starter',
                    'is_starter_kit' => true,
                ]),
            ],
            [
                'name' => 'MyGrowNet Storage - Basic',
                'slug' => 'storage-basic',
                'description' => '20GB secure cloud storage with sharing capabilities',
                'product_type' => 'storage',
                'price' => 50.00,
                'recurring_interval' => 'monthly',
                'is_active' => true,
                'is_commission_eligible' => true,
                'commission_rate' => 20.00, // 20% for level 1
                'metadata' => json_encode([
                    'quota_gb' => 20,
                    'max_file_size_mb' => 100,
                    'plan_slug' => 'basic',
                    'min_level' => 'professional',
                    'lp_value' => 100,
                    'map_value' => 20,
                ]),
            ],
            [
                'name' => 'MyGrowNet Storage - Growth',
                'slug' => 'storage-growth',
                'description' => '100GB storage with team folders and versioning',
                'product_type' => 'storage',
                'price' => 150.00,
                'recurring_interval' => 'monthly',
                'is_active' => true,
                'is_commission_eligible' => true,
                'commission_rate' => 20.00,
                'metadata' => json_encode([
                    'quota_gb' => 100,
                    'max_file_size_mb' => 500,
                    'plan_slug' => 'growth',
                    'min_level' => 'senior',
                    'lp_value' => 300,
                    'map_value' => 20,
                ]),
            ],
            [
                'name' => 'MyGrowNet Storage - Pro',
                'slug' => 'storage-pro',
                'description' => '500GB premium storage with priority support',
                'product_type' => 'storage',
                'price' => 300.00,
                'recurring_interval' => 'monthly',
                'is_active' => true,
                'is_commission_eligible' => true,
                'commission_rate' => 20.00,
                'metadata' => json_encode([
                    'quota_gb' => 500,
                    'max_file_size_mb' => 2048,
                    'plan_slug' => 'pro',
                    'min_level' => 'manager',
                    'lp_value' => 500,
                    'map_value' => 20,
                ]),
            ],
        ];
        
        foreach ($products as $productData) {
            $productData['category_id'] = $category->id;
            Product::create($productData);
        }
    }
}
```

#### 2. Link Storage Plans to Products

```php
// database/migrations/xxxx_add_product_id_to_storage_plans.php
Schema::table('storage_plans', function (Blueprint $table) {
    $table->foreignId('product_id')->nullable()->constrained()->onDelete('set null');
});
```

#### 3. Subscription Integration Service

```php
namespace App\Application\Storage\Services;

use App\Models\Product;
use App\Models\UserProductSubscription;
use App\Infrastructure\Storage\Persistence\Eloquent\StoragePlan;
use App\Infrastructure\Storage\Persistence\Eloquent\UserStorageSubscription;

class StorageSubscriptionService
{
    public function provisionFromProduct(int $userId, Product $product): void
    {
        $metadata = json_decode($product->metadata, true);
        $planSlug = $metadata['plan_slug'] ?? null;
        
        if (!$planSlug) {
            throw new \DomainException('Product does not have storage plan metadata');
        }
        
        $storagePlan = StoragePlan::where('slug', $planSlug)->firstOrFail();
        
        // Create or update storage subscription
        UserStorageSubscription::updateOrCreate(
            ['user_id' => $userId],
            [
                'storage_plan_id' => $storagePlan->id,
                'status' => 'active',
                'start_at' => now(),
                'end_at' => $product->recurring_interval ? now()->addMonth() : null,
                'source' => 'grownet',
            ]
        );
    }
    
    public function syncFromGrowNetSubscription(int $userId): void
    {
        // Get active storage product subscription
        $subscription = UserProductSubscription::where('user_id', $userId)
            ->where('status', 'active')
            ->whereHas('product', function ($query) {
                $query->where('product_type', 'storage');
            })
            ->with('product')
            ->orderBy('created_at', 'desc')
            ->first();
        
        if ($subscription) {
            $this->provisionFromProduct($userId, $subscription->product);
        } else {
            // Downgrade to starter plan
            $this->downgradeToStarter($userId);
        }
    }
    
    private function downgradeToStarter(int $userId): void
    {
        $starterPlan = StoragePlan::where('slug', 'starter')->firstOrFail();
        
        UserStorageSubscription::updateOrCreate(
            ['user_id' => $userId],
            [
                'storage_plan_id' => $starterPlan->id,
                'status' => 'active',
                'start_at' => now(),
                'end_at' => null,
                'source' => 'grownet',
            ]
        );
    }
}
```

#### 4. Event Listeners for Product Subscriptions

```php
namespace App\Infrastructure\Storage\Events;

use App\Events\ProductSubscriptionCreated;
use App\Application\Storage\Services\StorageSubscriptionService;

class ProvisionStorageOnSubscription
{
    public function __construct(
        private StorageSubscriptionService $storageSubscriptionService
    ) {}
    
    public function handle(ProductSubscriptionCreated $event): void
    {
        $product = $event->subscription->product;
        
        // Only handle storage products
        if ($product->product_type !== 'storage') {
            return;
        }
        
        $this->storageSubscriptionService->provisionFromProduct(
            $event->subscription->user_id,
            $product
        );
    }
}
```

```php
namespace App\Infrastructure\Storage\Events;

use App\Events\ProductSubscriptionCancelled;
use App\Application\Storage\Services\StorageSubscriptionService;

class HandleStorageSubscriptionCancellation
{
    public function __construct(
        private StorageSubscriptionService $storageSubscriptionService
    ) {}
    
    public function handle(ProductSubscriptionCancelled $event): void
    {
        $product = $event->subscription->product;
        
        if ($product->product_type !== 'storage') {
            return;
        }
        
        // Sync to check if user has other active storage subscriptions
        $this->storageSubscriptionService->syncFromGrowNetSubscription(
            $event->subscription->user_id
        );
    }
}
```

#### 5. Register Event Listeners

```php
// app/Providers/EventServiceProvider.php
protected $listen = [
    \App\Events\ProductSubscriptionCreated::class => [
        \App\Infrastructure\Storage\Events\ProvisionStorageOnSubscription::class,
    ],
    \App\Events\ProductSubscriptionCancelled::class => [
        \App\Infrastructure\Storage\Events\HandleStorageSubscriptionCancellation::class,
    ],
    \App\Events\ProductSubscriptionUpgraded::class => [
        \App\Infrastructure\Storage\Events\ProvisionStorageOnSubscription::class,
    ],
];
```

### Commission Integration

Storage product purchases automatically trigger commissions through the existing GrowNet MLM system:

1. **Product Purchase** → `ProductSubscriptionCreated` event
2. **Commission Calculation** → Existing `CommissionCalculationService` handles it
3. **Points Award** → LP and MAP automatically credited
4. **Storage Provisioning** → `ProvisionStorageOnSubscription` listener activates storage

No additional commission logic needed - the existing MLM system handles everything.

### Artisan Commands

```php
namespace App\Console\Commands\Storage;

use Illuminate\Console\Command;
use App\Application\Storage\Services\StorageSubscriptionService;
use App\Models\User;

class SyncStorageSubscriptions extends Command
{
    protected $signature = 'storage:sync-subscriptions';
    protected $description = 'Sync storage subscriptions from GrowNet product subscriptions';
    
    public function handle(StorageSubscriptionService $service): int
    {
        $this->info('Syncing storage subscriptions...');
        
        $users = User::whereHas('productSubscriptions', function ($query) {
            $query->where('status', 'active')
                  ->whereHas('product', function ($q) {
                      $q->where('product_type', 'storage');
                  });
        })->get();
        
        $bar = $this->output->createProgressBar($users->count());
        
        foreach ($users as $user) {
            $service->syncFromGrowNetSubscription($user->id);
            $bar->advance();
        }
        
        $bar->finish();
        $this->newLine();
        $this->info('Sync complete!');
        
        return 0;
    }
}
```

### Scheduled Tasks

```php
// app/Console/Kernel.php
protected function schedule(Schedule $schedule)
{
    // Sync storage subscriptions nightly
    $schedule->command('storage:sync-subscriptions')->daily();
    
    // Reconcile usage (verify DB matches S3)
    $schedule->command('storage:reconcile-usage')->daily();
    
    // Cleanup orphaned S3 objects
    $schedule->command('storage:cleanup-orphans')->daily();
}
```

