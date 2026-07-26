# Anti-Corruption Layer (ACL) Pattern

## Purpose
Isolate external dependencies behind stable interfaces so that external API changes, provider swaps, or SDK upgrades never propagate into domain code.

## Structure

```
app/AntiCorruption/{ExternalSystem}/
├── {ExternalSystem}Adapter.php     ← implements a Platform contract
└── README.md                       ← integration notes
```

## Rules

1. **ACL classes live in `app/AntiCorruption/`** — not in any module's domain code
2. **ACL classes implement Platform Core contracts** — always `implements PaymentGateway`, never a module-specific interface
3. **External exceptions are wrapped** — catch `Stripe\Exception\*` → throw `IntegrationException`
4. **No domain logic in ACL** — the adapter only translates: external format → internal format
5. **Configuration is injected** — never read env directly; constructor injection with typed parameters

## Adding a New Integration

1. Create `app/AntiCorruption/{Provider}/`
2. Implement the relevant `App\Domain\Platform\Contracts\*` interface
3. Wrap all external exceptions in `IntegrationException`
4. Register in a ServiceProvider with the appropriate configuration
5. Add integration tests in `tests/Integration/AntiCorruption/`
