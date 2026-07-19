# Materials Setup Guide

**Quick Guide:** How to set up and use materials in the CMS

---

## 🎯 Overview

The Materials module allows you to:
- Create a library of materials (aluminium profiles, glass, hardware, etc.)
- Organize materials by categories
- Track material prices and price history
- Add materials to jobs with quantities and wastage
- Generate purchase orders
- Use material templates for common job types

---

## 📋 Step-by-Step Setup

### Step 1: Access Materials Management

1. **Login to CMS** at `/cms`
2. **Look for "Materials" in the sidebar** (between Inventory and Assets)
3. **Click on "Materials"** to open the materials library

**Note:** Materials navigation is visible when:
- Construction modules are enabled, OR
- Fabrication module is enabled

---

### Step 2: Create Material Categories (Optional but Recommended)

Categories help organize your materials library.

**Common Categories for Aluminium/Construction:**
- Aluminium Profiles
- Glass & Glazing
- Hardware & Accessories
- Sealants & Adhesives
- Fasteners
- Finishing Materials
- Tools & Consumables

**To Create a Category:**
1. Go to Materials page
2. Click "Manage Categories" button
3. Click "Add Category"
4. Enter:
   - Name (e.g., "Aluminium Profiles")
   - Code (e.g., "ALU-PROF")
   - Description (optional)
   - Icon and color (optional)
5. Click "Save"

---

### Step 3: Add Materials to Your Library

**To Add a Material:**

1. **Go to Materials page**
2. **Click "New Material" button**
3. **Fill in the form:**

   **Basic Information:**
   - **Code:** Unique identifier (e.g., "ALU-6063-50X50")
   - **Name:** Descriptive name (e.g., "Aluminium Profile 6063 50x50mm")
   - **Category:** Select from dropdown (optional)
   - **Description:** Additional details (optional)

   **Pricing & Units:**
   - **Unit:** How it's measured (m, m², kg, pcs, meters, etc.)
   - **Current Price:** Price per unit in Kwacha
   - **Minimum Stock:** Alert level (optional)
   - **Reorder Level:** When to reorder (optional)

   **Supplier Information:**
   - **Supplier:** Supplier name
   - **Supplier Code:** Their product code
   - **Lead Time:** Days to delivery

   **Specifications (JSON):**
   - Add custom fields like:
     ```json
     {
       "dimensions": "50x50mm",
       "grade": "6063-T5",
       "color": "Mill Finish",
       "weight": "1.2 kg/m"
     }
     ```

4. **Click "Create Material"**

---

### Step 4: Seed Default Materials (Quick Start)

If you want to quickly populate your materials library with common items:

**Run the seeder:**
```bash
php artisan db:seed --class=DefaultMaterialsSeeder
```

**This will create 55+ materials including:**
- Aluminium profiles (various sizes)
- Glass types (clear, tinted, laminated)
- Hardware (hinges, handles, locks)
- Sealants and adhesives
- Fasteners
- Finishing materials

**Note:** The seeder creates materials for the first company in your database.

---

## 🔧 Using Materials in Jobs

### Method 1: Add Materials When Creating/Editing a Job

1. **Go to Jobs page**
2. **Create a new job or edit existing**
3. **Scroll to "Materials" section**
4. **Click "Add Material"**
5. **Select material from dropdown** (now populated!)
6. **Enter:**
   - Planned Quantity
   - Unit Price (auto-filled from material library)
   - Wastage Percentage (typical: 5-10% for profiles, 3-5% for glass)
   - Notes (optional)
7. **Click "Add Material"**
8. **Repeat for all materials needed**

### Method 2: Add Materials from Job Details Page

1. **Go to Jobs page**
2. **Click on a job to view details**
3. **Go to "Materials" tab**
4. **Click "Add Material"**
5. **Follow same steps as above**

---

## 📦 Creating Purchase Orders

Once you've added materials to jobs:

1. **Go to Materials → Purchase Orders**
2. **Click "New Purchase Order"**
3. **Select:**
   - Supplier
   - Job (optional - can be general PO)
   - Order Date
   - Expected Delivery Date
4. **Add Items:**
   - Select material
   - Enter quantity
   - Unit price (auto-filled)
   - Adjust if needed
5. **Review totals** (subtotal, tax, total)
6. **Click "Create Purchase Order"**

**PO Status Flow:**
- Draft → Sent → Confirmed → Received → Cancelled

---

## 📊 Material Templates (Advanced)

Create templates for common job types to speed up material planning.

**Example: Standard Window Template**
- Aluminium profile 50x50mm: 6m per window
- Glass 6mm clear: 1.5m² per window
- Hinges: 2 pcs per window
- Handles: 1 pc per window
- Sealant: 0.5 tube per window

**To Create a Template:**
1. Go to Materials → Templates
2. Click "New Template"
3. Enter:
   - Name (e.g., "Standard Window")
   - Job Type (e.g., "window_installation")
   - Description
4. Add template items with quantities per unit
5. Save template

**To Use a Template:**
1. When creating a job
2. Select job type that matches template
3. Click "Apply Material Template"
4. Quantities are auto-calculated based on job size

---

## 💡 Best Practices

### 1. Consistent Naming
Use clear, consistent naming:
- ✅ "Aluminium Profile 6063 50x50mm Mill Finish"
- ❌ "Alu prof 50x50"

### 2. Accurate Units
Always specify the correct unit:
- Profiles: meters (m)
- Glass: square meters (m²)
- Hardware: pieces (pcs)
- Sealants: tubes or kg
- Fasteners: pcs or boxes

### 3. Regular Price Updates
Update material prices regularly:
1. Go to Materials page
2. Click "Bulk Update Prices"
3. Upload CSV or update individually
4. System tracks price history automatically

### 4. Wastage Percentages
Use realistic wastage percentages:
- Aluminium profiles: 5-10%
- Glass: 3-5%
- Hardware: 0-2%
- Sealants: 10-15%

### 5. Supplier Information
Always add supplier details:
- Makes reordering easier
- Tracks lead times
- Helps with price comparisons

---

## 📈 Material Reports

**Available Reports:**
- Material usage by job
- Material costs by period
- Price history and trends
- Stock levels and reorder alerts
- Supplier performance
- Wastage analysis

**To Access:**
Go to Reports → Materials

---

## 🔍 Troubleshooting

### Issue: Material dropdown is empty when adding to job

**Solution:**
1. First create materials in Materials library
2. Or run the default materials seeder
3. Refresh the job page
4. Materials should now appear in dropdown

### Issue: Can't see Materials in sidebar

**Solution:**
1. Enable Construction Modules or Fabrication Module
2. Go to Settings → Modules
3. Toggle on "Construction Modules"
4. Refresh page

### Issue: Material prices not updating

**Solution:**
1. Check if you have permission to edit materials
2. Price history is tracked - old prices remain for historical jobs
3. New jobs will use current price

---

## 🎓 Quick Start Checklist

- [ ] Enable Construction or Fabrication modules
- [ ] Access Materials from sidebar
- [ ] Create material categories (optional)
- [ ] Add materials manually OR run seeder
- [ ] Verify materials appear in dropdown
- [ ] Add materials to a test job
- [ ] Create a purchase order
- [ ] Review material reports

---

## 📞 Need Help?

**Common Questions:**

**Q: How many materials should I create?**
A: Start with 20-30 most common materials. Add more as needed.

**Q: Can I import materials from Excel?**
A: Yes, use the bulk import feature (coming soon) or run the seeder.

**Q: Can I track material stock levels?**
A: Yes, set minimum stock and reorder levels. System will alert you.

**Q: Can I have different prices for different suppliers?**
A: Currently one price per material. Use material variants for different suppliers.

**Q: Can I delete a material?**
A: Yes, but only if it's not used in any jobs or purchase orders.

---

## 🚀 Next Steps

1. **Set up your materials library** (15-30 minutes)
2. **Create material categories** (5 minutes)
3. **Add materials to existing jobs** (ongoing)
4. **Create purchase orders** (as needed)
5. **Review material reports** (weekly/monthly)

---

**Last Updated:** April 25, 2026  
**Module:** Material Planning  
**Status:** ✅ Active
