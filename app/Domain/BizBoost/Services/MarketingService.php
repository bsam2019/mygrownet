<?php

declare(strict_types=1);

namespace App\Domain\BizBoost\Services;

use App\Domain\BizBoost\Entities\Post;
use App\Domain\BizBoost\Entities\Campaign;
use App\Domain\BizBoost\Repositories\PostRepositoryInterface;
use App\Domain\BizBoost\Repositories\CampaignRepositoryInterface;
use App\Domain\BizBoost\Repositories\TemplateRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class MarketingService
{
    public function __construct(
        private PostRepositoryInterface $postRepo,
        private CampaignRepositoryInterface $campaignRepo,
        private TemplateRepositoryInterface $templateRepo,
    ) {}

    public function getPosts(int $businessId, array $filters = []): array
    {
        return $this->postRepo->findByBusiness($businessId, $filters);
    }

    public function findPost(int $id): ?Post
    {
        return $this->postRepo->findById($id);
    }

    public function createPost(array $data): Post
    {
        return $this->postRepo->save(Post::create($data));
    }

    public function updatePost(int $id, array $data): ?Post
    {
        $existing = $this->postRepo->findById($id);
        if (!$existing) {
            return null;
        }
        $merged = array_merge($existing->toArray(), $data);
        $merged['id'] = $id;
        return $this->postRepo->save(Post::reconstitute($merged));
    }

    public function deletePost(int $id): void
    {
        $this->postRepo->delete($id);
    }

    public function storePostMedia(int $postId, array $mediaFiles): void
    {
        foreach ($mediaFiles as $index => $file) {
            $isVideo = in_array($file->getMimeType(), ['video/mp4', 'video/quicktime']);
            $path = $file->store('bizboost/posts', 'public');

            \App\Infrastructure\Persistence\Eloquent\BizBoostPostMediaModel::create([
                'post_id' => $postId,
                'type' => $isVideo ? 'video' : 'image',
                'path' => $path,
                'filename' => $file->getClientOriginalName(),
                'file_size' => $file->getSize(),
                'mime_type' => $file->getMimeType(),
                'sort_order' => $index,
            ]);
        }
    }

    public function deletePostMedia(int $postId): void
    {
        $mediaItems = \App\Infrastructure\Persistence\Eloquent\BizBoostPostMediaModel::where('post_id', $postId)->get();
        foreach ($mediaItems as $media) {
            Storage::disk('public')->delete($media->path);
            if ($media->thumbnail_path) {
                Storage::disk('public')->delete($media->thumbnail_path);
            }
            $media->delete();
        }
    }

    public function duplicatePost(int $postId): ?Post
    {
        $post = $this->postRepo->findById($postId);
        if (!$post) {
            return null;
        }

        $postData = $post->toArray();
        unset($postData['id'], $postData['created_at'], $postData['updated_at']);
        $postData['status'] = 'draft';
        $postData['scheduled_at'] = null;
        $postData['published_at'] = null;
        $postData['external_ids'] = null;
        $postData['analytics'] = null;
        $postData['error_message'] = null;
        $postData['retry_count'] = 0;

        $newPost = $this->postRepo->save(Post::reconstitute($postData));

        $oldMedia = \App\Infrastructure\Persistence\Eloquent\BizBoostPostMediaModel::where('post_id', $postId)->get();
        foreach ($oldMedia as $media) {
            $newMedia = $media->replicate();
            $newMedia->post_id = $newPost->id;
            $newMedia->save();
        }

        return $newPost;
    }

    public function getPostStats(int $businessId): array
    {
        return [
            'draft' => $this->postRepo->countByBusiness($businessId, ['status' => 'draft']),
            'scheduled' => $this->postRepo->countByBusiness($businessId, ['status' => 'scheduled']),
            'published' => $this->postRepo->countByBusiness($businessId, ['status' => 'published']),
            'failed' => $this->postRepo->countByBusiness($businessId, ['status' => 'failed']),
        ];
    }

    public function getCampaigns(int $businessId): array
    {
        return $this->campaignRepo->findByBusiness($businessId);
    }

    public function findCampaign(int $id): ?Campaign
    {
        return $this->campaignRepo->findById($id);
    }

    public function createCampaign(array $data): Campaign
    {
        return $this->campaignRepo->save(new Campaign(
            id: null,
            businessId: (int) $data['business_id'],
            name: $data['name'],
            description: $data['description'] ?? null,
            objective: $data['objective'],
            status: $data['status'] ?? 'draft',
            startDate: $data['start_date'],
            endDate: $data['end_date'],
            durationDays: (int) ($data['duration_days'] ?? 0),
            campaignConfig: $data['campaign_config'] ?? null,
            targetPlatforms: $data['target_platforms'] ?? null,
            analytics: null,
            postsCreated: null,
            postsPublished: null,
            createdAt: null,
            updatedAt: null,
        ));
    }

    public function updateCampaign(int $id, array $data): ?Campaign
    {
        $existing = $this->campaignRepo->findById($id);
        if (!$existing) {
            return null;
        }
        $merged = array_merge($existing->toArray(), $data);
        $merged['id'] = $id;
        return $this->campaignRepo->save(Campaign::reconstitute($merged));
    }

    public function deleteCampaign(int $id): void
    {
        $this->campaignRepo->delete($id);
    }

    public function getCampaignStats(int $businessId): array
    {
        return [
            'total' => $this->campaignRepo->countByBusiness($businessId),
            'active' => $this->campaignRepo->countByBusiness($businessId, 'active'),
            'completed' => $this->campaignRepo->countByBusiness($businessId, 'completed'),
            'draft' => $this->campaignRepo->countByBusiness($businessId, 'draft'),
        ];
    }

    public function getTemplateUsage(int $templateId): void
    {
        $this->templateRepo->incrementUsage($templateId);
    }
}