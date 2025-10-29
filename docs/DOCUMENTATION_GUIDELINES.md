# Documentation Guidelines for MyGrowNet

**Last Updated:** October 28, 2025

---

## Core Principle

**ONE document per feature** - Update existing docs instead of creating multiple versions.

---

## Guidelines

### ✅ DO

1. **Create ONE main document per feature**
   - Example: `RECEIPT_SYSTEM.md` for all receipt-related documentation
   - Example: `STARTER_KIT_IMPLEMENTATION.md` for starter kit feature

2. **Update existing documents** when changes are made
   - Add "Last Updated" date at the top
   - Add version number
   - Document what changed in a "Changelog" section

3. **Use clear, descriptive names**
   - `FEATURE_NAME.md` format
   - All caps for main feature docs
   - Descriptive and searchable

4. **Include essential sections**
   - Overview
   - Technical Implementation
   - Usage Examples
   - Troubleshooting
   - Changelog

### ❌ DON'T

1. **Don't create multiple docs for the same feature**
   - ❌ `RECEIPT_SYSTEM.md`, `RECEIPT_FLOW.md`, `RECEIPT_GUIDE.md`
   - ✅ Just `RECEIPT_SYSTEM.md` with all information

2. **Don't create "summary" or "final" docs**
   - These become outdated quickly
   - Update the main doc instead

3. **Don't duplicate information**
   - If it's in one doc, link to it
   - Don't copy-paste between docs

4. **Don't create session-specific docs**
   - ❌ `SESSION_SUMMARY_OCT_27.md`
   - ✅ Update the main feature doc

---

## Document Structure

### Main Feature Document Template

```markdown
# Feature Name

**Last Updated:** [Date]
**Version:** [X.Y]
**Status:** [Development/Testing/Production]

---

## Overview
Brief description of the feature

## Technical Implementation
Code locations, architecture, key files

## Usage
How to use the feature

## Configuration
Any configuration needed

## Troubleshooting
Common issues and solutions

## Changelog
### Version X.Y - [Date]
- Change 1
- Change 2
```

---

## Current Main Documents

### Features
- `RECEIPT_SYSTEM.md` - Complete receipt system documentation
- `STARTER_KIT_IMPLEMENTATION.md` - Starter kit feature
- `POINTS_SYSTEM_FIX_SUMMARY.md` - Points system
- `WALLET_POLICY_TERMS.md` - Wallet feature

### Platform
- `MYGROWNET_PLATFORM_CONCEPT.md` - Platform overview
- `LEVEL_STRUCTURE.md` - 7-level system
- `VENTURE_BUILDER_CONCEPT.md` - Venture builder feature

### Technical
- `tech.md` - Technology stack
- `structure.md` - Project structure
- `domain-design.md` - DDD guidelines

---

## Maintenance

### When Adding a Feature
1. Create ONE main document
2. Follow the template structure
3. Include all necessary information
4. Add to this list

### When Updating a Feature
1. Update the existing document
2. Update "Last Updated" date
3. Increment version number
4. Add entry to Changelog section

### When Removing a Feature
1. Archive the document (move to `docs/archive/`)
2. Don't delete (keep for reference)
3. Update README to reflect removal

---

## Benefits

✅ **Easy to find** - One place for all information
✅ **Easy to maintain** - Update one file, not many
✅ **Easy to understand** - Complete picture in one doc
✅ **Version controlled** - Git tracks all changes
✅ **No confusion** - No conflicting information

---

## Real Example: Sidebar Documentation

### ❌ What NOT to do (Sidebar Fix - Initial Approach)
Created multiple redundant documents:
- `SIDEBAR_FIX_GUIDE.md`
- `SIDEBAR_COLLAPSE_COMPLETE.md`
- `SIDEBAR_FIXES_COMPLETE.md`
- `SIDEBAR_CLEANUP_NEEDED.md`
- `SESSION_SUMMARY_SIDEBAR_FIXES.md`

**Problem:** 5 documents with overlapping information, hard to maintain, confusing.

### ✅ What TO do (Sidebar Fix - Corrected Approach)
Consolidated into ONE document:
- `SIDEBAR_FIX_GUIDE.md` - Complete guide with all information

**Result:** Single source of truth, easy to find, easy to maintain.

---

## Remember

> "If you're creating a second doc about the same feature, you're doing it wrong. Update the first one instead."

### Quick Check Before Creating a Doc
1. Does a doc for this feature already exist? → **Update it**
2. Am I creating a "summary" or "complete" version? → **Don't, update the original**
3. Am I documenting a session? → **Don't, update the feature doc**
4. Is this a new feature? → **Create ONE main doc**

---

**This guideline applies to all future development and documentation.**
