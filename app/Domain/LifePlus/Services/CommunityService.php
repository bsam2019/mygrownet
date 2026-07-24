<?php

namespace App\Domain\LifePlus\Services;

use App\Domain\LifePlus\Entities\LifePlusCommunityPost;
use App\Domain\LifePlus\Repositories\CommunityPostRepositoryInterface;

class CommunityService
{
    public function __construct(
        private readonly CommunityPostRepositoryInterface $postRepo,
    ) {}

    public function getPosts(array $filters = []): array
    {
        return array_map(fn($p) => $this->mapPost($p), $this->postRepo->findActive($filters));
    }

    public function getPost(int $id): ?array
    {
        $post = $this->postRepo->findById($id);
        return $post ? $this->mapPost($post) : null;
    }

    public function createPost(int $userId, array $data): array
    {
        $post = LifePlusCommunityPost::reconstitute([
            'user_id' => $userId,
            'type' => $data['type'] ?? 'notice',
            'title' => $data['title'],
            'content' => $data['content'] ?? null,
            'location' => $data['location'] ?? null,
            'event_date' => $data['event_date'] ?? null,
            'image_url' => $data['image_url'] ?? null,
            'is_promoted' => false,
            'expires_at' => $data['expires_at'] ?? now()->addDays(30)->toDateTimeString(),
        ]);

        return $this->mapPost($this->postRepo->save($post));
    }

    public function updatePost(int $id, int $userId, array $data): ?array
    {
        $post = $this->postRepo->findById($id);
        if (!$post || $post->userId !== $userId) return null;

        $merged = array_merge($post->toArray(), $data);
        return $this->mapPost($this->postRepo->save(LifePlusCommunityPost::reconstitute($merged)));
    }

    public function deletePost(int $id, int $userId): bool
    {
        $post = $this->postRepo->findById($id);
        if (!$post || $post->userId !== $userId) return false;
        return $this->postRepo->delete($id);
    }

    private function mapPost(LifePlusCommunityPost $post): array
    {
        return [
            'id' => $post->id,
            'type' => $post->type,
            'title' => $post->title,
            'content' => $post->content,
            'location' => $post->location,
            'event_date' => $post->eventDate?->format('Y-m-d H:i:s'),
            'image_url' => $post->imageUrl,
            'is_promoted' => $post->isPromoted,
        ];
    }
}
