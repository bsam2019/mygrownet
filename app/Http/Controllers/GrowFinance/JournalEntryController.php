<?php

namespace App\Http\Controllers\GrowFinance;

use App\Domain\GrowFinance\Services\AccountingService;
use App\Domain\GrowFinance\Services\PostingEngine;
use App\Domain\GrowFinance\Repositories\JournalEntryRepositoryInterface;
use App\Domain\GrowFinance\Repositories\JournalLineRepositoryInterface;
use App\Domain\GrowFinance\Repositories\AccountRepositoryInterface;
use App\Domain\GrowFinance\Repositories\AccountingPeriodRepositoryInterface;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class JournalEntryController extends Controller
{
    public function __construct(
        private AccountingService $accountingService,
        private PostingEngine $postingEngine,
        private JournalEntryRepositoryInterface $journalEntryRepo,
        private JournalLineRepositoryInterface $journalLineRepo,
        private AccountRepositoryInterface $accountRepo,
        private AccountingPeriodRepositoryInterface $periodRepo,
    ) {}

    public function index(Request $request): Response
    {
        $businessId = $request->user()->id;
        $status = $request->query('status');
        $periodId = $request->query('period_id');

        if ($status) {
            $entries = $this->journalEntryRepo->findByStatus($businessId, $status);
        } else {
            $entries = $this->journalEntryRepo->findByBusiness($businessId);
        }

        $periods = $this->periodRepo->findByBusiness($businessId);

        return Inertia::render('GrowFinance/Journals/Index', [
            'entries' => array_map(fn($e) => $e->toArray(), $entries),
            'periods' => array_map(fn($p) => $p->toArray(), $periods),
            'filters' => ['status' => $status, 'period_id' => $periodId],
        ]);
    }

    public function create(Request $request): Response
    {
        $businessId = $request->user()->id;
        $accounts = $this->accountRepo->findActive($businessId);
        $period = $this->periodRepo->findCurrent($businessId);

        return Inertia::render('GrowFinance/Journals/Create', [
            'accounts' => array_map(fn($a) => $a->toArray(), $accounts),
            'currentPeriod' => $period?->toArray(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $businessId = $request->user()->id;

        $validated = $request->validate([
            'description' => 'required|string|max:500',
            'reference' => 'nullable|string|max:100',
            'date' => 'required|date',
            'currency_code' => 'nullable|string|size:3',
            'exchange_rate' => 'nullable|numeric|min:0.0001|max:999999',
            'lines' => 'required|array|min:2',
            'lines.*.account_id' => 'required|integer|exists:growfinance_accounts,id',
            'lines.*.debit_amount' => 'required|numeric|min:0',
            'lines.*.credit_amount' => 'required|numeric|min:0',
            'lines.*.description' => 'nullable|string|max:255',
        ]);

        $entry = $this->accountingService->createJournalEntry(
            businessId: $businessId,
            description: $validated['description'],
            lines: $validated['lines'],
            reference: $validated['reference'] ?? null,
            createdBy: $request->user()->id,
            date: new \DateTimeImmutable($validated['date']),
            currencyCode: $validated['currency_code'] ?? 'ZMW',
            exchangeRate: (float) ($validated['exchange_rate'] ?? 1.0),
        );

        return redirect()->route('growfinance.journals.show', ['journal' => $entry['id']]);
    }

    public function show(Request $request, int $id): Response
    {
        $entry = $this->journalEntryRepo->findById($id);
        if (!$entry) {
            abort(404);
        }

        $lines = $this->journalLineRepo->findByJournalEntry($id);
        $accounts = $this->accountRepo->findActive($request->user()->id);
        $accountMap = [];
        foreach ($accounts as $a) {
            $accountMap[$a->id] = $a->toArray();
        }

        return Inertia::render('GrowFinance/Journals/Show', [
            'entry' => $entry->toArray(),
            'lines' => array_map(function ($l) use ($accountMap) {
                $data = $l->toArray();
                $data['account'] = $accountMap[$l->accountId] ?? null;
                return $data;
            }, $lines),
        ]);
    }

    public function post(Request $request, int $id): RedirectResponse
    {
        try {
            $this->postingEngine->post($id);
            return redirect()->route('growfinance.journals.show', ['journal' => $id])
                ->with('success', 'Journal entry posted successfully.');
        } catch (\DomainException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    public function reverse(Request $request, int $id): RedirectResponse
    {
        $validated = $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        try {
            $this->postingEngine->reverse($id, $validated['reason']);
            return redirect()->route('growfinance.journals.show', ['journal' => $id])
                ->with('success', 'Journal entry reversed.');
        } catch (\DomainException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }
}
