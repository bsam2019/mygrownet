# Domain-Driven Design: Payment System Implementation

## Overview

The Member Payment System has been refactored to follow Domain-Driven Design (DDD) principles, properly separating concerns across four distinct layers.

## Architecture Layers

### 1. Domain Layer (`app/Domain/Payment/`)
**Purpose**: Contains pure business logic with no framework dependencies

#### Entities
- `MemberPayment` - Rich domain entity with business rules
  - `create()` - Factory method for new payments
  - `verify()` - Business logic for payment verification
  - `reject()` - Business logic for payment rejection
  - `canBeModified()` - Business rule checking

#### Value Objects (Immutable)
- `PaymentAmount` - Validates and encapsulates payment amounts
- `PaymentMethod` - Enum for payment methods (MTN, Airtel, Bank, etc.)
- `PaymentType` - Enum for payment types (subscription, workshop, etc.)
- `PaymentStatus` - Enum for payment status (pending, verified, rejected)

#### Repository Interfaces
- `MemberPaymentRepositoryInterface` - Defines data access contract

#### Domain Events
- `PaymentSubmitted` - Fired when payment is submitted
- `PaymentVerified` - Fired when payment is verified

### 2. Application Layer (`app/Application/Payment/`)
**Purpose**: Orchestrates domain operations and use cases

#### Use Cases
- `SubmitPaymentUseCase` - Handles payment submission workflow
- `GetUserPaymentsUseCase` - Retrieves user's payment history
- `VerifyPaymentUseCase` - Handles admin payment verification

#### DTOs (Data Transfer Objects)
- `SubmitPaymentDTO` - Transfers payment submission data

### 3. Infrastructure Layer (`app/Infrastructure/Persistence/`)
**Purpose**: Framework-specific implementations

#### Eloquent Models
- `MemberPaymentModel` - Laravel Eloquent model for database access

#### Repository Implementations
- `EloquentMemberPaymentRepository` - Implements `MemberPaymentRepositoryInterface`
  - Converts between Eloquent models and domain entities
  - Handles all database operations

### 4. Presentation Layer (`app/Http/Controllers/`)
**Purpose**: HTTP request/response handling

#### Controllers
- `MemberPaymentController` - Thin controller delegating to use cases
  - Injects use cases via constructor
  - Validates HTTP requests
  - Converts domain entities to arrays for Inertia

## Dependency Flow

```
Presentation → Application → Domain ← Infrastructure
```

- Presentation depends on Application
- Application depends on Domain
- Infrastructure depends on Domain
- Domain has NO dependencies (pure business logic)

## Key DDD Patterns Used

### 1. Entity Pattern
```php
$payment = MemberPayment::create(
    userId: $userId,
    amount: PaymentAmount::fromFloat($amount),
    paymentMethod: PaymentMethod::fromString('mtn_momo'),
    // ...
);

$payment->verify($adminId, $notes);
```

### 2. Value Object Pattern
```php
$amount = PaymentAmount::fromFloat(500.00);
$amount->formatted(); // "K500.00"
$amount->value(); // 500.00
```

### 3. Repository Pattern
```php
interface MemberPaymentRepositoryInterface {
    public function save(MemberPayment $payment): MemberPayment;
    public function findById(int $id): ?MemberPayment;
    public function findByUserId(int $userId): array;
}
```

### 4. Use Case Pattern
```php
class SubmitPaymentUseCase {
    public function execute(SubmitPaymentDTO $dto): MemberPayment {
        // Validate business rules
        // Create domain entity
        // Persist via repository
        // Dispatch domain events
    }
}
```

### 5. Domain Events
```php
Event::dispatch(new PaymentSubmitted(
    paymentId: $payment->id(),
    userId: $payment->userId(),
    amount: $payment->amount()->value(),
    // ...
));
```

## Dependency Injection

### Service Provider
```php
// app/Providers/PaymentServiceProvider.php
$this->app->bind(
    MemberPaymentRepositoryInterface::class,
    EloquentMemberPaymentRepository::class
);
```

### Controller Constructor Injection
```php
public function __construct(
    private readonly GetUserPaymentsUseCase $getUserPaymentsUseCase,
    private readonly SubmitPaymentUseCase $submitPaymentUseCase
) {}
```

## Benefits of This Architecture

### 1. Separation of Concerns
- Business logic isolated in Domain layer
- Framework code isolated in Infrastructure layer
- Use cases clearly defined in Application layer

### 2. Testability
- Domain entities can be tested without database
- Use cases can be tested with mock repositories
- Infrastructure can be tested independently

### 3. Flexibility
- Easy to swap Eloquent for another ORM
- Easy to add new payment methods or types
- Business rules centralized and reusable

### 4. Maintainability
- Clear structure makes code easy to navigate
- Changes to business logic don't affect infrastructure
- Changes to database don't affect business logic

### 5. Domain-Centric
- Business rules are first-class citizens
- Domain language used throughout (ubiquitous language)
- Business logic is framework-agnostic

## File Structure

```
app/
├── Domain/
│   └── Payment/
│       ├── Entities/
│       │   └── MemberPayment.php
│       ├── ValueObjects/
│       │   ├── PaymentAmount.php
│       │   ├── PaymentMethod.php
│       │   ├── PaymentStatus.php
│       │   └── PaymentType.php
│       ├── Repositories/
│       │   └── MemberPaymentRepositoryInterface.php
│       └── Events/
│           ├── PaymentSubmitted.php
│           └── PaymentVerified.php
├── Application/
│   └── Payment/
│       ├── UseCases/
│       │   ├── SubmitPaymentUseCase.php
│       │   ├── GetUserPaymentsUseCase.php
│       │   └── VerifyPaymentUseCase.php
│       └── DTOs/
│           └── SubmitPaymentDTO.php
├── Infrastructure/
│   └── Persistence/
│       ├── Eloquent/
│       │   └── Payment/
│       │       └── MemberPaymentModel.php
│       └── Repositories/
│           └── Payment/
│               └── EloquentMemberPaymentRepository.php
└── Http/
    └── Controllers/
        └── MyGrowNet/
            └── MemberPaymentController.php
```

## Example Usage Flow

### 1. User Submits Payment
```
HTTP Request
    ↓
MemberPaymentController::store()
    ↓
SubmitPaymentUseCase::execute()
    ↓
MemberPayment::create() [Domain Entity]
    ↓
EloquentMemberPaymentRepository::save()
    ↓
PaymentSubmitted Event Dispatched
    ↓
HTTP Response
```

### 2. User Views Payment History
```
HTTP Request
    ↓
MemberPaymentController::index()
    ↓
GetUserPaymentsUseCase::execute()
    ↓
EloquentMemberPaymentRepository::findByUserId()
    ↓
Convert Domain Entities to Arrays
    ↓
Inertia Response
```

## Business Rules Enforced

1. **Payment Amount Validation**
   - Must be at least K50
   - Cannot be negative

2. **Payment Reference Uniqueness**
   - Checked before creating payment
   - Prevents duplicate submissions

3. **Status Transitions**
   - Can only verify pending payments
   - Cannot reject verified payments
   - Only pending payments can be modified

4. **Verification Rules**
   - Requires admin user ID
   - Records verification timestamp
   - Allows optional admin notes

## Future Enhancements

1. **Additional Use Cases**
   - `RejectPaymentUseCase`
   - `GetPendingPaymentsUseCase`
   - `SearchPaymentsUseCase`

2. **Domain Services**
   - `PaymentVerificationService` - Complex verification logic
   - `PaymentNotificationService` - Handle notifications

3. **Event Handlers**
   - Send email on `PaymentSubmitted`
   - Update user subscription on `PaymentVerified`
   - Log audit trail for all events

4. **Specifications**
   - `PendingPaymentSpecification`
   - `VerifiedPaymentSpecification`
   - Use for complex queries

## Testing Strategy

### Unit Tests (Domain Layer)
```php
test('payment amount must be at least K50', function () {
    expect(fn() => PaymentAmount::fromFloat(40))
        ->toThrow(InvalidArgumentException::class);
});

test('can verify pending payment', function () {
    $payment = MemberPayment::create(/* ... */);
    $payment->verify(adminId: 1, adminNotes: 'Verified');
    expect($payment->status()->isVerified())->toBeTrue();
});
```

### Integration Tests (Application Layer)
```php
test('submit payment use case creates payment', function () {
    $dto = SubmitPaymentDTO::fromArray([/* ... */]);
    $payment = $this->submitPaymentUseCase->execute($dto);
    expect($payment->id())->toBeGreaterThan(0);
});
```

### Feature Tests (Presentation Layer)
```php
test('user can submit payment', function () {
    $this->actingAs($user)
        ->post('/mygrownet/payments', [/* ... */])
        ->assertRedirect();
});
```

## Conclusion

This DDD implementation provides a solid foundation for the payment system with clear separation of concerns, testability, and maintainability. The architecture can easily accommodate future requirements while keeping business logic clean and framework-agnostic.
