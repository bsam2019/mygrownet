<?php

namespace App\Http\Controllers\GrowFinance;

use App\Domain\GrowFinance\Services\AccountingService;
use App\Http\Controllers\Controller;
use DateTimeImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OfflineSyncController extends Controller
{
    public function __construct(
        private AccountingService $accountingService,
    ) {}

    public function syncJournal(Request $request): JsonResponse
    {
        $businessId = $request->user()->id;
        $data = $request->validate([
            'description' => 'required|string',
            'date' => 'required|date',
            'lines' => 'required|array|min:2',
            'lines.*.account_id' => 'required|integer',
            'lines.*.debit_amount' => 'required|numeric|min:0',
            'lines.*.credit_amount' => 'required|numeric|min:0',
            'reference' => 'nullable|string',
        ]);

        $result = $this->accountingService->createJournalEntry(
            businessId: $businessId,
            description: $data['description'],
            lines: $data['lines'],
            reference: $data['reference'] ?? null,
            date: new DateTimeImmutable($data['date']),
            createdBy: $request->user()->id,
        );

        return response()->json($result, 201);
    }

    public function status(): JsonResponse
    {
        return response()->json([
            'server_time' => now()->toIso8601String(),
            'sync_available' => true,
        ]);
    }
}
