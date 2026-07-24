<?php

namespace App\Http\Controllers\Storage;

use App\Http\Controllers\Controller;
use App\Application\Storage\UseCases\UploadFileUseCase;
use App\Application\Storage\UseCases\DeleteFileUseCase;
use App\Application\Storage\UseCases\GenerateDownloadUrlUseCase;
use App\Domain\Storage\Repositories\StorageFileRepositoryInterface;
use App\Domain\Storage\Services\QuotaEnforcementService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class StorageFileController extends Controller
{
    public function __construct(
        private UploadFileUseCase $uploadUseCase,
        private DeleteFileUseCase $deleteUseCase,
        private GenerateDownloadUrlUseCase $downloadUseCase,
        private QuotaEnforcementService $quotaService,
        private StorageFileRepositoryInterface $fileRepo
    ) {}

    public function uploadInit(Request $request)
    {
        try {
            $validated = $request->validate([
                'folder_id' => 'nullable|string',
                'filename' => 'required|string|max:255',
                'size' => 'required|integer|min:1',
                'mime_type' => 'required|string',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Upload init validation failed', [
                'errors' => $e->errors(),
                'input' => $request->except('file'),
            ]);
            return response()->json([
                'error' => 'Validation failed',
                'details' => $e->errors()
            ], 422);
        }

        try {
            $result = $this->uploadUseCase->initiate(
                auth()->id(),
                $validated['folder_id'] ?? null,
                $validated['filename'],
                $validated['size'],
                $validated['mime_type']
            );

            return response()->json($result);
        } catch (\DomainException $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Upload initialization failed: ' . $e->getMessage()], 500);
        }
    }

    public function uploadComplete(Request $request)
    {
        try {
            $validated = $request->validate([
                'file_id' => 'required|string',
                's3_key' => 'required|string',
                'checksum' => 'nullable|string',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'error' => 'Validation failed',
                'details' => $e->errors()
            ], 422);
        }

        try {
            $this->uploadUseCase->complete($validated['file_id'], auth()->id());

            $file = $this->fileRepo->findById($validated['file_id']);

            return response()->json([
                'success' => true,
                'file' => $file ? [
                    'id' => $file->getId(),
                    'original_name' => $file->getOriginalName(),
                    'size_bytes' => $file->getSize()->toBytes(),
                    'mime_type' => $file->getMimeType()->getValue(),
                    'created_at' => null,
                ] : null,
            ]);
        } catch (\DomainException $e) {
            return response()->json(['error' => $e->getMessage()], 422);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Upload completion failed: ' . $e->getMessage()], 500);
        }
    }

    public function download(string $fileId)
    {
        try {
            $result = $this->downloadUseCase->execute($fileId, auth()->id());
            return response()->json($result);
        } catch (\DomainException $e) {
            return response()->json(['error' => $e->getMessage()], 403);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Download failed'], 500);
        }
    }

    public function stream(string $fileId)
    {
        try {
            $file = $this->fileRepo->findById($fileId);

            if (!$file || !$file->belongsToUser(auth()->id())) {
                abort(403, 'Unauthorized access to file');
            }

            $s3Path = $file->getS3Path();
            if (!Storage::disk('s3')->exists($s3Path->getKey())) {
                abort(404, 'File not found in storage');
            }

            $content = Storage::disk('s3')->get($s3Path->getKey());

            return response($content)
                ->header('Content-Type', $file->getMimeType()->getValue())
                ->header('Content-Disposition', 'inline; filename="' . $file->getOriginalName() . '"')
                ->header('Content-Length', $file->getSize()->toBytes())
                ->header('Cache-Control', 'private, max-age=3600')
                ->header('X-Content-Type-Options', 'nosniff');
        } catch (\Exception $e) {
            abort(500, 'Failed to stream file');
        }
    }

    public function forceDownload(string $fileId)
    {
        try {
            $file = $this->fileRepo->findById($fileId);

            if (!$file || !$file->belongsToUser(auth()->id())) {
                abort(403, 'Unauthorized access to file');
            }

            $s3Path = $file->getS3Path();
            if (!Storage::disk('s3')->exists($s3Path->getKey())) {
                abort(404, 'File not found in storage');
            }

            return Storage::disk('s3')->download($s3Path->getKey(), $file->getOriginalName());
        } catch (\Exception $e) {
            abort(500, 'Failed to download file');
        }
    }

    public function update(Request $request, string $fileId)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $file = $this->fileRepo->findById($fileId);

        if (!$file || !$file->belongsToUser(auth()->id())) {
            abort(403);
        }

        $file->rename($validated['name']);
        $this->fileRepo->save($file);

        return response()->json(['success' => true]);
    }

    public function move(Request $request, string $fileId)
    {
        $validated = $request->validate([
            'folder_id' => 'nullable|string',
        ]);

        $file = $this->fileRepo->findById($fileId);

        if (!$file || !$file->belongsToUser(auth()->id())) {
            abort(403);
        }

        $file->moveTo($validated['folder_id']);
        $this->fileRepo->save($file);

        return response()->json(['success' => true]);
    }

    public function destroy(string $fileId)
    {
        try {
            $this->deleteUseCase->execute($fileId, auth()->id());
            return response()->json(['success' => true]);
        } catch (\DomainException $e) {
            return response()->json(['error' => $e->getMessage()], 403);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Deletion failed: ' . $e->getMessage()], 500);
        }
    }
}