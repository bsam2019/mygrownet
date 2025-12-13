# GrowBiz Module Architecture

**Last Updated:** December 13, 2025
**Status:** Architecture Planning
**Priority:** HIGH - Core Module Design

---

## Overview

GrowBiz is a **standalone module** within the MyGrowNet platform ecosystem. It must be:

1. **Installable as a PWA** - Users can install it on their phones like a native app
2. **Mobile-First Design** - Primary interface designed for smartphones
3. **Modular Architecture** - Can operate independently or integrate with other modules
4. **Offline-Capable** - Works without internet connection

---

## Module Identity

### What GrowBiz IS
- ✅ A standalone business management module
- ✅ Installable Progressive Web App (PWA)
- ✅ Mobile-first, touch-optimized interface
- ✅ Works offline with sync capabilities
- ✅ Part of the MyGrowNet module ecosystem
- ✅ Can integrate with GrowFinance, BizBoost when available

### What GrowBiz is NOT
- ❌ A desktop-first application
- ❌ Dependent on other modules to function
- ❌ A web-only experience
- ❌ Requiring constant internet connection

---

## PWA Requirements

### Manifest Configuration
```json
{
  "name": "GrowBiz - Business Management",
  "short_name": "GrowBiz",
  "description": "Complete business management for SMEs",
  "start_url": "/growbiz",
  "display": "standalone",
  "background_color": "#ffffff",
  "theme_color": "#2563eb",
  "orientation": "portrait-primary",
  "icons": [
    {
      "src": "/icons/growbiz-192.png",
      "sizes": "192x192",
      "type": "image/png",
      "purpose": "any maskable"
    },
    {
      "src": "/icons/growbiz-512.png",
      "sizes": "512x512",
      "type": "image/png",
      "purpose": "any maskable"
    }
  ],
  "screenshots": [
    {
      "src": "/screenshots/growbiz-dashboard.png",
      "sizes": "1080x1920",
      "type": "image/png",
      "form_factor": "narrow"
    }
  ],
  "categories": ["business", "productivity"],
  "shortcuts": [
    {
      "name": "New Task",
      "url": "/growbiz/tasks/create",
      "icon": "/icons/task-add.png"
    },
    {
      "name": "POS Terminal",
      "url": "/growbiz/pos/terminal",
      "icon": "/icons/pos.png"
    },
    {
      "name": "Appointments",
      "url": "/growbiz/appointments",
      "icon": "/icons/calendar.png"
    }
  ]
}
```

### Service Worker Features
- **Precaching**: Static assets (JS, CSS, images, fonts)
- **Runtime Caching**: API responses with stale-while-revalidate
- **Background Sync**: Queue offline operations for later sync
- **Push Notifications**: Task reminders, appointment alerts
- **Offline Fallback**: Show cached data when offline

### Install Prompt
- Show install banner after 2nd visit
- "Add to Home Screen" button in settings
- Track installation analytics

---

## Mobile-First Design Principles

### Screen Sizes
- **Primary Target**: 360px - 428px (most smartphones)
- **Secondary**: 768px+ (tablets, desktop)
- **Minimum Supported**: 320px

### Touch Targets
- Minimum touch target: 44x44px
- Spacing between targets: 8px minimum
- Bottom navigation for primary actions
- Swipe gestures for common actions

### Navigation Pattern
```
┌─────────────────────────────────────┐
│  Header (App Bar)                   │
│  - Back button / Menu               │
│  - Title                            │
│  - Action buttons                   │
├─────────────────────────────────────┤
│                                     │
│  Main Content Area                  │
│  - Scrollable                       │
│  - Pull to refresh                  │
│  - Infinite scroll where needed     │
│                                     │
│                                     │
│                                     │
├─────────────────────────────────────┤
│  Bottom Navigation (5 items max)    │
│  Home | Tasks | To-Do | Team | More │
└─────────────────────────────────────┘
```

### Mobile UI Components
- **Bottom Sheets**: For forms, filters, actions
- **Floating Action Button (FAB)**: Primary create action
- **Swipe Actions**: Complete, delete, archive
- **Pull to Refresh**: Update data
- **Skeleton Loaders**: Loading states
- **Toast Notifications**: Feedback messages

### Gestures
- Swipe right: Complete task/todo
- Swipe left: Delete/archive
- Long press: Multi-select mode
- Pull down: Refresh
- Pinch: Zoom (Gantt chart)

---

## Module Structure

### Directory Organization
```
app/
├── Domain/GrowBiz/
│   ├── Entities/
│   ├── Services/
│   ├── Repositories/
│   └── ValueObjects/
├── Http/Controllers/GrowBiz/
├── Infrastructure/Persistence/Eloquent/
│   └── GrowBiz*.php
└── Providers/
    └── GrowBizServiceProvider.php

resources/js/
├── layouts/
│   └── GrowBizLayout.vue          # Mobile-first layout
├── pages/GrowBiz/
│   ├── Dashboard.vue
│   ├── Tasks/
│   ├── Todos/
│   ├── Projects/
│   ├── Inventory/
│   ├── Appointments/
│   └── POS/
├── components/GrowBiz/
│   ├── BottomNav.vue
│   ├── BottomSheet.vue
│   ├── FAB.vue
│   ├── SwipeableCard.vue
│   └── PullToRefresh.vue
└── composables/
    ├── useOffline.ts
    ├── usePWA.ts
    └── useSwipe.ts

public/
├── manifest.growbiz.json
├── sw-growbiz.js
└── icons/growbiz/
```

### Routes Structure
```php
// routes/growbiz.php
Route::prefix('growbiz')
    ->name('growbiz.')
    ->middleware(['auth', 'verified', 'module:growbiz'])
    ->group(function () {
        // Dashboard
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
        
        // Tasks
        Route::resource('tasks', TaskController::class);
        
        // Personal Todos
        Route::resource('todos', TodoController::class);
        
        // Projects
        Route::resource('projects', ProjectController::class);
        Route::get('projects/{project}/kanban', [ProjectController::class, 'kanban']);
        Route::get('projects/{project}/gantt', [ProjectController::class, 'gantt']);
        
        // Inventory
        Route::resource('inventory', InventoryController::class);
        
        // Appointments
        Route::resource('appointments', AppointmentController::class);
        
        // POS
        Route::prefix('pos')->name('pos.')->group(function () {
            Route::get('terminal', [POSController::class, 'terminal']);
            Route::post('sales', [POSController::class, 'createSale']);
            Route::get('shifts', [POSController::class, 'shifts']);
        });
        
        // PWA
        Route::get('manifest.json', [PWAController::class, 'manifest']);
        Route::get('offline', [PWAController::class, 'offline']);
    });
```

---

## Offline Architecture

### Data Sync Strategy
```
┌─────────────────┐     ┌─────────────────┐     ┌─────────────────┐
│   IndexedDB     │────▶│   Sync Queue    │────▶│   Server API    │
│   (Local)       │◀────│   (Background)  │◀────│   (Remote)      │
└─────────────────┘     └─────────────────┘     └─────────────────┘
```

### Offline-First Data Flow
1. **Read**: Check IndexedDB first → API if online → Update cache
2. **Write**: Save to IndexedDB → Queue for sync → Sync when online
3. **Conflict**: Server wins (with user notification)

### Cacheable Data
| Data Type | Cache Strategy | Sync Frequency |
|-----------|---------------|----------------|
| Tasks | Cache first | Real-time |
| Todos | Cache first | Real-time |
| Inventory | Cache first | On change |
| Appointments | Cache first | Every 5 min |
| POS Sales | Queue offline | On reconnect |
| Settings | Cache first | On change |

### IndexedDB Schema
```javascript
const dbSchema = {
  tasks: {
    keyPath: 'id',
    indexes: ['status', 'due_date', 'project_id']
  },
  todos: {
    keyPath: 'id',
    indexes: ['status', 'due_date', 'priority']
  },
  inventory: {
    keyPath: 'id',
    indexes: ['sku', 'category_id', 'low_stock']
  },
  appointments: {
    keyPath: 'id',
    indexes: ['date', 'status', 'provider_id']
  },
  pos_sales: {
    keyPath: 'id',
    indexes: ['date', 'synced']
  },
  sync_queue: {
    keyPath: 'id',
    indexes: ['entity', 'action', 'created_at']
  }
};
```

---

## Integration with MyGrowNet Ecosystem

### Module Detection
```php
// Check if other modules are available
$hasGrowFinance = ModuleService::isEnabled('growfinance', $user);
$hasBizBoost = ModuleService::isEnabled('bizboost', $user);

// Show integration options only when available
if ($hasGrowFinance) {
    // Show "Create Invoice" option in POS
    // Show "Link to Expense" in Inventory
}
```

### Cross-Module Features
| Feature | GrowBiz Only | + GrowFinance | + BizBoost |
|---------|--------------|---------------|------------|
| POS Sales | Manual recording | Auto-invoice | - |
| Inventory | Stock tracking | Purchase orders | Product sync |
| Appointments | Scheduling | Auto-invoice | Online booking |
| Customers | Basic CRM | Full accounting | Marketing |

### Standalone Mode
When GrowBiz is the only module:
- All features work independently
- No external dependencies
- Self-contained data model
- Basic reporting included

---

## Implementation Checklist

### Phase 1: PWA Foundation
- [ ] Create `manifest.growbiz.json`
- [ ] Create Service Worker (`sw-growbiz.js`)
- [ ] Add install prompt component
- [ ] Configure Vite for PWA build
- [ ] Add app icons (192px, 512px)
- [ ] Test installation on Android/iOS

### Phase 2: Mobile-First UI
- [ ] Refactor GrowBizLayout for mobile
- [ ] Implement BottomNav component
- [ ] Add BottomSheet component
- [ ] Add FAB component
- [ ] Implement swipe gestures
- [ ] Add pull-to-refresh
- [ ] Test on various screen sizes

### Phase 3: Offline Support
- [ ] Set up IndexedDB with Dexie.js
- [ ] Implement sync queue
- [ ] Add offline detection
- [ ] Cache API responses
- [ ] Handle offline mutations
- [ ] Add conflict resolution
- [ ] Test offline scenarios

### Phase 4: Push Notifications
- [ ] Configure web push
- [ ] Add notification permissions
- [ ] Implement task reminders
- [ ] Implement appointment alerts
- [ ] Test on mobile devices

---

## Technical Stack

### Frontend
- **Vue 3** with Composition API
- **TypeScript** for type safety
- **Tailwind CSS** for styling
- **Vite PWA Plugin** for PWA features
- **Dexie.js** for IndexedDB
- **Workbox** for Service Worker

### Backend
- **Laravel 12** with Inertia.js
- **Domain-Driven Design** architecture
- **SQLite/MySQL** database
- **Laravel Queue** for background jobs
- **Web Push** for notifications

### PWA Tools
```bash
# Install PWA dependencies
npm install vite-plugin-pwa workbox-window dexie

# Configure vite.config.ts
import { VitePWA } from 'vite-plugin-pwa'

export default defineConfig({
  plugins: [
    VitePWA({
      registerType: 'autoUpdate',
      manifest: false, // Use custom manifest
      workbox: {
        globPatterns: ['**/*.{js,css,html,ico,png,svg,woff2}'],
        runtimeCaching: [
          {
            urlPattern: /^https:\/\/api\./,
            handler: 'StaleWhileRevalidate',
          }
        ]
      }
    })
  ]
})
```

---

## Success Metrics

### PWA Metrics
- Install rate > 30% of active users
- Offline usage > 20% of sessions
- Push notification opt-in > 50%

### Mobile UX Metrics
- Time to interactive < 3 seconds
- First contentful paint < 1.5 seconds
- Lighthouse PWA score > 90

### User Engagement
- Daily active users on mobile > 70%
- Session duration on mobile > 5 minutes
- Feature adoption rate > 60%

---

## Related Documentation

- `docs/growbiz/GROWBIZ_ASSESSMENT_AND_ENHANCEMENTS.md` - Feature planning
- `docs/growbiz/USER_FLOW_AND_REMAINING_FEATURES.md` - User flows
- `docs/tech.md` - Technology stack
- `docs/structure.md` - Project structure

---

## Changelog

### December 13, 2025
- Initial architecture document created
- Defined PWA requirements
- Defined mobile-first design principles
- Defined offline architecture
- Created implementation checklist

---

**Document Owner:** Development Team
**Review Cycle:** Weekly during implementation
**Next Review:** December 20, 2025
