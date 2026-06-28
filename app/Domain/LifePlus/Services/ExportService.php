<?php

namespace App\Domain\LifePlus\Services;

use App\Infrastructure\Persistence\Eloquent\LifePlusExpenseModel;
use App\Infrastructure\Persistence\Eloquent\LifePlusTaskModel;
use App\Infrastructure\Persistence\Eloquent\LifePlusHabitModel;
use App\Infrastructure\Persistence\Eloquent\LifePlusNoteModel;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class ExportService
{
    /**
     * Export user data to JSON
     */
    public function exportToJson(int $userId): array
    {
        $data = $this->gatherUserData($userId);

        $filename = "lifeplus_export_{$userId}_" . Carbon::now()->format('Y-m-d_His') . '.json';
        $path = "exports/{$filename}";

        Storage::put($path, json_encode($data, JSON_PRETTY_PRINT));

        return [
            'filename' => $filename,
            'path' => $path,
            'url' => Storage::url($path),
            'size' => Storage::size($path),
            'created_at' => Carbon::now()->toISOString(),
        ];
    }

    /**
     * Export expenses to CSV
     */
    public function exportExpensesToCsv(int $userId, ?string $startDate = null, ?string $endDate = null): array
    {
        $query = LifePlusExpenseModel::where('user_id', $userId)
            ->with('category')
            ->orderBy('expense_date', 'desc');

        if ($startDate) {
            $query->where('expense_date', '>=', $startDate);
        }
        if ($endDate) {
            $query->where('expense_date', '<=', $endDate);
        }

        $expenses = $query->get();

        $csv = "Date,Category,Amount,Description\n";
        foreach ($expenses as $expense) {
            $csv .= sprintf(
                "%s,%s,%s,\"%s\"\n",
                $expense->expense_date->format('Y-m-d'),
                $expense->category?->name ?? 'Uncategorized',
                $expense->amount,
                str_replace('"', '""', $expense->description ?? '')
            );
        }

        $filename = "expenses_{$userId}_" . Carbon::now()->format('Y-m-d') . '.csv';
        $path = "exports/{$filename}";

        Storage::put($path, $csv);

        return [
            'filename' => $filename,
            'path' => $path,
            'url' => Storage::url($path),
            'count' => $expenses->count(),
            'total' => $expenses->sum('amount'),
        ];
    }

    /**
     * Export tasks to CSV
     */
    public function exportTasksToCsv(int $userId, bool $includeCompleted = true): array
    {
        $query = LifePlusTaskModel::where('user_id', $userId)
            ->orderBy('due_date', 'asc');

        if (!$includeCompleted) {
            $query->where('is_completed', false);
        }

        $tasks = $query->get();

        $csv = "Title,Priority,Due Date,Status,Completed At\n";
        foreach ($tasks as $task) {
            $csv .= sprintf(
                "\"%s\",%s,%s,%s,%s\n",
                str_replace('"', '""', $task->title),
                $task->priority ?? 'medium',
                $task->due_date?->format('Y-m-d H:i') ?? '',
                $task->is_completed ? 'Completed' : 'Pending',
                $task->completed_at?->format('Y-m-d H:i') ?? ''
            );
        }

        $filename = "tasks_{$userId}_" . Carbon::now()->format('Y-m-d') . '.csv';
        $path = "exports/{$filename}";

        Storage::put($path, $csv);

        return [
            'filename' => $filename,
            'path' => $path,
            'url' => Storage::url($path),
            'count' => $tasks->count(),
        ];
    }

    /**
     * Export notes to text file
     */
    public function exportNotesToText(int $userId): array
    {
        $notes = LifePlusNoteModel::where('user_id', $userId)
            ->orderBy('updated_at', 'desc')
            ->get();

        $content = "# My Notes Export\n";
        $content .= "# Exported: " . Carbon::now()->format('Y-m-d H:i:s') . "\n\n";

        foreach ($notes as $note) {
            $content .= "---\n";
            $content .= "## {$note->title}\n";
            $content .= "Created: {$note->created_at->format('Y-m-d')}\n\n";
            $content .= $note->content . "\n\n";
        }

        $filename = "notes_{$userId}_" . Carbon::now()->format('Y-m-d') . '.txt';
        $path = "exports/{$filename}";

        Storage::put($path, $content);

        return [
            'filename' => $filename,
            'path' => $path,
            'url' => Storage::url($path),
            'count' => $notes->count(),
        ];
    }

    /**
     * Gather all user data for full export
     */
    private function gatherUserData(int $userId): array
    {
        return [
            'exported_at' => Carbon::now()->toISOString(),
            'user_id' => $userId,
            'expenses' => LifePlusExpenseModel::where('user_id', $userId)
                ->with('category')
                ->get()
                ->map(fn($e) => [
                    'date' => $e->expense_date->format('Y-m-d'),
                    'amount' => $e->amount,
                    'category' => $e->category?->name,
                    'description' => $e->description,
                ])
                ->toArray(),
            'tasks' => LifePlusTaskModel::where('user_id', $userId)
                ->get()
                ->map(fn($t) => [
                    'title' => $t->title,
                    'description' => $t->description,
                    'priority' => $t->priority,
                    'due_date' => $t->due_date?->format('Y-m-d H:i'),
                    'is_completed' => $t->is_completed,
                    'completed_at' => $t->completed_at?->format('Y-m-d H:i'),
                ])
                ->toArray(),
            'habits' => LifePlusHabitModel::where('user_id', $userId)
                ->with('logs')
                ->get()
                ->map(fn($h) => [
                    'name' => $h->name,
                    'frequency' => $h->frequency,
                    'is_active' => $h->is_active,
                    'logs' => $h->logs->pluck('completed_date')->map(fn($d) => Carbon::parse($d)->format('Y-m-d'))->toArray(),
                ])
                ->toArray(),
            'notes' => LifePlusNoteModel::where('user_id', $userId)
                ->get()
                ->map(fn($n) => [
                    'title' => $n->title,
                    'content' => $n->content,
                    'is_pinned' => $n->is_pinned,
                    'created_at' => $n->created_at->format('Y-m-d'),
                ])
                ->toArray(),
        ];
    }
}
