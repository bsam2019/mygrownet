<?php

namespace App\Http\Controllers\Storage;

use App\Http\Controllers\Controller;
use App\Domain\Storage\Repositories\StorageFolderRepositoryInterface;
use App\Domain\Storage\Repositories\StorageFileRepositoryInterface;
use App\Domain\Storage\Entities\StorageFolder;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class StorageFolderController extends Controller
{
    public function __construct(
        private StorageFolderRepositoryInterface $folderRepo,
        private StorageFileRepositoryInterface $fileRepo
    ) {}

    public function index(Request $request)
    {
        $parentId = $request->query('parent_id');
        $userId = auth()->id();

        $folders = array_map(fn($f) => [
            'id' => $f->getId(),
            'name' => $f->getName(),
            'parent_id' => $f->getParentId(),
            'created_at' => null,
            'updated_at' => null,
        ], $this->folderRepo->findByUserId($userId, $parentId));

        $files = array_map(function ($f) {
            return [
                'id' => $f->getId(),
                'original_name' => $f->getOriginalName(),
                'extension' => $f->getExtension(),
                'mime_type' => $f->getMimeType()->getValue(),
                'size_bytes' => $f->getSize()->toBytes(),
                'folder_id' => $f->getFolderId(),
                'created_at' => null,
                'updated_at' => null,
            ];
        }, $this->fileRepo->findByUserId($userId, $parentId));

        return response()->json([
            'folders' => $folders,
            'files' => $files,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'parent_id' => 'nullable|string',
            'name' => 'required|string|max:255',
        ]);

        $userId = auth()->id();
        $parentId = $validated['parent_id'] ?? null;

        $existingFolders = $this->folderRepo->findByUserId($userId, $parentId);
        $exists = false;
        foreach ($existingFolders as $f) {
            if ($f->getName() === $validated['name']) {
                $exists = true;
                break;
            }
        }

        if ($exists) {
            return response()->json(['error' => 'A folder with this name already exists'], 422);
        }

        $folder = StorageFolder::create(
            id: (string) Str::uuid(),
            userId: $userId,
            parentId: $parentId,
            name: $validated['name']
        );

        $this->folderRepo->save($folder);

        return response()->json([
            'id' => $folder->getId(),
            'name' => $folder->getName(),
            'parent_id' => $folder->getParentId(),
        ], 201);
    }

    public function update(Request $request, string $folderId)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $folder = $this->folderRepo->findById($folderId);

        if (!$folder || !$folder->belongsToUser(auth()->id())) {
            abort(403);
        }

        $existingFolders = $this->folderRepo->findByUserId(auth()->id(), $folder->getParentId());
        foreach ($existingFolders as $f) {
            if ($f->getName() === $validated['name'] && $f->getId() !== $folderId) {
                return response()->json(['error' => 'A folder with this name already exists'], 422);
            }
        }

        $folder->rename($validated['name']);
        $this->folderRepo->save($folder);

        return response()->json([
            'id' => $folder->getId(),
            'name' => $folder->getName(),
            'parent_id' => $folder->getParentId(),
        ]);
    }

    public function move(Request $request, string $folderId)
    {
        $validated = $request->validate([
            'new_parent_id' => 'nullable|string',
        ]);

        $folder = $this->folderRepo->findById($folderId);

        if (!$folder || !$folder->belongsToUser(auth()->id())) {
            abort(403);
        }

        if ($this->isDescendant($validated['new_parent_id'] ?? null, $folderId)) {
            return response()->json(['error' => 'Cannot move folder into itself or its descendants'], 422);
        }

        $folder->moveTo($validated['new_parent_id'] ?? null);
        $this->folderRepo->save($folder);

        return response()->json([
            'id' => $folder->getId(),
            'name' => $folder->getName(),
            'parent_id' => $folder->getParentId(),
        ]);
    }

    public function destroy(string $folderId)
    {
        $folder = $this->folderRepo->findById($folderId);

        if (!$folder || !$folder->belongsToUser(auth()->id())) {
            abort(403);
        }

        if ($this->folderRepo->hasChildren($folderId) || $this->folderRepo->hasFiles($folderId)) {
            return response()->json(['error' => 'Folder must be empty before deletion'], 422);
        }

        $this->folderRepo->delete($folder);

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

        $parent = $this->folderRepo->findById($potentialParentId);
        if (!$parent || !$parent->getParentId()) {
            return false;
        }

        return $this->isDescendant($parent->getParentId(), $folderId);
    }
}