# More Tab - Content Specification

**Last Updated:** November 23, 2025  
**Purpose:** Define exactly what goes on the More tab

---

## Complete More Tab Layout

```
┌─────────────────────────────────────────┐
│                                         │
│  ┌───────────────────────────────────┐  │
│  │  COMPACT PROFILE CARD             │  │
│  │  ─────────────────────────────────│  │
│  │  [Avatar] John Doe • Pro ⭐       │  │
│  │  john@example.com                 │  │
│  │  Progress: ████░░░░ 65% → Senior  │  │
│  │  [Edit Profile Button]            │  │
│  └───────────────────────────────────┘  │
│                                         │
│  ┌───────────────────────────────────┐  │
│  │  👤 ACCOUNT                       │  │
│  ├───────────────────────────────────┤  │
│  │  Edit Profile              →      │  │
│  │  Change Password           →      │  │
│  │  Verification Status       →      │  │
│  └───────────────────────────────────┘  │
│                                         │
│  ┌───────────────────────────────────┐  │
│  │  💬 SUPPORT & HELP                │  │
│  ├───────────────────────────────────┤  │
│  │  Messages              [3] →      │  │
│  │  Support Tickets           →      │  │
│  │  Help Center               →      │  │
│  │  FAQs                      →      │  │
│  └───────────────────────────────────┘  │
│                                         │
│  ┌───────────────────────────────────┐  │
│  │  ⚙️ SETTINGS                      │  │
│  ├───────────────────────────────────┤  │
│  │  Notifications             →      │  │
│  │  Language          English →      │  │
│  │  Theme             Light   →      │  │
│  └───────────────────────────────────┘  │
│                                         │
│  ┌───────────────────────────────────┐  │
│  │  📱 APP & VIEW                    │  │
│  ├───────────────────────────────────┤  │
│  │  Install App               →      │  │
│  │  Switch to Classic View    →      │  │
│  │  About MyGrowNet           →      │  │
│  │  Terms & Privacy           →      │  │
│  └───────────────────────────────────┘  │
│                                         │
│  ┌───────────────────────────────────┐  │
│  │  🚪 LOGOUT                        │  │
│  └───────────────────────────────────┘  │
│                                         │
│         MyGrowNet v1.0.0                │
│                                         │
└─────────────────────────────────────────┘
```

---

## Section 1: Compact Profile Card

**Purpose:** Quick profile overview with edit access

**Content:**
- Avatar (40x40px circle with initial)
- Name + Tier badge + Starter kit indicator (⭐)
- Email address
- Membership progress bar with percentage
- "Edit Profile" button

**Actions:**
- Tap card or button → Opens Edit Profile Modal

**Design:**
- Gradient background (blue-50 to indigo-50)
- Border (blue-100)
- Rounded corners (xl)
- Padding: 16px

---

## Section 2: Account (3 items)

**Purpose:** Personal account management

### 1. Edit Profile
- **Icon:** UserCircleIcon
- **Action:** Opens Edit Profile Modal
- **Shows:** Name, email, phone, address editing

### 2. Change Password
- **Icon:** KeyIcon
- **Action:** Navigate to password change page
- **Shows:** Current password, new password, confirm password

### 3. Verification Status
- **Icon:** ShieldCheckIcon
- **Badge:** "Verified" (green) or "Pending" (yellow) or "Not Verified" (gray)
- **Action:** Opens verification modal/page
- **Shows:** ID verification, phone verification, email verification status

---

## Section 3: Support & Help (4 items)

**Purpose:** Communication and assistance

### 1. Messages
- **Icon:** EnvelopeIcon
- **Badge:** Unread count (red badge with number)
- **Action:** Opens Messages Modal
- **Shows:** Inbox, sent messages, compose

### 2. Support Tickets
- **Icon:** TicketIcon
- **Badge:** Open tickets count (if any)
- **Action:** Opens Support Tickets Modal
- **Shows:** Active tickets, create new ticket, ticket history

### 3. Help Center
- **Icon:** QuestionMarkCircleIcon
- **Action:** Opens Help Center Modal
- **Shows:** Getting started guide, tutorials, common issues

### 4. FAQs
- **Icon:** DocumentTextIcon
- **Action:** Opens FAQs page/modal
- **Shows:** Frequently asked questions with search

---

## Section 4: Settings (3 items)

**Purpose:** App preferences and configuration

### 1. Notifications
- **Icon:** BellIcon
- **Action:** Opens Notifications Settings Modal
- **Shows:** 
  - Push notifications toggle
  - Email notifications toggle
  - SMS notifications toggle
  - Notification types (earnings, team, system)

### 2. Language
- **Icon:** LanguageIcon
- **Subtitle:** Current language (e.g., "English")
- **Action:** Opens Language Selector Modal
- **Shows:** Available languages (English, Bemba, Nyanja, etc.)

### 3. Theme
- **Icon:** SunIcon (light) or MoonIcon (dark)
- **Subtitle:** Current theme (e.g., "Light")
- **Action:** Opens Theme Selector Modal
- **Shows:** Light, Dark, Auto (system)

---

## Section 5: App & View (4 items)

**Purpose:** App installation and view options

### 1. Install App
- **Icon:** ArrowDownTrayIcon
- **Condition:** Only show if PWA not installed
- **Action:** Triggers PWA install prompt
- **Shows:** Install confirmation

### 2. Switch to Classic View
- **Icon:** ComputerDesktopIcon
- **Action:** Navigate to classic dashboard
- **Shows:** Desktop-style dashboard

### 3. About MyGrowNet
- **Icon:** InformationCircleIcon
- **Action:** Opens About Modal
- **Shows:** 
  - App version
  - Company info
  - Mission statement
  - Contact information

### 4. Terms & Privacy
- **Icon:** DocumentTextIcon
- **Action:** Opens Terms & Privacy Modal
- **Shows:** 
  - Terms of Service
  - Privacy Policy
  - Cookie Policy
  - Data handling

---

## Section 6: Logout Button

**Purpose:** Sign out of account

**Design:**
- Full-width button
- Red background (red-50)
- Red border (red-200)
- Red text (red-600)
- Icon: ArrowRightOnRectangleIcon

**Action:**
- Opens Logout Confirmation Modal
- "Are you sure you want to logout?"
- [Cancel] [Logout] buttons

---

## Footer: Version Info

**Content:** "MyGrowNet v1.0.0"
**Style:** Small gray text, centered
**Purpose:** Version tracking for support

---

## What's NOT on More Tab

These items stay on their respective tabs:

### Home Tab
- Balance card
- Quick stats
- Quick actions
- Earnings overview
- Starter kit banner

### Team Tab
- Network stats
- Referral link
- Team members list
- Level breakdown

### Wallet Tab
- Balance details
- Deposit/Withdraw buttons
- Transaction history
- Earnings breakdown

### Tools Tab
- Learning resources
- Calculators
- Business tools
- Premium tools

---

## Badge Logic

**Messages Badge:**
```javascript
// Show red badge with count if unread > 0
messagingData?.unread_count > 0 ? messagingData.unread_count : null
```

**Support Tickets Badge:**
```javascript
// Show orange badge with count if open tickets > 0
supportTickets?.open_count > 0 ? supportTickets.open_count : null
```

**Verification Badge:**
```javascript
// Show status badge
if (user.is_verified) return { text: 'Verified', color: 'green' };
if (user.verification_pending) return { text: 'Pending', color: 'yellow' };
return { text: 'Not Verified', color: 'gray' };
```

---

## Conditional Display Rules

### Install App Button
```javascript
// Only show if:
// 1. PWA is supported
// 2. PWA is not already installed
// 3. Install prompt is available
showInstallButton && !isPWAInstalled
```

### Verification Status
```javascript
// Always show, but badge changes based on status
// Encourages users to complete verification
```

### Language Setting
```javascript
// Show current language as subtitle
// Only show if multiple languages are available
availableLanguages.length > 1
```

### Theme Setting
```javascript
// Show current theme as subtitle
// Icon changes: SunIcon (light), MoonIcon (dark)
```

---

## Interaction Patterns

### Menu Item Tap
1. User taps menu item
2. Slight scale animation (active:scale-95)
3. Background changes (hover:bg-gray-50)
4. Action executes (modal opens or navigation)

### Badge Display
- Red badge: Urgent (unread messages)
- Orange badge: Attention needed (open tickets)
- Green badge: Success (verified)
- Yellow badge: Pending (verification pending)
- Gray badge: Neutral (not verified)

### Section Headers
- Small icon (16x16px)
- Bold text
- Gray background (gray-50)
- Subtle border bottom

---

## Accessibility

### Touch Targets
- Minimum 44x44px for all interactive elements
- Adequate spacing between items (16px)

### Color Contrast
- Text: gray-900 on white (21:1 ratio)
- Icons: gray-400 (sufficient contrast)
- Badges: white text on colored background

### Screen Reader
- Proper ARIA labels
- Badge counts announced
- Section headers marked as headings

---

## Mobile Optimization

### Scrolling
- Smooth scroll behavior
- Momentum scrolling on iOS
- Pull-to-refresh (optional)

### Loading States
- Skeleton loader for profile card
- Shimmer effect for menu items
- Preserve layout during load

### Error Handling
- Show error toast if action fails
- Retry button for failed actions
- Graceful degradation

---

## Summary: What Goes on More Tab

✅ **Include:**
1. Compact profile card (avatar, name, tier, progress)
2. Account settings (edit profile, password, verification)
3. Support & help (messages, tickets, help center, FAQs)
4. App settings (notifications, language, theme)
5. App options (install, switch view, about, terms)
6. Logout button
7. Version info

❌ **Don't Include:**
- Financial information (goes to Wallet tab)
- Team/network data (goes to Team tab)
- Learning resources (goes to Tools tab)
- Quick actions (stays on Home tab)
- Stats/analytics (stays on Home tab)

---

## Implementation Priority

### Must Have (Phase 1)
- ✅ Compact profile card
- ✅ Edit Profile
- ✅ Change Password
- ✅ Messages (with badge)
- ✅ Support Tickets
- ✅ Help Center
- ✅ Settings (basic)
- ✅ Switch to Classic View
- ✅ Logout

### Nice to Have (Phase 2)
- ✅ Verification Status
- ✅ FAQs
- ✅ Notifications settings
- ✅ Language selector
- ✅ Theme selector
- ✅ About page
- ✅ Terms & Privacy

### Future Enhancement (Phase 3)
- ✅ Advanced notification preferences
- ✅ Accessibility settings
- ✅ Data export
- ✅ Account deletion
- ✅ Two-factor authentication

---

This specification ensures the More tab is:
- **Organized** - Clear sections with logical grouping
- **Efficient** - Compact design saves space
- **Intuitive** - Follows familiar patterns
- **Complete** - All necessary settings accessible
- **Scalable** - Easy to add new items in future
