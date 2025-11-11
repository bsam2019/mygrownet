# Team by Level Feature

**Last Updated:** November 7, 2025
**Status:** Production Ready

## Overview

The Team by Level feature allows users to view their entire network organized by referral levels (1-7). This provides a clear hierarchical view of their team structure with detailed member information at each level.

## Implementation

### Backend

**Controller:** `app/Http/Controllers/ReferralController.php`

- **Method:** `referralsByLevel(Request $request)`
- **Route:** `GET /my-team/by-level`
- **Route Name:** `my-team.by-level`

**Key Features:**
- Recursively fetches team members up to 7 levels deep
- Returns level summaries with member counts
- Includes member details: name, email, phone, referrer, tier, join date
- Optimized queries to prevent N+1 issues

### Frontend

**Component:** `resources/js/Pages/Referrals/ByLevel.vue`

**Features:**
- Visual level summary cards showing member count per level
- Interactive level selection
- Detailed member table for selected level
- Shows referrer information for each member
- Displays member tier and join date
- Responsive design for mobile and desktop

### Navigation

- Added "By Level" button in the My Team page (`/my-team`)
- Back button in the By Level page to return to main team view

## User Experience

### Level Summary Cards
- 7 cards representing levels 1-7
- Each card shows:
  - Level number
  - Member count
  - Visual icon (🌱 → ⭐)
  - Color-coded by level
- Click any card to view members at that level

### Member Details Table
Displays for selected level:
- Member name and email
- Phone number
- Who referred them
- Their current tier (with color-coded badge)
- Join date

## Data Structure

### Level Summary
```typescript
interface LevelSummary {
    level: number;
    count: number;
    members: Member[];
}
```

### Member
```typescript
interface Member {
    id: number;
    name: string;
    email: string;
    phone: string;
    referrer_id: number;
    referrer_name: string;
    joined_date: string;
    tier: string;
    level: number;
}
```

## Usage

1. Navigate to "My Team" page
2. Click "By Level" button
3. View level summary cards
4. Click any level card to see members at that level
5. View detailed member information in the table

## Benefits

- **Clear Hierarchy:** Easy to understand team structure
- **Quick Navigation:** Jump to any level instantly
- **Detailed Information:** See who referred whom
- **Performance Tracking:** Monitor growth at each level
- **Contact Information:** Phone numbers for easy communication

## Technical Notes

- Uses recursive queries for efficient data fetching
- Implements proper eager loading to avoid N+1 queries
- Responsive design works on all screen sizes
- Color-coded tiers for quick visual identification
- Level icons provide visual hierarchy representation

## Future Enhancements

Potential improvements:
- Export team list by level
- Filter by tier within each level
- Search functionality
- Sort by join date or name
- Activity status indicators
- Commission earned per level
