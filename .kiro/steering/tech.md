# Technology Stack

## Backend
- **Framework**: Laravel 12.0 (PHP 8.2+)
- **Database**: SQLite (development), configurable for production
- **Authentication**: Laravel's built-in auth system
- **Permissions**: Spatie Laravel Permission package
- **API**: Inertia.js for SPA-like experience
- **Queue System**: Laravel queues for background processing

## Frontend
- **Framework**: Vue 3 with TypeScript
- **Build Tool**: Vite with Laravel plugin
- **Styling**: Tailwind CSS with custom design system
- **UI Components**: Radix Vue, Heroicons, Lucide icons
- **Charts**: Chart.js with Vue Chart.js wrapper
- **Utilities**: VueUse, Class Variance Authority, SweetAlert2
- **Routing**: Ziggy for Laravel route integration

## Development Tools
- **Testing**: Pest PHP for backend testing
- **Code Quality**: Laravel Pint (PHP), ESLint + Prettier (JS/TS)
- **Type Checking**: TypeScript with strict mode enabled
- **Package Management**: Composer (PHP), npm (Node.js)

## Common Commands

### Development
```bash
# Start development environment (runs server, queue, and Vite)
composer dev

# Start with SSR support
composer dev:ssr

# Frontend development only
npm run dev

# Build for production
npm run build
npm run build:ssr
```

### Code Quality
```bash
# Format code
npm run format
npm run format:check

# Lint JavaScript/TypeScript
npm run lint

# PHP code style (Laravel Pint)
./vendor/bin/pint
```

### Laravel Artisan
```bash
# Run migrations
php artisan migrate

# Start queue worker
php artisan queue:work

# Clear caches
php artisan cache:clear
php artisan config:clear
php artisan view:clear
```

### Testing
```bash
# Run PHP tests
./vendor/bin/pest

# Run specific test
./vendor/bin/pest tests/Feature/ExampleTest.php
```

## Design System & Color Scheme

### Primary Color Palette
The VBIF platform uses a professional, trustworthy color scheme centered around blues and greens to convey financial stability and growth:

**Primary Colors:**
- **Primary Blue**: `#2563eb` (blue-600) - Main brand color for CTAs, links, and primary actions
- **Primary Blue Light**: `#3b82f6` (blue-500) - Hover states and secondary elements
- **Primary Blue Dark**: `#1d4ed8` (blue-700) - Active states and emphasis

**Secondary Colors:**
- **Success Green**: `#059669` (emerald-600) - Positive returns, profits, success states
- **Success Green Light**: `#10b981` (emerald-500) - Growth indicators, positive trends
- **Warning Amber**: `#d97706` (amber-600) - Pending states, caution indicators
- **Error Red**: `#dc2626` (red-600) - Losses, errors, critical alerts

**Accent Colors:**
- **Premium Indigo**: `#4f46e5` (indigo-600) - Elite tier, premium features
- **Premium Purple**: `#7c3aed` (violet-600) - Referral bonuses, special rewards

### Tier-Specific Colors
Each investment tier has its own color identity:
- **Basic**: Gray (`#6b7280`) - Entry level, neutral
- **Starter**: Blue (`#3b82f6`) - Standard blue
- **Builder**: Blue (`#2563eb`) - Primary blue (popular tier)
- **Leader**: Blue (`#1d4ed8`) - Darker blue for authority
- **Elite**: Indigo (`#4f46e5`) - Premium indigo for exclusivity

### Neutral Palette
- **Background**: `#f9fafb` (gray-50) - Main background
- **Surface**: `#ffffff` (white) - Cards, modals, content areas
- **Border**: `#e5e7eb` (gray-200) - Subtle borders and dividers
- **Text Primary**: `#111827` (gray-900) - Main text content
- **Text Secondary**: `#6b7280` (gray-500) - Supporting text, labels

### Usage Guidelines
- Use **Primary Blue** for main CTAs, navigation, and brand elements
- Use **Success Green** for financial gains, positive metrics, and completed states
- Use **Warning Amber** sparingly for pending approvals and caution states
- Use **Premium Indigo** exclusively for elite features and high-value elements
- Maintain high contrast ratios (4.5:1 minimum) for accessibility
- Use gradients sparingly: `from-blue-500 to-blue-600` for premium feel