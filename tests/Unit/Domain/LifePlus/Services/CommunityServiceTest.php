<?php

namespace Tests\Unit\Domain\LifePlus\Services;

use App\Domain\LifePlus\Entities\LifePlusCommunityPost;
use App\Domain\LifePlus\Repositories\CommunityPostRepositoryInterface;
use App\Domain\LifePlus\Services\CommunityService;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class CommunityServiceTest extends TestCase
{
    private CommunityPostRepositoryInterface $postRepo;
    private CommunityService $service;

    protected function setUp(): void
    {
        $this->postRepo = $this->createMock(CommunityPostRepositoryInterface::class);
        $this->service = new CommunityService($this->postRepo);
    }

    #[Test]
    public function getPosts_returns_mapped_posts()
    {
        $post = LifePlusCommunityPost::reconstitute(['id' => 1, 'user_id' => 42, 'type' => 'notice', 'title' => 'Announcement']);
        $this->postRepo->expects($this->once())->method('findActive')->with([])->willReturn([$post]);

        $result = $this->service->getPosts();

        $this->assertCount(1, $result);
        $this->assertSame(1, $result[0]['id']);
        $this->assertSame('notice', $result[0]['type']);
    }

    #[Test]
    public function getPost_returns_null_on_not_found()
    {
        $this->postRepo->expects($this->once())->method('findById')->with(1)->willReturn(null);

        $this->assertNull($this->service->getPost(1));
    }

    #[Test]
    public function getPost_returns_mapped_post()
    {
        $post = LifePlusCommunityPost::reconstitute(['id' => 1, 'user_id' => 42, 'type' => 'event', 'title' => 'Meetup', 'content' => 'Hello']);
        $this->postRepo->expects($this->once())->method('findById')->with(1)->willReturn($post);

        $result = $this->service->getPost(1);

        $this->assertSame('Meetup', $result['title']);
        $this->assertSame('Hello', $result['content']);
    }

    #[Test]
    public function createPost_saves_and_returns_mapped()
    {
        $saved = LifePlusCommunityPost::reconstitute(['id' => 10, 'user_id' => 42, 'type' => 'notice', 'title' => 'New post']);
        $this->postRepo->expects($this->once())->method('save')->willReturn($saved);

        $result = $this->service->createPost(42, ['title' => 'New post']);

        $this->assertSame(10, $result['id']);
        $this->assertSame('notice', $result['type']);
    }

    #[Test]
    public function createPost_applies_default_type_and_expiry()
    {
        $this->postRepo->expects($this->once())->method('save')->willReturnCallback(function (LifePlusCommunityPost $post) {
            $this->assertSame('notice', $post->type);
            $this->assertFalse($post->isPromoted);
            return $post;
        });

        $this->service->createPost(42, ['title' => 'Post']);
    }

    #[Test]
    public function updatePost_returns_null_on_not_found()
    {
        $this->postRepo->expects($this->once())->method('findById')->with(1)->willReturn(null);

        $this->assertNull($this->service->updatePost(1, 42, ['title' => 'Updated']));
    }

    #[Test]
    public function updatePost_returns_null_on_user_mismatch()
    {
        $post = LifePlusCommunityPost::reconstitute(['id' => 1, 'user_id' => 99, 'type' => 'notice', 'title' => 'Other']);
        $this->postRepo->expects($this->once())->method('findById')->with(1)->willReturn($post);

        $this->assertNull($this->service->updatePost(1, 42, ['title' => 'Updated']));
    }

    #[Test]
    public function updatePost_merges_and_saves()
    {
        $post = LifePlusCommunityPost::reconstitute(['id' => 1, 'user_id' => 42, 'type' => 'notice', 'title' => 'Old']);
        $updated = LifePlusCommunityPost::reconstitute(['id' => 1, 'user_id' => 42, 'type' => 'notice', 'title' => 'Updated']);

        $this->postRepo->expects($this->once())->method('findById')->with(1)->willReturn($post);
        $this->postRepo->expects($this->once())->method('save')->willReturn($updated);

        $result = $this->service->updatePost(1, 42, ['title' => 'Updated']);

        $this->assertSame('Updated', $result['title']);
    }

    #[Test]
    public function deletePost_returns_true_on_success()
    {
        $post = LifePlusCommunityPost::reconstitute(['id' => 1, 'user_id' => 42, 'type' => 'notice', 'title' => 'Delete']);
        $this->postRepo->expects($this->once())->method('findById')->with(1)->willReturn($post);
        $this->postRepo->expects($this->once())->method('delete')->with(1)->willReturn(true);

        $this->assertTrue($this->service->deletePost(1, 42));
    }

    #[Test]
    public function deletePost_returns_false_on_user_mismatch()
    {
        $post = LifePlusCommunityPost::reconstitute(['id' => 1, 'user_id' => 99, 'type' => 'notice', 'title' => 'Other']);
        $this->postRepo->expects($this->once())->method('findById')->with(1)->willReturn($post);

        $this->assertFalse($this->service->deletePost(1, 42));
    }

    #[Test]
    public function deletePost_returns_false_on_not_found()
    {
        $this->postRepo->expects($this->once())->method('findById')->with(1)->willReturn(null);

        $this->assertFalse($this->service->deletePost(1, 42));
    }
}
