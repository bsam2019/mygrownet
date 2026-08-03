# Cross-Module Contract Migration Tracker

Tracks remaining `app(Service::class)` calls that should be migrated to `IntegrationRegistry::resolve()`.

## Remaining Calls (0 instances)

All 13 tracked `app(Service::class)` calls have been migrated to `IntegrationRegistry::resolve()`.

## Migrated Calls

| Caller | Old Call | New Call | Phase |
|---|---|---|---|
| `app/Http/Controllers/Admin/SupportTicketController.php` | `app(Employee\NotificationService)` | `$registry->resolve(NotificationProvider::class)` | Phase 3 ✅ |
| `app/Services/GrowBuilder/StorageService.php:80` | `app(SubscriptionService)` | `$registry->resolve(SubscriptionProvider)` → `getUserTier()` | Jul 2026 ✅ |
| `app/Http/Controllers/GrowBuilder/SiteController.php:51` | `app(SubscriptionService)` | `$registry->resolve(SubscriptionProvider)` → `getUserTier()` | Jul 2026 ✅ |
| `app/Http/Controllers/GrowBuilder/SiteController.php:52` | `app(TierConfigurationService)` | `$registry->resolve(TierProvider)` → `getTierConfig()/getLimit()` | Jul 2026 ✅ |
| `app/Http/Controllers/GrowBuilder/SiteController.php:973` | `app(SubscriptionService)` | `$registry->resolve(SubscriptionProvider)` → `clearCache()` | Jul 2026 ✅ |
| `app/Http/Controllers/BizBoost/SubscriptionController.php:197,233` | `app(ModuleSubscriptionService)` | `$registry->resolve(SubscriptionManagementProvider)` → `subscribe()/cancel()` | Jul 2026 ✅ |
| `app/Http/Controllers/GrowNet/MyGrowNetWalletController.php:107` | `app(LoanService)` | `$registry->resolve(LoanProvider)` → `getLoanSummary()` | Jul 2026 ✅ |
| `app/Http/Controllers/GrowNet/GrowNetDashboardController.php:128,380` | `app(LoanService)` | `$registry->resolve(LoanProvider)` → `getLoanSummary()` | Jul 2026 ✅ |
| `app/Http/Controllers/GrowNet/DashboardController.php:257` | `app(LoanService)` | `$registry->resolve(LoanProvider)` → `getLoanSummary()` | Jul 2026 ✅ |
| `app/Http/Controllers/DashboardController.php:74,167,380` | `app(WalletService)` | `$registry->resolve(WalletProvider)` → `getWalletBreakdown()/calculateBalance()` | Jul 2026 ✅ |
| `app/Application/Payment/UseCases/VerifyPaymentUseCase.php:147` | `app(StarterKitService)` | `$registry->resolve(StarterKitProvider)` → `completePurchase()` | Jul 2026 ✅ |
| `app/Application/Payment/UseCases/VerifyPaymentUseCase.php:214` | `app(ReferralMatrixService)` | `$registry->resolve(ReferralProvider)` → `findNextAvailablePosition()` | Jul 2026 ✅ |
| `app/Console/Commands/DiagnoseWalletBalance.php:100,104` | `app(WalletService)` | `$registry->resolve(WalletProvider)` → `calculateBalance()` | Jul 2026 ✅ |

## Broken References Fixed

| Caller | Old Reference | Fix |
|---|---|---|
| `app/Domain/GrowNet/Services/WalletService.php` (deleted) | `Wallet\WalletService` (non-existent) | Proxy file deleted; consumers migrated to WalletProvider |
| `app/Domain/GrowNet/Services/StarterKitService.php` | `\App\Domain\Wallet\Services\WalletService` (wrong path) | Corrected to `\App\Domain\GrowNet\Wallet\Services\WalletService` |
| `app/Domain/StarterKit/Services/GiftService.php` | `\App\Domain\GrowNet\Services\WalletService` (deleted proxy) | Migrated to `$registry->resolve(WalletProvider::class)` |
| `app/Application/StarterKit/UseCases/GiftStarterKitUseCase.php` | `GrowNet\Services\WalletService` (deleted proxy) | Migrated to `$registry->resolve(WalletProvider::class)` via constructor injection |

## Contracts Defined (Jul 2026)

| Contract | Provider Module | Interface | Implementation | Capability |
|---|---|---|---|---|
| `SubscriptionProvider` | Module | `App\Domain\Module\Contracts\SubscriptionProvider` | `App\Infrastructure\Contracts\Module\SubscriptionProviderImpl` | `module.subscription` |
| `TierProvider` | Module | `App\Domain\Module\Contracts\TierProvider` | `App\Infrastructure\Contracts\Module\TierProviderImpl` | `module.tier` |
| `SubscriptionManagementProvider` | Module | `App\Domain\Module\Contracts\SubscriptionManagementProvider` | `App\Infrastructure\Contracts\Module\SubscriptionManagementProviderImpl` | `module.subscription_management` |
| `LoanProvider` | Financial | `App\Domain\Financial\Contracts\LoanProvider` | `App\Infrastructure\Contracts\Financial\LoanProviderImpl` | `financial.loan` |
| `WalletProvider` | GrowNet | `App\Domain\GrowNet\Contracts\WalletProvider` | `App\Infrastructure\Contracts\GrowNet\WalletProviderImpl` | `grownet.wallet` |
| `StarterKitProvider` | GrowNet | `App\Domain\GrowNet\Contracts\StarterKitProvider` | `App\Infrastructure\Contracts\GrowNet\StarterKitProviderImpl` | `grownet.starter_kit` |
| `ReferralProvider` | GrowNet | `App\Domain\GrowNet\Contracts\ReferralProvider` | `App\Infrastructure\Contracts\GrowNet\ReferralProviderImpl` | `grownet.referral` |

## Domain-Layer Cross-Module Calls (for awareness — not migrated)

| Caller | Module | Calls | Issue |
|---|---|---|---|
| `app/Domain/GrowNet/Services/StarterKitService.php` | GrowNet | `Announcement\EventBasedAnnouncementService` | Refactor needed |
| `app/Domain/Financial/Services/TransactionIntegrityService.php` | Financial | `GrowNet\WalletService` | Refactor needed |
