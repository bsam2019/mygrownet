# Design Document

## Overview

This design document outlines the implementation of a standalone landing page for BizBoost that allows users to discover, learn about, and install BizBoost as a PWA without requiring authentication. The landing page will serve as a marketing and onboarding entry point for users who are specifically interested in BizBoost.

## Architecture

### Route Structure

```
/bizboost/welcome     → Public landing page (no auth required)
/bizboost             → Dashboard (auth required, redirects to setup if needed)
/bizboost/setup       → Setup wizard (auth required)
/login?redirect=/bizboost → Login with BizBoost redirect
/register?redirect=/bizboost → Register with BizBoost redirect
```

### Component Architecture

```
┌─────────────────────────────────────────────────────────────┐
│                    BizBoost Welcome Page                     │
├─────────────────────────────────────────────────────────────┤
│  ┌─────────────────────────────────────────────────────┐   │
│  │                    Header                            │   │
│  │  Logo | Navigation | Login/Get Started Buttons       │   │
│  └─────────────────────────────────────────────────────┘   │
│  ┌─────────────────────────────────────────────────────┐   │
│  │                    Hero Section                      │   │
│  │  Headline | Subheadline | CTA Buttons | App Preview  │   │
│  └─────────────────────────────────────────────────────┘   │
│  ┌─────────────────────────────────────────────────────┐   │
│  │                  Features Section                    │   │
│  │  AI Content | Customers | Social | Analytics         │   │
│  └─────────────────────────────────────────────────────┘   │
│  ┌─────────────────────────────────────────────────────┐   │
│  │                  Pricing Section                     │   │
│  │  Free | Starter | Growth | Pro tiers                 │   │
│  └─────────────────────────────────────────────────────┘   │
│  ┌─────────────────────────────────────────────────────┐   │
│  │                  Install Section                     │   │
│  │  PWA Install Prompt | iOS Instructions               │   │
│  └─────────────────────────────────────────────────────┘   │
│  ┌─────────────────────────────────────────────────────┐   │
│  │                     Footer                           │   │
│  │  MyGrowNet Links | Legal | Social                    │   │
│  └─────────────────────────────────────────────────────┘   │
└─────────────────────────────────────────────────────────────┘
```

## Components and Interfaces

### Backend Components

#### 1. WelcomeController (New)

**File:** `app/Http/Controllers/BizBoost/WelcomeController.php`

```php
<?php

namespace App\Http\Controllers\BizBoost;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

class WelcomeController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('BizBoost/Welcome', [
            'features' => $this->getFeatures(),
            'pricingTiers' => $this->getPricingTiers(),
        ]);
    }

    private function getFeatures(): array
    {
        return [
            [
                'icon' => 'sparkles',
                'title' => 'AI Content Generator',
                'description' => 'Create engaging social media posts with AI assistance',
            ],
            [
                'icon' => 'users',
                'title' => 'Customer Management',
                'description' => 'Track and manage your customer relationships',
            ],
            [
                'icon' => 'share',
                'title' => 'Social Media Tools',
                'description' => 'Schedule and publish to multiple platforms',
            ],
            [
                'icon' => 'chart',
                'title' => 'Analytics Dashboard',
                'description' => 'Track your business growth and performance',
            ],
        ];
    }

    private function getPricingTiers(): array
    {
        return [
            'free' => [
                'name' => 'Free',
                'price' => 0,
                'features' => ['10 posts/month', '5 AI credits', '50 customers'],
            ],
            'starter' => [
                'name' => 'Starter',
                'price' => 49,
                'features' => ['50 posts/month', '50 AI credits', '500 customers'],
            ],
            'growth' => [
                'name' => 'Growth',
                'price' => 99,
                'features' => ['Unlimited posts', '200 AI credits', 'Unlimited customers'],
                'popular' => true,
            ],
            'pro' => [
                'name' => 'Pro',
                'price' => 199,
                'features' => ['Unlimited posts', 'Unlimited AI', 'Team members'],
            ],
        ];
    }
}
```

#### 2. Route Registration

**File:** `routes/bizboost.php` (update)

Add public welcome route before authenticated routes:

```php
// Public welcome/landing page (no auth required)
Route::get('/bizboost/welcome', [WelcomeController::class, 'index'])->name('bizboost.welcome');
```

### Frontend Components

#### 1. Welcome Page Component

**File:** `resources/js/Pages/BizBoost/Welcome.vue`

Main landing page component with sections for:
- Hero with headline and CTA
- Feature highlights
- Pricing tiers
- PWA install prompt
- Footer with MyGrowNet links

#### 2. PWA Install Integration

Reuse existing `usePWA` composable and `InstallPrompt` component from the mobile experience spec.

## Data Models

No new data models required. The landing page uses static content and existing module configuration from the seeder.

### Module Seeder Update

**File:** `database/seeders/ModuleSeeder.php`

Update BizBoost module to include welcome route:

```php
[
    'id' => 'bizboost',
    'name' => 'BizBoost',
    'slug' => 'bizboost',
    // ... existing config ...
    'routes' => json_encode([
        'integrated' => '/bizboost',
        'standalone' => '/bizboost',
        'setup' => '/bizboost/setup',
        'welcome' => '/bizboost/welcome',  // NEW: Public landing page
    ]),
    // ... rest of config ...
]
```

## Error Handling

### Authentication Redirect

When unauthenticated users try to access `/bizboost` (dashboard):
1. Laravel auth middleware redirects to `/login`
2. Add `intended` URL to session for post-login redirect
3. After login, redirect to `/bizboost` or `/bizboost/setup` based on setup status

### PWA Installation Errors

- Handle browser compatibility gracefully
- Show iOS-specific instructions when needed
- Provide fallback for unsupported browsers

## Testing Strategy

### Unit Tests

1. **WelcomeController Test**
   - Test that welcome page renders without authentication
   - Test that features and pricing data is passed correctly

### Feature Tests

1. **Public Access Test**
   - Verify `/bizboost/welcome` is accessible without auth
   - Verify `/bizboost` redirects to login when not authenticated

2. **Redirect Flow Test**
   - Test login with `redirect=/bizboost` parameter
   - Verify redirect to BizBoost after authentication

### Browser Tests (Manual)

1. **PWA Installation**
   - Test install prompt on Android Chrome
   - Test iOS instructions display on Safari
   - Test installed PWA opens to correct URL

2. **Responsive Design**
   - Test landing page on mobile devices
   - Test landing page on desktop browsers

## UI/UX Design

### Color Scheme

Following BizBoost's existing branding:
- Primary: Violet (`#7c3aed` / violet-600)
- Secondary: Purple (`#9333ea` / purple-600)
- Background: Gradient from violet-50 to white
- Text: Gray-900 for headings, Gray-600 for body

### Typography

- Headings: Bold, large (text-4xl to text-6xl)
- Body: Regular, medium (text-base to text-lg)
- CTAs: Semibold, medium (text-sm to text-base)

### Layout

- Mobile-first responsive design
- Max-width container (max-w-7xl)
- Generous padding and spacing
- Card-based feature and pricing sections

### Animations

- Subtle fade-in on scroll for sections
- Hover effects on buttons and cards
- Smooth transitions for interactive elements

## Implementation Notes

### PWA Manifest

The existing `public/bizboost-manifest.json` already has correct configuration:
- `start_url: "/bizboost"`
- `scope: "/bizboost"`
- BizBoost-specific icons and branding

### SEO Considerations

The welcome page should include:
- Proper meta tags for BizBoost
- Open Graph tags for social sharing
- Structured data for search engines

### Performance

- Lazy load images below the fold
- Minimize JavaScript bundle for landing page
- Use static content where possible
