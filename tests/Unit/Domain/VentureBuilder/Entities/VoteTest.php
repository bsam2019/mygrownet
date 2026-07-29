<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\VentureBuilder\Entities;

use App\Domain\VentureBuilder\Entities\Vote;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class VoteTest extends TestCase
{
    #[Test]
    public function can_be_created_with_minimal_data(): void
    {
        $vote = new Vote(
            resolutionId: 1,
            shareholderId: 5,
            userId: 42,
            vote: 'for',
        );

        $this->assertSame(1, $vote->resolutionId);
        $this->assertSame(5, $vote->shareholderId);
        $this->assertSame(42, $vote->userId);
        $this->assertSame('for', $vote->vote);
    }

    #[Test]
    public function can_be_reconstituted_from_array(): void
    {
        $data = [
            'id' => 8,
            'resolution_id' => 1,
            'shareholder_id' => 5,
            'user_id' => 42,
            'vote' => 'against',
            'equity_at_vote' => 15.0,
            'comment' => 'Not in favor',
        ];

        $vote = Vote::reconstitute($data);

        $this->assertSame(8, $vote->id);
        $this->assertSame('against', $vote->vote);
        $this->assertSame(15.0, $vote->equityAtVote);
        $this->assertSame('Not in favor', $vote->comment);
    }

    #[Test]
    public function to_array_returns_all_fields(): void
    {
        $vote = new Vote(
            resolutionId: 1,
            shareholderId: 5,
            userId: 42,
            vote: 'abstain',
            id: 8,
            equityAtVote: 10.0,
            votedAt: new DateTimeImmutable('2026-06-01 10:00:00'),
        );

        $arr = $vote->toArray();

        $this->assertSame(8, $arr['id']);
        $this->assertSame(1, $arr['resolution_id']);
        $this->assertSame('abstain', $arr['vote']);
        $this->assertSame(10.0, $arr['equity_at_vote']);
        $this->assertSame('2026-06-01 10:00:00', $arr['voted_at']);
    }
}
