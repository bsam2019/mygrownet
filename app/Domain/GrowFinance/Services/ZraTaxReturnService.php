<?php
declare(strict_types=1);

namespace App\Domain\GrowFinance\Services;

use App\Domain\GrowFinance\Repositories\TaxReturnRepositoryInterface;
use DateTimeImmutable;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ZraTaxReturnService
{
    private string $baseUrl;
    private ?string $token = null;

    public function __construct(
        private TaxEngine $taxEngine,
        private TaxReturnRepositoryInterface $taxReturnRepo,
    ) {
        $this->baseUrl = config('services.zra.base_url', 'https://api.zra.gov.zm/taxreturns/v1');
    }

    public function authenticate(): bool
    {
        $tpin = config('services.zra.tpin');
        $password = config('services.zra.password');

        if (empty($tpin) || empty($password)) {
            Log::warning('ZRA credentials not configured');
            return false;
        }

        try {
            $response = Http::post($this->baseUrl . '/auth/token', [
                'tpin' => $tpin,
                'password' => $password,
            ]);

            if ($response->successful()) {
                $this->token = $response->json('token');
                return true;
            }

            Log::error('ZRA tax return auth failed', ['response' => $response->body()]);
            return false;
        } catch (\Throwable $e) {
            Log::error('ZRA tax return auth error', ['message' => $e->getMessage()]);
            return false;
        }
    }

    public function submitVatReturn(int $businessId, string $period): array
    {
        // YYYY-MM format — compute period start/end
        $fromStr = $period . '-01';
        $from = DateTimeImmutable::createFromFormat('Y-m-d', $fromStr);
        if (!$from) {
            return ['success' => false, 'error' => 'Invalid period format. Use YYYY-MM.'];
        }
        $to = $from->modify('last day of this month 23:59:59');

        $periodStart = $from->format('Y-m-d');
        $periodEnd = $to->format('Y-m-d');

        // Get VAT return data from TaxEngine
        $vatData = $this->taxEngine->getVatReturn($businessId, $periodStart, $periodEnd);

        if (!$this->token && !$this->authenticate()) {
            throw new \RuntimeException('Failed to authenticate with ZRA');
        }

        $payload = [
            'tax_period' => $period,
            'submission_type' => 'ORIGINAL',
            'tpin' => config('services.zra.tpin'),
            'business_type' => 'VAT',
            'returns' => [
                'output_vat' => round($vatData['output_vat'] ?? 0, 2),
                'input_vat' => round($vatData['input_vat'] ?? 0, 2),
                'net_vat_payable' => round($vatData['net_vat_payable'] ?? 0, 2),
                'total_sales' => round($vatData['total_sales'] ?? 0, 2),
                'total_purchases' => round($vatData['total_purchases'] ?? 0, 2),
            ],
            'declaration' => [
                'declared_by' => 'System',
                'declaration_date' => date('Y-m-d'),
                'is_correct' => true,
            ],
        ];

        try {
            $response = Http::withToken($this->token)
                ->post($this->baseUrl . '/returns/vat', $payload);

            if ($response->successful()) {
                $data = $response->json();
                $zraRef = $data['submission_reference'] ?? null;

                // Save tax return via TaxEngine, then update with ZRA reference
                $saved = $this->taxEngine->saveTaxReturn(
                    $businessId,
                    'vat',
                    $periodStart,
                    $periodEnd,
                );

                // The saveTaxReturn returns a TaxReturn entity saved as DRAFT.
                // We update its status and ZRA reference via the repository.
                $updated = new \App\Domain\GrowFinance\Entities\TaxReturn(
                    id: $saved->id,
                    businessId: $saved->businessId,
                    returnType: $saved->returnType,
                    periodLabel: $saved->periodLabel,
                    periodStart: $saved->periodStart,
                    periodEnd: $saved->periodEnd,
                    dueDate: $saved->dueDate,
                    outputVat: $saved->outputVat,
                    inputVat: $saved->inputVat,
                    netVatPayable: $saved->netVatPayable,
                    totalSales: $saved->totalSales,
                    totalPurchases: $saved->totalPurchases,
                    withholdingCollected: $saved->withholdingCollected,
                    withholdingPaid: $saved->withholdingPaid,
                    status: \App\Domain\GrowFinance\ValueObjects\TaxReturnStatus::SUBMITTED,
                    filedAt: new DateTimeImmutable('now'),
                    zraReference: $zraRef,
                    submittedAt: new DateTimeImmutable('now'),
                    notes: $saved->notes,
                    createdAt: $saved->createdAt,
                    updatedAt: new DateTimeImmutable('now'),
                );
                $this->taxReturnRepo->save($updated);

                return [
                    'success' => true,
                    'submission_reference' => $zraRef,
                    'message' => 'VAT return submitted successfully',
                    'raw_response' => $data,
                ];
            }

            Log::error('ZRA VAT return submission failed', [
                'business_id' => $businessId,
                'period' => $period,
                'response' => $response->body(),
            ]);

            return [
                'success' => false,
                'error' => $response->json('message') ?? 'Submission failed',
                'raw_response' => $response->json(),
            ];
        } catch (\Throwable $e) {
            Log::error('ZRA VAT return submission error', [
                'business_id' => $businessId,
                'period' => $period,
                'message' => $e->getMessage(),
            ]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function checkSubmissionStatus(string $submissionReference): array
    {
        if (!$this->token && !$this->authenticate()) {
            throw new \RuntimeException('Failed to authenticate with ZRA');
        }

        try {
            $response = Http::withToken($this->token)
                ->get($this->baseUrl . '/returns/status/' . $submissionReference);

            if ($response->successful()) {
                return [
                    'found' => true,
                    'status' => $response->json('status'),
                    'data' => $response->json(),
                ];
            }

            return ['found' => false, 'error' => $response->json('message') ?? 'Not found'];
        } catch (\Throwable $e) {
            return ['found' => false, 'error' => $e->getMessage()];
        }
    }

    public function getFilingCalendar(): array
    {
        if (!$this->token && !$this->authenticate()) {
            throw new \RuntimeException('Failed to authenticate with ZRA');
        }

        try {
            $response = Http::withToken($this->token)
                ->get($this->baseUrl . '/returns/calendar');

            if ($response->successful()) {
                return $response->json();
            }

            return [];
        } catch (\Throwable $e) {
            Log::error('Failed to fetch ZRA filing calendar', ['message' => $e->getMessage()]);
            return [];
        }
    }
}
