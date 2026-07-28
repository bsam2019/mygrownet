<?php

declare(strict_types=1);

namespace App\Domain\GrowFinance\Services;

use App\Domain\Core\Services\OutboxService;
use App\Domain\GrowFinance\Entities\BankConnection;
use App\Domain\GrowFinance\Repositories\AccountRepositoryInterface;
use App\Domain\GrowFinance\Repositories\BankConnectionRepositoryInterface;
use App\Domain\GrowFinance\Repositories\JournalEntryRepositoryInterface;
use App\Domain\GrowFinance\Repositories\JournalLineRepositoryInterface;
use App\Domain\GrowFinance\ValueObjects\JournalStatus;
use DateTimeImmutable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class BankIntegrationService
{
    public function __construct(
        private BankConnectionRepositoryInterface $connectionRepo,
        private AccountRepositoryInterface $accountRepo,
        private JournalEntryRepositoryInterface $journalEntryRepo,
        private JournalLineRepositoryInterface $journalLineRepo,
        private readonly OutboxService $outbox,
    ) {}

    public function connect(int $businessId, string $bankName, string $accountName, string $accountNumber, string $connectionType, array $credentials = []): BankConnection
    {
        $existing = $this->connectionRepo->findByBusiness($businessId);
        foreach ($existing as $conn) {
            if ($conn->accountNumber === $accountNumber) {
                throw new \RuntimeException("Bank account {$accountNumber} is already connected");
            }
        }

        $connection = new BankConnection(
            id: null,
            businessId: $businessId,
            bankName: $bankName,
            accountName: $accountName,
            accountNumber: $accountNumber,
            connectionType: $connectionType,
            status: 'active',
            credentials: $credentials,
        );

        return $this->connectionRepo->save($connection);
    }

    public function disconnect(int $connectionId): void
    {
        $this->connectionRepo->delete($connectionId);
    }

    public function syncTransactions(int $connectionId, ?int $growFinanceAccountId = null): array
    {
        $connection = $this->connectionRepo->findById($connectionId);
        if (!$connection) {
            throw new \RuntimeException("Bank connection not found");
        }
        if ($connection->status !== 'active') {
            throw new \RuntimeException("Bank connection is not active");
        }

        $transactions = $this->fetchTransactions($connection);
        $imported = 0;

        foreach ($transactions as $txn) {
            try {
                $this->importTransaction($connection->businessId, $growFinanceAccountId, $txn);
                $imported++;
            } catch (\Throwable $e) {
                Log::warning("Failed to import bank transaction", [
                    'connection_id' => $connectionId,
                    'txn_ref' => $txn['reference'] ?? 'unknown',
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return ['imported' => $imported, 'total' => count($transactions)];
    }

    private function fetchTransactions(BankConnection $connection): array
    {
        return match ($connection->connectionType) {
            'zatca' => $this->fetchZatca($connection),
            'csv' => [],
            default => $this->fetchGenericApi($connection),
        };
    }

    private function fetchZatca(BankConnection $connection): array
    {
        $credentials = $connection->credentials ?? [];
        $apiKey = $credentials['api_key'] ?? '';
        $baseUrl = $credentials['base_url'] ?? 'https://api.zatca.gov.zm';

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'Accept' => 'application/json',
            ])->get($baseUrl . '/api/v1/transactions', [
                'account' => $connection->accountNumber,
                'from_date' => $connection->lastSyncAt ?? now()->subDays(30)->format('Y-m-d'),
            ]);

            if ($response->successful()) {
                return $response->json()['data'] ?? [];
            }

            Log::warning("ZATCA API returned error", [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);
            return [];
        } catch (\Throwable $e) {
            Log::error("ZATCA API request failed", ['error' => $e->getMessage()]);
            return [];
        }
    }

    private function fetchGenericApi(BankConnection $connection): array
    {
        $credentials = $connection->credentials ?? [];
        $apiUrl = $credentials['api_url'] ?? '';
        $apiKey = $credentials['api_key'] ?? '';

        if (empty($apiUrl)) {
            return [];
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'Accept' => 'application/json',
            ])->get($apiUrl . '/transactions', [
                'account' => $connection->accountNumber,
                'since' => $connection->lastSyncAt ?? now()->subDays(30)->format('Y-m-d'),
            ]);

            if ($response->successful()) {
                return $response->json()['data'] ?? $response->json()['transactions'] ?? [];
            }

            return [];
        } catch (\Throwable $e) {
            Log::error("Bank API request failed", ['error' => $e->getMessage()]);
            return [];
        }
    }

    private function importTransaction(int $businessId, ?int $growFinanceAccountId, array $txn): void
    {
        $reference = $txn['reference'] ?? $txn['transaction_id'] ?? 'unknown';
        $description = $txn['description'] ?? 'Bank import';
        $amount = (float) ($txn['amount'] ?? 0);
        $date = $txn['date'] ?? $txn['transaction_date'] ?? now()->format('Y-m-d');
        $type = $txn['type'] ?? ($amount >= 0 ? 'credit' : 'debit');
        $absAmount = abs($amount);

        $existing = $this->journalEntryRepo->findByReference($businessId, 'BANK-' . $reference);
        if ($existing !== null) {
            return;
        }

        $accountId = $growFinanceAccountId;
        if (!$accountId) {
            $bankAccount = $this->accountRepo->findByCode($businessId, '1120');
            if (!$bankAccount) {
                throw new \RuntimeException("No bank account (1120) found for business {$businessId}");
            }
            $accountId = $bankAccount->id;
        }

        $isCredit = strtolower($type) === 'credit' || $amount > 0;

        DB::transaction(function () use ($businessId, $accountId, $absAmount, $description, $reference, $date, $isCredit) {
            $journalNumber = 'BANK-' . $reference;
            $entry = $this->journalEntryRepo->save(new \App\Domain\GrowFinance\Entities\JournalEntry(
                id: null,
                businessId: $businessId,
                journalNumber: $journalNumber,
                date: new DateTimeImmutable($date),
                description: 'Bank import: ' . $description,
                reference: $reference,
                status: JournalStatus::DRAFT,
                createdBy: null,
            ));

            $suspenseAccount = $this->accountRepo->findByCode($businessId, '2200');
            $suspenseId = $suspenseAccount ? $suspenseAccount->id : $accountId;

            if ($isCredit) {
                $this->journalLineRepo->save(new \App\Domain\GrowFinance\Entities\JournalLine(
                    id: null, journalEntryId: $entry->id,
                    accountId: $accountId, debitAmount: $absAmount, creditAmount: 0,
                    description: $description,
                ));
                $this->journalLineRepo->save(new \App\Domain\GrowFinance\Entities\JournalLine(
                    id: null, journalEntryId: $entry->id,
                    accountId: $suspenseId, debitAmount: 0, creditAmount: $absAmount,
                    description: 'Suspense - ' . $description,
                ));
            } else {
                $this->journalLineRepo->save(new \App\Domain\GrowFinance\Entities\JournalLine(
                    id: null, journalEntryId: $entry->id,
                    accountId: $suspenseId, debitAmount: $absAmount, creditAmount: 0,
                    description: 'Suspense - ' . $description,
                ));
                $this->journalLineRepo->save(new \App\Domain\GrowFinance\Entities\JournalLine(
                    id: null, journalEntryId: $entry->id,
                    accountId: $accountId, debitAmount: 0, creditAmount: $absAmount,
                    description: $description,
                ));
            }

            $this->outbox->insert(
                eventName: 'growfinance.bank.transaction.imported.v1',
                payload: [
                    'business_id' => $businessId,
                    'reference' => $reference,
                    'amount' => $absAmount,
                    'type' => $isCredit ? 'credit' : 'debit',
                    'description' => $description,
                    'date' => $date,
                ],
                context: ['business_id' => $businessId],
                publisher: 'growfinance',
            );
        });
    }

    public function getConnections(int $businessId): array
    {
        return array_map(fn($c) => $c->toArray(), $this->connectionRepo->findByBusiness($businessId));
    }

    public function getActiveConnections(int $businessId): array
    {
        return array_map(fn($c) => $c->toArray(), $this->connectionRepo->findActiveByBusiness($businessId));
    }
}
