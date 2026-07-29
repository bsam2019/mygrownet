<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\VentureBuilder\Entities;

use App\Domain\VentureBuilder\Entities\Update;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class UpdateTest extends TestCase
{
    #[Test]
    public function can_be_created_with_minimal_data(): void
    {
        $update = new Update(
            ventureId: 1,
            title: 'Progress Update',
            visibility: 'public',
            postedBy: 42,
            content: 'We have made great progress.',
        );

        $this->assertSame(1, $update->ventureId);
        $this->assertSame('Progress Update', $update->title);
        $this->assertSame('public', $update->visibility);
        $this->assertSame(42, $update->postedBy);
        $this->assertSame('We have made great progress.', $update->content);
    }

    #[Test]
    public function is_published_returns_true_when_published_at_in_past(): void
    {
        $update = new Update(
            ventureId: 1, title: 'T', visibility: 'public', postedBy: 1, content: 'C',
            publishedAt: new DateTimeImmutable('-1 hour'),
        );

        $this->assertTrue($update->isPublished());
    }

    #[Test]
    public function is_published_returns_false_when_no_published_at(): void
    {
        $update = new Update(ventureId: 1, title: 'T', visibility: 'public', postedBy: 1, content: 'C');
        $this->assertFalse($update->isPublished());
    }

    #[Test]
    public function is_draft_returns_true_when_no_published_at(): void
    {
        $update = new Update(ventureId: 1, title: 'T', visibility: 'public', postedBy: 1, content: 'C');
        $this->assertTrue($update->isDraft());
    }

    #[Test]
    public function is_draft_returns_false_when_published(): void
    {
        $update = new Update(
            ventureId: 1, title: 'T', visibility: 'public', postedBy: 1, content: 'C',
            publishedAt: new DateTimeImmutable('-1 hour'),
        );

        $this->assertFalse($update->isDraft());
    }

    #[Test]
    public function can_be_viewed_by_admin_for_drafts(): void
    {
        $update = new Update(ventureId: 1, title: 'T', visibility: 'public', postedBy: 1, content: 'C');
        $this->assertTrue($update->canBeViewedBy('admin'));
        $this->assertFalse($update->canBeViewedBy('investor'));
        $this->assertFalse($update->canBeViewedBy('guest'));
    }

    #[Test]
    public function can_be_viewed_by_anyone_when_public_and_published(): void
    {
        $update = new Update(
            ventureId: 1, title: 'T', visibility: 'public', postedBy: 1, content: 'C',
            publishedAt: new DateTimeImmutable('-1 hour'),
        );

        $this->assertTrue($update->canBeViewedBy('guest'));
        $this->assertTrue($update->canBeViewedBy('investor'));
        $this->assertTrue($update->canBeViewedBy('admin'));
    }

    #[Test]
    public function can_be_viewed_by_investors_only_when_published(): void
    {
        $update = new Update(
            ventureId: 1, title: 'T', visibility: 'investors_only', postedBy: 1, content: 'C',
            publishedAt: new DateTimeImmutable('-1 hour'),
        );

        $this->assertTrue($update->canBeViewedBy('investor'));
        $this->assertTrue($update->canBeViewedBy('shareholder'));
        $this->assertTrue($update->canBeViewedBy('admin'));
        $this->assertFalse($update->canBeViewedBy('guest'));
    }

    #[Test]
    public function can_be_viewed_by_shareholders_only_when_published(): void
    {
        $update = new Update(
            ventureId: 1, title: 'T', visibility: 'shareholders_only', postedBy: 1, content: 'C',
            publishedAt: new DateTimeImmutable('-1 hour'),
        );

        $this->assertTrue($update->canBeViewedBy('shareholder'));
        $this->assertTrue($update->canBeViewedBy('admin'));
        $this->assertFalse($update->canBeViewedBy('investor'));
        $this->assertFalse($update->canBeViewedBy('guest'));
    }

    #[Test]
    public function can_be_reconstituted_from_array(): void
    {
        $data = [
            'id' => 6,
            'venture_id' => 1,
            'title' => 'Milestone Reached',
            'content' => 'We hit our first milestone',
            'visibility' => 'public',
            'posted_by' => 42,
            'type' => 'milestone',
            'is_pinned' => true,
            'views_count' => 200,
            'published_at' => '2026-03-15 10:00:00',
        ];

        $update = Update::reconstitute($data);

        $this->assertSame(6, $update->id);
        $this->assertSame('Milestone Reached', $update->title);
        $this->assertSame('milestone', $update->type);
        $this->assertTrue($update->isPinned);
        $this->assertSame(200, $update->viewsCount);
        $this->assertTrue($update->isPublished());
    }

    #[Test]
    public function to_array_returns_all_fields(): void
    {
        $update = new Update(
            ventureId: 1,
            title: 'Update',
            visibility: 'public',
            postedBy: 42,
            content: 'Content here',
            id: 6,
            isPinned: true,
            createdAt: new DateTimeImmutable('2026-02-01 12:00:00'),
        );

        $arr = $update->toArray();

        $this->assertSame(6, $arr['id']);
        $this->assertSame('Update', $arr['title']);
        $this->assertTrue($arr['is_pinned']);
        $this->assertSame('2026-02-01 12:00:00', $arr['created_at']);
    }
}
