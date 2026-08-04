<?php

namespace App\Domain\PlatformPayments\Gateways;

use App\Domain\PlatformPayments\DTOs\PaymentRequest;
use App\Domain\PlatformPayments\DTOs\PaymentResponse;
use App\Domain\PlatformPayments\DTOs\RefundRequest;
use App\Domain\PlatformPayments\DTOs\RefundResponse;
use App\Domain\PlatformPayments\Enums\PaymentStatus;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * PawaPay Merchant API V2 gateway.
 *
 * Reference: https://docs.pawapay.io/v2
 *
 * V2 differences from V1 (all handled here):
 *  - Endpoints are under /v2 (e.g. /v2/deposits).
 *  - depositId / payoutId / refundId MUST be UUIDv4.
 *  - amount is a STRING (e.g. "15" or "10.50"), not a number.
 *  - payer/recipient uses { type: "MMO", accountDetails: { provider, phoneNumber } }.
 *  - Callbacks are signed with RFC-9421 HTTP Message Signatures (ECDSA-P-256-SHA256),
 *    NOT an HMAC "X-Webhook-Signature" header.
 *  - Initiation returns ACCEPTED/REJECTED/DUPLICATE_IGNORED; final status comes via
 *    callback or GET /v2/deposits/{id} which wraps the deposit in { status: FOUND|NOT_FOUND, data: {...} }.
 */
class PawapayGateway extends AbstractPaymentGateway
{
    private string $baseUrl;

    public function __construct(array $credentials, bool $testMode = false)
    {
        parent::__construct($credentials, $testMode);
        $this->baseUrl = $testMode
            ? 'https://api.sandbox.pawapay.io'
            : 'https://api.pawapay.io';
    }

    /**
     * Initiate a deposit. Returns immediately with ACCEPTED/REJECTED; the final
     * status arrives via callback or status polling.
     */
    public function initiatePayment(PaymentRequest $request): PaymentResponse
    {
        try {
            // V2 requires a UUIDv4 depositId (idempotency key). Prefer the provided
            // reference when it is already a UUID; otherwise generate one.
            $depositId = $this->validUuid($request->reference)
                ? $request->reference
                : (string) Str::uuid();

            $payload = [
                'depositId' => $depositId,
                'amount' => $this->formatAmount($request->amount),
                'currency' => strtoupper($request->currency),
                'payer' => [
                    'type' => 'MMO',
                    'accountDetails' => [
                        'provider' => $this->detectProvider($request->phoneNumber),
                        'phoneNumber' => $this->formatPhoneNumber($request->phoneNumber),
                    ],
                ],
                'clientReferenceId' => $request->reference,
            ];

            if ($request->description) {
                $payload['customerMessage'] = $this->formatCustomerMessage($request->description);
            }

            $this->logActivity('Initiating deposit (V2)', $payload);

            $response = Http::withHeaders($this->authHeaders())
                ->withBody(json_encode($payload), 'application/json')
                ->post("{$this->baseUrl}/v2/deposits");

            $body = $response->json() ?? [];
            $initStatus = strtoupper((string) ($body['status'] ?? 'UNKNOWN'));

            // Accepted for processing; final status comes via callback / polling.
            if ($initStatus === 'ACCEPTED' || $initStatus === 'DUPLICATE_IGNORED') {
                return new PaymentResponse(
                    success: true,
                    status: PaymentStatus::PENDING,
                    transactionReference: $depositId,
                    externalReference: $body['depositId'] ?? $depositId,
                    message: 'Payment initiated successfully',
                    rawResponse: $body,
                );
            }

            $failure = $body['failureReason'] ?? [];
            $failureCode = $failure['failureCode'] ?? null;
            $failureMessage = $failure['failureMessage'] ?? ($body['message'] ?? 'Payment initiation failed');

            return new PaymentResponse(
                success: false,
                status: PaymentStatus::FAILED,
                transactionReference: $depositId,
                message: $failureCode ? "[{$failureCode}] {$failureMessage}" : $failureMessage,
                rawResponse: $body,
            );

        } catch (\Exception $e) {
            $this->logError('Payment initiation', $e, $request->toArray());

            return new PaymentResponse(
                success: false,
                status: PaymentStatus::FAILED,
                transactionReference: $request->reference,
                message: $e->getMessage(),
            );
        }
    }

    /**
     * Check the current status of a deposit by its depositId.
     *
     * V2 wraps the result: { "status": "FOUND"|"NOT_FOUND", "data": { "depositId", "status", ... } }
     */
    public function verifyPayment(string $transactionReference): PaymentResponse
    {
        try {
            $response = Http::withHeaders($this->authHeaders())
                ->get("{$this->baseUrl}/v2/deposits/{$transactionReference}");

            if ($response->status() === 404) {
                return new PaymentResponse(
                    success: false,
                    status: PaymentStatus::PENDING,
                    transactionReference: $transactionReference,
                    message: 'Deposit not found (still processing or unknown)',
                    rawResponse: ['status' => 'NOT_FOUND'],
                );
            }

            $body = $response->json() ?? [];
            $searchStatus = strtoupper((string) ($body['status'] ?? 'NOT_FOUND'));

            if ($searchStatus === 'FOUND' && isset($body['data']['status'])) {
                $deposit = $body['data'];
                $status = $this->mapStatus((string) $deposit['status']);

                return new PaymentResponse(
                    success: $status === PaymentStatus::COMPLETED,
                    status: $status,
                    transactionReference: $transactionReference,
                    externalReference: $deposit['depositId'] ?? $transactionReference,
                    message: $status === PaymentStatus::FAILED
                        ? ($deposit['failureReason']['failureCode'] ?? 'Payment failed')
                        : ($status === PaymentStatus::COMPLETED ? 'Payment completed' : 'Payment in progress'),
                    rawResponse: $deposit,
                );
            }

            return new PaymentResponse(
                success: false,
                status: PaymentStatus::PENDING,
                transactionReference: $transactionReference,
                message: 'Deposit not found',
                rawResponse: $body,
            );

        } catch (\Exception $e) {
            $this->logError('Payment verification', $e, ['reference' => $transactionReference]);

            return new PaymentResponse(
                success: false,
                status: PaymentStatus::FAILED,
                transactionReference: $transactionReference,
                message: $e->getMessage(),
            );
        }
    }

    public function refundPayment(RefundRequest $request): RefundResponse
    {
        try {
            $refundId = $this->validUuid($request->refundReference ?? '')
                ? $request->refundReference
                : (string) Str::uuid();

            $payload = [
                'refundId' => $refundId,
                'depositId' => $request->transactionReference,
                'amount' => $this->formatAmount((string) $request->amount),
                'reason' => $request->reason,
            ];

            $response = Http::withHeaders($this->authHeaders())
                ->withBody(json_encode($payload), 'application/json')
                ->post("{$this->baseUrl}/v2/refunds");

            $body = $response->json() ?? [];
            $status = strtoupper((string) ($body['status'] ?? 'REJECTED'));

            if ($status === 'ACCEPTED' || $status === 'DUPLICATE_IGNORED') {
                return new RefundResponse(
                    success: true,
                    refundReference: $refundId,
                    message: 'Refund processed successfully',
                    rawResponse: $body,
                );
            }

            $failure = $body['failureReason'] ?? [];
            $failureCode = $failure['failureCode'] ?? null;
            $failureMessage = $failure['failureMessage'] ?? 'Refund failed';

            return new RefundResponse(
                success: false,
                refundReference: $refundId,
                message: $failureCode ? "[{$failureCode}] {$failureMessage}" : $failureMessage,
                rawResponse: $body,
            );

        } catch (\Exception $e) {
            $this->logError('Refund processing', $e, $request->toArray());

            return new RefundResponse(
                success: false,
                refundReference: '',
                message: $e->getMessage(),
            );
        }
    }

    public function getName(): string
    {
        return 'PawaPay';
    }

    public function validateConfiguration(array $credentials): array
    {
        $errors = [];

        if (empty($credentials['api_token'])) {
            $errors[] = 'API token is required';
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors,
        ];
    }

    public function getRequiredFields(): array
    {
        return [
            [
                'name' => 'api_token',
                'label' => 'API Token',
                'type' => 'password',
                'required' => true,
                'description' => 'Your PawaPay API token from the dashboard',
            ],
        ];
    }

    public function supportsTestMode(): bool
    {
        return true;
    }

    /**
     * Verify an RFC-9421 HTTP Message Signature on a PawaPay callback.
     *
     * V2 callbacks carry Signature + Signature-Input + Signature-Date + Content-Digest
     * headers (ECDSA-P-256-SHA256 by default). The public key is fetched from the
     * PawaPay Public Keys endpoint and cached. If PawaPay signed callbacks are NOT
     * enabled on the account, no Signature header is present and verification is
     * skipped (accept), matching PawaPay's "optional" signature model.
     *
     * @return bool true when the callback is authentic (or signatures are disabled)
     */
    public function verifyWebhookSignature(
        string $payload,
        string $signatureHeader = '',
        string $signatureInputHeader = '',
        string $signatureDate = '',
        string $contentDigest = '',
    ): bool {
        // No signature headers -> signed callbacks are not enabled on the account.
        if (empty($signatureHeader) && empty($signatureInputHeader)) {
            Log::channel('payment')->debug('[PawaPay] callback has no signature headers; accepting');

            return true;
        }

        if (empty($signatureHeader) || empty($signatureInputHeader)) {
            return false;
        }

        try {
            // 1. Verify Content-Digest (sha-256 or sha-512) matches the raw body.
            if ($contentDigest !== '' && ! $this->verifyContentDigest($payload, $contentDigest)) {
                return false;
            }

            // 2. Build the RFC-9421 signature base.
            $base = $this->buildSignatureBase(
                payload: $payload,
                signatureInput: $signatureInputHeader,
                signatureDate: $signatureDate,
                contentDigest: $contentDigest,
                method: 'POST',
                path: '/api/webhooks/payments/pawapay',
            );
            if ($base === null) {
                return false;
            }

            // 3. Extract the key id + signature value.
            [$keyId, $sig] = $this->extractSignature($signatureHeader);
            if ($keyId === null || $sig === null) {
                return false;
            }

            // 4. Fetch the public key and verify ECDSA/RSASSA.
            $publicKeyPem = $this->fetchPublicKey($keyId);
            if ($publicKeyPem === null) {
                Log::channel('payment')->warning('[PawaPay] public key not found for keyid', ['keyid' => $keyId]);

                return false;
            }

            return $this->verifyRfc9421Signature($base, $sig, $publicKeyPem);

        } catch (\Throwable $e) {
            $this->logError('Webhook signature verification', $e);

            return false;
        }
    }

    /**
     * Map a PawaPay V2 deposit/payout/refund status to the platform PaymentStatus.
     */
    public function mapStatus(string $pawapayStatus): PaymentStatus
    {
        return match(strtoupper($pawapayStatus)) {
            'COMPLETED' => PaymentStatus::COMPLETED,
            'FAILED' => PaymentStatus::FAILED,
            'CANCELLED' => PaymentStatus::CANCELLED,
            'EXPIRED' => PaymentStatus::EXPIRED,
            'REFUNDED' => PaymentStatus::REFUNDED,
            'ACCEPTED', 'PROCESSING', 'IN_RECONCILIATION', 'ENQUEUED', 'SUBMITTED' => PaymentStatus::PROCESSING,
            default => PaymentStatus::PROCESSING,
        };
    }

    // ------------------------------------------------------------------
    // Helpers
    // ------------------------------------------------------------------

    private function authHeaders(): array
    {
        return [
            'Authorization' => 'Bearer ' . ($this->credentials['api_token'] ?? ''),
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ];
    }

    private function validUuid(string $value): bool
    {
        return (bool) preg_match(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i',
            $value
        );
    }

    /**
     * Format an amount as the PawaPay V2 string. Examples: "15", "10.50".
     * Leading zeroes are not permitted except for values < 1 (e.g. "0.50").
     */
    private function formatAmount(string $amount): string
    {
        $amount = trim($amount);

        if ($amount === '') {
            return '0';
        }

        // Integer
        if (str_contains($amount, '.') === false) {
            return ltrim($amount, '0') === '' ? '0' : ltrim($amount, '0');
        }

        [$whole, $decimal] = explode('.', $amount, 2);
        $decimal = substr($decimal, 0, 3); // ≤3 decimal places

        // Trim trailing zeroes unless the value is < 1
        $decimal = rtrim($decimal, '0');

        if ($decimal === '') {
            $whole = ltrim($whole, '0');
            return $whole === '' ? '0' : $whole;
        }

        // Keep a single leading zero for values < 1; strip leading zeroes otherwise.
        $whole = $whole === '0' ? '0' : ltrim($whole, '0');
        $whole = $whole === '' ? '0' : $whole;

        return "{$whole}.{$decimal}";
    }

    /**
     * PawaPay customerMessage: 4-22 chars, alphanumeric + spaces only.
     */
    private function formatCustomerMessage(string $description): string
    {
        $clean = preg_replace('/[^a-zA-Z0-9 ]/', '', $description);
        $clean = trim($clean ?? '');

        if ($clean === '') {
            return 'Payment';
        }

        if (strlen($clean) > 22) {
            $clean = substr($clean, 0, 22);
        }

        return $clean;
    }

    /**
     * Detect the PawaPay provider from a Zambian mobile number.
     */
    private function detectProvider(string $phoneNumber): string
    {
        $clean = preg_replace('/[^0-9]/', '', $phoneNumber);

        if (str_starts_with($clean, '260')) {
            $clean = substr($clean, 3);
        }

        // MTN: 096, 076, 077
        if (preg_match('/^(096|076|077)/', $clean)) {
            return 'MTN_MOMO_ZMB';
        }

        // Airtel: 097, 075
        if (preg_match('/^(097|075)/', $clean)) {
            return 'AIRTEL_OAPI_ZMB';
        }

        // Zamtel: 095
        if (preg_match('/^(095)/', $clean)) {
            return 'ZAMTEL_ZMB';
        }

        return 'MTN_MOMO_ZMB'; // Default
    }

    // ------------------------------------------------------------------
    // RFC-9421 signature helpers
    // ------------------------------------------------------------------

    private function verifyContentDigest(string $payload, string $contentDigestHeader): bool
    {
        // Header format: "sha-256=:BASE64:" or "sha-512=:BASE64:"
        if (! preg_match('/^sha-(256|512)=:(.*):$/i', trim($contentDigestHeader), $m)) {
            return false;
        }

        $algo = strtolower($m[1]) === '256' ? 'sha256' : 'sha512';
        $expected = base64_decode($m[2], true);
        $actual = hash($algo, $payload, true);

        return hash_equals($expected, $actual);
    }

    /**
     * Build the RFC-9421 signature base string from the callback headers.
     */
    private function buildSignatureBase(
        string $payload,
        string $signatureInput,
        string $signatureDate,
        string $contentDigest,
        string $method,
        string $path,
    ): ?string {
        // Parse Signature-Input: sig-pp=("@method" "@authority" "@path" "signature-date" "content-digest" "content-type");alg="ecdsa-p256-sha256";keyid="...";created=...;expires=...
        if (! preg_match('/^([a-z0-9_-]+)=\s*\(([^)]*)\)(.*)$/i', trim($signatureInput), $m)) {
            return null;
        }

        $label = $m[1];
        $covered = $m[2]; // ("@method" "@authority" "@path" "signature-date" ...)

        preg_match_all('/"(@?[a-z0-9_-]+)"/i', $covered, $coveredMatches);
        $coveredComponents = $coveredMatches[1];

        // @authority is "host:port"; we only have a path, so approximate from URL parts.
        $authority = 'mygrownet.com';

        $lines = [];
        foreach ($coveredComponents as $component) {
            $lines[] = $this->signatureBaseComponent($component, $method, $path, $authority, $signatureDate, $contentDigest, $label);
        }

        $signatureParams = trim($m[3]);

        return implode("\n", $lines) . "\n" . '"@signature-params": ' . $signatureParams;
    }

    private function signatureBaseComponent(
        string $component,
        string $method,
        string $path,
        string $authority,
        string $signatureDate,
        string $contentDigest,
        string $label,
    ): string {
        return match ($component) {
            '@method' => '"@method": ' . $method,
            '@authority' => '"@authority": ' . $authority,
            '@path' => '"@path": ' . $path,
            'signature-date' => '"signature-date": ' . $signatureDate,
            'content-digest' => '"content-digest": ' . $contentDigest,
            'content-type' => '"content-type": application/json; charset=UTF-8',
            default => '',
        };
    }

    /**
     * Extract (keyid, base64 signature) from the Signature header.
     * Header format: sig-pp=:BASE64:;keyid="...";...
     */
    private function extractSignature(string $signatureHeader): ?array
    {
        if (! preg_match('/^([a-z0-9_-]+)=:(.*):;/i', trim($signatureHeader), $m)) {
            if (! preg_match('/^([a-z0-9_-]+)=:(.*):$/i', trim($signatureHeader), $m)) {
                return null;
            }
        }

        $sig = base64_decode($m[2], true);
        if ($sig === false) {
            return null;
        }

        $keyId = null;
        if (preg_match('/keyid="([^"]+)"/', $signatureHeader, $km)) {
            $keyId = $km[1];
        }

        return [$keyId, $sig];
    }

    /**
     * Fetch the PawaPay public key for the given keyid (cached).
     *
     * Public Keys endpoint: GET /v2/toolkit/public-keys  (returns the active key(s)).
     */
    private function fetchPublicKey(string $keyId): ?string
    {
        $cacheKey = 'pawapay_public_key_' . md5($keyId);

        return \Illuminate\Support\Facades\Cache::remember($cacheKey, 3600, function () use ($keyId) {
            try {
                $response = Http::withHeaders($this->authHeaders())
                    ->get("{$this->baseUrl}/v2/toolkit/public-keys");

                if (! $response->successful()) {
                    Log::channel('payment')->warning('[PawaPay] public-keys fetch failed', [
                        'status' => $response->status(),
                        'body' => $response->body(),
                    ]);

                    return null;
                }

                $data = $response->json() ?? [];
                $keys = $data['data'] ?? $data['publicKeys'] ?? [];

                foreach ($keys as $key) {
                    $kId = $key['keyId'] ?? $key['keyid'] ?? $key['id'] ?? null;
                    if ($kId === $keyId) {
                        return $key['publicKey'] ?? $key['value'] ?? $key['pem'] ?? null;
                    }
                }

                // Fall back to the first available key.
                if (! empty($keys)) {
                    $first = $keys[0];

                    return $first['publicKey'] ?? $first['value'] ?? $first['pem'] ?? null;
                }

                return null;
            } catch (\Throwable $e) {
                $this->logError('Fetch PawaPay public key', $e, ['keyid' => $keyId]);

                return null;
            }
        });
    }

    /**
     * Verify an ECDSA-P-256-SHA256 / RSASSA-PKCS1-v1_5-SHA256 / RSASSA-PSS-SHA512
     * signature over the RFC-9421 signature base.
     */
    private function verifyRfc9421Signature(string $signatureBase, string $signature, string $publicKeyPem): bool
    {
        // Parse the key into a usable format. PawaPay keys are PEM-encoded SPKI
        // ("-----BEGIN PUBLIC KEY-----"). ECDSA signatures are raw r||s concatenation
        // (64 bytes for P-256); verify with openssl after DER-wrapping.
        if (! preg_match('/-----BEGIN PUBLIC KEY-----/', $publicKeyPem)) {
            $publicKeyPem = "-----BEGIN PUBLIC KEY-----\n"
                . chunk_split($publicKeyPem, 64, "\n")
                . "-----END PUBLIC KEY-----\n";
        }

        $key = openssl_pkey_get_public($publicKeyPem);
        if ($key === false) {
            Log::channel('payment')->warning('[PawaPay] invalid public key PEM');

            return false;
        }

        $keyDetails = openssl_pkey_get_details($key);
        $keyType = $keyDetails['key']['type'] ?? null;
        $bits = $keyDetails['bits'] ?? 0;

        // ECDSA: raw r||s (64 bytes for P-256, 96 for P-384). RFC-9421 uses raw
        // point signature values, so wrap into DER for openssl_verify.
        if (in_array($keyType, [OPENSSL_KEYTYPE_EC], true)) {
            $der = $this->ecRawToDer($signature);
            if ($der === null) {
                return false;
            }

            return openssl_verify($signatureBase, $der, $key, OPENSSL_ALGO_SHA256) === 1;
        }

        // RSA: try PKCS1-v1_5 SHA256, then PSS SHA512.
        if (openssl_verify($signatureBase, $signature, $key, OPENSSL_ALGO_SHA256) === 1) {
            return true;
        }

        return openssl_verify($signatureBase, $signature, $key, OPENSSL_ALGO_SHA512) === 1;
    }

    /**
     * Convert a raw ECDSA r||s signature (RFC-9421) into a DER SEQUENCE for openssl_verify.
     */
    private function ecRawToDer(string $raw): ?string
    {
        $length = strlen($raw);
        if ($length !== 64 && $length !== 96) {
            return null;
        }

        $half = intdiv($length, 2);
        $r = $this->padInteger($raw, 0, $half);
        $s = $this->padInteger($raw, $half, $length);

        // DER SEQUENCE { INTEGER r, INTEGER s }
        return "\x30" . chr(2 + strlen($r) + 2 + strlen($s))
            . "\x02" . chr(strlen($r)) . $r
            . "\x02" . chr(strlen($s)) . $s;
    }

    private function padInteger(string $raw, int $start, int $end): string
    {
        $bytes = substr($raw, $start, $end - $start);
        // strip leading zero bytes, then ensure a 0x00 prefix if high bit set
        $bytes = ltrim($bytes, "\x00");
        if ($bytes === '') {
            $bytes = "\x00";
        }
        if ((ord($bytes[0]) & 0x80) !== 0) {
            $bytes = "\x00" . $bytes;
        }

        return $bytes;
    }
}
