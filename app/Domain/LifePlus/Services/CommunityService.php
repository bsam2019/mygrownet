<?php

namespace App\Domain\LifePlus\Services;

use App\Infrastructure\Persistence\Eloquent\LifePlusCommunityPostModel;
use Carbon\Carbon;

class CommunityService
{
    public function getPosts(array $filters = []): array
    {
        $query = LifePlusCommunityPostModel::with('author');

        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (!empty($filters['location'])) {
            $query->where('location', 'like', '%' . $filters['location'] . '%');
        }

        // Filter out expired posts
        $query->where(function ($q) {
            $q->whereNull('expires_at')
              ->orWhere('expires_at', '>', now());
        });

        return $query->orderBy('is_promoted', 'desc')
            ->orderBy('created_at', 'desc')
            ->limit($filters['limit'] ?? 50)
            ->get()
            ->map(fn($p) => $this->mapPost($p))
            ->toArray();
    }

    public function getNotices(): array
    {
        return $this->getPosts(['type' => 'notice']);
    }

    public function getEvents(): array
    {
        return LifePlusCommunityPostModel::where('type', 'event')
            ->where(function ($q) {
                $q->whereNull('event_date')
                  ->orWhere('event_date', '>=', now());
            })
            ->with('author')
            ->orderBy('event_date')
            ->get()
            ->map(fn($p) => $this->mapPost($p))
            ->toArray();
    }

    public function getLostFound(): array
    {
        return $this->getPosts(['type' => 'lost_found']);
    }

    public function getPost(int $id): ?array
    {
        $post = LifePlusCommunityPostModel::with('author')->find($id);
        return $post ? $this->mapPost($post) : null;
    }

    public function createPost(int $userId, array $data): array
    {
        $post = LifePlusCommunityPostModel::create([
            'user_id' => $userId,
            'type' => $data['type'] ?? 'notice',
            'title' => $data['title'],
            'content' => $data['content'] ?? null,
            'location' => $data['location'] ?? null,
            'event_date' => $data['event_date'] ?? null,
            'image_url' => $data['image_url'] ?? null,
            'is_promoted' => false,
            'expires_at' => $data['expires_at'] ?? now()->addDays(30),
        ]);

        return $this->mapPost($post->load('author'));
    }

    public function updatePost(int $id, int $userId, array $data): ?array
    {
        $post = LifePlusCommunityPostModel::where('id', $id)
            ->where('user_id', $userId)
            ->first();

        if (!$post) return null;

        $post->update($data);
        return $this->mapPost($post->fresh('author'));
    }

    public function deletePost(int $id, int $userId): bool
    {
        return LifePlusCommunityPostModel::where('id', $id)
            ->where('user_id', $userId)
            ->delete() > 0;
    }

    private function mapPost($post): array
    {
        $currentUserId = auth()->id();
        $content = $post->content;
        
        return [
            'id' => $post->id,
            'type' => $post->type,
            'type_label' => match($post->type) {
                'notice' => 'Notice',
                'event' => 'Event',
                'lost_found' => 'Lost & Found',
                default => 'Post',
            },
            'type_icon' => match($post->type) {
                'notice' => '📢',
                'event' => '📅',
                'lost_found' => '🔍',
                default => '📝',
            },
            'title' => $post->title,
            'content' => $content,
            'excerpt' => $content ? \Str::limit($content, 100) : null,
            'location' => $post->location,
            'event_date' => $post->event_date?->format('Y-m-d H:i'),
            'formatted_event_date' => $post->event_date?->format('M d, Y g:i A'),
            'image_url' => $post->image_url,
            'is_promoted' => $post->is_promoted,
            'poster' => $post->author ? [
                'id' => $post->author->id,
                'name' => $post->author->name,
            ] : null,
            'posted_at' => $post->created_at->diffForHumans(),
            'created_at' => $post->created_at->toISOString(),
            'is_owner' => $currentUserId && $post->user_id === $currentUserId,
        ];
    }
}
