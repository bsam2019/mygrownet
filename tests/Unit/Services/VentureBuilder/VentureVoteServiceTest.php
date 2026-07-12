<?php

use App\Infrastructure\Persistence\Eloquent\VentureBuilder\VentureModel;
use App\Infrastructure\Persistence\Eloquent\VentureBuilder\VentureResolutionModel;
use App\Infrastructure\Persistence\Eloquent\VentureBuilder\VentureShareholderModel;
use App\Infrastructure\Persistence\Eloquent\VentureBuilder\VentureVoteModel;
use App\Services\VentureBuilder\VentureVoteService;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->service = new VentureVoteService();
    $this->user = User::factory()->create();
    Auth::login($this->user);
});

describe('createResolution', function () {
    it('creates a draft resolution for funded or active ventures', function () {
        $venture = VentureModel::factory()->funded()->create();

        $resolution = $this->service->createResolution($venture, 'Test Resolution', 'Description', 'ordinary');

        expect($resolution->status)->toBe('draft');
        expect($resolution->title)->toBe('Test Resolution');
        expect($resolution->type)->toBe('ordinary');
        expect($resolution->created_by)->toBe($this->user->id);
    });

    it('rejects resolution creation for draft ventures', function () {
        $venture = VentureModel::factory()->create(['status' => 'draft']);

        expect(fn () => $this->service->createResolution($venture, 'Test', 'Desc'))
            ->toThrow(\InvalidArgumentException::class);
    });
});

describe('openVoting', function () {
    it('opens voting for a draft resolution', function () {
        $resolution = VentureResolutionModel::factory()->create(['status' => 'draft']);

        $result = $this->service->openVoting($resolution);

        expect($result->status)->toBe('voting');
    });

    it('rejects opening voting for non-draft resolution', function () {
        $resolution = VentureResolutionModel::factory()->voting()->create();

        expect(fn () => $this->service->openVoting($resolution))
            ->toThrow(\InvalidArgumentException::class);
    });
});

describe('castVote', function () {
    it('casts a vote on an open resolution', function () {
        $venture = VentureModel::factory()->active()->create();
        $resolution = VentureResolutionModel::factory()->voting()->create(['venture_id' => $venture->id]);
        VentureShareholderModel::factory()->create([
            'venture_id' => $venture->id,
            'user_id' => $this->user->id,
            'equity_percentage' => 25.5,
            'status' => 'active',
        ]);

        $vote = $this->service->castVote($this->user, $resolution, 'for');

        expect($vote->vote)->toBe('for');
        expect($vote->equity_at_vote)->toBe(25.5);
        expect($resolution->fresh()->votes_for)->toBe(25.5);
        expect($resolution->fresh()->total_voted_equity)->toBe(25.5);
    });

    it('rejects voting when not a shareholder', function () {
        $venture = VentureModel::factory()->active()->create();
        $resolution = VentureResolutionModel::factory()->voting()->create(['venture_id' => $venture->id]);

        expect(fn () => $this->service->castVote($this->user, $resolution, 'for'))
            ->toThrow(\InvalidArgumentException::class, 'not an active shareholder');
    });

    it('rejects duplicate votes', function () {
        $venture = VentureModel::factory()->active()->create();
        $resolution = VentureResolutionModel::factory()->voting()->create(['venture_id' => $venture->id]);
        $shareholder = VentureShareholderModel::factory()->create([
            'venture_id' => $venture->id,
            'user_id' => $this->user->id,
            'equity_percentage' => 25.5,
            'status' => 'active',
        ]);

        $this->service->castVote($this->user, $resolution, 'for');

        expect(fn () => $this->service->castVote($this->user, $resolution, 'against'))
            ->toThrow(\InvalidArgumentException::class, 'already voted');
    });

    it('rejects voting on non-voting resolution', function () {
        $resolution = VentureResolutionModel::factory()->create(['status' => 'passed']);

        expect(fn () => $this->service->castVote($this->user, $resolution, 'for'))
            ->toThrow(\InvalidArgumentException::class, 'not open for voting');
    });

    it('logs audit event on vote', function () {
        $venture = VentureModel::factory()->active()->create();
        $resolution = VentureResolutionModel::factory()->voting()->create(['venture_id' => $venture->id]);
        VentureShareholderModel::factory()->create([
            'venture_id' => $venture->id,
            'user_id' => $this->user->id,
            'equity_percentage' => 25.5,
            'status' => 'active',
        ]);

        $this->service->castVote($this->user, $resolution, 'for');

        expect(AuditLog::where('event_type', 'venture_vote_cast')->count())->toBe(1);
    });
});

describe('tallyResults', function () {
    it('marks resolution as passed when for votes exceed threshold', function () {
        $resolution = VentureResolutionModel::factory()->voting()->create([
            'votes_for' => 60.0,
            'votes_against' => 20.0,
            'votes_abstain' => 10.0,
            'total_voted_equity' => 90.0,
            'pass_threshold_percentage' => 50.0,
            'status' => 'voting',
        ]);

        $result = $this->service->tallyResults($resolution);

        expect($result->status)->toBe('passed');
        expect($result->result_notes)->toContain('Resolution passed');
    });

    it('marks resolution as rejected when for votes below threshold', function () {
        $resolution = VentureResolutionModel::factory()->voting()->create([
            'votes_for' => 30.0,
            'votes_against' => 40.0,
            'votes_abstain' => 10.0,
            'total_voted_equity' => 80.0,
            'pass_threshold_percentage' => 50.0,
            'status' => 'voting',
        ]);

        $result = $this->service->tallyResults($resolution);

        expect($result->status)->toBe('rejected');
    });

    it('rejects tallying non-voting resolution', function () {
        $resolution = VentureResolutionModel::factory()->create(['status' => 'draft']);

        expect(fn () => $this->service->tallyResults($resolution))
            ->toThrow(\InvalidArgumentException::class);
    });

    it('logs audit event on tally', function () {
        $resolution = VentureResolutionModel::factory()->voting()->create([
            'votes_for' => 60.0,
            'votes_against' => 20.0,
            'pass_threshold_percentage' => 50.0,
            'status' => 'voting',
        ]);

        $this->service->tallyResults($resolution);

        $eventType = 'venture_resolution_passed';
        expect(AuditLog::where('event_type', $eventType)->count())->toBe(1);
    });
});
