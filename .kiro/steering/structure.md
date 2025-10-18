# Project Structure

## Laravel Application Structure (Domain-Driven Design)

### Backend (PHP) - Domain-Oriented Structure
```
app/
├── Domain/                    # Domain layer (business logic)
│   ├── Investment/           # Investment Management bounded context
│   │   ├── Entities/         # Core business entities
│   │   ├── ValueObjects/     # Immutable value objects
│   │   ├── Services/         # Domain services
│   │   ├── Repositories/     # Repository interfaces
│   │   └── Events/           # Domain events
│   ├── Reward/              # Reward & Commission bounded context
│   ├── Financial/           # Financial Operations bounded context
│   └── User/                # User Management bounded context
├── Application/             # Application layer (use cases)
│   ├── UseCases/            # Application use cases
│   ├── Commands/            # Command handlers
│   ├── Queries/             # Query handlers
│   └── DTOs/                # Data transfer objects
├── Infrastructure/          # Infrastructure layer (technical concerns)
│   ├── Persistence/         # Database implementations
│   │   ├── Eloquent/        # Eloquent models (data layer)
│   │   └── Repositories/    # Repository implementations
│   ├── External/            # External service integrations
│   └── Events/              # Event handlers
├── Presentation/            # Presentation layer (existing structure)
│   ├── Http/
│   │   ├── Controllers/     # Request handlers (thin controllers)
│   │   ├── Middleware/      # HTTP middleware
│   │   └── Requests/        # Form request validation
│   └── Console/             # Artisan commands
├── Actions/                 # Single-purpose action classes (legacy)
├── Exports/                 # Data export classes
├── Models/                  # Eloquent models (legacy, migrate to Infrastructure)
├── Notifications/           # Email/SMS notifications
├── Policies/                # Authorization policies
├── Providers/               # Service providers
├── Services/                # Business logic services (legacy, migrate to Domain)
└── Traits/                  # Reusable traits
```

### Frontend (Vue/TypeScript)
```
resources/
├── css/              # Global styles
├── js/
│   ├── Components/   # Reusable Vue components
│   ├── Layouts/      # Page layouts
│   ├── Pages/        # Inertia.js pages
│   ├── types/        # TypeScript type definitions
│   ├── app.ts        # Main application entry
│   └── ssr.ts        # Server-side rendering entry
└── views/            # Blade templates (minimal, mostly for Inertia)
```

### Configuration & Build
```
config/               # Laravel configuration files
database/
├── factories/        # Model factories for testing
├── migrations/       # Database schema migrations
└── seeders/          # Database seeders
routes/               # Route definitions
storage/              # File storage, logs, cache
public/               # Web-accessible files
vendor/               # PHP dependencies
node_modules/         # Node.js dependencies
```

## Key Conventions

### File Naming
- **PHP**: PascalCase for classes (`UserController.php`, `InvestmentTier.php`)
- **Vue**: PascalCase for components (`UserDashboard.vue`, `InvestmentForm.vue`)
- **TypeScript**: camelCase for files, PascalCase for types
- **Migrations**: snake_case with timestamp prefix

### Code Organization (Domain-Driven)
- **Domain Entities**: Rich domain models with business logic and invariants
- **Value Objects**: Immutable objects representing domain concepts
- **Domain Services**: Stateless services for complex business operations
- **Repositories**: Interfaces for data access defined in domain, implemented in infrastructure
- **Use Cases**: Application services orchestrating domain operations
- **Controllers**: Thin controllers delegating to use cases
- **Eloquent Models**: Data access layer in Infrastructure/Persistence
- **Actions**: Single-responsibility classes for specific tasks (legacy)
- **Components**: Reusable Vue components with TypeScript props

### Path Aliases
- `@/` → `resources/js/` (frontend)
- `ziggy-js` → Ziggy route helper integration

### Database Conventions
- Table names: plural snake_case (`investment_tiers`, `user_investments`)
- Foreign keys: `{model}_id` format (`user_id`, `investment_tier_id`)
- Pivot tables: alphabetical order (`investment_user`, not `user_investment`)

### API Patterns
- Use Inertia.js for page rendering (not traditional API endpoints)
- Form submissions return Inertia responses
- Real-time updates via Laravel Echo (if implemented)
- Validation handled by Form Request classes
## Do
main-Driven Design Migration Strategy

### Phase 1: Establish Domain Structure
- Create domain directories and interfaces
- Identify bounded contexts and core entities
- Define repository interfaces in domain layer

### Phase 2: Extract Domain Logic
- Move business logic from Models to Domain Entities
- Create Value Objects for domain concepts
- Implement Domain Services for complex operations

### Phase 3: Implement Application Layer
- Create Use Cases for application workflows
- Implement Command and Query handlers
- Define DTOs for data transfer

### Phase 4: Refactor Infrastructure
- Move Eloquent models to Infrastructure/Persistence
- Implement repository interfaces
- Set up dependency injection for repositories

### Phase 5: Update Presentation Layer
- Refactor controllers to use Use Cases
- Update existing Services to delegate to domain layer
- Maintain backward compatibility during transition

### Coexistence Strategy
During migration, both old and new structures will coexist:
- Legacy code remains in original locations
- New features use domain-driven structure
- Gradual refactoring of existing features
- Use interfaces to decouple old and new code

### Domain Boundaries
- **Investment Context**: Tiers, user investments, tier upgrades
- **Reward Context**: Commissions, referrals, matrix positions, bonuses
- **Financial Context**: Withdrawals, profit distributions, penalties
- **User Context**: Authentication, profiles, membership management