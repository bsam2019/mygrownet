<?php
declare(strict_types=1);

namespace App\Domain\GrowFinance\Services;

use App\Domain\GrowFinance\Repositories\CustomerRepositoryInterface;
use App\Domain\GrowFinance\Repositories\InvoiceItemRepositoryInterface;
use App\Domain\GrowFinance\Repositories\InvoiceRepositoryInterface;
use App\Domain\GrowFinance\Repositories\TaxRateRepositoryInterface;
use DateTimeImmutable;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ZraEInvoiceService
{
    private string $baseUrl;
    private ?string $token = null;

    public function __construct(
        private InvoiceRepositoryInterface $invoiceRepo,
        private InvoiceItemRepositoryInterface $invoiceItemRepo,
        private CustomerRepositoryInterface $customerRepo,
        private TaxRateRepositoryInterface $taxRateRepo,
    ) {
        $this->baseUrl = config('services.zra.base_url', 'https://api.zra.gov.zm/smartinvoice/v1');
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

            Log::error('ZRA auth failed', ['response' => $response->body()]);
            return false;
        } catch (\Throwable $e) {
            Log::error('ZRA auth error', ['message' => $e->getMessage()]);
            return false;
        }
    }

    public function submitInvoice(int $invoiceId): array
    {
        $invoice = $this->invoiceRepo->findById($invoiceId);
        if (!$invoice) {
            throw new \RuntimeException("Invoice #{$invoiceId} not found");
        }

        if (!$this->token && !$this->authenticate()) {
            throw new \RuntimeException('Failed to authenticate with ZRA');
        }

        $payload = $this->buildInvoicePayload($invoice);

        try {
            $response = Http::withToken($this->token)
                ->post($this->baseUrl . '/invoices', $payload);

            if ($response->successful()) {
                $data = $response->json();
                return [
                    'success' => true,
                    'zra_reference' => $data['reference'] ?? null,
                    'qr_code_url' => $data['qr_code_url'] ?? null,
                    'validation_code' => $data['validation_code'] ?? null,
                    'raw_response' => $data,
                ];
            }

            Log::error('ZRA invoice submission failed', [
                'invoice_id' => $invoiceId,
                'response' => $response->body(),
            ]);

            return [
                'success' => false,
                'error' => $response->json('message') ?? 'ZRA submission failed',
                'raw_response' => $response->json(),
            ];
        } catch (\Throwable $e) {
            Log::error('ZRA invoice submission error', [
                'invoice_id' => $invoiceId,
                'message' => $e->getMessage(),
            ]);
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function verifyInvoice(string $zraReference): array
    {
        if (!$this->token && !$this->authenticate()) {
            throw new \RuntimeException('Failed to authenticate with ZRA');
        }

        try {
            $response = Http::withToken($this->token)
                ->get($this->baseUrl . '/invoices/' . $zraReference . '/verify');

            if ($response->successful()) {
                return [
                    'verified' => true,
                    'data' => $response->json(),
                ];
            }

            return [
                'verified' => false,
                'error' => $response->json('message') ?? 'Verification failed',
            ];
        } catch (\Throwable $e) {
            return ['verified' => false, 'error' => $e->getMessage()];
        }
    }

    private function buildInvoicePayload($invoice): array
    {
        $items = [];
        $invoiceItems = $this->invoiceItemRepo->findByInvoice($invoice->id);

        foreach ($invoiceItems as $item) {
            $lineTotal = $item->quantity * $item->unitPrice;
            $vatRate = $item->taxRate > 0 ? $item->taxRate : 16.0;
            $vatAmount = $lineTotal * ($vatRate / 100);

            $items[] = [
                'description' => $item->description ?? 'Item',
                'quantity' => $item->quantity,
                'unit_price' => round($item->unitPrice, 2),
                'line_total' => round($lineTotal, 2),
                'tax_rate' => $vatRate,
                'tax_amount' => round($vatAmount, 2),
                'total' => round($lineTotal + $vatAmount, 2),
            ];
        }

        // If no items found, use invoice-level totals
        if (empty($items)) {
            $items[] = [
                'description' => 'Invoice items',
                'quantity' => 1,
                'unit_price' => round($invoice->subtotal, 2),
                'line_total' => round($invoice->subtotal, 2),
                'tax_rate' => $invoice->taxAmount > 0 && $invoice->subtotal > 0
                    ? round(($invoice->taxAmount / $invoice->subtotal) * 100, 2)
                    : 16.0,
                'tax_amount' => round($invoice->taxAmount, 2),
                'total' => round($invoice->totalAmount, 2),
            ];
        }

        // Resolve customer details
        $customerName = 'Walk-in Customer';
        $customerTin = null;
        $customerAddress = null;
        if ($invoice->customerId) {
            $customer = $this->customerRepo->findById($invoice->customerId);
            if ($customer) {
                $customerName = $customer->name;
                $customerAddress = $customer->address;
                $customerTin = $customer->taxNumber;
            }
        }

        return [
            'invoice_number' => $invoice->invoiceNumber ?? 'INV-' . $invoice->id,
            'invoice_date' => $invoice->invoiceDate?->format('Y-m-d') ?? date('Y-m-d'),
            'currency' => 'ZMW',
            'customer' => [
                'name' => $customerName,
                'tin' => $customerTin,
                'address' => $customerAddress,
            ],
            'items' => $items,
            'subtotal' => round($invoice->subtotal, 2),
            'total_tax' => round($invoice->taxAmount, 2),
            'grand_total' => round($invoice->totalAmount, 2),
            'notes' => $invoice->notes,
        ];
    }

    public function healthCheck(): array
    {
        try {
            $response = Http::timeout(5)->get($this->baseUrl . '/health');
            return ['reachable' => $response->successful()];
        } catch (\Throwable $e) {
            return ['reachable' => false, 'error' => $e->getMessage()];
        }
    }
}
