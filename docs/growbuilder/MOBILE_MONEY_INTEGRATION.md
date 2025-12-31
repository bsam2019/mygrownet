# GrowBuilder Mobile Money Integration

**Last Updated:** December 29, 2025  
**Status:** ✅ Complete (Backend + Frontend)  
**Priority:** P0 - Critical for Zambian market

## Overview

Complete mobile money payment integration for GrowBuilder sites, allowing site owners to configure their own payment gateway credentials and accept payments from customers via multiple Zambian mobile money providers.

## User Access

Site owners configure payment gateways at:
```
/growbuilder/sites/{site_id}/payment/config
```

This page is accessible from the GrowBuilder dashboard under site settings.

## Architecture

### Separation of Concerns

**MyGrowNet Payment System** (existing)
- Platform-level payments (subscriptions, internal transactions)
- Uses platform's payment credentials
- Located in `app/Domain/Payment/`

**GrowBuilder Payment System** (new)
- Per-site payment configuration
- Users add their own API credentials
- Multi-gateway support
- Located in `app/Domain/GrowBuilder/Payment/`

### Supported Payment Gateways

1. **PawaPay** (Recommended)
   - Multi-provider aggregator
   - Supports MTN, Airtel, Zamtel
   - Test mode available
   - Automatic provider detection
   - Website: https://pawapay.io

2. **Flutterwave**
   - Mobile money, cards, and bank transfers
   - Multi-country support
   - Test mode available
   - Automated refunds
   - Website: https://flutterwave.com

3. **DPO PayGate**
   - Secure payment gateway
   - Mobile money and card support
   - XML-based API
   - Refund support
   - Website: https://www.dpogroup.com

**Note:** Direct mobile money APIs (MTN, Airtel, Zamtel) are not publicly available and have been replaced with aggregator services.

## Implementation

### Domain Structure

```
app/Domain/GrowBuilder/Payment/
├── Contracts/
│   └── PaymentGatewayInterface.php      # Gateway contract
├── DTOs/
│   ├── PaymentRequest.php               # Payment initiation DTO
│   ├── PaymentResponse.php              # Payment response DTO
│   ├── RefundRequest.php                # Refund request DTO
│   └── RefundResponse.php               # Refund response DTO
├── Enums/
│   ├── PaymentGateway.php               # Available gateways
│   └── PaymentStatus.php                # Transaction statuses
├── Gateways/
│   ├── AbstractPaymentGateway.php       # Base gateway class
│   ├── PawapayGateway.php              # PawaPay implementation
│   ├── FlutterwaveGateway.php          # Flutterwave implementation
│   └── DpoGateway.php                  # DPO PayGate implementation
└── Services/
    ├── GrowBuilderPaymentService.php    # Main payment service
    └── PaymentGatewayFactory.php        # Gateway factory
```

### Database Schema

**growbuilder_site_payment_configs**
```sql
- id
- site_id (foreign key)
- gateway (enum: pawapay, flutterwave, dpo)
- credentials (encrypted JSON)
- is_active (boolean)
- test_mode (boolean)
- webhook_secret (string)
- settings (JSON)
- timestamps
```

**growbuilder_site_payment_transactions**
```sql
- id
- site_id (foreign key)
- payment_config_id (foreign key)
- transaction_reference (unique)
- external_reference (indexed)
- amount (decimal)
- currency (default: ZMW)
- phone_number
- customer_name
- customer_email
- description
- status (enum: pending, processing, completed, failed, cancelled, refunded, expired)
- metadata (JSON)
- raw_response (JSON)
- refund_reference
- refund_amount
- refund_reason
- verified_at
- refunded_at
- timestamps
```

### Models

**SitePaymentConfig**
- Stores encrypted payment gateway credentials
- One active config per site
- Supports test and production modes
- Auto-generates webhook secrets

**SitePaymentTransaction**
- Logs all payment transactions
- Tracks payment status lifecycle
- Stores raw gateway responses
- Supports refund tracking

### Controllers

**PaymentConfigController**
- `index()` - Show payment configuration page
- `getGatewayFields()` - Get required fields for gateway
- `store()` - Save payment configuration
- `destroy()` - Delete configuration
- `test()` - Test configuration validity

**PaymentWebhookController**
- `handle()` - Process payment webhooks from gateways

## Usage

### 1. Configure Payment Gateway (Site Owner)

```php
// Get available gateways
$gateways = PaymentGatewayFactory::getAvailableGateways();

// Get required fields for a gateway
$fields = PaymentGatewayFactory::getGatewayFields(PaymentGateway::PAWAPAY);

// Save configuration
$config = SitePaymentConfig::create([
    'site_id' => $siteId,
    'gateway' => 'pawapay',
    'credentials' => [
        'api_token' => 'your_api_token',
    ],
    'test_mode' => true,
    'is_active' => true,
]);
```

### 2. Initiate Payment

```php
use App\Domain\GrowBuilder\Payment\DTOs\PaymentRequest;
use App\Domain\GrowBuilder\Payment\Services\GrowBuilderPaymentService;

$paymentService = app(GrowBuilderPaymentService::class);

$request = new PaymentRequest(
    amount: '100.00',
    currency: 'ZMW',
    phoneNumber: '0961234567',
    reference: 'ORDER_' . uniqid(),
    description: 'Product purchase',
    customerName: 'John Doe',
    customerEmail: 'john@example.com',
    metadata: ['order_id' => 123],
    callbackUrl: route('payment.webhook', $siteId),
);

$response = $paymentService->initiatePayment($siteId, $request);

if ($response->success) {
    // Payment initiated
    // Customer will receive mobile money prompt
}
```

### 3. Verify Payment

```php
$response = $paymentService->verifyPayment($siteId, $transactionReference);

if ($response->status === PaymentStatus::COMPLETED) {
    // Payment successful
    // Fulfill order
}
```

### 4. Process Refund

```php
use App\Domain\GrowBuilder\Payment\DTOs\RefundRequest;

$refundRequest = new RefundRequest(
    transactionReference: 'ORDER_123',
    amount: '100.00',
    reason: 'Customer requested refund',
);

$response = $paymentService->refundPayment($siteId, $refundRequest);
```

### 5. Handle Webhooks

```php
// Route: POST /api/growbuilder/sites/{site}/payment/webhook
// Automatically processes payment status updates
```

## Gateway-Specific Configuration

### PawaPay

**Required Fields:**
- `api_token` - API token from PawaPay dashboard

**Features:**
- Automatic provider detection (MTN, Airtel, Zamtel)
- Test mode support (sandbox environment)
- Refund support
- Webhook notifications

**Sign up:** https://pawapay.io

### Flutterwave

**Required Fields:**
- `public_key` - Public key from Flutterwave dashboard
- `secret_key` - Secret key from Flutterwave dashboard

**Features:**
- Mobile money (MTN, Airtel, Zamtel)
- Card payments
- Bank transfers
- Test mode support
- Automated refunds
- Webhook notifications

**Sign up:** https://flutterwave.com

### DPO PayGate

**Required Fields:**
- `company_token` - Company token from DPO dashboard
- `service_type` - Service type code (e.g., 3854)

**Features:**
- Mobile money support
- Card payments
- XML-based API
- Refund support
- Webhook notifications

**Sign up:** https://www.dpogroup.com

## Security

### Credential Encryption
- All gateway credentials encrypted using Laravel's `Crypt` facade
- Stored as encrypted JSON in database
- Decrypted only when needed for API calls

### Webhook Security
- Unique webhook secret per site
- Signature verification (gateway-dependent)
- Request logging for audit trail

### Access Control
- Only site owners can configure payments
- Authorization checks on all endpoints
- Encrypted credential storage

## Testing

### Test Mode
- All gateways support test mode (except Zamtel)
- Use sandbox/UAT environments
- No real money transactions

### Test Credentials
Each gateway provides test credentials:
- **PawaPay:** Sandbox API token
- **Flutterwave:** Test public/secret keys
- **DPO:** Test company token

## Logging

### Payment Log Channel
- Dedicated `payment` log channel
- Located at `storage/logs/payment.log`
- Rotates daily, keeps 90 days
- Logs all payment activities

### Logged Events
- Payment initiation
- Payment verification
- Refund processing
- Webhook processing
- Errors and exceptions

## Error Handling

### Payment Failures
- Graceful error handling
- Detailed error messages
- Transaction status tracking
- Automatic retry logic (where applicable)

### Webhook Failures
- Failed webhooks logged
- Manual verification available
- Status reconciliation

## Next Steps

### Phase 1: Core Implementation ✅
- [x] Payment gateway abstraction layer
- [x] Gateway implementations (3 gateways: PawaPay, Flutterwave, DPO)
- [x] Database schema and models
- [x] Payment service layer
- [x] Controllers and routes
- [x] Webhook handling

### Phase 2: Frontend UI ✅
- [x] Payment configuration page (`PaymentConfig.vue`)
- [x] Gateway selection modal (`PaymentGatewayModal.vue`)
- [x] Dynamic credential input forms
- [x] Test connection functionality
- [x] Transaction history table (`TransactionHistory.vue`)
- [x] Transaction details modal (`TransactionDetailsModal.vue`)
- [x] Webhook URL display and copy
- [x] Status badges and formatting

### Phase 3: Integration ✅
- [x] Integrate with GrowBuilder checkout flow
- [x] Customer-facing payment initiation
- [x] Payment status polling on checkout page
- [x] Backward compatibility with legacy payment system
- [ ] Order management integration (email notifications)
- [ ] Receipt generation

### Phase 4: Advanced Features (Future)
- [ ] Recurring payments
- [ ] Payment links
- [ ] QR code payments
- [ ] Multi-currency support

## API Reference

### PaymentGatewayInterface

```php
interface PaymentGatewayInterface
{
    public function initiatePayment(PaymentRequest $request): PaymentResponse;
    public function verifyPayment(string $transactionReference): PaymentResponse;
    public function refundPayment(RefundRequest $request): RefundResponse;
    public function getName(): string;
    public function validateConfiguration(array $credentials): array;
    public function getRequiredFields(): array;
    public function supportsTestMode(): bool;
}
```

### Payment Statuses

- `pending` - Payment initiated, awaiting customer action
- `processing` - Payment being processed by provider
- `completed` - Payment successful
- `failed` - Payment failed
- `cancelled` - Payment cancelled by customer
- `refunded` - Payment refunded
- `expired` - Payment request expired

## Troubleshooting

### Common Issues

**Issue: Payment initiation fails**
- Check gateway credentials
- Verify test mode setting
- Check phone number format
- Review payment logs

**Issue: Webhook not received**
- Verify webhook URL is publicly accessible
- Check webhook secret configuration
- Review gateway webhook settings
- Check firewall rules

**Issue: Payment stuck in pending**
- Manually verify payment status
- Check customer's mobile money account
- Contact gateway support

## Changelog

### December 29, 2025
- ✅ **Complete implementation (Backend + Frontend + Checkout Integration)**
- Created payment gateway abstraction layer
- Replaced direct mobile money APIs with publicly available gateways:
  - Removed: MTN MoMo, Airtel Money, Zamtel Kwacha, MoneyUnify (no public APIs)
  - Added: Flutterwave, DPO PayGate (publicly available)
  - Kept: PawaPay (aggregator)
- Set up database schema (2 tables: configs, transactions)
- Built service layer and controllers
- **Completed frontend UI:**
  - Payment configuration page with gateway selection
  - Dynamic form fields based on selected gateway
  - Transaction history with pagination
  - Transaction details modal
  - Webhook URL management
  - Test connection functionality
- **Integrated with checkout flow:**
  - Updated CheckoutController to use new payment system
  - Added payment initiation in checkout process
  - Implemented payment status polling
  - Maintained backward compatibility with legacy system
  - Support for redirect-based payments (Flutterwave, DPO)
- Added routes for configuration, transactions, and webhooks
- Full separation from MyGrowNet payment system
- Site owners configure at `/growbuilder/sites/{site}/payment/config`
