<?php

namespace Tests\Feature\VentureBuilder;

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
use App\Domain\VentureBuilder\Repositories\CategoryRepositoryInterface;
use App\Domain\VentureBuilder\Repositories\VentureRepositoryInterface;
use App\Domain\VentureBuilder\Repositories\InvestmentRepositoryInterface;
use App\Domain\VentureBuilder\Repositories\ShareholderRepositoryInterface;
use App\Domain\VentureBuilder\Repositories\DividendRepositoryInterface;
use App\Domain\VentureBuilder\Repositories\DocumentRepositoryInterface;
use App\Domain\VentureBuilder\Repositories\ResolutionRepositoryInterface;
use App\Domain\VentureBuilder\Repositories\ShareTransferRepositoryInterface;
use App\Domain\VentureBuilder\Repositories\UpdateRepositoryInterface;
use App\Domain\VentureBuilder\Repositories\VoteRepositoryInterface;
use App\Infrastructure\Persistence\Eloquent\VentureBuilder\VentureCategoryModel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class RepositoriesTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private User $otherUser;
    private User $admin;
    private int $categoryId;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create(['id' => 1]);
        $this->otherUser = User::factory()->create(['id' => 2]);
        $this->admin = User::factory()->create(['id' => 3]);
        $this->categoryId = VentureCategoryModel::create(['name' => 'Default', 'slug' => 'default'])->id;
    }

    // ————— CATEGORY —————

    #[Test]
    public function category_crud(): void
    {
        $repo = app(CategoryRepositoryInterface::class);

        $data = ['name' => 'Tech', 'slug' => 'tech', 'is_active' => true, 'sort_order' => 1];
        $saved = $repo->findById(0);
        $this->assertNull($saved);

        VentureCategoryModel::create($data);
        $found = $repo->findBySlug('tech');
        $this->assertNotNull($found);
        $this->assertSame('Tech', $found['name']);

        $byId = $repo->findById($found['id']);
        $this->assertNotNull($byId);
        $this->assertSame('Tech', $byId['name']);
    }

    #[Test]
    public function category_getActive(): void
    {
        $repo = app(CategoryRepositoryInterface::class);

        VentureCategoryModel::create(['name' => 'A', 'slug' => 'a', 'is_active' => true, 'sort_order' => 1]);
        VentureCategoryModel::create(['name' => 'B', 'slug' => 'b', 'is_active' => false, 'sort_order' => 2]);
        VentureCategoryModel::create(['name' => 'C', 'slug' => 'c', 'is_active' => true, 'sort_order' => 0]);

        $active = $repo->getActive();
        $this->assertCount(3, $active);
        $names = array_column($active, 'name');
        $this->assertContains('Default', $names);
        $this->assertContains('A', $names);
        $this->assertContains('C', $names);
        $this->assertNotContains('B', $names);
    }

    // ————— VENTURE —————

    #[Test]
    public function venture_crud(): void
    {
        $repo = app(VentureRepositoryInterface::class);

        $this->assertNull($repo->findById(999));
        $this->assertNull($repo->findBySlug('nonexistent'));

        $v = Venture::reconstitute([
            'category_id' => $this->categoryId,
            'title' => 'Green Energy',
            'slug' => 'green-energy',
            'description' => 'A green energy venture',
            'funding_target' => 10000,
            'status' => 'draft',
            'created_by' => $this->user->id,
        ]);

        $saved = $repo->save($v);
        $this->assertNotNull($saved->id);
        $this->assertSame('Green Energy', $saved->title);

        $found = $repo->findById($saved->id);
        $this->assertNotNull($found);
        $this->assertSame('green-energy', $found->slug);

        $bySlug = $repo->findBySlug('green-energy');
        $this->assertNotNull($bySlug);
        $this->assertSame($saved->id, $bySlug->id);
    }

    #[Test]
    public function venture_update(): void
    {
        $repo = app(VentureRepositoryInterface::class);
        $v = $repo->save(Venture::reconstitute(['category_id' => $this->categoryId, 'description' => 'desc', 'funding_target' => 10000, 'title' => 'Orig', 'slug' => 'orig', 'status' => 'draft', 'created_by' => $this->user->id]));

        $updated = $repo->save(Venture::reconstitute([
            'category_id' => $this->categoryId,
            'id' => $v->id,
            'title' => 'Updated',
            'slug' => 'orig',
            'description' => 'desc',
            'funding_target' => 10000,
            'status' => 'review',
            'created_by' => $this->user->id,
        ]));

        $this->assertSame('Updated', $updated->title);
        $this->assertSame('review', $updated->status->value());
    }

    #[Test]
    public function venture_findFunding(): void
    {
        $repo = app(VentureRepositoryInterface::class);
        $repo->save(Venture::reconstitute(['category_id' => $this->categoryId, 'description' => 'desc', 'funding_target' => 10000, 'title' => 'A', 'slug' => 'a', 'status' => 'draft', 'created_by' => $this->user->id]));
        $repo->save(Venture::reconstitute(['category_id' => $this->categoryId, 'description' => 'desc', 'funding_target' => 10000, 'title' => 'B', 'slug' => 'b', 'status' => 'funding', 'created_by' => $this->user->id]));
        $repo->save(Venture::reconstitute(['category_id' => $this->categoryId, 'description' => 'desc', 'funding_target' => 10000, 'title' => 'C', 'slug' => 'c', 'status' => 'funding', 'created_by' => $this->user->id]));

        $funding = $repo->findFunding(10);
        $this->assertCount(2, $funding);
    }

    #[Test]
    public function venture_findFeatured(): void
    {
        $repo = app(VentureRepositoryInterface::class);
        $repo->save(Venture::reconstitute(['category_id' => $this->categoryId, 'description' => 'desc', 'funding_target' => 10000, 'title' => 'A', 'slug' => 'a', 'status' => 'funding', 'created_by' => $this->user->id, 'is_featured' => true]));
        $repo->save(Venture::reconstitute(['category_id' => $this->categoryId, 'description' => 'desc', 'funding_target' => 10000, 'title' => 'B', 'slug' => 'b', 'status' => 'draft', 'created_by' => $this->user->id, 'is_featured' => true]));

        $featured = $repo->findFeatured(10);
        $this->assertCount(1, $featured);
        $this->assertSame('A', $featured[0]->title);
    }

    #[Test]
    public function venture_findAll_with_filters(): void
    {
        $repo = app(VentureRepositoryInterface::class);
        $cat = VentureCategoryModel::create(['name' => 'Tech', 'slug' => 'tech']);
        $repo->save(Venture::reconstitute(['title' => 'Alpha', 'slug' => 'alpha', 'description' => 'desc', 'funding_target' => 10000, 'status' => 'funding', 'created_by' => $this->user->id, 'category_id' => $cat->id]));
        $repo->save(Venture::reconstitute(['category_id' => $this->categoryId, 'description' => 'desc', 'funding_target' => 10000, 'title' => 'Beta', 'slug' => 'beta', 'status' => 'draft', 'created_by' => $this->user->id]));

        $all = $repo->findAll([], 10);
        $this->assertCount(2, $all);

        $filtered = $repo->findAll(['status' => 'funding'], 10);
        $this->assertCount(1, $filtered);

        $catFiltered = $repo->findAll(['category_id' => $cat->id], 10);
        $this->assertCount(1, $catFiltered);

        $search = $repo->findAll(['search' => 'beta'], 10);
        $this->assertCount(1, $search);
    }

    #[Test]
    public function venture_status_transitions(): void
    {
        $repo = app(VentureRepositoryInterface::class);
        $v = $repo->save(Venture::reconstitute(['category_id' => $this->categoryId, 'description' => 'desc', 'funding_target' => 10000, 'title' => 'T', 'slug' => 't', 'status' => 'funding', 'created_by' => $this->user->id]));

        $repo->updateStatus($v->id, 'funded');
        $found = $repo->findById($v->id);
        $this->assertSame('funded', $found->status->value());

        $repo->updateStatus($v->id, 'active');
        $found = $repo->findById($v->id);
        $this->assertSame('active', $found->status->value());
    }

    #[Test]
    public function venture_increment_decrement_counters(): void
    {
        $repo = app(VentureRepositoryInterface::class);
        $v = $repo->save(Venture::reconstitute(['category_id' => $this->categoryId, 'description' => 'desc', 'funding_target' => 10000, 'title' => 'T', 'slug' => 't', 'status' => 'funding', 'created_by' => $this->user->id]));

        $repo->incrementTotalRaised($v->id, 5000);
        $repo->incrementInvestorCount($v->id);
        $found = $repo->findById($v->id);
        $this->assertSame(5000.0, $found->totalRaised);
        $this->assertSame(1, $found->investorCount);

        $repo->decrementTotalRaised($v->id, 1000);
        $repo->decrementInvestorCount($v->id);
        $found = $repo->findById($v->id);
        $this->assertSame(4000.0, $found->totalRaised);
        $this->assertSame(0, $found->investorCount);
    }

    #[Test]
    public function venture_getStats(): void
    {
        $repo = app(VentureRepositoryInterface::class);
        $repo->save(Venture::reconstitute(['category_id' => $this->categoryId, 'description' => 'desc', 'funding_target' => 10000, 'title' => 'A', 'slug' => 'a', 'status' => 'funding', 'created_by' => $this->user->id, 'total_raised' => 5000]));
        $repo->save(Venture::reconstitute(['category_id' => $this->categoryId, 'description' => 'desc', 'funding_target' => 10000, 'title' => 'B', 'slug' => 'b', 'status' => 'active', 'created_by' => $this->user->id, 'total_raised' => 3000]));
        $repo->save(Venture::reconstitute(['category_id' => $this->categoryId, 'description' => 'desc', 'funding_target' => 10000, 'title' => 'C', 'slug' => 'c', 'status' => 'draft', 'created_by' => $this->user->id]));

        $stats = $repo->getStats();
        $this->assertSame(3, $stats['total_ventures']);
        $this->assertSame(8000.0, $stats['total_funding_raised']);
        $this->assertSame(1, $stats['funding_ventures']);
        $this->assertSame(30000.0, $stats['total_funding_target']);
    }

    // ————— INVESTMENT —————

    #[Test]
    public function investment_crud(): void
    {
        $repo = app(InvestmentRepositoryInterface::class);
        $ventureId = $this->createVentureId();

        $i = new Investment(
            ventureId: $ventureId,
            userId: $this->user->id,
            amount: 5000,
            sharesAllocated: 500,
            status: InvestmentStatus::pending(),
            paymentMethod: 'mobile_money',
        );

        $saved = $repo->save($i);
        $this->assertNotNull($saved->id);
        $this->assertSame(5000.0, $saved->amount);
        $this->assertTrue($saved->isPending());

        $found = $repo->findById($saved->id);
        $this->assertNotNull($found);
        $this->assertSame($ventureId, $found->ventureId);
    }

    #[Test]
    public function investment_findByVenture(): void
    {
        $repo = app(InvestmentRepositoryInterface::class);
        $v1 = $this->createVentureId();
        $v2 = $this->createVentureId();

        $repo->save(new Investment(ventureId: $v1, userId: $this->user->id, amount: 100, status: InvestmentStatus::pending()));
        $repo->save(new Investment(ventureId: $v1, userId: $this->user->id, amount: 200, status: InvestmentStatus::confirmed()));
        $repo->save(new Investment(ventureId: $v2, userId: $this->user->id, amount: 300, status: InvestmentStatus::pending()));

        $fromV1 = $repo->findByVenture($v1);
        $this->assertCount(2, $fromV1);

        $confirmed = $repo->findByVenture($v1, ['status' => 'confirmed']);
        $this->assertCount(1, $confirmed);
    }

    #[Test]
    public function investment_findByUser(): void
    {
        $repo = app(InvestmentRepositoryInterface::class);
        $v = $this->createVentureId();

        $repo->save(new Investment(ventureId: $v, userId: $this->user->id, amount: 100, status: InvestmentStatus::pending()));
        $repo->save(new Investment(ventureId: $v, userId: $this->otherUser->id, amount: 200, status: InvestmentStatus::pending()));

        $userInvestments = $repo->findByUser($this->user->id);
        $this->assertCount(1, $userInvestments);
    }

    #[Test]
    public function investment_findPending(): void
    {
        $repo = app(InvestmentRepositoryInterface::class);
        $v = $this->createVentureId();

        $repo->save(new Investment(ventureId: $v, userId: $this->user->id, amount: 100, status: InvestmentStatus::pending()));
        $repo->save(new Investment(ventureId: $v, userId: $this->user->id, amount: 200, status: InvestmentStatus::confirmed()));
        $pending = $repo->findPending();
        $this->assertCount(1, $pending);
    }

    #[Test]
    public function investment_findConfirmedByUserAndVenture(): void
    {
        $repo = app(InvestmentRepositoryInterface::class);
        $v = $this->createVentureId();

        $repo->save(new Investment(ventureId: $v, userId: $this->user->id, amount: 100, status: InvestmentStatus::pending()));
        $repo->save(new Investment(ventureId: $v, userId: $this->user->id, amount: 200, status: InvestmentStatus::confirmed()));
        $confirmed = $repo->findConfirmedByUserAndVenture($this->user->id, $v);
        $this->assertCount(1, $confirmed);
    }

    #[Test]
    public function investment_updateStatus(): void
    {
        $repo = app(InvestmentRepositoryInterface::class);
        $v = $this->createVentureId();
        $saved = $repo->save(new Investment(ventureId: $v, userId: $this->user->id, amount: 100, status: InvestmentStatus::pending()));

        $repo->updateStatus($saved->id, 'confirmed');
        $found = $repo->findById($saved->id);
        $this->assertSame('confirmed', $found->status->value());
    }

    #[Test]
    public function investment_getTotalInvestedByUser(): void
    {
        $repo = app(InvestmentRepositoryInterface::class);
        $v = $this->createVentureId();

        $repo->save(new Investment(ventureId: $v, userId: $this->user->id, amount: 1000, status: InvestmentStatus::confirmed()));
        $repo->save(new Investment(ventureId: $v, userId: $this->user->id, amount: 500, status: InvestmentStatus::pending()));
        $repo->save(new Investment(ventureId: $v, userId: $this->user->id, amount: 2000, status: InvestmentStatus::confirmed()));

        $total = $repo->getTotalInvestedByUser($this->user->id, $v);
        $this->assertSame(3000.0, $total);
    }

    // ————— SHAREHOLDER —————

    #[Test]
    public function shareholder_crud(): void
    {
        $repo = app(ShareholderRepositoryInterface::class);
        $v = $this->createVentureId();
        $invId = $this->createInvestmentId($v);

        $s = new Shareholder(
            ventureId: $v,
            userId: $this->user->id,
            totalInvestment: 5000,
            sharesOwned: 500,
            equityPercentage: 10.0,
            status: ShareholderStatus::active(),
            investmentId: $invId,
            certificateNumber: 'CERT-' . uniqid(),
            registrationDate: new \DateTimeImmutable('2026-01-01'),
        );

        $saved = $repo->save($s);
        $this->assertNotNull($saved->id);
        $this->assertTrue($saved->isActive());

        $found = $repo->findById($saved->id);
        $this->assertNotNull($found);
        $this->assertSame(5000.0, $found->totalInvestment);
    }

    #[Test]
    public function shareholder_findByVenture(): void
    {
        $repo = app(ShareholderRepositoryInterface::class);
        $v = $this->createVentureId();
        $inv1 = $this->createInvestmentId($v);
        $inv2 = $this->createInvestmentId($v);

        $repo->save(new Shareholder(ventureId: $v, userId: $this->user->id, status: ShareholderStatus::active(), investmentId: $inv1, totalInvestment: 5000, sharesOwned: 500, equityPercentage: 5.0, certificateNumber: 'CERT-A', registrationDate: new \DateTimeImmutable('2026-01-01')));
        $repo->save(new Shareholder(ventureId: $v, userId: $this->otherUser->id, status: ShareholderStatus::active(), investmentId: $inv2, totalInvestment: 3000, sharesOwned: 300, equityPercentage: 3.0, certificateNumber: 'CERT-B', registrationDate: new \DateTimeImmutable('2026-01-01')));

        $this->assertCount(2, $repo->findByVenture($v));
    }

    #[Test]
    public function shareholder_findByUserAndVenture(): void
    {
        $repo = app(ShareholderRepositoryInterface::class);
        $v = $this->createVentureId();
        $invId = $this->createInvestmentId($v);

        $repo->save(new Shareholder(ventureId: $v, userId: $this->user->id, status: ShareholderStatus::active(), investmentId: $invId, totalInvestment: 5000, sharesOwned: 500, equityPercentage: 5.0, certificateNumber: 'CERT-A', registrationDate: new \DateTimeImmutable('2026-01-01')));
        $found = $repo->findByUserAndVenture($this->user->id, $v);
        $this->assertNotNull($found);

        $notFound = $repo->findByUserAndVenture(999, $v);
        $this->assertNull($notFound);
    }

    #[Test]
    public function shareholder_findActiveByVenture(): void
    {
        $repo = app(ShareholderRepositoryInterface::class);
        $v = $this->createVentureId();
        $inv1 = $this->createInvestmentId($v);
        $inv2 = $this->createInvestmentId($v);

        $repo->save(new Shareholder(ventureId: $v, userId: $this->user->id, status: ShareholderStatus::active(), investmentId: $inv1, totalInvestment: 5000, sharesOwned: 500, equityPercentage: 5.0, certificateNumber: 'CERT-A', registrationDate: new \DateTimeImmutable('2026-01-01')));
        $repo->save(new Shareholder(ventureId: $v, userId: $this->otherUser->id, status: ShareholderStatus::inactive(), investmentId: $inv2, totalInvestment: 3000, sharesOwned: 300, equityPercentage: 3.0, certificateNumber: 'CERT-B', registrationDate: new \DateTimeImmutable('2026-01-01')));

        $this->assertCount(1, $repo->findActiveByVenture($v));
    }

    #[Test]
    public function shareholder_findActiveByUserAndVenture(): void
    {
        $repo = app(ShareholderRepositoryInterface::class);
        $v = $this->createVentureId();
        $invId = $this->createInvestmentId($v);

        $repo->save(new Shareholder(ventureId: $v, userId: $this->user->id, status: ShareholderStatus::active(), investmentId: $invId, totalInvestment: 5000, sharesOwned: 500, equityPercentage: 5.0, certificateNumber: 'CERT-A', registrationDate: new \DateTimeImmutable('2026-01-01')));
        $found = $repo->findActiveByUserAndVenture($this->user->id, $v);
        $this->assertNotNull($found);
    }

    #[Test]
    public function shareholder_increment_decrement(): void
    {
        $repo = app(ShareholderRepositoryInterface::class);
        $v = $this->createVentureId();
        $invId = $this->createInvestmentId($v);
        $s = $repo->save(new Shareholder(ventureId: $v, userId: $this->user->id, totalInvestment: 5000, sharesOwned: 500, equityPercentage: 10.0, status: ShareholderStatus::active(), investmentId: $invId, certificateNumber: 'CERT-A', registrationDate: new \DateTimeImmutable('2026-01-01')));

        $repo->decrementShares($s->id, 100);
        $repo->decrementInvestment($s->id, 1000);
        $found = $repo->findById($s->id);
        $this->assertSame(400.0, $found->sharesOwned);
        $this->assertSame(4000.0, $found->totalInvestment);

        $repo->incrementShares($s->id, 200);
        $repo->incrementInvestment($s->id, 2000);
        $found = $repo->findById($s->id);
        $this->assertSame(600.0, $found->sharesOwned);
        $this->assertSame(6000.0, $found->totalInvestment);

        $repo->updateEquity($s->id, 15.0);
        $found = $repo->findById($s->id);
        $this->assertSame(15.0, $found->equityPercentage);
    }

    #[Test]
    public function shareholder_getTotalSharesByVenture(): void
    {
        $repo = app(ShareholderRepositoryInterface::class);
        $v = $this->createVentureId();
        $inv1 = $this->createInvestmentId($v);
        $inv2 = $this->createInvestmentId($v);

        $repo->save(new Shareholder(ventureId: $v, userId: $this->user->id, sharesOwned: 500, status: ShareholderStatus::active(), investmentId: $inv1, totalInvestment: 5000, equityPercentage: 5.0, certificateNumber: 'CERT-A', registrationDate: new \DateTimeImmutable('2026-01-01')));
        $repo->save(new Shareholder(ventureId: $v, userId: $this->otherUser->id, sharesOwned: 300, status: ShareholderStatus::active(), investmentId: $inv2, totalInvestment: 3000, equityPercentage: 3.0, certificateNumber: 'CERT-B', registrationDate: new \DateTimeImmutable('2026-01-01')));

        $this->assertSame(800.0, $repo->getTotalSharesByVenture($v));
    }

    // ————— DIVIDEND —————

    #[Test]
    public function dividend_crud(): void
    {
        $repo = app(DividendRepositoryInterface::class);
        $v = $this->createVentureId();
        $sh = $this->createShareholderId($v);

        $d = $repo->create([
            'venture_id' => $v,
            'shareholder_id' => $sh,
            'dividend_period' => 'Q1-2026',
            'declaration_date' => '2026-01-15',
            'payment_date' => '2026-01-31',
            'equity_percentage_at_payment' => 10.0,
            'amount' => 500,
            'status' => 'declared',
        ]);

        $this->assertNotNull($d->id);
        $this->assertSame('declared', $d->status->value());

        $found = $repo->findById($d->id);
        $this->assertNotNull($found);
        $this->assertSame(500.0, $found->amount);
    }

    #[Test]
    public function dividend_updateStatus(): void
    {
        $repo = app(DividendRepositoryInterface::class);
        $v = $this->createVentureId();
        $sh = $this->createShareholderId($v);
        $d = $repo->create(['venture_id' => $v, 'shareholder_id' => $sh, 'amount' => 100, 'status' => 'declared', 'dividend_period' => 'Q1-2026', 'declaration_date' => '2026-01-15', 'payment_date' => '2026-01-31', 'equity_percentage_at_payment' => 5.0]);

        $repo->updateStatus($d->id, 'paid', ['payment_method' => 'wallet']);
        $found = $repo->findById($d->id);
        $this->assertSame('paid', $found->status->value());
    }

    #[Test]
    public function dividend_findByVenture_and_shareholder(): void
    {
        $repo = app(DividendRepositoryInterface::class);
        $v = $this->createVentureId();
        $sh = $this->createShareholderId($v);

        $repo->create(['venture_id' => $v, 'shareholder_id' => $sh, 'amount' => 100, 'status' => 'declared', 'dividend_period' => 'Q1-2026', 'declaration_date' => '2026-01-15', 'payment_date' => '2026-01-31', 'equity_percentage_at_payment' => 5.0]);
        $repo->create(['venture_id' => $v, 'shareholder_id' => $sh, 'amount' => 200, 'status' => 'paid', 'dividend_period' => 'Q2-2026', 'declaration_date' => '2026-04-15', 'payment_date' => '2026-04-30', 'equity_percentage_at_payment' => 5.0]);

        $this->assertCount(2, $repo->findByVenture($v));
        $this->assertCount(2, $repo->findByShareholder($sh));
    }

    // ————— DOCUMENT —————

    #[Test]
    public function document_crud(): void
    {
        $repo = app(DocumentRepositoryInterface::class);
        $v = $this->createVentureId();

        $d = new Document(
            ventureId: $v,
            title: 'Business Plan',
            filePath: 'docs/plan.pdf',
            visibility: 'public',
            uploadedBy: $this->user->id,
            type: 'other',
            fileName: 'plan.pdf',
            fileType: 'application/pdf',
            fileSize: 1024,
        );

        $saved = $repo->save($d);
        $this->assertNotNull($saved->id);
        $this->assertTrue($saved->isPublic());

        $found = $repo->findById($saved->id);
        $this->assertNotNull($found);
        $this->assertSame('Business Plan', $found->title);

        $repo->delete($saved->id);
        $this->assertNull($repo->findById($saved->id));
    }

    #[Test]
    public function document_findByVenture(): void
    {
        $repo = app(DocumentRepositoryInterface::class);
        $v = $this->createVentureId();

        $repo->save(new Document(ventureId: $v, title: 'Public', filePath: 'a', visibility: 'public', uploadedBy: 1, type: 'other', fileName: 'a.pdf', fileType: 'pdf', fileSize: 100));
        $repo->save(new Document(ventureId: $v, title: 'Admin', filePath: 'b', visibility: 'admin_only', uploadedBy: 1, type: 'other', fileName: 'b.pdf', fileType: 'pdf', fileSize: 200));

        $all = $repo->findByVenture($v);
        $this->assertCount(2, $all);

        $public = $repo->findByVenture($v, 'public');
        $this->assertCount(1, $public);
    }

    // ————— RESOLUTION —————

    #[Test]
    public function resolution_crud(): void
    {
        $repo = app(ResolutionRepositoryInterface::class);
        $v = $this->createVentureId();

        $r = new Resolution(
            ventureId: $v,
            title: 'Expand Operations',
            description: 'desc',
            status: ResolutionStatus::draft(),
            createdBy: $this->user->id,
        );

        $saved = $repo->save($r);
        $this->assertNotNull($saved->id);

        $found = $repo->findById($saved->id);
        $this->assertNotNull($found);
        $this->assertSame('Expand Operations', $found->title);
    }

    #[Test]
    public function resolution_findByVenture(): void
    {
        $repo = app(ResolutionRepositoryInterface::class);
        $v = $this->createVentureId();

        $repo->save(new Resolution(ventureId: $v, title: 'A', description: 'desc', status: ResolutionStatus::draft(), createdBy: 1));
        $repo->save(new Resolution(ventureId: $v, title: 'B', description: 'desc', status: ResolutionStatus::voting(), createdBy: 1));

        $this->assertCount(2, $repo->findByVenture($v));
    }

    #[Test]
    public function resolution_updateStatus(): void
    {
        $repo = app(ResolutionRepositoryInterface::class);
        $v = $this->createVentureId();
        $r = $repo->save(new Resolution(ventureId: $v, title: 'T', description: 'desc', status: ResolutionStatus::draft(), createdBy: $this->user->id));

        $repo->updateStatus($r->id, 'voting');
        $found = $repo->findById($r->id);
        $this->assertSame('voting', $found->status->value());

        $repo->incrementVote($r->id, 'votes_for', 50.0);
        $found = $repo->findById($r->id);
        $this->assertSame(50.0, $found->votesFor);
    }

    // ————— SHARE TRANSFER —————

    #[Test]
    public function share_transfer_crud(): void
    {
        $repo = app(ShareTransferRepositoryInterface::class);
        $v = $this->createVentureId();

        $t = new ShareTransfer(
            ventureId: $v,
            fromUserId: $this->user->id,
            toUserId: $this->otherUser->id,
            shares: 100,
            status: TransferStatus::pending(),
        );

        $saved = $repo->save($t);
        $this->assertNotNull($saved->id);
        $this->assertTrue($saved->isPending());

        $found = $repo->findById($saved->id);
        $this->assertNotNull($found);
    }

    #[Test]
    public function share_transfer_findByVenture_and_pending(): void
    {
        $repo = app(ShareTransferRepositoryInterface::class);
        $v = $this->createVentureId();

        $repo->save(new ShareTransfer(ventureId: $v, fromUserId: 1, toUserId: 2, shares: 50, status: TransferStatus::pending()));
        $repo->save(new ShareTransfer(ventureId: $v, fromUserId: 1, toUserId: 3, shares: 30, status: TransferStatus::approved()));

        $this->assertCount(2, $repo->findByVenture($v));
        $this->assertCount(1, $repo->findPending());
    }

    #[Test]
    public function share_transfer_updateStatus(): void
    {
        $repo = app(ShareTransferRepositoryInterface::class);
        $v = $this->createVentureId();
        $t = $repo->save(new ShareTransfer(ventureId: $v, fromUserId: 1, toUserId: 2, shares: 50, status: TransferStatus::pending()));

        $repo->updateStatus($t->id, 'approved', ['approved_by' => $this->admin->id]);
        $found = $repo->findById($t->id);
        $this->assertSame('approved', $found->status->value());
    }

    // ————— UPDATE —————

    #[Test]
    public function update_crud(): void
    {
        $repo = app(UpdateRepositoryInterface::class);
        $v = $this->createVentureId();

        $u = new Update(
            ventureId: $v,
            title: 'Milestone Update',
            content: 'content',
            visibility: 'public',
            postedBy: $this->user->id,
        );

        $saved = $repo->save($u);
        $this->assertNotNull($saved->id);
        $this->assertTrue($saved->isDraft());

        $found = $repo->findById($saved->id);
        $this->assertNotNull($found);

        $repo->delete($saved->id);
        $this->assertNull($repo->findById($saved->id));
    }

    #[Test]
    public function update_findByVenture(): void
    {
        $repo = app(UpdateRepositoryInterface::class);
        $v = $this->createVentureId();

        $published = new Update(ventureId: $v, title: 'Pub', content: 'content', visibility: 'public', postedBy: 1, publishedAt: new \DateTimeImmutable('-1 hour'));
        $draft = new Update(ventureId: $v, title: 'Draft', content: 'content', visibility: 'public', postedBy: 1);

        $repo->save($published);
        $repo->save($draft);

        $publishedOnly = $repo->findByVenture($v, true);
        $this->assertCount(1, $publishedOnly);

        $all = $repo->findByVenture($v, false);
        $this->assertCount(2, $all);
    }

    // ————— VOTE —————

    #[Test]
    public function vote_crud(): void
    {
        $repo = app(VoteRepositoryInterface::class);
        $v = $this->createVentureId();
        $r = $this->createResolutionId($v);
        $sh = $this->createShareholderId($v);

        $vote = new Vote(
            resolutionId: $r,
            shareholderId: $sh,
            userId: $this->user->id,
            vote: 'for',
            equityAtVote: 5.0,
        );

        $saved = $repo->save($vote);
        $this->assertNotNull($saved->id);
        $this->assertSame('for', $saved->vote);

        $found = $repo->findByResolutionAndShareholder($r, $sh);
        $this->assertNotNull($found);
        $this->assertSame($saved->id, $found->id);

        $notFound = $repo->findByResolutionAndShareholder(999, 999);
        $this->assertNull($notFound);
    }

    // ————— HELPERS —————

    private function createVentureId(): int
    {
        $repo = app(VentureRepositoryInterface::class);
        $id = uniqid();
        $v = $repo->save(Venture::reconstitute([
            'category_id' => $this->categoryId,
            'title' => "Venture {$id}",
            'slug' => "v-{$id}",
            'description' => 'A test venture',
            'status' => 'funding',
            'funding_target' => 100000,
            'created_by' => $this->user->id,
        ]));
        return $v->id;
    }

    private function createInvestmentId(int $ventureId): int
    {
        $repo = app(InvestmentRepositoryInterface::class);
        $i = $repo->save(new Investment(
            ventureId: $ventureId,
            userId: $this->user->id,
            amount: 5000,
            status: InvestmentStatus::confirmed(),
        ));
        return $i->id;
    }

    private function createShareholderId(int $ventureId): int
    {
        $invId = $this->createInvestmentId($ventureId);
        $repo = app(ShareholderRepositoryInterface::class);
        $sh = $repo->save(new Shareholder(
            ventureId: $ventureId,
            userId: $this->user->id,
            totalInvestment: 5000,
            sharesOwned: 500,
            equityPercentage: 10.0,
            status: ShareholderStatus::active(),
            investmentId: $invId,
            certificateNumber: 'CERT-' . uniqid(),
            registrationDate: new \DateTimeImmutable('2026-01-01'),
        ));
        return $sh->id;
    }

    private function createResolutionId(int $ventureId): int
    {
        $repo = app(ResolutionRepositoryInterface::class);
        $r = $repo->save(new Resolution(
            ventureId: $ventureId,
            title: 'Test Resolution',
            description: 'desc',
            status: ResolutionStatus::voting(),
            createdBy: $this->user->id,
        ));
        return $r->id;
    }
}
