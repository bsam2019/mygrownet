# AI Layout Generation Fix

**Last Updated:** December 29, 2025  
**Status:** ✅ Complete (All 3 Phases Implemented)

---

## Problem (RESOLVED)

User reported that the AI assistant was "locked into rules" and "cannot create new pages outside current restrictions". The AI was over-constrained with rigid ALWAYS/NEVER rules that prevented creative and unconventional layouts.

---

## Solution Implemented

### ✅ Phase 1: Remove Over-Constraints (COMPLETE)

**Changes Made:**
- Changed "ALWAYS/NEVER" to "Prefer/Avoid" in system prompt
- Simplified `enforceIntelligentRules()` method - removed all forcing logic except:
  - Position defaults (if missing)
  - Style defaults (if missing)
  - Creativity keyword detection
- AI now respects user requests for unconventional layouts

**Files Modified:**
- `app/Services/GrowBuilder/AIContentService.php`

### ✅ Phase 2: Creativity Modes (COMPLETE)

**Changes Made:**
- Added `creativityLevel` to AIContext interface ('guided' | 'balanced' | 'creative')
- Backend adjusts system prompt based on creativity level:
  - **Guided:** Follows best practices closely, conventional layouts
  - **Balanced:** (Default) Flexible with guidelines, adapts to user
  - **Creative:** Maximum freedom, experimental layouts
- Updated `useAIContext` composable to accept creativity level parameter
- Added `getCreativityInstructions()` method in backend
- Added UI toggle in AIHeader component with dropdown menu
- Creativity level persists to localStorage per site
- Integrated creativity level into AI context and backend

**Files Modified:**
- `app/Services/GrowBuilder/AIContentService.php`
- `resources/js/pages/GrowBuilder/Editor/composables/useAIContext.ts`
- `resources/js/pages/GrowBuilder/Editor/components/modals/AIAssistantModal.vue`
- `resources/js/pages/GrowBuilder/Editor/components/modals/ai/AIHeader.vue`

### ✅ Phase 3: Learning System (COMPLETE)

**Changes Made:**

1. **Rejection Tracking:**
   - Added "Dismiss" button to AI message actions
   - Records feedback when users explicitly reject suggestions
   - Records feedback when users click "Retry" (implicit rejection)
   - Tracks both applied (true) and rejected (false) feedback

2. **Enhanced AI Prompt:**
   - Added "LEARNING FROM USER FEEDBACK" section to system prompt
   - AI instructed on how to interpret acceptance rates
   - Adaptive behavior guidelines (try different approaches after rejection)
   - Industry-specific learning integration

3. **Feedback Flow:**
   - User applies → `recordFeedback(type, true)` → saved to database
   - User dismisses → `recordFeedback(type, false)` → saved to database
   - User retries → `recordFeedback(type, false)` → regenerates with new approach
   - AI acknowledges dismissal and offers to try different approach

**Files Modified:**
- `resources/js/pages/GrowBuilder/Editor/components/modals/ai/AIMessageList.vue`
  - Added XMarkIcon import
  - Added "Dismiss" button for actionable messages
  - Added `handleDismiss()` and `handleRegenerate()` functions
  - Updated emit types to include dismiss-content event
  
- `resources/js/pages/GrowBuilder/Editor/components/modals/AIAssistantModal.vue`
  - Added `@dismiss-content` event handler
  - Added `handleDismissContent()` function
  - Updated `regenerateMessage()` to accept feedback data
  
- `app/Services/GrowBuilder/AIContentService.php`
  - Added "LEARNING FROM USER FEEDBACK" section to system prompt
  - Instructions for AI to adapt based on acceptance rates

**Database Schema (existing):**
- `growbuilder_ai_feedback` table tracks:
  - `action_type` - Type of action (content, style, page, etc.)
  - `section_type` - Section type if applicable
  - `business_type` - For industry-specific learning
  - `applied` - Boolean (true = accepted, false = rejected)
  - `user_message` - Original user request
  - `ai_response` - AI's response (truncated)
  - `context` - Additional context data

---

## How It Works

### User Experience

1. **Apply Button:** User clicks "Apply" → Content applied + positive feedback recorded
2. **Dismiss Button:** User clicks "Dismiss" → Negative feedback recorded + AI offers alternative
3. **Retry Button:** User clicks "Retry" → Negative feedback recorded + AI regenerates

### Learning Flow

```
User Action → Feedback Recorded → Database Updated → AI Insights Generated
                                                            ↓
                                              Next Request Uses Insights
```

### AI Adaptation

The AI receives feedback data in its context:
- Overall acceptance rate
- Preferred content types
- Avoided content types
- Session-specific feedback
- Industry-specific insights (from similar businesses)

Based on this data, the AI:
- Continues successful approaches (high acceptance)
- Tries different approaches (low acceptance)
- Asks clarifying questions (multiple rejections)
- Applies industry best practices (new users)

---

## Testing

### Test Cases Verified:

✅ **Feedback Recording:**
- Apply button records positive feedback
- Dismiss button records negative feedback
- Retry button records negative feedback before regenerating

✅ **AI Adaptation:**
- AI receives feedback context in system prompt
- Low acceptance rate triggers more careful suggestions
- Preferred types are prioritized

✅ **UI Feedback:**
- Dismiss button visible on actionable messages
- AI acknowledges dismissal with helpful message
- Retry regenerates with different approach

---

## Files Modified (Complete List)

1. **Backend:**
   - `app/Services/GrowBuilder/AIContentService.php`
     - Phase 1: Flexible guidelines, simplified enforcement
     - Phase 2: Creativity mode instructions
     - Phase 3: Learning from feedback instructions

2. **Frontend:**
   - `resources/js/pages/GrowBuilder/Editor/composables/useAIContext.ts`
     - Added CreativityLevel type
     - Added creativity level parameter
   
   - `resources/js/pages/GrowBuilder/Editor/components/modals/AIAssistantModal.vue`
     - Phase 2: Creativity level state and persistence
     - Phase 3: Dismiss handler, updated regenerate handler
   
   - `resources/js/pages/GrowBuilder/Editor/components/modals/ai/AIHeader.vue`
     - Creativity mode toggle UI
   
   - `resources/js/pages/GrowBuilder/Editor/components/modals/ai/AIMessageList.vue`
     - Phase 3: Dismiss button, updated emit types

3. **Documentation:**
   - `docs/growbuilder/AI_LAYOUT_GENERATION_FIX.md` (this file)

---

## Changelog

### December 29, 2025 - Phase 3 Complete
- ✅ Added "Dismiss" button to reject suggestions
- ✅ Rejection feedback now recorded to database
- ✅ Retry button records feedback before regenerating
- ✅ AI acknowledges dismissal and offers alternatives
- ✅ Added "LEARNING FROM USER FEEDBACK" to AI prompt
- ✅ AI adapts behavior based on acceptance rates
- ✅ All 3 phases now complete

### December 29, 2025 - Phase 2 Complete
- ✅ Added creativity level UI toggle
- ✅ Creativity level persists to localStorage
- ✅ Backend uses creativity level in prompts

### December 29, 2025 - Phase 1 Complete
- ✅ Changed rigid rules to flexible guidelines
- ✅ AI respects user requests for unconventional layouts
