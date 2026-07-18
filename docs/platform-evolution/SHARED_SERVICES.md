# Platform Shared Services — Reserved Contracts

These contracts are reserved to prevent duplicate implementations across modules. Implementation is out of scope — document only.

| Service | Responsibility | Typical Methods |
|---|---|---|
| `StorageService` | File uploads, media management, CDN | `upload()`, `delete()`, `url()`, `temporaryUrl()` |
| `SearchService` | Full-text, filtered search across modules | `search()`, `index()`, `reindex()` |
| `PaymentGateway` | Unified payment processing | `charge()`, `refund()`, `verify()`, `webhook()` |
| `AuditService` | Structured activity logging | `log()`, `query()`, `export()` |
| `AIService` | AI/ML capabilities (recommendations, predictions) | `predict()`, `classify()`, `embed()` |
| `ReportingService` | Cross-module report generation | `generate()`, `schedule()`, `export()` |

## Rules
1. Do not create duplicate services inside a module
2. If you need one of these capabilities, extend the shared contract
3. Contracts live in `app/Domain/Platform/Contracts/`
4. Implementations live in `app/Infrastructure/Services/`
