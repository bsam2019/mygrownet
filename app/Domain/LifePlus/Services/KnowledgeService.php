<?php

namespace App\Domain\LifePlus\Services;

use App\Infrastructure\Persistence\Eloquent\LifePlusKnowledgeItemModel;
use App\Infrastructure\Persistence\Eloquent\LifePlusUserDownloadModel;
use Carbon\Carbon;

class KnowledgeService
{
    public function getItems(array $filters = []): array
    {
        $query = LifePlusKnowledgeItemModel::query();

        if (!empty($filters['category'])) {
            $query->where('category', $filters['category']);
        }

        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (!empty($filters['featured'])) {
            $query->where('is_featured', true);
        }

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%");
            });
        }

        return $query->orderBy('is_featured', 'desc')
            ->orderBy('created_at', 'desc')
            ->limit($filters['limit'] ?? 50)
            ->get()
            ->map(fn($i) => $this->mapItem($i))
            ->toArray();
    }

    public function getItem(int $id): ?array
    {
        $item = LifePlusKnowledgeItemModel::find($id);
        return $item ? $this->mapItem($item) : null;
    }

    public function getDailyTip(): ?array
    {
        // Get a random daily tip or the most recent featured one
        $tip = LifePlusKnowledgeItemModel::where('is_daily_tip', true)
            ->inRandomOrder()
            ->first();

        if (!$tip) {
            $tip = LifePlusKnowledgeItemModel::where('is_featured', true)
                ->inRandomOrder()
                ->first();
        }

        return $tip ? $this->mapItem($tip) : null;
    }

    public function getCategories(): array
    {
        return [
            ['id' => 'finance', 'name' => 'Finance', 'icon' => '💰'],
            ['id' => 'business', 'name' => 'Business', 'icon' => '📈'],
            ['id' => 'health', 'name' => 'Health', 'icon' => '❤️'],
            ['id' => 'parenting', 'name' => 'Parenting', 'icon' => '👨‍👩‍👧'],
            ['id' => 'motivation', 'name' => 'Motivation', 'icon' => '🌟'],
            ['id' => 'skills', 'name' => 'Skills', 'icon' => '🛠️'],
            ['id' => 'farming', 'name' => 'Farming', 'icon' => '🌾'],
        ];
    }

    public function downloadItem(int $itemId, int $userId): bool
    {
        $item = LifePlusKnowledgeItemModel::find($itemId);
        if (!$item) return false;

        LifePlusUserDownloadModel::firstOrCreate([
            'user_id' => $userId,
            'knowledge_item_id' => $itemId,
        ], [
            'downloaded_at' => now(),
        ]);

        return true;
    }

    public function getUserDownloads(int $userId): array
    {
        return LifePlusUserDownloadModel::where('user_id', $userId)
            ->with('knowledgeItem')
            ->orderBy('downloaded_at', 'desc')
            ->get()
            ->filter(fn($d) => $d->knowledgeItem !== null)
            ->map(fn($d) => [
                'id' => $d->id,
                'knowledge_item' => $this->mapItem($d->knowledgeItem),
                'downloaded_at' => Carbon::parse($d->downloaded_at)->format('M d, Y'),
            ])
            ->values()
            ->toArray();
    }

    private function mapItem($item): array
    {
        return [
            'id' => $item->id,
            'title' => $item->title,
            'content' => $item->content,
            'excerpt' => $item->content ? substr(strip_tags($item->content), 0, 150) . '...' : null,
            'category' => $item->category,
            'category_icon' => collect($this->getCategories())
                ->firstWhere('id', $item->category)['icon'] ?? '📚',
            'type' => $item->type,
            'type_icon' => match($item->type) {
                'article' => '📄',
                'audio' => '🎧',
                'video' => '🎬',
                default => '📚',
            },
            'media_url' => $item->media_url,
            'duration_seconds' => $item->duration_seconds,
            'formatted_duration' => $item->duration_seconds 
                ? gmdate('i:s', $item->duration_seconds) 
                : null,
            'is_featured' => $item->is_featured,
            'is_daily_tip' => $item->is_daily_tip,
            'created_at' => $item->created_at->format('M d, Y'),
        ];
    }
}
