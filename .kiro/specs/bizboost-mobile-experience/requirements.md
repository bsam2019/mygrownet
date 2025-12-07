# Requirements Document

## Introduction

This document outlines the requirements for enhancing the BizBoost module's mobile experience within the MyGrowNet platform. BizBoost is one of several modules (alongside GrowFinance and GrowBiz) that users access through the MyGrowNet home hub after logging in.

**Important Context:**
- MyGrowNet already has an existing PWA that users install on their phones
- Users access `/home` or `/home-hub` after login to see available modules
- BizBoost is accessed at `/bizboost` as a module within the main MyGrowNet app
- The existing service worker (`public/sw.js`) handles caching for the entire platform
- A separate BizBoost manifest (`public/bizboost-manifest.json`) exists for module-specific metadata

The goal is to make BizBoost feel like a native mobile application when accessed within the MyGrowNet PWA, with features like bottom navigation, gesture-based interactions, enhanced offline support, and app-like transitions—all while working within the existing MyGrowNet PWA infrastructure.

## Requirements

### Requirement 1: Enhanced PWA Integration for BizBoost Module

**User Story:** As a MyGrowNet user accessing BizBoost on mobile, I want the BizBoost module to feel like a native app experience within the MyGrowNet PWA, so that I can efficiently manage my business tools.

#### Acceptance Criteria

1. WHEN a user navigates to BizBoost within the MyGrowNet PWA THEN the system SHALL provide a seamless app-like experience
2. WHEN the MyGrowNet PWA is installed AND the user accesses BizBoost THEN the system SHALL display BizBoost in full-screen mode (inherited from MyGrowNet PWA)
3. WHEN the device is offline THEN the system SHALL serve cached BizBoost pages via the existing MyGrowNet service worker
4. WHEN the existing service worker caches assets THEN it SHALL include BizBoost-specific routes and assets
5. IF the user has push notifications enabled for MyGrowNet THEN the system SHALL deliver BizBoost-specific notifications (sales, messages, reminders)
6. WHEN BizBoost pages are visited THEN the system SHALL cache them for offline access using the existing MyGrowNet service worker

### Requirement 2: Bottom Tab Navigation

**User Story:** As a mobile user, I want to navigate BizBoost using a bottom navigation bar like native apps, so that I can easily reach key features with my thumb.

#### Acceptance Criteria

1. WHEN viewing BizBoost on a mobile device (screen width < 1024px) THEN the system SHALL display a fixed bottom navigation bar
2. WHEN the bottom navigation is displayed THEN the system SHALL hide the traditional sidebar
3. WHEN a user taps a bottom nav item THEN the system SHALL navigate to that section with a smooth transition
4. WHEN a nav item is active THEN the system SHALL highlight it with the primary violet color and show the label
5. WHEN the user scrolls down THEN the system SHALL optionally hide the bottom nav to maximize content area
6. WHEN the user scrolls up THEN the system SHALL show the bottom nav again
7. WHEN the bottom nav is displayed THEN the system SHALL include: Dashboard, Products, Customers, Sales, and a "More" menu
8. WHEN the user taps "More" THEN the system SHALL display a slide-up sheet with additional navigation options

### Requirement 3: Pull-to-Refresh

**User Story:** As a mobile user, I want to pull down on lists to refresh data, so that I can update content using a familiar mobile gesture.

#### Acceptance Criteria

1. WHEN a user pulls down on a list page (Products, Customers, Sales, Posts) THEN the system SHALL display a refresh indicator
2. WHEN the pull distance exceeds the threshold THEN the system SHALL trigger a data refresh
3. WHEN refreshing THEN the system SHALL display a spinning indicator with "Refreshing..." text
4. WHEN the refresh completes THEN the system SHALL update the list with new data and hide the indicator
5. IF the refresh fails THEN the system SHALL display an error toast and restore the previous state

### Requirement 4: Swipe Actions on List Items

**User Story:** As a mobile user, I want to swipe on list items to reveal quick actions, so that I can perform common tasks without opening detail pages.

#### Acceptance Criteria

1. WHEN a user swipes left on a list item THEN the system SHALL reveal action buttons (Edit, Delete)
2. WHEN a user swipes right on a list item THEN the system SHALL reveal a primary action (e.g., Call for customers, Record Sale for products)
3. WHEN the swipe distance exceeds 50% of the item width THEN the system SHALL auto-trigger the primary action
4. WHEN action buttons are revealed THEN the system SHALL display them with appropriate colors (red for delete, violet for edit)
5. WHEN the user taps elsewhere THEN the system SHALL close any open swipe actions
6. IF the item is being swiped THEN the system SHALL prevent vertical scrolling to avoid conflicts

### Requirement 5: Sheet-Style Modals

**User Story:** As a mobile user, I want modals to slide up from the bottom like native app sheets, so that the interface feels more natural on mobile.

#### Acceptance Criteria

1. WHEN a modal is triggered on mobile THEN the system SHALL animate it sliding up from the bottom of the screen
2. WHEN the sheet modal is displayed THEN the system SHALL show a drag handle at the top
3. WHEN the user drags the sheet down THEN the system SHALL allow dismissal by swiping down
4. WHEN the sheet is dragged past 50% of its height THEN the system SHALL auto-dismiss with animation
5. WHEN the sheet is displayed THEN the system SHALL dim the background with a semi-transparent overlay
6. WHEN the user taps the overlay THEN the system SHALL dismiss the sheet
7. WHEN the sheet contains a form THEN the system SHALL prevent accidental dismissal while typing

### Requirement 6: Native-Style Headers

**User Story:** As a mobile user, I want page headers with back buttons and contextual actions, so that navigation feels consistent with native apps.

#### Acceptance Criteria

1. WHEN viewing a detail or sub-page on mobile THEN the system SHALL display a header with a back button
2. WHEN the user taps the back button THEN the system SHALL navigate to the previous page with a slide transition
3. WHEN the page has contextual actions THEN the system SHALL display them as icons in the header right side
4. WHEN the header title is too long THEN the system SHALL truncate it with ellipsis
5. WHEN scrolling down THEN the system SHALL optionally collapse the header to a compact version
6. WHEN the header is displayed THEN the system SHALL use the BizBoost violet gradient as background

### Requirement 7: Skeleton Loading States

**User Story:** As a mobile user, I want to see skeleton placeholders while content loads, so that the app feels responsive even on slow connections.

#### Acceptance Criteria

1. WHEN a page is loading THEN the system SHALL display skeleton placeholders matching the expected content layout
2. WHEN loading a list THEN the system SHALL display 5-8 skeleton list items
3. WHEN loading a detail page THEN the system SHALL display skeleton blocks for header, content sections, and actions
4. WHEN loading stats/cards THEN the system SHALL display skeleton cards with animated shimmer effect
5. WHEN content loads THEN the system SHALL fade in the real content smoothly
6. IF loading takes more than 3 seconds THEN the system SHALL display a "Still loading..." message

### Requirement 8: Floating Action Button (FAB)

**User Story:** As a mobile user, I want a floating action button for primary actions, so that I can quickly create new items without navigating through menus.

#### Acceptance Criteria

1. WHEN viewing a list page on mobile THEN the system SHALL display a FAB in the bottom-right corner
2. WHEN the FAB is displayed THEN the system SHALL position it above the bottom navigation bar
3. WHEN the user taps the FAB THEN the system SHALL either perform the primary action or expand to show multiple actions
4. WHEN the FAB expands THEN the system SHALL display action options with labels (e.g., Add Product, Add Customer, Record Sale)
5. WHEN the user scrolls down THEN the system SHALL shrink the FAB to just an icon
6. WHEN the user scrolls up THEN the system SHALL expand the FAB to show the label again
7. WHEN the FAB is tapped THEN the system SHALL provide haptic feedback (if supported)

### Requirement 9: Gesture-Based Interactions

**User Story:** As a mobile user, I want to use gestures like pinch-to-zoom on images and swipe between tabs, so that interactions feel natural.

#### Acceptance Criteria

1. WHEN viewing product images THEN the system SHALL allow pinch-to-zoom gesture
2. WHEN on a tabbed page THEN the system SHALL allow horizontal swipe to switch between tabs
3. WHEN swiping between tabs THEN the system SHALL animate the transition smoothly
4. WHEN the user performs a long-press on an item THEN the system SHALL enter selection mode or show a context menu
5. WHEN in selection mode THEN the system SHALL allow multi-select by tapping additional items
6. WHEN items are selected THEN the system SHALL display a contextual action bar at the top

### Requirement 10: App-Like Transitions

**User Story:** As a mobile user, I want smooth page transitions that feel like a native app, so that navigation feels fluid and responsive.

#### Acceptance Criteria

1. WHEN navigating forward (e.g., list to detail) THEN the system SHALL use a slide-in-from-right transition
2. WHEN navigating back THEN the system SHALL use a slide-out-to-right transition
3. WHEN opening a modal THEN the system SHALL use a fade-in with scale-up transition
4. WHEN closing a modal THEN the system SHALL use a fade-out with scale-down transition
5. WHEN switching bottom nav tabs THEN the system SHALL use a cross-fade transition
6. WHEN transitions occur THEN the system SHALL maintain 60fps for smooth animation
7. IF the device prefers reduced motion THEN the system SHALL use instant transitions instead

### Requirement 11: Haptic Feedback

**User Story:** As a mobile user, I want haptic feedback on interactions, so that the app feels more tactile and responsive.

#### Acceptance Criteria

1. WHEN the user taps a button THEN the system SHALL trigger a light haptic feedback (if supported)
2. WHEN the user completes a swipe action THEN the system SHALL trigger a medium haptic feedback
3. WHEN an error occurs THEN the system SHALL trigger a warning haptic pattern
4. WHEN a success action completes (e.g., sale recorded) THEN the system SHALL trigger a success haptic pattern
5. IF the device does not support haptics THEN the system SHALL gracefully skip haptic feedback
6. WHEN haptic feedback is triggered THEN the system SHALL respect the user's system haptic settings

### Requirement 12: Offline Support (Enhanced for BizBoost Module)

**User Story:** As a mobile user, I want to access basic BizBoost features offline within the MyGrowNet app, so that I can view my data even without internet connection.

#### Acceptance Criteria

1. WHEN the device goes offline THEN the system SHALL display an offline indicator in the BizBoost header
2. WHEN offline THEN the system SHALL serve cached versions of previously visited BizBoost pages (via MyGrowNet service worker)
3. WHEN offline THEN the system SHALL allow viewing of cached products, customers, and recent sales
4. WHEN the user attempts an action that requires internet THEN the system SHALL queue the action for later sync
5. WHEN the device comes back online THEN the system SHALL automatically sync queued actions
6. WHEN syncing THEN the system SHALL display a "Syncing..." indicator
7. IF a sync conflict occurs THEN the system SHALL notify the user and allow resolution
8. WHEN the MyGrowNet service worker is updated THEN it SHALL include BizBoost routes in its cache strategy
