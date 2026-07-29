<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\VentureBuilder\Entities;

use App\Domain\VentureBuilder\Entities\Resolution;
use App\Domain\VentureBuilder\ValueObjects\ResolutionStatus;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class ResolutionTest extends TestCase
{
    #[Test]
    public function can_be_created_with_minimal_data(): void
    {
        $resolution = new Resolution(
            ventureId: 1,
            title: 'Approve Budget',
            status: ResolutionStatus::draft(),
            description: 'Approve the annual budget',
        );

        $this->assertSame(1, $resolution->ventureId);
        $this->assertSame('Approve Budget', $resolution->title);
        $this->assertSame('Approve the annual budget', $resolution->description);
        $this->assertTrue($resolution->status->isDraft());
    }

    #[Test]
    public function is_open_for_voting_returns_false_when_not_voting(): void
    {
        $resolution = new Resolution(
            ventureId: 1, title: 'T', status: ResolutionStatus::draft(), description: 'D',
        );

        $this->assertFalse($resolution->isOpenForVoting());
    }

    #[Test]
    public function is_open_for_voting_returns_true_when_voting_within_window(): void
    {
        $resolution = new Resolution(
            ventureId: 1,
            title: 'T',
            status: ResolutionStatus::voting(),
            description: 'D',
            votingStartsAt: new DateTimeImmutable('-1 day'),
            votingEndsAt: new DateTimeImmutable('+1 day'),
        );

        $this->assertTrue($resolution->isOpenForVoting());
    }

    #[Test]
    public function is_open_for_voting_returns_false_when_before_start(): void
    {
        $resolution = new Resolution(
            ventureId: 1,
            title: 'T',
            status: ResolutionStatus::voting(),
            description: 'D',
            votingStartsAt: new DateTimeImmutable('+1 day'),
        );

        $this->assertFalse($resolution->isOpenForVoting());
    }

    #[Test]
    public function is_open_for_voting_returns_false_when_after_end(): void
    {
        $resolution = new Resolution(
            ventureId: 1,
            title: 'T',
            status: ResolutionStatus::voting(),
            description: 'D',
            votingStartsAt: new DateTimeImmutable('-2 days'),
            votingEndsAt: new DateTimeImmutable('-1 day'),
        );

        $this->assertFalse($resolution->isOpenForVoting());
    }

    #[Test]
    public function is_open_for_voting_returns_true_with_no_dates(): void
    {
        $resolution = new Resolution(
            ventureId: 1, title: 'T', status: ResolutionStatus::voting(), description: 'D',
        );

        $this->assertTrue($resolution->isOpenForVoting());
    }

    #[Test]
    public function can_be_reconstituted_from_array(): void
    {
        $data = [
            'id' => 2,
            'venture_id' => 1,
            'title' => 'Elect Board',
            'description' => 'Election of board members',
            'status' => 'voting',
            'type' => 'special',
            'pass_threshold_percentage' => 66.6,
            'votes_for' => 80.0,
            'votes_against' => 20.0,
            'created_by' => 42,
        ];

        $resolution = Resolution::reconstitute($data);

        $this->assertSame(2, $resolution->id);
        $this->assertSame('Elect Board', $resolution->title);
        $this->assertTrue($resolution->status->isVoting());
        $this->assertSame('special', $resolution->type);
        $this->assertSame(66.6, $resolution->passThresholdPercentage);
        $this->assertSame(80.0, $resolution->votesFor);
        $this->assertSame(42, $resolution->createdBy);
    }

    #[Test]
    public function reconstitute_defaults_status_to_draft(): void
    {
        $resolution = Resolution::reconstitute([
            'venture_id' => 1,
            'title' => 'T',
            'description' => 'D',
        ]);

        $this->assertTrue($resolution->status->isDraft());
    }

    #[Test]
    public function to_array_returns_all_fields(): void
    {
        $resolution = new Resolution(
            ventureId: 1,
            title: 'Vote',
            status: ResolutionStatus::passed(),
            description: 'A resolution',
            id: 2,
            type: 'ordinary',
            votesFor: 90.0,
            votesAgainst: 10.0,
            createdAt: new DateTimeImmutable('2026-03-01 09:00:00'),
        );

        $arr = $resolution->toArray();

        $this->assertSame(2, $arr['id']);
        $this->assertSame('passed', $arr['status']);
        $this->assertSame('ordinary', $arr['type']);
        $this->assertSame(90.0, $arr['votes_for']);
        $this->assertSame('2026-03-01 09:00:00', $arr['created_at']);
    }
}
