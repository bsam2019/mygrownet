<?php

namespace App\Http\Controllers\Storage;

use App\Http\Controllers\Controller;
use App\Application\Storage\UseCases\CreateFileShareUseCase;
use App\Infrastructure\Storage\Persistence\Eloquent\FileShare;
use App\Infrastructure\Storage\Persistence\Eloquent\StorageFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Inertia\Inertia;

class FileShareController extends Controller
{
    public function __construct(
        private CreateFileShareUseCase $createShareUseCase
    ) {}

    /**
     * Create a new file share
     */
    public function store(Request $request, string $fileId)
    {
        $validated = $request->validate([
            'password' => 'nullable|string|min:4',
            'expires_in_days' => 'nullable|integer|min:1|max:365',
            'max_downloads' => 'nullable|integer|min:1|max:1000',
        ]);

        try {
            $result = $this->createShareUseCase->execute(
                $fileId,
                auth()->id(),
                $validated['password'] ?? null,
                $validated['expires_in_days'] ?? null,
                $validated['max_downloads'] ?? null
            );

            return response()->json($result);
        } catch (\DomainException $e) {
            return response()->json(['error' => $e->getMessage()], 403);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to create share'], 500);
        }
    }

    /**
     * Get all shares for a file
     */
    public function index(string $fileId)
    {
        $file = StorageFile::findOrFail($fileId);
        
        if ($file->user_id !== auth()->id()) {
            abort(403);
        }

        $shares = FileShare::where('file_id', $fileId)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($share) {
                return [
                    'id' => $share->id,
                    'share_url' => url("/share/{$share->share_token}"),
                    'has_password' => !empty($share->password),
                    'expires_at' => $share->expires_at?->toIso8601String(),
                    'max_downloads' => $share->max_downloads,
                    'download_count' => $share->download_count,
                    'is_active' => $share->is_active,
                    'can_access' => $share->canAccess(),
                    'created_at' => $share->created_at->toIso8601String(),
                ];
            });

        return response()->json(['shares' => $shares]);
    }

    /**
     * Delete a share
     */
    public function destroy(string $shareId)
    {
        $share = FileShare::findOrFail($shareId);
        
        if ($share->user_id !== auth()->id()) {
            abort(403);
        }

        $share->delete();

        return response()->json(['success' => true]);
    }

    /**
     * Public share access page
     */
    public function show(string $token)
    {
        $share = FileShare::where('share_token', $token)
            ->with('file')
            ->firstOrFail();

        if (!$share->canAccess()) {
            return Inertia::render('GrowBackup/ShareExpired');
        }

        // If password protected and not verified, show password form
        if ($share->password && !session()->has("share_access_{$token}")) {
            return Inertia::render('GrowBackup/SharePassword', [
                'token' => $token,
                'filename' => $share->file->original_name,
            ]);
        }

        // Show file preview/download page
        return Inertia::render('GrowBackup/ShareView', [
            'token' => $token,
            'file' => [
                'id' => $share->file->id,
                'name' => $share->file->original_name,
                'size' => $share->file->formatted_size,
                'mime_type' => $share->file->mime_type,
                'can_preview' => $share->allow_preview,
            ],
            'share' => [
                'expires_at' => $share->expires_at?->toIso8601String(),
                'downloads_remaining' => $share->max_downloads 
                    ? $share->max_downloads - $share->download_count 
                    : null,
            ],
        ]);
    }

    /**
     * Verify password for protected share
     */
    public function verifyPassword(Request $request, string $token)
    {
        $validated = $request->validate([
            'password' => 'required|string',
        ]);

        $share = FileShare::where('share_token', $token)->firstOrFail();

        if (!Hash::check($validated['password'], $share->password)) {
            return response()->json(['error' => 'Incorrect password'], 401);
        }

        // Generate temporary access token
        $accessToken = Str::random(64);
        session()->put("share_access_{$token}", $accessToken);

        return response()->json(['success' => true]);
    }

    /**
     * Stream shared file for preview
     */
    public function stream(string $token)
    {
        $share = FileShare::where('share_token', $token)
            ->with('file')
            ->firstOrFail();

        if (!$share->canAccess()) {
            abort(403, 'This share link has expired or reached its download limit');
        }

        // Check password if required
        if ($share->password && !session()->has("share_access_{$token}")) {
            abort(403, 'Password required');
        }

        $file = $share->file;
        
        if (!Storage::disk('s3')->exists($file->s3_key)) {
            abort(404, 'File not found');
        }

        // Get file content from S3
        $content = Storage::disk('s3')->get($file->s3_key);
        
        // Return streaming response with proper headers
        return response($content)
            ->header('Content-Type', $file->mime_type)
            ->header('Content-Disposition', 'inline; filename="' . $file->original_name . '"')
            ->header('Content-Length', $file->size_bytes)
            ->header('Cache-Control', 'private, max-age=3600');
    }

    /**
     * Download shared file
     */
    public function download(string $token)
    {
        $share = FileShare::where('share_token', $token)
            ->with('file')
            ->firstOrFail();

        if (!$share->canAccess()) {
            abort(403, 'This share link has expired or reached its download limit');
        }

        // Check password if required
        if ($share->password && !session()->has("share_access_{$token}")) {
            abort(403, 'Password required');
        }

        // Increment download count
        $share->incrementDownloadCount();

        // Stream file from S3
        $file = $share->file;
        
        if (!Storage::disk('s3')->exists($file->s3_key)) {
            abort(404, 'File not found');
        }

        return Storage::disk('s3')->download($file->s3_key, $file->original_name);
    }
}
