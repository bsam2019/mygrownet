<?php

namespace App\Http\Controllers\LifePlus;

use App\Http\Controllers\Controller;
use App\Domain\LifePlus\Services\NoteService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class NoteController extends Controller
{
    public function __construct(protected NoteService $noteService) {}

    public function index()
    {
        return Inertia::render('LifePlus/Notes/Index', [
            'notes' => $this->noteService->getNotes(auth()->id()),
        ]);
    }

    public function show(int $id)
    {
        $note = $this->noteService->getNote($id, auth()->id());

        if (!$note) {
            return redirect()->route('lifeplus.notes.index')
                ->with('error', 'Note not found');
        }

        return Inertia::render('LifePlus/Notes/Show', [
            'note' => $note,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'nullable|string|max:10000',
            'is_pinned' => 'nullable|boolean',
            'local_id' => 'nullable|string',
        ]);

        $note = $this->noteService->createNote(auth()->id(), $validated);

        if ($request->wantsJson()) {
            return response()->json($note, 201);
        }

        return back()->with('success', 'Note created');
    }

    public function update(Request $request, int $id)
    {
        $validated = $request->validate([
            'title' => 'nullable|string|max:255',
            'content' => 'nullable|string|max:10000',
            'is_pinned' => 'nullable|boolean',
        ]);

        $note = $this->noteService->updateNote($id, auth()->id(), $validated);

        if (!$note) {
            return response()->json(['error' => 'Note not found'], 404);
        }

        if ($request->wantsJson()) {
            return response()->json($note);
        }

        return back()->with('success', 'Note updated');
    }

    public function togglePin(int $id)
    {
        $note = $this->noteService->togglePin($id, auth()->id());

        if (!$note) {
            return response()->json(['error' => 'Note not found'], 404);
        }

        return response()->json($note);
    }

    public function destroy(int $id)
    {
        $deleted = $this->noteService->deleteNote($id, auth()->id());

        if (!$deleted) {
            return response()->json(['error' => 'Note not found'], 404);
        }

        return back()->with('success', 'Note deleted');
    }

    public function sync(Request $request)
    {
        $validated = $request->validate([
            'notes' => 'required|array',
            'notes.*.title' => 'required|string|max:255',
            'notes.*.local_id' => 'required|string',
        ]);

        $synced = $this->noteService->syncNotes(auth()->id(), $validated['notes']);

        return response()->json(['synced' => $synced]);
    }
}
