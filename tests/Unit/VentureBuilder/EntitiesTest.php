<?php

namespace Tests\Unit\VentureBuilder;

use App\Domain\VentureBuilder\Entities\Venture;
use App\Domain\VentureBuilder\Entities\Category;
use App\Domain\VentureBuilder\Entities\Investment;
use App\Domain\VentureBuilder\Entities\Shareholder;
use App\Domain\VentureBuilder\Entities\Dividend;
use App\Domain\VentureBuilder\Entities\Document;
use App\Domain\VentureBuilder\Entities\Resolution;
use App\Domain\VentureBuilder\Entities\ShareTransfer;
use App\Domain\VentureBuilder\Entities\Update;
use App\Domain\VentureBuilder\Entities\Vote;
use App\Domain\VentureBuilder\ValueObjects\VentureStatus;
use App\Domain\VentureBuilder\ValueObjects\InvestmentStatus;
use App\Domain\VentureBuilder\ValueObjects\ShareholderStatus;
use App\Domain\VentureBuilder\ValueObjects\DividendStatus;
use App\Domain\VentureBuilder\ValueObjects\ResolutionStatus;
use App\Domain\VentureBuilder\ValueObjects\TransferStatus;
use DateTimeImmutable;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class EntitiesTest extends TestCase
{
    // ————— VOs —————

    public function test_venture_status_state_machine(): void
    {
        $s = VentureStatus::draft();
        $this->assertTrue($s->isDraft());
        $this->assertTrue($s->canTransitionTo('review'));
        $this->assertTrue($s->canTransitionTo('cancelled'));
        $this->assertFalse($s->canTransitionTo('funding'));
        $this->assertSame(['review', 'cancelled'], $s->allowedTransitions());

        $s2 = VentureStatus::funding();
        $this->assertTrue($s2->isFunding());
        $this->assertTrue($s2->canAcceptInvestments());
        $this->assertTrue($s2->canTransitionTo('funded'));
        $this->assertTrue($s2->canTransitionTo('cancelled'));
        $this->assertFalse($s2->canTransitionTo('active'));

        $completed = VentureStatus::completed();
        $this->assertEmpty($completed->allowedTransitions());

        $cancelled = VentureStatus::cancelled();
        $this->assertEmpty($cancelled->allowedTransitions());

        $funded = VentureStatus::funded();
        $this->assertTrue($funded->isFunded());
    }

    public function test_venture_status_from_string(): void
    {
        $this->assertTrue(VentureStatus::fromString('draft')->isDraft());
        $this->assertTrue(VentureStatus::fromString('funding')->isFunding());
        $this->assertTrue(VentureStatus::fromString('active')->isActive());
    }

    public function test_venture_status_invalid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        VentureStatus::fromString('nonexistent');
    }

    public function test_investment_status(): void
    {
        $p = InvestmentStatus::pending();
        $this->assertTrue($p->isPending());
        $this->assertFalse($p->isConfirmed());
        $this->assertTrue($p->canBeCancelled());

        $c = InvestmentStatus::confirmed();
        $this->assertTrue($c->isConfirmed());
        $this->assertFalse($c->canBeCancelled());

        $comp = InvestmentStatus::completed();
        $this->assertTrue($comp->isConfirmed());
    }

    public function test_investment_status_invalid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        InvestmentStatus::fromString('bogus');
    }

    public function test_shareholder_status(): void
    {
        $this->assertTrue(ShareholderStatus::active()->isActive());
        $this->assertFalse(ShareholderStatus::inactive()->isActive());
        $this->assertFalse(ShareholderStatus::removed()->isActive());
    }

    public function test_dividend_status(): void
    {
        $d = DividendStatus::declared();
        $this->assertTrue($d->isDeclared());
        $this->assertFalse($d->isPaid());

        $p = DividendStatus::paid();
        $this->assertTrue($p->isPaid());
        $this->assertFalse($p->isDeclared());
    }

    public function test_resolution_status(): void
    {
        $d = ResolutionStatus::draft();
        $this->assertTrue($d->isDraft());
        $this->assertFalse($d->isVoting());

        $v = ResolutionStatus::voting();
        $this->assertTrue($v->isVoting());
    }

    public function test_transfer_status(): void
    {
        $p = TransferStatus::pending();
        $this->assertTrue($p->isPending());

        $this->assertFalse(TransferStatus::approved()->isPending());
        $this->assertFalse(TransferStatus::rejected()->isPending());
    }

    // ————— ENTITY ROUND-TRIPS —————

    public static function entityRoundTripProvider(): array
    {
        $now = '2026-07-29 10:00:00';
        $date = '2026-07-29';

        return [
            'Venture full' => [
                Venture::class,
                [
                    'id' => 1,
                    'category_id' => 5,
                    'title' => 'Green Energy',
                    'slug' => 'green-energy',
                    'description' => 'A green energy venture',
                    'business_model' => 'equity',
                    'featured_image' => 'img.jpg',
                    'funding_target' => 100000,
                    'minimum_investment' => 1000,
                    'maximum_investment' => 50000,
                    'share_price' => 10,
                    'total_raised' => 40000,
                    'investor_count' => 12,
                    'funding_start_date' => $date,
                    'funding_end_date' => $date,
                    'expected_launch_date' => $date,
                    'actual_launch_date' => $date,
                    'status' => 'funding',
                    'company_name' => 'Green Energy Ltd',
                    'company_registration_number' => 'REG-001',
                    'company_formation_date' => $date,
                    'mygrownet_equity_percentage' => 15.5,
                    'revenue_projections' => [2026 => 50000, 2027 => 100000],
                    'risk_factors' => 'Market risk',
                    'expected_roi_months' => 24,
                    'is_featured' => true,
                    'views_count' => 500,
                    'created_by' => 10,
                    'approved_by' => 20,
                    'approved_at' => $now,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
            ],
            'Venture minimal' => [
                Venture::class,
                [
                    'title' => 'Minimal',
                    'slug' => 'minimal',
                    'status' => 'draft',
                ],
            ],
            'Category full' => [
                Category::class,
                [
                    'id' => 3,
                    'name' => 'Tech',
                    'slug' => 'tech',
                    'description' => 'Tech ventures',
                    'icon' => 'tech-icon',
                    'sort_order' => 1,
                    'is_active' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
            ],
            'Category minimal' => [
                Category::class,
                ['name' => 'Health', 'slug' => 'health'],
            ],
            'Investment full' => [
                Investment::class,
                [
                    'id' => 1,
                    'venture_id' => 1,
                    'user_id' => 5,
                    'amount' => 5000,
                    'shares_allocated' => 500,
                    'equity_percentage' => 2.5,
                    'status' => 'confirmed',
                    'payment_method' => 'mobile_money',
                    'payment_reference' => 'MM_ABC123',
                    'payment_confirmed_at' => $now,
                    'is_shareholder' => true,
                    'shareholder_registered_at' => $date,
                    'shareholder_certificate_number' => 'CERT-001',
                    'notes' => 'Early investor',
                    'processed_by' => 10,
                    'processed_at' => $now,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
            ],
            'Investment minimal' => [
                Investment::class,
                [
                    'venture_id' => 1,
                    'user_id' => 5,
                    'amount' => 1000,
                    'status' => 'pending',
                ],
            ],
            'Shareholder full' => [
                Shareholder::class,
                [
                    'id' => 1,
                    'venture_id' => 1,
                    'user_id' => 5,
                    'investment_id' => 10,
                    'total_investment' => 5000,
                    'shares_owned' => 500,
                    'equity_percentage' => 5.0,
                    'certificate_number' => 'CERT-001',
                    'registration_date' => $date,
                    'shareholder_agreement_path' => 'agreements/1.pdf',
                    'agreement_signed' => true,
                    'agreement_signed_at' => $now,
                    'status' => 'active',
                    'total_dividends_received' => 200,
                    'last_dividend_date' => $now,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
            ],
            'Shareholder minimal' => [
                Shareholder::class,
                [
                    'venture_id' => 1,
                    'user_id' => 5,
                    'investment_id' => 10,
                    'status' => 'active',
                ],
            ],
            'Dividend full' => [
                Dividend::class,
                [
                    'id' => 1,
                    'venture_id' => 1,
                    'shareholder_id' => 5,
                    'dividend_period' => 'Q1-2026',
                    'declaration_date' => $date,
                    'amount' => 1000,
                    'equity_percentage_at_payment' => 5.0,
                    'status' => 'paid',
                    'notes' => 'Quarterly dividend',
                    'payment_date' => $date,
                    'paid_at' => $now,
                    'payment_method' => 'wallet',
                    'payment_reference' => 'DIV-ABC',
                    'processed_by' => 10,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
            ],
            'Dividend minimal' => [
                Dividend::class,
                [
                    'venture_id' => 1,
                    'shareholder_id' => 5,
                    'amount' => 500,
                    'status' => 'declared',
                ],
            ],
            'Document full' => [
                Document::class,
                [
                    'id' => 1,
                    'venture_id' => 1,
                    'title' => 'Business Plan',
                    'description' => 'Full plan',
                    'type' => 'plan',
                    'file_path' => 'docs/plan.pdf',
                    'file_name' => 'plan.pdf',
                    'file_type' => 'application/pdf',
                    'file_size' => 2048576,
                    'visibility' => 'public',
                    'is_confidential' => false,
                    'download_count' => 50,
                    'uploaded_by' => 10,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
            ],
            'Document minimal' => [
                Document::class,
                [
                    'venture_id' => 1,
                    'title' => 'Doc',
                    'file_path' => 'docs/doc.pdf',
                    'visibility' => 'public',
                    'uploaded_by' => 1,
                    'type' => 'other',
                ],
            ],
            'Resolution full' => [
                Resolution::class,
                [
                    'id' => 1,
                    'venture_id' => 1,
                    'title' => 'Expand Operations',
                    'description' => 'Vote on expansion',
                    'type' => 'ordinary',
                    'status' => 'voting',
                    'voting_starts_at' => $now,
                    'voting_ends_at' => $now,
                    'pass_threshold_percentage' => 50,
                    'votes_for' => 75.5,
                    'votes_against' => 24.5,
                    'votes_abstain' => 5.0,
                    'total_voted_equity' => 80.0,
                    'result_notes' => 'Passed',
                    'created_by' => 10,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
            ],
            'Resolution minimal' => [
                Resolution::class,
                [
                    'venture_id' => 1,
                    'title' => 'Test',
                    'description' => 'desc',
                    'status' => 'draft',
                ],
            ],
            'ShareTransfer full' => [
                ShareTransfer::class,
                [
                    'id' => 1,
                    'venture_id' => 1,
                    'from_user_id' => 5,
                    'to_user_id' => 10,
                    'shares' => 100,
                    'price_per_share' => 10.50,
                    'total_value' => 1050,
                    'status' => 'approved',
                    'reason' => 'Sale',
                    'admin_notes' => 'Approved',
                    'approved_by' => 1,
                    'approved_at' => $now,
                    'completed_at' => $now,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
            ],
            'ShareTransfer minimal' => [
                ShareTransfer::class,
                [
                    'venture_id' => 1,
                    'from_user_id' => 5,
                    'to_user_id' => 10,
                    'shares' => 50,
                    'status' => 'pending',
                ],
            ],
            'Update full' => [
                Update::class,
                [
                    'id' => 1,
                    'venture_id' => 1,
                    'title' => 'Milestone Reached',
                    'content' => 'We hit our first milestone',
                    'type' => 'milestone',
                    'visibility' => 'public',
                    'send_notification' => true,
                    'is_pinned' => true,
                    'views_count' => 200,
                    'posted_by' => 10,
                    'published_at' => $now,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
            ],
            'Update minimal' => [
                Update::class,
                [
                    'venture_id' => 1,
                    'title' => 'Draft',
                    'content' => 'content',
                    'visibility' => 'investors_only',
                    'posted_by' => 1,
                ],
            ],
            'Vote full' => [
                Vote::class,
                [
                    'id' => 1,
                    'resolution_id' => 1,
                    'shareholder_id' => 5,
                    'user_id' => 5,
                    'vote' => 'for',
                    'equity_at_vote' => 5.0,
                    'comment' => 'Strong support',
                    'voted_at' => $now,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
            ],
            'Vote minimal' => [
                Vote::class,
                [
                    'resolution_id' => 1,
                    'shareholder_id' => 5,
                    'user_id' => 5,
                    'vote' => 'against',
                ],
            ],
        ];
    }

    #[DataProvider('entityRoundTripProvider')]
    public function test_entity_round_trip(string $class, array $data): void
    {
        $entity = $class::reconstitute($data);
        $this->assertInstanceOf($class, $entity);

        $output = $entity->toArray();

        foreach ($data as $key => $value) {
            $this->assertArrayHasKey($key, $output, "Missing key: {$key}");
        }
    }

    // ————— ENTITY BEHAVIOR —————

    public function test_venture_getFundingProgressPercentage(): void
    {
        $v = Venture::reconstitute(['title' => 'T', 'slug' => 't', 'status' => 'funding']);
        $this->assertSame(0.0, $v->getFundingProgressPercentage());

        $v2 = Venture::reconstitute(['title' => 'T', 'slug' => 't', 'status' => 'funding', 'funding_target' => 0]);
        $this->assertSame(0.0, $v2->getFundingProgressPercentage());

        $v3 = Venture::reconstitute(['title' => 'T', 'slug' => 't', 'status' => 'funding', 'funding_target' => 1000, 'total_raised' => 500]);
        $this->assertSame(50.0, $v3->getFundingProgressPercentage());

        $v4 = Venture::reconstitute(['title' => 'T', 'slug' => 't', 'status' => 'funding', 'funding_target' => 1000, 'total_raised' => 1500]);
        $this->assertSame(100.0, $v4->getFundingProgressPercentage());
    }

    public function test_venture_isFundingOpen(): void
    {
        $v = Venture::reconstitute(['title' => 'T', 'slug' => 't', 'status' => 'funding']);
        $this->assertTrue($v->isFundingOpen());

        $v2 = Venture::reconstitute(['title' => 'T', 'slug' => 't', 'status' => 'draft']);
        $this->assertFalse($v2->isFundingOpen());

        $v3 = Venture::reconstitute(['title' => 'T', 'slug' => 't', 'status' => 'funding', 'funding_end_date' => (new DateTimeImmutable('-1 day'))->format('Y-m-d H:i:s')]);
        $this->assertFalse($v3->isFundingOpen());
    }

    public function test_venture_canAcceptInvestments(): void
    {
        $v = Venture::reconstitute(['title' => 'T', 'slug' => 't', 'status' => 'funding', 'funding_target' => 1000]);
        $this->assertTrue($v->canAcceptInvestments());

        $v2 = Venture::reconstitute(['title' => 'T', 'slug' => 't', 'status' => 'funding', 'funding_target' => 1000, 'total_raised' => 1000]);
        $this->assertFalse($v2->canAcceptInvestments());

        $v3 = Venture::reconstitute(['title' => 'T', 'slug' => 't', 'status' => 'funded']);
        $this->assertFalse($v3->canAcceptInvestments());
    }

    public function test_investment_behavior(): void
    {
        $c = Investment::reconstitute(['venture_id' => 1, 'user_id' => 1, 'amount' => 100, 'status' => 'confirmed']);
        $this->assertTrue($c->isConfirmed());
        $this->assertFalse($c->isPending());
        $this->assertFalse($c->canBeCancelled());

        $p = Investment::reconstitute(['venture_id' => 1, 'user_id' => 1, 'amount' => 100, 'status' => 'pending']);
        $this->assertTrue($p->isPending());
        $this->assertTrue($p->canBeCancelled());
    }

    public function test_shareholder_behavior(): void
    {
        $a = Shareholder::reconstitute(['venture_id' => 1, 'user_id' => 1, 'investment_id' => 10, 'status' => 'active', 'agreement_signed' => true]);
        $this->assertTrue($a->isActive());
        $this->assertTrue($a->hasSignedAgreement());

        $b = Shareholder::reconstitute(['venture_id' => 1, 'user_id' => 1, 'investment_id' => 10, 'status' => 'active']);
        $this->assertFalse($b->hasSignedAgreement());
    }

    public function test_dividend_behavior(): void
    {
        $p = Dividend::reconstitute(['venture_id' => 1, 'shareholder_id' => 1, 'amount' => 100, 'status' => 'paid']);
        $this->assertTrue($p->isPaid());
        $this->assertFalse($p->isPending());

        $d = Dividend::reconstitute(['venture_id' => 1, 'shareholder_id' => 1, 'amount' => 100, 'status' => 'declared']);
        $this->assertTrue($d->isPending());
    }

    public function test_document_behavior(): void
    {
        $d = Document::reconstitute(['venture_id' => 1, 'title' => 'Doc', 'file_path' => 'f', 'visibility' => 'public', 'uploaded_by' => 1, 'type' => 'other']);
        $this->assertTrue($d->isPublic());
        $this->assertTrue($d->canBeAccessedBy('guest'));

        $d2 = Document::reconstitute(['venture_id' => 1, 'title' => 'Doc', 'file_path' => 'f', 'visibility' => 'admin_only', 'uploaded_by' => 1, 'type' => 'other']);
        $this->assertFalse($d2->canBeAccessedBy('investor'));
        $this->assertTrue($d2->canBeAccessedBy('admin'));
    }

    public function test_document_getFileSizeFormatted(): void
    {
        $d = Document::reconstitute(['venture_id' => 1, 'title' => 'D', 'file_path' => 'f', 'visibility' => 'public', 'uploaded_by' => 1, 'type' => 'other', 'file_size' => 500]);
        $this->assertSame('500 B', $d->getFileSizeFormatted());

        $d2 = Document::reconstitute(['venture_id' => 1, 'title' => 'D', 'file_path' => 'f', 'visibility' => 'public', 'uploaded_by' => 1, 'type' => 'other', 'file_size' => 2048]);
        $this->assertSame('2 KB', $d2->getFileSizeFormatted());

        $d3 = Document::reconstitute(['venture_id' => 1, 'title' => 'D', 'file_path' => 'f', 'visibility' => 'public', 'uploaded_by' => 1, 'type' => 'other', 'file_size' => 2097152]);
        $this->assertSame('2 MB', $d3->getFileSizeFormatted());
    }

    public function test_update_behavior(): void
    {
        $d = Update::reconstitute(['venture_id' => 1, 'title' => 'Draft', 'content' => 'content', 'visibility' => 'public', 'posted_by' => 1]);
        $this->assertTrue($d->isDraft());
        $this->assertFalse($d->isPublished());

        $p = Update::reconstitute(['venture_id' => 1, 'title' => 'Published', 'content' => 'content', 'visibility' => 'public', 'posted_by' => 1, 'published_at' => (new DateTimeImmutable('-1 hour'))->format('Y-m-d H:i:s')]);
        $this->assertTrue($p->isPublished());
        $this->assertFalse($p->isDraft());
    }

    public function test_update_canBeViewedBy(): void
    {
        $u = Update::reconstitute(['venture_id' => 1, 'title' => 'T', 'content' => 'content', 'visibility' => 'shareholders_only', 'posted_by' => 1, 'published_at' => (new DateTimeImmutable('-1 hour'))->format('Y-m-d H:i:s')]);
        $this->assertTrue($u->canBeViewedBy('shareholder'));
        $this->assertTrue($u->canBeViewedBy('admin'));
        $this->assertFalse($u->canBeViewedBy('investor'));
        $this->assertFalse($u->canBeViewedBy('guest'));

        $draft = Update::reconstitute(['venture_id' => 1, 'title' => 'T', 'content' => 'content', 'visibility' => 'public', 'posted_by' => 1]);
        $this->assertTrue($draft->canBeViewedBy('admin'));
        $this->assertFalse($draft->canBeViewedBy('investor'));
    }

    public function test_resolution_isOpenForVoting(): void
    {
        $r = Resolution::reconstitute(['venture_id' => 1, 'title' => 'T', 'description' => 'desc', 'status' => 'voting']);
        $this->assertTrue($r->isOpenForVoting());

        $r2 = Resolution::reconstitute(['venture_id' => 1, 'title' => 'T', 'description' => 'desc', 'status' => 'draft']);
        $this->assertFalse($r2->isOpenForVoting());

        $r3 = Resolution::reconstitute(['venture_id' => 1, 'title' => 'T', 'description' => 'desc', 'status' => 'voting', 'voting_ends_at' => (new DateTimeImmutable('-1 day'))->format('Y-m-d H:i:s')]);
        $this->assertFalse($r3->isOpenForVoting());
    }

    public function test_share_transfer_behavior(): void
    {
        $p = ShareTransfer::reconstitute(['venture_id' => 1, 'from_user_id' => 1, 'to_user_id' => 2, 'shares' => 100, 'status' => 'pending']);
        $this->assertTrue($p->isPending());
        $this->assertFalse($p->isCompleted());

        $a = ShareTransfer::reconstitute(['venture_id' => 1, 'from_user_id' => 1, 'to_user_id' => 2, 'shares' => 100, 'status' => 'approved']);
        $this->assertTrue($a->isCompleted());
        $this->assertFalse($a->isPending());
    }
}
