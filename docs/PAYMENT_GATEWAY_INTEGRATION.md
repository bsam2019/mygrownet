# Payment Gateway Integration

**Last Updated:** December 13, 2025
**Status:** Production Ready

## Overview

MyGrowNet integrates with multiple mobile money payment gateways to support collections (receiving payments) and disbursements (sending payouts) across Africa.

## Supported Gateways

### 1. MoneyUnify (Primary - Zambia)
- **Focus:** Zambia mobile money
- **Providers:** MTN, Airtel, Zamtel
- **Currencies:** ZMW, USD
- **GitHub:** https://github.com/blessedjasonmwanza/MoneyUnify
- **Docs:** https://moneyunify.com/developers

### 2. PawaPay (Pan-African)
- **Coverage:** 12+ African countries
- **Countries:** ZM, TZ, UG, KE, GH, CI, SN, CM, RW, BJ, BF, MW
- **Currencies:** ZMW, TZS, UGX, KES, GHS, XOF, XAF, RWF, MWK, USD

## Architecture

```
app/Domain/Payment/
├── Contracts/
│   └── PaymentGatewayInterface.php    # Gateway contract
├── DTOs/
│   ├── CollectionRequest.php          # Collection input
│   ├── CollectionResponse.php         # Collection result
│   ├── DisbursementRequest.php        # Disbursement input
│   └── DisbursementResponse.php       # Disbursement result
├── Enums/
│   └── TransactionStatus.php          # Status enum
├── Gateways/
│   ├── AbstractPaymentGateway.php     # Base gateway
│   ├── MoneyUnifyGateway.php          # MoneyUnify impl
│   └── PawapayGateway.php             # PawaPay impl
└── Services/
    └── PaymentService.php             # Main service
```

## Configuration

### Environment Variables
```env
# Default gateway
PAYMENT_DEFAULT_GATEWAY=moneyunify

# MoneyUnify (Zambia - Collections Only)
# Get MUID from: https://moneyunify.com (Dashboard > Settings)
# Uses official package: blessedjasonmwanza/moneyunify
MONEYUNIFY_BASE_URL=https://api.moneyunify.com/v2
MONEYUNIFY_MUID=your_moneyunify_id
MONEYUNIFY_WEBHOOK_SECRET=your_webhook_secret

# PawaPay (Pan-African - Collections + Disbursements)
# Sandbox: https://api.sandbox.pawapay.io
# Production: https://api.pawapay.io
PAWAPAY_BASE_URL=https://api.sandbox.pawapay.io
PAWAPAY_API_TOKEN=your_api_token
PAWAPAY_WEBHOOK_SECRET=your_webhook_secret
```

### Gateway Capabilities
| Gateway | Collections | Disbursements | Countries |
|---------|-------------|---------------|-----------|
| MoneyUnify | ✅ | ❌ | Zambia only |
| PawaPay | ✅ | ✅ | 12+ African countries |

**Note:** For disbursements (sending money/payouts), use PawaPay. MoneyUnify only supports collections (receiving payments).

## API Endpoints

### Collections (Receive Money)
```
POST /api/payments/collect
{
    "phone_number": "0971234567",
    "amount": 100.00,
    "currency": "ZMW",
    "provider": "mtn",
    "reference": "ORDER-123",
    "description": "Payment for order",
    "gateway": "moneyunify"  // optional
}
```

### Disbursements (Send Money)
```
POST /api/payments/disburse
{
    "phone_number": "0971234567",
    "amount": 50.00,
    "currency": "ZMW",
    "provider": "airtel",
    "reference": "PAYOUT-456",
    "description": "Commission payout",
    "gateway": "pawapay"  // optional
}
```

### Check Status
```
GET /api/payments/status/{transactionId}?type=collection&gateway=moneyunify
```

### List Gateways
```
GET /api/payments/gateways
```

## Webhooks

Configure these URLs in your payment provider dashboards:

- **MoneyUnify:** `https://yourdomain.com/api/webhooks/payments/moneyunify`
- **PawaPay:** `https://yourdomain.com/api/webhooks/payments/pawapay`

## Usage Examples

### In a Controller
```php
use App\Domain\Payment\Services\PaymentService;
use App\Domain\Payment\DTOs\CollectionRequest;

class SubscriptionController extends Controller
{
    public function __construct(private PaymentService $paymentService) {}

    public function pay(Request $request)
    {
        $collectionRequest = new CollectionRequest(
            phoneNumber: $request->phone,
            amount: 500.00,
            currency: 'ZMW',
            provider: 'mtn',
            reference: 'SUB-' . uniqid(),
            description: 'Monthly subscription'
        );

        $response = $this->paymentService->collect($collectionRequest);

        if ($response->success) {
            // Store transaction ID, show pending status
            return response()->json([
                'status' => 'pending',
                'transaction_id' => $response->transactionId
            ]);
        }

        return response()->json(['error' => $response->message], 400);
    }
}
```

### Checking Transaction Status
```php
$status = $this->paymentService->checkCollectionStatus($transactionId);

if ($status === TransactionStatus::COMPLETED) {
    // Activate subscription
}
```

## Transaction Statuses

| Status | Description |
|--------|-------------|
| `pending` | Transaction initiated, awaiting user action |
| `processing` | Being processed by provider |
| `completed` | Successfully completed |
| `failed` | Transaction failed |
| `cancelled` | Cancelled by user or system |
| `expired` | Timed out |
| `refunded` | Money returned |

## Provider Codes

### MoneyUnify (Zambia)
- `MTN` - MTN Mobile Money
- `AIRTEL` - Airtel Money
- `ZAMTEL` - Zamtel Kwacha

### PawaPay
- `MTN_MOMO_ZMB` - MTN Zambia
- `AIRTEL_ZMB` - Airtel Zambia
- `MPESA_KEN` - M-Pesa Kenya
- `MTN_MOMO_GHA` - MTN Ghana
- See gateway for full list

## Security

- All API endpoints require authentication (`auth:sanctum`)
- Webhook endpoints verify signatures
- Sensitive credentials stored in environment variables
- All transactions logged for audit

## Adding New Gateways

1. Create gateway class extending `AbstractPaymentGateway`
2. Implement all interface methods
3. Register in `PaymentService::registerGateways()`
4. Add configuration to `config/services.php`
5. Add webhook handler if needed
