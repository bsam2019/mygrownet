<?php

declare(strict_types=1);

namespace Tests\Unit\Domain\VentureBuilder\Services;

use App\Domain\VentureBuilder\Entities\Resolution;
use App\Domain\VentureBuilder\Entities\Shareholder;
use App\Domain\VentureBuilder\Entities\Venture;
use App\Domain\VentureBuilder\Entities\Vote;
use App\Domain\VentureBuilder\Repositories\ResolutionRepositoryInterface;
use App\Domain\VentureBuilder\Repositories\ShareholderRepositoryInterface;
use App\Domain\VentureBuilder\Repositories\VentureRepositoryInterface;
use App\Domain\VentureBuilder\Repositories\VoteRepositoryInterface;
use App\Domain\VentureBuilder\Services\VentureVoteService;
use App\Domain\VentureBuilder\ValueObjects\ResolutionStatus;
use App\Domain\VentureBuilder\ValueObjects\ShareholderStatus;
use App\Domain\VentureBuilder\ValueObjects\VentureStatus;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class VentureVoteServiceTest extends TestCase
{
    private ResolutionRepositoryInterface $resolutionRepo;
    private VoteRepositoryInterface $voteRepo;
    private ShareholderRepositoryInterface $shareholderRepo;
    private VentureRepositoryInterface $ventureRepo;
    private VentureVoteService $service;

    protected function setUp(): void
    {
        $this->resolutionRepo = $this->createStub(ResolutionRepositoryInterface::class);
        $this->voteRepo = $this->createStub(VoteRepositoryInterface::class);
        $this->shareholderRepo = $this->createStub(ShareholderRepositoryInterface::class);
        $this->ventureRepo = $this->createStub(VentureRepositoryInterface::class);
        $this->service = new VentureVoteService(
            $this->resolutionRepo,
            $this->voteRepo,
            $this->shareholderRepo,
            $this->ventureRepo,
        );
    }

    #[Test]
    public function create_resolution_throws_when_venture_not_funded_or_active(): void
    {
        $venture = new Venture(title: 'T', slug: 't', status: VentureStatus::funding(), id: 1);
        $this->ventureRepo->method('findById')->with(1)->willReturn($venture);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('funded or active');

        $this->service->createResolution(1, 'Title', 'Description');
    }

    #[Test]
    public function create_resolution_throws_when_venture_not_found(): void
    {
        $this->ventureRepo->method('findById')->with(999)->willReturn(null);

        $this->expectException(\InvalidArgumentException::class);

        $this->service->createResolution(999, 'Title', 'Description');
    }

    #[Test]
    public function open_voting_throws_when_not_draft(): void
    {
        $resolution = new Resolution(ventureId: 1, title: 'T', status: ResolutionStatus::voting(), description: 'D', id: 5);
        $this->resolutionRepo->method('findById')->with(5)->willReturn($resolution);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Only draft resolutions');

        $this->service->openVoting(5);
    }

    #[Test]
    public function open_voting_throws_when_not_found(): void
    {
        $this->resolutionRepo->method('findById')->with(999)->willReturn(null);

        $this->expectException(\InvalidArgumentException::class);

        $this->service->openVoting(999);
    }

    #[Test]
    public function cast_vote_throws_when_resolution_not_open(): void
    {
        $resolution = new Resolution(ventureId: 1, title: 'T', status: ResolutionStatus::draft(), description: 'D', id: 5);
        $this->resolutionRepo->method('findById')->with(5)->willReturn($resolution);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('not open for voting');

        $this->service->castVote(42, 5, 'for');
    }

    #[Test]
    public function cast_vote_throws_when_not_a_shareholder(): void
    {
        $resolution = new Resolution(
            ventureId: 1, title: 'T', status: ResolutionStatus::voting(), description: 'D', id: 5,
            votingStartsAt: new DateTimeImmutable('-1 day'),
            votingEndsAt: new DateTimeImmutable('+1 day'),
        );

        $this->resolutionRepo->method('findById')->with(5)->willReturn($resolution);
        $this->shareholderRepo->method('findActiveByUserAndVenture')->with(42, 1)->willReturn(null);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('not an active shareholder');

        $this->service->castVote(42, 5, 'for');
    }

    #[Test]
    public function cast_vote_throws_when_already_voted(): void
    {
        $resolution = new Resolution(
            ventureId: 1, title: 'T', status: ResolutionStatus::voting(), description: 'D', id: 5,
            votingStartsAt: new DateTimeImmutable('-1 day'),
            votingEndsAt: new DateTimeImmutable('+1 day'),
        );
        $shareholder = new Shareholder(ventureId: 1, userId: 42, status: ShareholderStatus::active(), investmentId: 1, id: 3);
        $existingVote = new Vote(resolutionId: 5, shareholderId: 3, userId: 42, vote: 'for');

        $this->resolutionRepo->method('findById')->with(5)->willReturn($resolution);
        $this->shareholderRepo->method('findActiveByUserAndVenture')->with(42, 1)->willReturn($shareholder);
        $this->voteRepo->method('findByResolutionAndShareholder')->with(5, 3)->willReturn($existingVote);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('already voted');

        $this->service->castVote(42, 5, 'for');
    }

    #[Test]
    public function cast_vote_throws_for_invalid_vote_value(): void
    {
        $resolution = new Resolution(
            ventureId: 1, title: 'T', status: ResolutionStatus::voting(), description: 'D', id: 5,
            votingStartsAt: new DateTimeImmutable('-1 day'),
            votingEndsAt: new DateTimeImmutable('+1 day'),
        );
        $shareholder = new Shareholder(ventureId: 1, userId: 42, status: ShareholderStatus::active(), investmentId: 1, id: 3);

        $this->resolutionRepo->method('findById')->with(5)->willReturn($resolution);
        $this->shareholderRepo->method('findActiveByUserAndVenture')->with(42, 1)->willReturn($shareholder);
        $this->voteRepo->method('findByResolutionAndShareholder')->with(5, 3)->willReturn(null);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('must be "for", "against", or "abstain"');

        $this->service->castVote(42, 5, 'invalid');
    }

    #[Test]
    public function tally_results_throws_when_not_voting(): void
    {
        $resolution = new Resolution(ventureId: 1, title: 'T', status: ResolutionStatus::draft(), description: 'D', id: 5);
        $this->resolutionRepo->method('findById')->with(5)->willReturn($resolution);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Only resolutions in voting status');

        $this->service->tallyResults(5);
    }

    #[Test]
    public function tally_results_throws_when_not_found(): void
    {
        $this->resolutionRepo->method('findById')->with(999)->willReturn(null);

        $this->expectException(\InvalidArgumentException::class);

        $this->service->tallyResults(999);
    }
}
