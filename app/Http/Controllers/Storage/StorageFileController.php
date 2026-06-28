<?php

namespace App\Http\Controllers\Storage;

use App\Http\Controllers\Controller;
use App\Application\Storage\UseCases\UploadFileUseCase;
use App\Application\Storage\UseCases\DeleteFileUseCase;
use App\Application\Storage\UseCases\GenerateDownloadUrlUseCase;
use App\Infrastructure\Storage\Persistence\Eloquent\StorageFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Domain\Storage\Services\QuotaEnforcementService;
use Illuminate\Support\Facades\Storage;

class StorageFileController extends Controller
{
    public function __construct(
        private UploadFileUseCase $uploadUseCase,
        private DeleteFileUseCase $deleteUseCase,
        private GenerateDownloadUrlUseCase $downloadUseCase,
        private QuotaEnforcementService $quotaService
    ) {}

    public function uploadInit(Request $request)
    {
        try {
            $validated = $request->validate([
                'folder_id' => 'nullable|string|exists:storage_folders,id',
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
            Log::info('Upload init started', [
                'user_id' => auth()->id(),
                'filename' => $validated['filename'],
                'size' => $validated['size'],
            ]);

            $result = $this->uploadUseCase->initiate(
                auth()->id(),
                $validated['folder_id'] ?? null,
                $validated['filename'],
                $validated['size'],
                $validated['mime_type']
            );

            Log::info('Upload init successful', ['file_id' => $result['file_id']]);

            return response()->json($result);
        } catch (\DomainException $e) {
            Log::error('Upload init domain exception', [
                'message' => $e->getMessage(),
                'user_id' => auth()->id(),
                'file' => $validated['filename'],
            ]);
            return response()->json(['error' => $e->getMessage()], 422);
        } catch (\Exception $e) {
            Log::error('Upload init failed', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
                'file' => $validated['filename'] ?? 'unknown',
                'line' => $e->getLine(),
                'file_path' => $e->getFile(),
            ]);
            return response()->json([
                'error' => 'Upload initialization failed: ' . $e->getMessage()
            ], 500);
        }
    }

    public function uploadComplete(Request $request)
    {
        Log::info('Upload complete endpoint hit', [
            'all_data' => $request->all(),
            'user_id' => auth()->id(),
        ]);

        try {
            $validated = $request->validate([
                'file_id' => 'required|uuid|exists:storage_files,id',
                's3_key' => 'required|string',
                'checksum' => 'nullable|string',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Upload complete validation failed', [
                'errors' => $e->errors(),
                'input' => $request->all(),
            ]);
            return response()->json([
                'error' => 'Validation failed',
                'details' => $e->errors()
            ], 422);
        }

        Log::info('Upload complete request validated', [
            'file_id' => $validated['file_id'],
            's3_key' => $validated['s3_key'],
            'user_id' => auth()->id(),
        ]);

        try {
            $this->uploadUseCase->complete(
                $validated['file_id'],
                auth()->id()
            );

            $file = StorageFile::find($validated['file_id']);

            Log::info('Upload completed successfully', [
                'file_id' => $file->id,
                'filename' => $file->original_name,
            ]);

            return response()->json([
                'success' => true,
                'file' => [
                    'id' => $file->id,
                    'original_name' => $file->original_name,
                    'size_bytes' => $file->size_bytes,
                    'formatted_size' => $file->formatted_size,
                    'mime_type' => $file->mime_type,
                    'created_at' => $file->created_at,
                ],
            ]);
        } catch (\DomainException $e) {
            Log::error('Upload complete domain exception', [
                'file_id' => $validated['file_id'],
                'error' => $e->getMessage(),
            ]);
            return response()->json(['error' => $e->getMessage()], 422);
        } catch (\Exception $e) {
            Log::error('Upload complete failed', [
                'file_id' => $validated['file_id'],
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
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
            Log::error('Download URL generation failed', [
                'file_id' => $fileId,
                'error' => $e->getMessage()
            ]);
            return response()->json(['error' => 'Download failed'], 500);
        }
    }

    /**
     * Stream file content through Laravel (hides S3 URLs from users)
     */
    public function stream(string $fileId)
    {
        try {
            $file = StorageFile::findOrFail($fileId);

            // Authorization check
            if ($file->user_id !== auth()->id()) {
                abort(403, 'Unauthorized access to file');
            }

            // Check if file exists in S3
            if (!Storage::disk('s3')->exists($file->s3_key)) {
                abort(404, 'File not found in storage');
            }

            // Get file content from S3
            $content = Storage::disk('s3')->get($file->s3_key);
            
            // Return streaming response with proper headers
            return response($content)
                ->header('Content-Type', $file->mime_type)
                ->header('Content-Disposition', 'inline; filename="' . $file->original_name . '"')
                ->header('Content-Length', $file->size_bytes)
                ->header('Cache-Control', 'private, max-age=3600')
                ->header('X-Content-Type-Options', 'nosniff');
                
        } catch (\Exception $e) {
            Log::error('File streaming failed', [
                'file_id' => $fileId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            abort(500, 'Failed to stream file');
        }
    }

    /**
     * Download file (forces download instead of inline display)
     */
    public function forceDownload(string $fileId)
    {
        try {
            $file = StorageFile::findOrFail($fileId);

            // Authorization check
            if ($file->user_id !== auth()->id()) {
                abort(403, 'Unauthorized access to file');
            }

            // Check if file exists in S3
            if (!Storage::disk('s3')->exists($file->s3_key)) {
                abort(404, 'File not found in storage');
            }

            // Stream download from S3
            return Storage::disk('s3')->download($file->s3_key, $file->original_name);
                
        } catch (\Exception $e) {
            Log::error('File download failed', [
                'file_id' => $fileId,
                'error' => $e->getMessage()
            ]);
            abort(500, 'Failed to download file');
        }
    }

    public function update(Request $request, string $fileId)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $file = StorageFile::findOrFail($fileId);

        if ($file->user_id !== auth()->id()) {
            abort(403);
        }

        $file->update(['original_name' => $validated['name']]);

        return response()->json([
            'success' => true,
            'file' => $file,
        ]);
    }

    public function move(Request $request, string $fileId)
    {
        $validated = $request->validate([
            'folder_id' => 'nullable|uuid|exists:storage_folders,id',
        ]);

        $file = StorageFile::findOrFail($fileId);

        if ($file->user_id !== auth()->id()) {
            abort(403);
        }

        $file->update(['folder_id' => $validated['folder_id']]);

        return response()->json([
            'success' => true,
            'file' => $file,
        ]);
    }

    public function destroy(string $fileId)
    {
        Log::info('File deletion requested', [
            'file_id' => $fileId,
            'user_id' => auth()->id()
        ]);
        
        try {
            $this->deleteUseCase->execute($fileId, auth()->id());

            Log::info('File deleted successfully', ['file_id' => $fileId]);
            
            return response()->json(['success' => true]);
        } catch (\DomainException $e) {
            Log::warning('File deletion domain exception', [
                'file_id' => $fileId,
                'error' => $e->getMessage()
            ]);
            return response()->json(['error' => $e->getMessage()], 403);
        } catch (\Exception $e) {
            Log::error('File deletion failed', [
                'file_id' => $fileId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => 'Deletion failed: ' . $e->getMessage()], 500);
        }
    }
}
