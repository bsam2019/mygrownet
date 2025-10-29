# Documentation Guidelines

## Core Principle

**ONE document per feature** - Always update existing docs instead of creating multiple versions.

---

## Rules

### ✅ DO

1. **Create ONE main document per feature**
   - Example: `RECEIPT_SYSTEM.md` for all receipt documentation
   - Example: `STARTER_KIT_IMPLEMENTATION.md` for starter kit

2. **Update existing documents** when making changes
   - Don't create new versions
   - Add "Last Updated" date
   - Document changes in a Changelog section

3. **Use clear, descriptive names**
   - `FEATURE_NAME.md` format
   - All caps for main feature docs

### ❌ DON'T

1. **Don't create multiple docs for the same feature**
   - ❌ `FEATURE.md`, `FEATURE_COMPLETE.md`, `FEATURE_FINAL.md`, `FEATURE_SUMMARY.md`
   - ✅ Just `FEATURE.md` with all information

2. **Don't create session-specific docs**
   - ❌ `SESSION_SUMMARY_OCT_27.md`
   - ✅ Update the main feature doc

3. **Don't duplicate information**
   - If it's documented, link to it
   - Don't copy-paste between docs

---

## Real Example: Sidebar Documentation

### ❌ What NOT to do
Initially created 5 redundant documents:
- `SIDEBAR_FIX_GUIDE.md`
- `SIDEBAR_COLLAPSE_COMPLETE.md`
- `SIDEBAR_FIXES_COMPLETE.md`
- `SIDEBAR_CLEANUP_NEEDED.md`
- `SESSION_SUMMARY_SIDEBAR_FIXES.md`

**Problem:** Overlapping information, hard to maintain, confusing.

### ✅ What TO do
Consolidated into ONE document:
- `SIDEBAR_FIX_GUIDE.md` - Complete guide

**Result:** Single source of truth, easy to maintain.

---

## Quick Check Before Creating a Doc

1. Does a doc for this feature exist? → **Update it**
2. Am I creating a "summary" or "complete" version? → **Don't, update the original**
3. Am I documenting a session? → **Don't, update the feature doc**
4. Is this a new feature? → **Create ONE main doc**

---

## Document Template

```markdown
# Feature Name

**Last Updated:** [Date]
**Status:** [Development/Testing/Production]

## Overview
Brief description

## Implementation
Technical details, files modified

## Usage
How to use

## Troubleshooting
Common issues

## Changelog
### [Date]
- Change 1
- Change 2
```

---

## Remember

> "If you're creating a second doc about the same feature, you're doing it wrong. Update the first one instead."

**This applies to ALL documentation work.**
