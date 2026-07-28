<?php

declare(strict_types=1);

namespace App\Http\Controllers\GrowFinance;

use App\Domain\GrowFinance\Services\AnomalyDetectionService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AnomalyController extends Controller
{
    public function __construct(
        private AnomalyDetectionService $anomalyService,
    ) {}

    public function index(Request $request)
    {
        $businessId = $request->user()->id;
        $from = $request->has('from') ? new \DateTimeImmutable($request->input('from')) : null;
        $to = $request->has('to') ? new \DateTimeImmutable($request->input('to')) : null;
        $results = $this->anomalyService->runAll($businessId, $from, $to);

        $all = array_merge(
            $results['duplicate_invoices'],
            $results['unusual_journal_patterns'],
            $results['out_of_range_amounts'],
            $results['unusual_payment_patterns'],
            $results['missing_reference_journals'],
        );

        $results['summary'] = [
            'total' => count($all),
            'high' => count(array_filter($all, fn($a) => ($a['severity'] ?? 'low') === 'high')),
            'medium' => count(array_filter($all, fn($a) => ($a['severity'] ?? 'low') === 'medium')),
            'low' => count(array_filter($all, fn($a) => ($a['severity'] ?? 'low') === 'low')),
            'by_type' => [
                'duplicate_invoices' => count($results['duplicate_invoices']),
                'unusual_journal_patterns' => count($results['unusual_journal_patterns']),
                'out_of_range_amounts' => count($results['out_of_range_amounts']),
                'unusual_payment_patterns' => count($results['unusual_payment_patterns']),
                'missing_reference_journals' => count($results['missing_reference_journals']),
            ],
        ];

        return Inertia::render('GrowFinance/Reports/Anomalies', [
            'anomalies' => $all,
            'summary' => $results['summary'],
            'filters' => [
                'from' => $request->input('from', date('Y-m-d', strtotime('-90 days'))),
                'to' => $request->input('to', date('Y-m-d')),
            ],
        ]);
    }
}
