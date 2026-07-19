# Materials Quick Start Guide 🚀

**Get your materials library set up in 2 minutes!**

---

## 🎯 Two Ways to Set Up Materials

### ✅ Option 1: Automatic Setup (RECOMMENDED - 30 seconds)

Run this single command to instantly create 55+ materials:

```bash
php artisan db:seed --class=DefaultMaterialsSeeder
```

**What this creates:**

**For Aluminium Fabrication Companies:**
- ✅ 5 Aluminium profiles (50x50mm, 60x60mm, sash, mullion, tracks)
- ✅ 4 Glass types (clear 4mm, clear 6mm, tinted, frosted)
- ✅ 6 Hardware items (handles, hinges, locks, rollers, screws)
- ✅ 4 Sealants (silicone clear/black, weather strip, foam tape)

**For Construction Companies:**
- ✅ 5 Cement & concrete materials (OPC, rapid cement, sand, aggregate)
- ✅ 5 Building materials (bricks, blocks, roofing sheets, tiles)
- ✅ 5 Steel & metal (rebars Y10/Y12/Y16, wire mesh, angle iron)
- ✅ 4 Timber (2x4, 2x6, plywood 12mm/18mm)
- ✅ 5 Electrical (cables, conduits, switches, sockets)
- ✅ 5 Plumbing (PVC pipes, copper pipes, elbows, tees)

**Plus 10 material categories automatically created!**

---

### ✅ Option 2: Manual Setup (5-10 minutes)

If you prefer to add materials manually or customize them:

#### Step 1: Access Materials Page

1. **Login to CMS** → `/cms`
2. **Look in sidebar** for "Materials" (between Inventory and Assets)
3. **Click "Materials"**

**Can't see Materials in sidebar?**
- Make sure Construction Modules or Fabrication Module is enabled
- Go to Settings → Modules → Toggle "Construction Modules" ON

#### Step 2: Create Material Categories (Optional)

Categories help organize your materials:

1. Click **"Manage Categories"** button (top right)
2. Click **"Add Category"**
3. Fill in:
   - **Name:** e.g., "Aluminium Profiles"
   - **Code:** e.g., "ALU_PROF"
   - **Icon:** Select from dropdown
   - **Color:** Choose a color
4. Click **"Save"**

**Suggested Categories:**
- Aluminium Profiles
- Glass & Glazing
- Hardware & Fittings
- Sealants & Adhesives
- Building Materials
- Cement & Concrete
- Steel & Metal
- Timber & Wood
- Electrical
- Plumbing

#### Step 3: Add Materials

1. Click **"Add Material"** button (top right)
2. Fill in the form:

**Basic Information:**
```
Code: ALU-50X50
Name: Aluminium Profile 50x50mm
Category: Aluminium Profiles (select from dropdown)
Description: Standard aluminium frame profile
```

**Pricing & Units:**
```
Unit: meters (or m, m², kg, pcs, etc.)
Current Price: 45.00
Minimum Stock: 100 (optional)
Reorder Level: 50 (optional)
```

**Supplier Information:**
```
Supplier: ABC Aluminium Suppliers
Supplier Code: ALU-50-50-MF
Lead Time Days: 7
```

**Specifications (Optional JSON):**
```json
{
  "dimensions": "50x50mm",
  "grade": "6063-T5",
  "finish": "Mill Finish",
  "weight": "1.2 kg/m"
}
```

3. Click **"Create Material"**
4. **Repeat** for all materials you need

---

## 📍 Where to Find Materials Page

### Navigation Path:
```
CMS Dashboard
  └── Sidebar
      └── Materials (📦 icon)
          └── Materials Library
```

### Direct URL:
```
http://your-domain.com/cms/materials
```

### Breadcrumb:
```
CMS > Materials
```

---

## 🔍 Verify Materials Are Working

### Test 1: Check Materials Count
```bash
php artisan tinker
>>> App\Infrastructure\Persistence\Eloquent\CMS\MaterialModel::count()
# Should show number > 0
```

### Test 2: View Materials in Browser
1. Go to `/cms/materials`
2. You should see a table with all materials
3. Search, filter, and sort should work

### Test 3: Add Material to Job
1. Go to any job
2. Click "Add Material" button
3. Dropdown should show all your materials ✅

---

## 📊 What the Seeder Creates

### Material Categories (10)
```
1. Aluminium Profiles (ALU_PROF)
2. Glass & Glazing (GLASS)
3. Hardware & Fittings (HARDWARE)
4. Sealants & Adhesives (SEALANT)
5. Building Materials (BUILD_MAT)
6. Cement & Concrete (CEMENT)
7. Steel & Metal (STEEL)
8. Timber & Wood (TIMBER)
9. Electrical (ELECTRIC)
10. Plumbing (PLUMB)
```

### Sample Materials Created

**Aluminium Profiles:**
- ALU-FRAME-001: Aluminium Frame Profile 50x50mm @ K45/m
- ALU-FRAME-002: Aluminium Frame Profile 60x60mm @ K55/m
- ALU-SASH-001: Aluminium Sash Profile @ K38/m
- ALU-MULLION-001: Aluminium Mullion Profile @ K42/m
- ALU-TRACK-001: Sliding Window Track @ K35/m

**Glass:**
- GLASS-CLEAR-4MM: Clear Glass 4mm @ K120/m²
- GLASS-CLEAR-6MM: Clear Glass 6mm @ K180/m²
- GLASS-TINTED-6MM: Tinted Glass 6mm @ K220/m²
- GLASS-FROSTED-6MM: Frosted Glass 6mm @ K250/m²

**Hardware:**
- HANDLE-SLIDE-001: Sliding Window Handle @ K25/pcs
- HANDLE-CASE-001: Casement Window Handle @ K35/pcs
- HINGE-HEAVY-001: Heavy Duty Hinge @ K18/pcs
- LOCK-SLIDE-001: Sliding Window Lock @ K22/pcs
- ROLLER-SLIDE-001: Sliding Window Roller @ K15/pcs

**And 40+ more materials!**

---

## 🎨 Materials Page Features

### Main Features:
- ✅ **Search:** Find materials by name or code
- ✅ **Filter:** By category, status (active/inactive)
- ✅ **Sort:** By name, price, category
- ✅ **Pagination:** 20 materials per page
- ✅ **Quick Actions:** Edit, delete, view price history

### Material Details:
- Material code and name
- Category badge
- Unit of measurement
- Current price (in Kwacha)
- Supplier information
- Status (active/inactive)
- Last updated date

### Bulk Actions:
- Bulk price updates
- Export to CSV
- Import from CSV (coming soon)

---

## 🔧 Common Tasks

### Update Material Price
1. Go to Materials page
2. Find the material
3. Click "Edit" button
4. Update "Current Price"
5. Click "Save"
6. Price history is automatically tracked!

### View Price History
1. Go to Materials page
2. Find the material
3. Click "Price History" button
4. See all price changes with dates

### Deactivate Material
1. Go to Materials page
2. Find the material
3. Click "Edit" button
4. Toggle "Is Active" to OFF
5. Click "Save"
6. Material won't appear in job dropdowns

### Delete Material
1. Go to Materials page
2. Find the material
3. Click "Delete" button
4. Confirm deletion
5. **Note:** Can only delete if not used in any jobs!

---

## 🚨 Troubleshooting

### Issue: "Materials" not in sidebar

**Solution:**
```bash
# 1. Enable construction modules
# Go to: Settings → Modules → Toggle "Construction Modules" ON

# 2. Or check if fabrication module is enabled
# Go to: Settings → Modules → Toggle "Fabrication Module" ON

# 3. Refresh the page
```

### Issue: Seeder says "No companies found"

**Solution:**
```bash
# Update your company industry type
php artisan tinker
>>> $company = App\Infrastructure\Persistence\Eloquent\CMS\CompanyModel::first()
>>> $company->industry_type = 'aluminium_fabrication'
>>> $company->save()
>>> exit

# Then run seeder again
php artisan db:seed --class=DefaultMaterialsSeeder
```

### Issue: Materials dropdown still empty in job

**Solution:**
1. Verify materials exist: Go to `/cms/materials`
2. Check materials are active (green badge)
3. Refresh the job page (Ctrl+F5)
4. Clear browser cache
5. Check browser console for errors

### Issue: Can't access materials page (404)

**Solution:**
```bash
# Clear route cache
php artisan route:clear

# Verify route exists
php artisan route:list --path=cms/materials

# Should show 8 routes
```

---

## 📝 Quick Commands Reference

```bash
# Seed default materials
php artisan db:seed --class=DefaultMaterialsSeeder

# Check materials count
php artisan tinker
>>> App\Infrastructure\Persistence\Eloquent\CMS\MaterialModel::count()

# Check categories count
>>> App\Infrastructure\Persistence\Eloquent\CMS\MaterialCategoryModel::count()

# View first 5 materials
>>> App\Infrastructure\Persistence\Eloquent\CMS\MaterialModel::take(5)->get(['code', 'name', 'current_price'])

# Clear all materials (careful!)
>>> App\Infrastructure\Persistence\Eloquent\CMS\MaterialModel::truncate()

# Re-run seeder
>>> exit
php artisan db:seed --class=DefaultMaterialsSeeder
```

---

## ✅ Success Checklist

After setup, verify:

- [ ] Materials page accessible at `/cms/materials`
- [ ] Materials appear in the table
- [ ] Can search and filter materials
- [ ] Can create new material manually
- [ ] Can edit existing material
- [ ] Can view price history
- [ ] Materials appear in job dropdown
- [ ] Can add material to job successfully

---

## 🎯 Next Steps

1. **Run the seeder** (30 seconds)
   ```bash
   php artisan db:seed --class=DefaultMaterialsSeeder
   ```

2. **Access materials page** (`/cms/materials`)

3. **Verify materials exist** (should see 50+ materials)

4. **Test adding material to job**
   - Go to any job
   - Click "Add Material"
   - Select from dropdown ✅

5. **Customize materials** as needed
   - Update prices
   - Add more materials
   - Create custom categories

---

## 📞 Need More Help?

**Documentation:**
- Full guide: `docs/cms/MATERIALS_SETUP_GUIDE.md`
- Deployment: `docs/cms/CONSTRUCTION_MODULES_DEPLOYMENT.md`

**Support:**
- Check browser console for errors
- Verify database tables exist
- Ensure migrations ran successfully

---

**Last Updated:** April 25, 2026  
**Status:** ✅ Ready to Use  
**Estimated Setup Time:** 30 seconds (with seeder) or 10 minutes (manual)
