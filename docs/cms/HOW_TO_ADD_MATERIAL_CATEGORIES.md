# How to Add Material Categories

**Quick Guide:** Managing material categories in the CMS

---

## 🎯 Overview

Material categories help you organize your materials library into logical groups like:
- Aluminium Profiles
- Glass & Glazing
- Hardware & Fittings
- Sealants & Adhesives
- Building Materials
- And more...

---

## 📍 Where to Manage Categories

### Method 1: From Materials Page (Recommended)

1. **Go to Materials page:**
   ```
   /cms/materials
   ```

2. **Click "Manage Categories" button** (top right, next to "Add Material")

3. **You'll see the Categories page** with all existing categories

### Method 2: Direct URL

Navigate directly to:
```
/cms/material-categories
```

---

## ➕ How to Add a New Category

### Step-by-Step:

1. **Click "Add Category" button** (blue button, top right)

2. **Fill in the form:**

   **Required Fields:**
   - **Name:** Category name (e.g., "Paints & Coatings")

   **Optional Fields:**
   - **Code:** Short code (e.g., "PAINT")
   - **Description:** Brief description of what materials belong here
   - **Color:** Choose a color for the category badge
     - Gray, Blue, Green, Yellow, Red, Purple, Pink, Indigo, Orange
   - **Sort Order:** Number to control display order (0 = first)
   - **Active:** Toggle to enable/disable category

3. **Click "Create" button**

4. **Category is created!** ✅

---

## ✏️ How to Edit a Category

1. **Go to Categories page** (`/cms/material-categories`)

2. **Find the category** you want to edit

3. **Click the pencil icon** (edit button)

4. **Update the fields** you want to change

5. **Click "Update" button**

6. **Category is updated!** ✅

---

## 🗑️ How to Delete a Category

**Important:** You can only delete categories that have NO materials assigned to them.

1. **Go to Categories page**

2. **Find the category** you want to delete

3. **Check the material count:**
   - If it shows "0 materials" → You can delete it
   - If it shows "5 materials" → You must reassign or delete those materials first

4. **Click the trash icon** (delete button)

5. **Confirm deletion**

6. **Category is deleted!** ✅

---

## 📋 Category Examples

### For Aluminium Fabrication:

| Name | Code | Color | Description |
|------|------|-------|-------------|
| Aluminium Profiles | ALU_PROF | Gray | Window and door frame profiles |
| Glass & Glazing | GLASS | Blue | All types of glass and glazing materials |
| Hardware & Fittings | HARDWARE | Orange | Handles, hinges, locks, rollers |
| Sealants & Adhesives | SEALANT | Green | Silicone, weather strips, foam tape |
| Fasteners | FASTENER | Yellow | Screws, bolts, rivets |
| Finishing Materials | FINISH | Purple | Powder coating, anodizing materials |

### For Construction:

| Name | Code | Color | Description |
|------|------|-------|-------------|
| Cement & Concrete | CEMENT | Gray | Cement, sand, aggregate, concrete |
| Building Materials | BUILD_MAT | Orange | Bricks, blocks, roofing materials |
| Steel & Metal | STEEL | Blue | Rebars, wire mesh, angle iron |
| Timber & Wood | TIMBER | Yellow | Timber, plywood, wood products |
| Electrical | ELECTRIC | Yellow | Cables, conduits, switches, sockets |
| Plumbing | PLUMB | Blue | Pipes, fittings, valves |
| Paints & Coatings | PAINT | Purple | Paints, primers, sealers |
| Insulation | INSUL | Green | Thermal and sound insulation |

---

## 🎨 Color Guide

Choose colors that make sense for your categories:

- **Gray:** Neutral materials (profiles, cement)
- **Blue:** Water-related (glass, plumbing)
- **Green:** Eco-friendly or sealants
- **Yellow:** Electrical or timber
- **Red:** Safety or critical items
- **Purple:** Finishing or premium items
- **Orange:** Hardware or building materials
- **Pink:** Specialty items
- **Indigo:** Premium or high-value items

---

## 🔢 Sort Order

Control the order categories appear in dropdowns:

- **0** = First
- **1** = Second
- **2** = Third
- etc.

**Example:**
```
Sort Order 0: Aluminium Profiles (most used)
Sort Order 1: Glass & Glazing
Sort Order 2: Hardware & Fittings
Sort Order 3: Sealants & Adhesives
Sort Order 10: Miscellaneous (least used)
```

---

## 💡 Best Practices

### 1. Keep Categories Broad
✅ Good: "Hardware & Fittings"  
❌ Too specific: "Sliding Window Handles"

### 2. Use Consistent Naming
✅ Good: "Cement & Concrete", "Steel & Metal"  
❌ Inconsistent: "Cement/Concrete", "Steel+Metal"

### 3. Assign Logical Colors
- Related categories should have similar colors
- Important categories can have brighter colors

### 4. Set Meaningful Sort Orders
- Most frequently used categories first (0, 1, 2)
- Less common categories last (10, 20, 30)

### 5. Use Codes for Integration
- Codes are useful for imports/exports
- Keep them short and uppercase
- Use underscores for spaces (ALU_PROF, not ALU-PROF)

---

## 🔄 Using Categories with Materials

### When Creating a Material:

1. **Go to Materials page** → Click "Add Material"

2. **Select Category** from dropdown
   - All active categories appear here
   - Sorted by sort_order

3. **Fill in other material details**

4. **Save material**

### When Filtering Materials:

1. **Go to Materials page**

2. **Use Category filter** (dropdown in filters section)

3. **Select a category** to see only materials in that category

---

## 📊 Category Statistics

On the Categories page, each category shows:
- **Name and color badge**
- **Code** (if set)
- **Description** (if set)
- **Material count** (how many materials use this category)
- **Active/Inactive status**

---

## 🚨 Common Issues

### Issue: Can't delete category

**Reason:** Category has materials assigned to it

**Solution:**
1. Go to Materials page
2. Filter by that category
3. Either:
   - Delete all materials in that category, OR
   - Reassign materials to a different category
4. Then delete the category

### Issue: Category not showing in dropdown

**Reason:** Category is marked as inactive

**Solution:**
1. Go to Categories page
2. Edit the category
3. Check the "Active" checkbox
4. Save

### Issue: Categories in wrong order

**Reason:** Sort order not set correctly

**Solution:**
1. Go to Categories page
2. Edit each category
3. Set sort_order values (0, 1, 2, 3, etc.)
4. Save

---

## 🎓 Quick Start Checklist

- [ ] Access Categories page from Materials
- [ ] Review existing 10 default categories
- [ ] Add a new custom category
- [ ] Edit a category to change color
- [ ] Set sort orders for your workflow
- [ ] Test category dropdown in Add Material form
- [ ] Filter materials by category

---

## 📝 API Endpoints

For developers or integrations:

```
GET    /cms/material-categories       - List all categories
POST   /cms/material-categories       - Create new category
PUT    /cms/material-categories/{id}  - Update category
DELETE /cms/material-categories/{id}  - Delete category
```

---

## 🎯 Summary

**To add a category:**
1. Materials page → "Manage Categories" button
2. Click "Add Category"
3. Fill in name (required) and optional fields
4. Click "Create"

**To use a category:**
1. When creating a material
2. Select from Category dropdown
3. Material is now organized under that category

**To manage categories:**
- Edit: Click pencil icon
- Delete: Click trash icon (only if no materials)
- Reorder: Set sort_order values

---

**That's it!** You now have full control over your material categories. 🎉

---

**Last Updated:** April 25, 2026  
**Feature:** Material Categories Management  
**Status:** ✅ Fully Functional
