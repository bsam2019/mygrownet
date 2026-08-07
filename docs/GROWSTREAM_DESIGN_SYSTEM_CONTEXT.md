# GrowStream Design System Implementation Context

## Response to Claude Sonnet's Questions

This document provides the complete codebase context needed to refine the Stitch design system for MyGrowNet's GrowStream module.

---

## 1. Tailwind Config Scope

**Q: Is tailwind.config.js shared across all modules or GrowStream-specific?**

**A: SHARED across all MyGrowNet modules** (BizBoost, GrowNet, StockFlow, BMS, GrowMart, ZamStay, etc.)

**Evidence:**
- Single `tailwind.config.js` at project root
- Content paths include all Vue files: `'./resources/js/**/*.{vue,js,ts,jsx,tsx}'`
- Vite config has per-module builds (`build:growstream`, `build:bizboost`, etc.) but they all use the same Tailwind config
- 18+ modules in the platform use the same shared Tailwind setup

**Implication:** 
- Changing global color tokens in `tailwind.config.js` WILL affect all other modules
- GrowStream needs **scoped CSS custom properties** or **module-specific class names** to avoid breaking other modules
- Recommend using CSS custom properties in a GrowStream-specific stylesheet that overrides the base tokens only for GrowStream pages

---

## 2. Dark Mode Support

**Q: Is there an existing dark-mode toggle, or is dark the only theme?**

**A: Platform has BOTH light and dark themes with system preference detection**

**Evidence from `resources/views/app.blade.php` and `resources/css/app.css`:**

```php
// app.blade.php line 1
<html lang="..." @class(['dark' => ($appearance ?? 'system') == 'dark'])>

// Dark mode detection script (runs before styles)
const appearance = '{{ $appearance ?? "system" }}';
if (appearance === 'system') {
    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
    if (prefersDark) {
        document.documentElement.classList.add('dark');
    }
}
```

```css
/* app.css has both :root and .dark definitions */
:root {
    --background: 0 0% 100%; /* white */
    --primary: 0 0% 9%; /* near-black */
    /* ... */
}

.dark {
    --background: 0 0% 3.9%; /* near-black */
    --primary: 0 0% 98%; /* near-white */
    /* ... */
}
```

**Current Implementation:**
- Tailwind dark mode: `darkMode: ['class']` (uses `.dark` class)
- User preference stored as `$appearance` variable (system|light|dark)
- System preference auto-detects via `matchMedia`
- All colors defined as HSL custom properties that flip in dark mode

**Implication:**
- Stitch's design system MUST include both light AND dark variants
- Cannot assume "dark-only" platform
- Document 7's tokens should be exported for BOTH light and dark themes

---

## 3. Shared Layout Components

**Q: Are Navbar/Footer shared platform-wide or GrowStream-specific?**

**A: Each module has its OWN layout component** - GrowStream has `GrowStreamLayout.vue`

**Evidence:**
- 20+ module-specific layouts exist: `BizBoostLayout.vue`, `GrowNetGuestLayout.vue`, `StockFlowLayout.vue`, `GrowMartLayout.vue`, etc.
- `GrowStreamLayout.vue` is GrowStream-specific (navigation items point to GrowStream routes)
- No shared `PlatformLayout.vue` used across modules

**Current GrowStream Layout Navigation:**
```javascript
// GrowStreamLayout.vue
const navItems = [
    { label: 'Home', href: () => route('growstream.home') },
    { label: 'Discover', href: () => route('growstream.browse') },
    { label: 'Search', href: () => route('growstream.search') },
    { label: 'Library', href: () => route('growstream.my-videos') },
    { label: 'Profile', href: () => route('growstream.my-videos') },
];
```

**Implication:**
- Stitch's `Navbar.vue` and `BottomNavBar.vue` can REPLACE `GrowStreamLayout.vue` directly
- No need to adapt to a shared component contract
- Clean drop-in replacement is possible

---

## 4. Font Loading

**Q: Are custom fonts already loaded?**

**A: Partially - only Instrument Sans is loaded**

**Current State:**
- `tailwind.config.js`: `fontFamily.sans: ['Instrument Sans', ...defaultTheme.fontFamily.sans]`
- `resources/css/app.css`: `--font-sans: 'Instrument Sans', ui-sans-serif, ...`
- **Plus Jakarta Sans**: NOT loaded (Stitch requirement)
- **Be Vietnam Pro**: NOT loaded (Stitch documentation mentions it but doesn't use it)
- Font loading mechanism: NOT found (no `<link>` tags in app.blade.php, no `@font-face` rules)

**Implication:**
- **Need to ADD font loading for Plus Jakarta Sans** via Google Fonts CDN or self-hosted
- Current Instrument Sans may conflict unless explicitly overridden for GrowStream
- Be Vietnam Pro can be **DROPPED** (documented but unused in Stitch components)

**Recommended Action:**
Add to `resources/views/app.blade.php` head:
```html
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
```

---

## 5. Icon System

**Q: Which icon library is in use?**

**A: Heroicons (SVG) and Lucide Vue Next**

**Evidence from `package.json` dependencies:**
```json
{
    "@heroicons/vue": "^2.2.0",
    "lucide": "^0.468.0",
    "lucide-vue-next": "^0.468.0"
}
```

**Material Icons NOT installed** - Stitch components use `<span class="material-icons">` which will NOT work

**Current Icon Usage in GrowStreamLayout:**
```vue
<!-- SVG path strings, not icon components -->
<svg>
    <path d="M3 12l9-9 9 9M5 10v10h14V10"/>
</svg>
```

**Implication:**
- **Stitch components MUST be converted** from Material Icons to Heroicons or Lucide
- Cannot use `<span class="material-icons">home</span>` syntax
- Must use `<HomeIcon class="h-5 w-5" />` (Heroicons) or `<Home :size="20" />` (Lucide)

**Recommended Action:**
1. Convert all Material Icon references in Stitch components to Lucide Vue Next
2. Use `lucide-vue-next` for consistency with the rest of MyGrowNet
3. Example conversion:
   ```vue
   <!-- OLD (Stitch) -->
   <span class="material-icons">home</span>
   
   <!-- NEW (MyGrowNet compatible) -->
   <script setup>
   import { Home } from 'lucide-vue-next';
   </script>
   <Home :size="20" />
   ```

---

## 6. Existing Route/Page Structure

**Q: Which Inertia pages correspond to Home, Discovery, Studio, etc.?**

**A: GrowStream page mapping:**

| Stitch Screen | MyGrowNet Page File | Inertia Route Name |
|---|---|---|
| **Home** | `resources/js/pages/GrowStream/Home.vue` | `growstream.home` |
| **Discovery/Browse** | `resources/js/pages/GrowStream/Browse.vue` | `growstream.browse` |
| **Search** | `resources/js/pages/GrowStream/Search.vue` | `growstream.search` |
| **Studio (Creator)** | `resources/js/pages/GrowStream/Creator/*.vue` | `growstream.creator.*` |
| **My Library** | `resources/js/pages/GrowStream/MyVideos.vue` | `growstream.my-videos` |
| **Video Detail** | `resources/js/pages/GrowStream/VideoDetail.vue` | `growstream.video.detail` |
| **Series Detail** | `resources/js/pages/GrowStream/SeriesDetail.vue` | `growstream.series.detail` |
| **Creator Profile** | `resources/js/pages/GrowStream/CreatorProfile.vue` | `growstream.creator.profile` |
| **Notifications** | `resources/js/pages/GrowStream/Notifications.vue` | `growstream.notifications` |
| **Admin** | `resources/js/pages/GrowStream/Admin/*.vue` | `growstream.admin.*` |

**Subdomain Structure:**
- Main domain: `mygrownet.com/growstream/*`
- Subdomain: `growstream.mygrownet.com/*` (root-level routes)

---

## 7. Existing Design Tokens (Platform-Wide)

**Q: Any existing tokens to align with or that would conflict?**

**A: Platform uses shadcn/ui-style HSL tokens (neutral grays, no brand colors defined)**

**Current Token System (from `resources/css/app.css`):**

```css
:root {
    --background: 0 0% 100%;        /* Pure white */
    --foreground: 0 0% 3.9%;        /* Near-black text */
    --primary: 0 0% 9%;             /* NEUTRAL gray (not branded!) */
    --secondary: 0 0% 92.1%;        /* Light gray */
    --muted: 0 0% 96.1%;            /* Very light gray */
    --accent: 0 0% 96.1%;           /* Same as muted */
    --destructive: 0 84.2% 60.2%;   /* Red */
    --border: 0 0% 92.8%;           /* Light gray */
    --radius: 0.5rem;               /* 8px */
}

.dark {
    --background: 0 0% 3.9%;        /* Near-black */
    --foreground: 0 0% 98%;         /* Near-white text */
    --primary: 0 0% 98%;            /* White (flipped) */
    --secondary: 0 0% 14.9%;        /* Dark gray */
    --muted: 0 0% 6.9%;             /* Darker gray */
}
```

**Key Observations:**
1. **NO brand colors** - primary is achromatic gray, not a branded blue/orange/rust
2. **shadcn/ui architecture** - uses semantic tokens (background, foreground, primary, secondary, muted, accent)
3. **HSL format** - all colors as `hsl(var(--token))` with separated H/S/L values
4. **Sidebar tokens** exist - for nav components

**Conflict Risk:**
- If GrowStream changes `--primary` globally, ALL modules will change
- StockFlow may use `--primary` for their brand blue
- BizBoost may use `--primary` for their brand color

**Implication:**
- **DO NOT modify global tokens** in `:root` or `.dark`
- **CREATE GrowStream-scoped tokens** with a namespace:
  ```css
  /* GrowStream-specific overrides */
  .growstream-page {
      --gs-primary: 16 92% 54%;           /* #a73400 from Document 7 */
      --gs-primary-container: 25 97% 90%; /* lighter variant */
      --gs-on-primary: 0 0% 100%;         /* white text on primary */
      /* ... full Document 7 token set */
  }
  ```
- Apply `.growstream-page` class to `GrowStreamLayout.vue` root element

---

## 8. Additional Platform Context

### Module Build System
- **Per-module Vite builds**: Each module has its own build target
- **Separate entry points**: `resources/js/app-{module}.ts` (e.g., `app-growstream.ts`)
- **Isolated bundles**: GrowStream CSS can be scoped without affecting other modules

### Mobile App Deadline
- **iOS/Android apps launching in 1 month**
- **Design tokens MUST be platform-agnostic** (not just Tailwind-specific)
- Recommend exporting Document 7 tokens as JSON/YAML for native teams

### Current GrowStream Implementation
- **No custom GrowStream CSS file** - uses global app.css
- **No gs- prefixed classes** in codebase
- **Dark theme in screenshot** is platform-wide dark mode, not GrowStream-specific

---

## Recommendations for Claude Sonnet

### 1. Color Token Strategy

**Use Document 7's #a73400 as canonical primary**, not #e25822 from component export:
- Document 7 is a complete Material 3-style token system
- #a73400 is deeper, more premium, better for trust/financial context
- #e25822 looks like component-generation drift

**Export tokens in 3 formats:**

#### A. CSS Custom Properties (for web)
```css
/* resources/css/growstream-tokens.css */
.growstream-theme {
    /* Primary palette */
    --gs-primary: 16 92% 54%;
    --gs-on-primary: 0 0% 100%;
    --gs-primary-container: 25 97% 90%;
    --gs-on-primary-container: 16 100% 10%;
    
    /* Surface variants (Material 3) */
    --gs-surface: 0 0% 7%;
    --gs-surface-dim: 0 0% 4%;
    --gs-surface-bright: 0 0% 15%;
    --gs-surface-container-lowest: 0 0% 3%;
    --gs-surface-container-low: 0 0% 6%;
    --gs-surface-container: 0 0% 8%;
    --gs-surface-container-high: 0 0% 11%;
    --gs-surface-container-highest: 0 0% 14%;
    
    /* ... full Document 7 token set */
}
```

#### B. Tailwind Config Extension (for utilities)
```javascript
// tailwind.config.js
module.exports = {
    theme: {
        extend: {
            colors: {
                'gs-primary': 'hsl(var(--gs-primary) / <alpha-value>)',
                'gs-on-primary': 'hsl(var(--gs-on-primary) / <alpha-value>)',
                // ... all GrowStream tokens
            }
        }
    }
}
```

#### C. Platform-Agnostic JSON (for iOS/Android)
```json
{
    "primary": {
        "value": "#a73400",
        "type": "color"
    },
    "primary-container": {
        "value": "#ffdacf",
        "type": "color"
    },
    "surface-container-low": {
        "dark": "#1a1c18",
        "light": "#f0f0ea"
    }
}
```

### 2. Font Loading Fix

Add to `resources/views/app.blade.php` `<head>`:
```html
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
```

Update `tailwind.config.js` with GrowStream-scoped font:
```javascript
fontFamily: {
    'gs-sans': ['Plus Jakarta Sans', 'system-ui', 'sans-serif'],
}
```

### 3. Icon Conversion

All Stitch components must convert from Material Icons to Lucide Vue Next:

| Material Icons | Lucide Vue Next |
|---|---|
| `<span class="material-icons">home</span>` | `<Home :size="20" />` |
| `<span class="material-icons">search</span>` | `<Search :size="20" />` |
| `<span class="material-icons">add</span>` | `<Plus :size="20" />` |
| `<span class="material-icons">notifications</span>` | `<Bell :size="20" />` |
| `<span class="material-icons">person</span>` | `<User :size="20" />` |

Import example:
```vue
<script setup lang="ts">
import { Home, Search, Plus, Bell, User } from 'lucide-vue-next';
</script>
```

### 4. Layout Integration

Replace `GrowStreamLayout.vue` with Stitch's `Navbar.vue` + `BottomNavBar.vue`:

**Current:**
```vue
<!-- GrowStreamLayout.vue (160+ lines) -->
<template>
    <div>
        <header><!-- custom nav --></header>
        <slot />
        <footer><!-- custom bottom nav --></footer>
    </div>
</template>
```

**New:**
```vue
<!-- GrowStreamLayout.vue (refactored) -->
<script setup lang="ts">
import Navbar from '@/components/GrowStream/Stitch/Navbar.vue';
import BottomNavBar from '@/components/GrowStream/Stitch/BottomNavBar.vue';
</script>

<template>
    <div class="growstream-theme">
        <Navbar />
        <main class="min-h-screen bg-[var(--gs-surface)]">
            <slot />
        </main>
        <BottomNavBar />
    </div>
</template>
```

### 5. Radius/Spacing Reconciliation

**Document 7 inconsistency found:**
- Document says: `border-radius: 10px` and `spacing: 16px` (explicit values)
- Generated components use: `rounded-lg` (Tailwind token) and `gap-6` (24px)

**Recommendation:**
- Trust the explicit pixel values in Document 7 (`10px`, `16px`)
- Define in CSS custom properties:
  ```css
  --gs-radius: 10px;
  --gs-spacing-base: 16px;
  ```
- Update Tailwind config:
  ```javascript
  borderRadius: {
      'gs': 'var(--gs-radius)',
  },
  spacing: {
      'gs': 'var(--gs-spacing-base)',
      'gs-2': 'calc(var(--gs-spacing-base) * 2)',
  }
  ```

---

## Final Implementation Checklist

- [ ] Export Document 7 tokens as CSS custom properties with `.growstream-theme` scope
- [ ] Export Document 7 tokens as JSON for iOS/Android teams
- [ ] Add Plus Jakarta Sans font loading to app.blade.php
- [ ] Convert all Material Icons in Stitch components to Lucide Vue Next
- [ ] Reconcile 10px radius and 16px spacing inconsistencies
- [ ] Scope GrowStream colors to avoid breaking other modules (BizBoost, StockFlow, etc.)
- [ ] Replace GrowStreamLayout.vue with Stitch Navbar + BottomNavBar
- [ ] Create `resources/css/growstream-tokens.css` for scoped theme
- [ ] Test both light AND dark modes (not just dark)
- [ ] Verify navigation routes map correctly to existing pages

---

## Questions to Confirm

Before generating refined files, please confirm:

1. **Primary color choice**: Use #a73400 (Document 7) or #e25822 (component export)?
2. **Scope strategy**: Scoped `.growstream-theme` class or global token replacement?
3. **Font priority**: Keep Instrument Sans platform-wide and add Plus Jakarta Sans for GrowStream only?
4. **Icon library**: Convert all to Lucide Vue Next or keep some as raw SVG paths?
5. **Light mode support**: Full light theme implementation or dark-primary with light-fallback?

---

**Generated by:** opencode (Kiro AI)  
**Date:** 2026-08-07  
**For:** Claude Sonnet - GrowStream Stitch Design System Refinement
