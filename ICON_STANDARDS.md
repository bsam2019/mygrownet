# Icon Standards Guide

**Created:** November 23, 2025  
**Status:** Official Standards

---

## 📐 Icon Size Standards

### Size Scale
- **xs:** `h-4 w-4` (16px) - Badges, inline indicators, small buttons
- **sm:** `h-5 w-5` (20px) - Standard buttons, menu items, list items
- **md:** `h-6 w-6` (24px) - Card headers, feature icons, section headers
- **lg:** `h-8 w-8` (32px) - Page headers, emphasis, large buttons
- **xl:** `h-12 w-12` (48px) - Empty states, hero sections, major features

### Usage Guidelines

```vue
<!-- Inline badge icon -->
<CheckCircleIcon class="h-4 w-4 text-green-600" />

<!-- Button icon -->
<button class="p-3">
  <PlusIcon class="h-5 w-5" />
</button>

<!-- Card header icon -->
<div class="flex items-center gap-2">
  <UsersIcon class="h-6 w-6 text-blue-600" />
  <h3>My Team</h3>
</div>

<!-- Page header icon -->
<div class="flex items-center gap-3">
  <ChartBarIcon class="h-8 w-8 text-indigo-600" />
  <h1>Analytics</h1>
</div>

<!-- Empty state icon -->
<div class="w-16 h-16 mx-auto">
  <InboxIcon class="h-12 w-12 text-gray-400" />
</div>
```

---

## 🎨 Icon Color Standards

### Semantic Colors

**Primary Actions (Blue)**
- Navigation items
- Primary buttons
- Info messages
- Links

```vue
<HomeIcon class="h-5 w-5 text-blue-600" />
<InformationCircleIcon class="h-5 w-5 text-blue-500" />
```

**Success/Money (Green)**
- Earnings, profits
- Success states
- Completed actions
- Positive indicators

```vue
<CurrencyDollarIcon class="h-5 w-5 text-green-600" />
<CheckCircleIcon class="h-5 w-5 text-green-500" />
```

**Premium/Special (Purple/Indigo)**
- Premium features
- Elite tier
- Special rewards
- Exclusive content

```vue
<SparklesIcon class="h-5 w-5 text-purple-600" />
<CrownIcon class="h-5 w-5 text-indigo-600" />
```

**Warning/Pending (Orange/Amber)**
- Pending states
- Warnings
- Caution indicators
- Attention needed

```vue
<ExclamationTriangleIcon class="h-5 w-5 text-orange-600" />
<ClockIcon class="h-5 w-5 text-amber-500" />
```

**Error/Critical (Red)**
- Errors
- Failed states
- Critical alerts
- Logout/delete

```vue
<XCircleIcon class="h-5 w-5 text-red-600" />
<TrashIcon class="h-5 w-5 text-red-500" />
```

**Neutral (Gray)**
- Disabled states
- Inactive items
- Placeholder content
- Secondary actions

```vue
<EllipsisHorizontalIcon class="h-5 w-5 text-gray-400" />
<LockClosedIcon class="h-5 w-5 text-gray-500" />
```

---

## 📚 Icon Library

**Primary:** Heroicons (Outline for most, Solid for emphasis)

### Common Icons by Category

#### Navigation
```typescript
import {
  HomeIcon,
  UsersIcon,
  WalletIcon,
  AcademicCapIcon,
  Cog6ToothIcon,
  ArrowRightOnRectangleIcon
} from '@heroicons/vue/24/outline';
```

#### Actions
```typescript
import {
  PlusIcon,
  PencilIcon,
  TrashIcon,
  ArrowPathIcon,
  MagnifyingGlassIcon,
  FunnelIcon
} from '@heroicons/vue/24/outline';
```

#### Status
```typescript
import {
  CheckCircleIcon,
  XCircleIcon,
  ExclamationTriangleIcon,
  InformationCircleIcon,
  ClockIcon
} from '@heroicons/vue/24/outline';
```

#### Financial
```typescript
import {
  CurrencyDollarIcon,
  BanknotesIcon,
  CreditCardIcon,
  ChartBarIcon,
  ArrowTrendingUpIcon
} from '@heroicons/vue/24/outline';
```

#### Social
```typescript
import {
  UserPlusIcon,
  UserGroupIcon,
  ChatBubbleLeftIcon,
  EnvelopeIcon,
  ShareIcon
} from '@heroicons/vue/24/outline';
```

---

## 🚫 Emoji Usage Guidelines

### When to Use Emojis
✅ Tool category headers (📚, 🧮, 👑)  
✅ Quick visual recognition  
✅ Playful, friendly contexts  
✅ Non-critical UI elements  

### When NOT to Use Emojis
❌ Navigation buttons  
❌ Form inputs  
❌ Critical actions  
❌ Professional contexts  
❌ Accessibility-critical areas  

### Approved Emojis
- 📚 Learning/Education
- 🧮 Calculator/Tools
- 👑 Premium/Elite
- 🎯 Goals/Targets
- 💰 Money/Earnings
- 🌐 Network/Global
- 📊 Analytics/Charts
- 🎉 Celebration/Success

---

## ✅ Icon Checklist

### Before Adding an Icon
- [ ] Is the size appropriate for context?
- [ ] Is the color semantically correct?
- [ ] Is it from Heroicons library?
- [ ] Does it have proper accessibility?
- [ ] Is it consistent with existing usage?

### Accessibility Requirements
```vue
<!-- Always include aria-label for icon-only buttons -->
<button aria-label="Close menu">
  <XMarkIcon class="h-5 w-5" />
</button>

<!-- Include sr-only text for screen readers -->
<button>
  <PlusIcon class="h-5 w-5" />
  <span class="sr-only">Add new item</span>
</button>

<!-- Use aria-hidden for decorative icons -->
<div>
  <SparklesIcon class="h-5 w-5" aria-hidden="true" />
  <span>Premium Feature</span>
</div>
```

---

## 📋 Icon Audit Results

### Current Usage (Compliant)
✅ Using Heroicons consistently  
✅ Most sizes are appropriate  
✅ Colors follow semantic meaning  

### Areas for Improvement
⚠️ Some inconsistent sizes in filters  
⚠️ Mix of outline/solid variants  
⚠️ Missing aria-labels in some places  

---

## 🔧 Implementation Examples

### Button with Icon
```vue
<button class="flex items-center gap-2 px-4 py-3 bg-blue-600 text-white rounded-lg min-h-[44px]">
  <PlusIcon class="h-5 w-5" />
  <span>Add Member</span>
</button>
```

### Icon-Only Button
```vue
<button 
  class="p-3 rounded-lg hover:bg-gray-100 min-w-[44px] min-h-[44px]"
  aria-label="Search"
>
  <MagnifyingGlassIcon class="h-5 w-5 text-gray-600" />
</button>
```

### Card Header with Icon
```vue
<div class="flex items-center gap-3 mb-4">
  <div class="w-10 h-10 bg-blue-100 rounded-xl flex items-center justify-center">
    <ChartBarIcon class="h-6 w-6 text-blue-600" />
  </div>
  <h3 class="text-lg font-bold">Analytics</h3>
</div>
```

### Status Indicator
```vue
<div class="flex items-center gap-2">
  <CheckCircleIcon class="h-4 w-4 text-green-600" />
  <span class="text-sm text-green-700">Verified</span>
</div>
```

---

## 🎯 Quick Reference

| Context | Size | Example |
|---------|------|---------|
| Badge | xs (h-4) | Status indicators |
| Button | sm (h-5) | Action buttons |
| Card Header | md (h-6) | Section headers |
| Page Header | lg (h-8) | Main headings |
| Empty State | xl (h-12) | No content screens |

| Purpose | Color | Example |
|---------|-------|---------|
| Navigation | Blue | Menu items |
| Success | Green | Completed |
| Premium | Purple | Elite features |
| Warning | Orange | Pending |
| Error | Red | Failed |
| Neutral | Gray | Disabled |

