<?php

namespace App\Http\Controllers\Storage;

use App\Http\Controllers\Controller;
use App\Infrastructure\Storage\Persistence\Eloquent\StorageFolder;
use App\Infrastructure\Storage\Persistence\Eloquent\StorageFile;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class StorageFolderController extends Controller
{
    public function index(Request $request)
    {
        $parentId = $request->query('parent_id');
        $userId = auth()->id();

        $folders = StorageFolder::forUser($userId)
            ->inFolder($parentId)
            ->orderBy('name')
            ->get();

        $files = StorageFile::forUser($userId)
            ->inFolder($parentId)
            ->notDeleted()
            ->orderBy('original_name')
            ->get();

        return response()->json([
            'folders' => $folders,
            'files' => $files->map(function ($file) {
                return [
                    'id' => $file->id,
                    'original_name' => $file->original_name,
                    'extension' => $file->extension,
                    'mime_type' => $file->mime_type,
                    'size_bytes' => $file->size_bytes,
                    'formatted_size' => $file->formatted_size,
                    'folder_id' => $file->folder_id,
                    'created_at' => $file->created_at,
                    'updated_at' => $file->updated_at,
                ];
            }),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'parent_id' => 'nullable|uuid|exists:storage_folders,id',
            'name' => 'required|string|max:255',
        ]);

        // Check if folder with same name exists in parent
        $exists = StorageFolder::forUser(auth()->id())
            ->where('parent_id', $validated['parent_id'] ?? null)
            ->where('name', $validated['name'])
            ->exists();

        if ($exists) {
            return response()->json([
                'error' => 'A folder with this name already exists'
            ], 422);
        }

        $folder = StorageFolder::create([
            'id' => Str::uuid(),
            'user_id' => auth()->id(),
            'parent_id' => $validated['parent_id'] ?? null,
            'name' => $validated['name'],
        ]);

        return response()->json($folder, 201);
    }

    public function update(Request $request, string $folderId)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $folder = StorageFolder::findOrFail($folderId);

        if ($folder->user_id !== auth()->id()) {
            abort(403);
        }

        // Check if folder with same name exists in parent
        $exists = StorageFolder::forUser(auth()->id())
            ->where('parent_id', $folder->parent_id)
            ->where('name', $validated['name'])
            ->where('id', '!=', $folderId)
            ->exists();

        if ($exists) {
            return response()->json([
                'error' => 'A folder with this name already exists'
            ], 422);
        }

        $folder->update($validated);

        return response()->json($folder);
    }

    public function move(Request $request, string $folderId)
    {
        $validated = $request->validate([
            'new_parent_id' => 'nullable|uuid|exists:storage_folders,id',
        ]);

        $folder = StorageFolder::findOrFail($folderId);

        if ($folder->user_id !== auth()->id()) {
            abort(403);
        }

        // Prevent moving folder into itself or its descendants
        if ($this->isDescendant($validated['new_parent_id'], $folderId)) {
            return response()->json([
                'error' => 'Cannot move folder into itself or its descendants'
            ], 422);
        }

        $folder->update(['parent_id' => $validated['new_parent_id']]);

        return response()->json($folder);
    }

    public function destroy(string $folderId)
    {
        $folder = StorageFolder::findOrFail($folderId);

        if ($folder->user_id !== auth()->id()) {
            abort(403);
        }

        // Check if folder is empty
        if (!$folder->isEmpty()) {
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
