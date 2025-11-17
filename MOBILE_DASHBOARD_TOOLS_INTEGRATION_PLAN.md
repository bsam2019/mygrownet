# Mobile Dashboard Tools Integration Plan

**Issue:** Tools are not integrated into the mobile dashboard using SPA approach.

**Current State:**
- Tools exist as separate pages
- Modals just link to external pages
- Breaks SPA experience

**Required State:**
- Tools embedded directly in Learn tab
- Tab selector to switch between tools
- Everything stays within mobile dashboard
- No page navigation

---

## Implementation Plan

### 1. Add Tool State Variable
```typescript
const activeTool = ref<'content' | 'calculator' | 'goals' | 'network'>('content');
```

### 2. Replace Learn Tab Content

**Structure:**
```
Learn Tab
├─ Tool Selector (4 buttons)
│  ├─ Content
│  ├─ Calculator
│  ├─ Goals
│  └─ Network
│
├─ Content Section (v-show="activeTool === 'content'")
│  └─ E-books, Videos, Templates grid
│
├─ Calculator Section (v-show="activeTool === 'calculator'")
│  └─ Embedded earnings calculator
│
├─ Goals Section (v-show="activeTool === 'goals'")
│  └─ Embedded goal tracker
│
└─ Network Section (v-show="activeTool === 'network'")
   └─ Embedded network visualizer
```

### 3. Embed Calculator Component

**Simplified version for mobile:**
- Earning type selector
- Basic inputs
- Results display
- Compact layout

### 4. Embed Goals Component

**Simplified version for mobile:**
- Active goals list
- Create goal button
- Progress bars
- Compact cards

### 5. Embed Network Component

**Simplified version for mobile:**
- Network stats
- Level breakdown
- Simplified tree view
- Expandable nodes

---

## Next Steps

1. Add `activeTool` state variable
2. Create tool selector UI
3. Embed simplified calculator
4. Embed simplified goals tracker
5. Embed simplified network visualizer
6. Remove external navigation modals
7. Test SPA navigation

---

**This will keep everything within the mobile dashboard SPA!**
