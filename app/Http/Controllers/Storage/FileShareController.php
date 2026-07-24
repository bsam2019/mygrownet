<?php

namespace App\Http\Controllers\Storage;

use App\Http\Controllers\Controller;
use App\Application\Storage\UseCases\CreateFileShareUseCase;
use App\Domain\Storage\Repositories\FileShareRepositoryInterface;
use App\Domain\Storage\Repositories\StorageFileRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Inertia\Inertia;

class FileShareController extends Controller
{
    public function __construct(
        private CreateFileShareUseCase $createShareUseCase,
        private FileShareRepositoryInterface $shareRepo,
        private StorageFileRepositoryInterface $fileRepo
    ) {}

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

    public function index(string $fileId)
    {
        $file = $this->fileRepo->findById($fileId);

        if (!$file || !$file->belongsToUser(auth()->id())) {
            abort(403);
        }

        $shares = collect($this->shareRepo->findByFileId($fileId))->map(function ($share) {
            $expiresAt = $share['expires_at'] ?? null;
            $createdAt = $share['created_at'] ?? null;

            return [
                'id' => $share['id'],
                'share_url' => url("/share/{$share['share_token']}"),
                'has_password' => !empty($share['password']),
                'expires_at' => $expiresAt ? (is_string($expiresAt) ? $expiresAt : date('c', strtotime($expiresAt))) : null,
                'max_downloads' => $share['max_downloads'],
                'download_count' => $share['download_count'],
                'is_active' => $share['is_active'],
                'can_access' => $this->checkCanAccess($share),
                'created_at' => $createdAt ? (is_string($createdAt) ? $createdAt : date('c', strtotime($createdAt))) : null,
            ];
        })->all();

        return response()->json(['shares' => $shares]);
    }

    public function destroy(string $shareId)
    {
        $share = $this->shareRepo->findById($shareId);

        if (!$share) {
            abort(404);
        }

        if ($share['user_id'] !== auth()->id()) {
            abort(403);
        }

        $this->shareRepo->delete($shareId);

        return response()->json(['success' => true]);
    }

    public function show(string $token)
    {
        $share = $this->shareRepo->findByToken($token);

        if (!$share) {
            abort(404);
        }

        if (!$this->checkCanAccess($share)) {
            return Inertia::render('GrowBackup/ShareExpired');
        }

        if ($share['password'] && !session()->has("share_access_{$token}")) {
            return Inertia::render('GrowBackup/SharePassword', [
                'token' => $token,
                'filename' => $share['file']['original_name'] ?? 'Unknown',
            ]);
        }

        return Inertia::render('GrowBackup/ShareView', [
            'token' => $token,
            'file' => [
                'id' => $share['file']['id'] ?? null,
                'name' => $share['file']['original_name'] ?? 'Unknown',
                'size' => '',
                'mime_type' => $share['file']['mime_type'] ?? '',
                'can_preview' => $share['allow_preview'] ?? false,
            ],
            'share' => [
                'expires_at' => $share['expires_at'] ?? null,
                'downloads_remaining' => $share['max_downloads']
                    ? $share['max_downloads'] - ($share['download_count'] ?? 0)
                    : null,
            ],
        ]);
    }

    public function verifyPassword(Request $request, string $token)
    {
        $validated = $request->validate([
            'password' => 'required|string',
        ]);

        $share = $this->shareRepo->findByToken($token);

        if (!$share) {
            abort(404);
        }

        if (!Hash::check($validated['password'], $share['password'])) {
            return response()->json(['error' => 'Incorrect password'], 401);
        }

        $accessToken = Str::random(64);
        session()->put("share_access_{$token}", $accessToken);

        return response()->json(['success' => true]);
    }

    public function stream(string $token)
    {
        $share = $this->shareRepo->findByToken($token);

        if (!$share) {
            abort(404);
        }

        if (!$this->checkCanAccess($share)) {
            abort(403, 'This share link has expired or reached its download limit');
        }

        if ($share['password'] && !session()->has("share_access_{$token}")) {
            abort(403, 'Password required');
        }

        $file = $share['file'] ?? null;
        if (!$file) {
            abort(404, 'File not found');
        }

        if (!Storage::disk('s3')->exists($file['s3_key'])) {
            abort(404, 'File not found');
        }

        $content = Storage::disk('s3')->get($file['s3_key']);

        return response($content)
            ->header('Content-Type', $file['mime_type'])
            ->header('Content-Disposition', 'inline; filename="' . $file['original_name'] . '"')
            ->header('Content-Length', $file['size_bytes'])
            ->header('Cache-Control', 'private, max-age=3600');
    }

    public function download(string $token)
    {
        $share = $this->shareRepo->findByToken($token);

        if (!$share) {
            abort(404);
        }

        if (!$this->checkCanAccess($share)) {
            abort(403, 'This share link has expired or reached its download limit');
        }

        if ($share['password'] && !session()->has("share_access_{$token}")) {
            abort(403, 'Password required');
        }

        $this->shareRepo->incrementDownloadCount($share['id']);

        $file = $share['file'] ?? null;
        if (!$file) {
            abort(404, 'File not found');
        }

        if (!Storage::disk('s3')->exists($file['s3_key'])) {
            abort(404, 'File not found');
        }

        return Storage::disk('s3')->download($file['s3_key'], $file['original_name']);
    }

    private function checkCanAccess(array $share): bool
    {
        if (!$share['is_active']) {
            return false;
        }

        if ($share['expires_at']) {
            $expiresAt = is_string($share['expires_at']) ? strtotime($share['expires_at']) : strtotime($share['expires_at']);
            if ($expiresAt && $expiresAt < time()) {
                return false;
            }
        }

        if ($share['max_downloads'] && ($share['download_count'] ?? 0) >= $share['max_downloads']) {
            return false;
        }

        return true;
    }
}