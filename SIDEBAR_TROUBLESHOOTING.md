# Sidebar Organizational Links - Troubleshooting Guide

## Issue: Can't See Organizational Structure Links

### Quick Fix Steps

#### 1. **Click to Expand the "Employees" Section**
The organizational structure links are inside a collapsible "Employees" submenu. You need to:
1. Look for the **"Employees"** section in the admin sidebar
2. Click on it to expand the submenu
3. You should now see:
   - All Employees
   - Departments
   - Positions
   - **Organizational Chart** ⭐
   - **KPI Management** ⭐
   - **Hiring Roadmap** ⭐
   - Performance
   - Commissions

#### 2. **Auto-Expand Feature**
I've just added code to automatically expand the Employees submenu when you're on organizational pages. This should work after the dev server hot-reloads the component.

#### 3. **Hard Refresh Browser**
If you still don't see the links:
1. Press `Ctrl + Shift + R` (Windows/Linux) or `Cmd + Shift + R` (Mac)
2. This clears the browser cache and reloads

#### 4. **Check Browser Console**
1. Press `F12` to open Developer Tools
2. Look at the Console tab
3. Check for any errors (red text)
4. If you see errors, share them with me

---

## Direct Access URLs

You can also access the pages directly by typing these URLs:

### Organizational Structure Pages
- **Organizational Chart:** `http://your-domain/admin/organization`
- **KPI Management:** `http://your-domain/admin/organization/kpis`
- **Hiring Roadmap:** `http://your-domain/admin/organization/hiring-roadmap`

### Example (if running locally)
- `http://localhost:8000/admin/organization`
- `http://localhost:8000/admin/organization/kpis`
- `http://localhost:8000/admin/organization/hiring-roadmap`

---

## Verification Checklist

### ✅ Backend Verification
Run these commands to verify routes are registered:
```bash
php artisan route:list --name=organization
```

You should see 12 routes including:
- `admin.organization.index`
- `admin.organization.kpis.index`
- `admin.organization.hiring.index`

### ✅ Frontend Verification
Check if the sidebar file was updated:
```bash
# Check the file modification time
ls -la resources/js/components/CustomAdminSidebar.vue
```

### ✅ Dev Server Check
Make sure your dev server is running:
```bash
npm run dev
```

Look for output like:
```
VITE v5.x.x  ready in xxx ms
➜  Local:   http://localhost:5173/
```

---

## What I Just Fixed

### Auto-Expand Logic Added
I added code to automatically expand the "Employees" submenu when you're on:
- `/admin/organization/*` pages
- `/admin/employees/*` pages
- `/admin/departments/*` pages
- `/admin/positions/*` pages
- `/admin/performance/*` pages
- `/admin/commissions/*` pages

This means when you navigate to any organizational structure page, the submenu will automatically open.

---

## Still Not Working?

### Check These:

1. **Is the dev server running?**
   ```bash
   # Check if Vite is running
   netstat -ano | findstr :5173
   ```

2. **Clear browser cache:**
   - Chrome: `Ctrl + Shift + Delete` → Clear cached images and files
   - Firefox: `Ctrl + Shift + Delete` → Cached Web Content

3. **Check localStorage:**
   - Open browser console (F12)
   - Go to Application tab → Local Storage
   - Look for `admin.sidebarSubmenus`
   - Delete it if it exists
   - Refresh the page

4. **Restart dev server:**
   ```bash
   # Stop the dev server (Ctrl + C)
   # Then restart
   npm run dev
   ```

---

## Expected Behavior

### When Sidebar is Expanded (Desktop)
- You should see "Employees" section with a chevron icon
- Click it to expand/collapse
- When expanded, you see 8 menu items including the 3 new organizational links

### When Sidebar is Collapsed (Desktop)
- You see only icons
- Hover over the UserCheck icon to see "Employees" tooltip
- Click it to expand the sidebar

### On Mobile
- Sidebar is hidden by default
- Click hamburger menu to open
- "Employees" section works the same as desktop

---

## Screenshot Guide

### What You Should See:

```
Admin Sidebar
├── User Management ▼
├── LGR Management ▼
├── Finance ▼
├── Venture Builder ▼
├── Growth Fund ▼
├── Reports ▼
├── Employees ▼ ← CLICK HERE
│   ├── All Employees
│   ├── Departments
│   ├── Positions
│   ├── Organizational Chart ⭐ NEW
│   ├── KPI Management ⭐ NEW
│   ├── Hiring Roadmap ⭐ NEW
│   ├── Performance
│   └── Commissions
└── System ▼
```

---

## Quick Test

1. **Open your browser**
2. **Navigate to:** `http://localhost:8000/admin/organization`
3. **Expected result:** 
   - You should see the Organizational Chart page
   - The sidebar "Employees" section should be auto-expanded
   - "Organizational Chart" link should be highlighted in blue

---

## Need More Help?

If you're still having issues, please provide:
1. Screenshot of your sidebar
2. Browser console errors (F12 → Console tab)
3. Output of: `php artisan route:list --name=organization`
4. Confirmation that dev server is running

---

**Last Updated:** November 5, 2025  
**Status:** Auto-expand feature added, should work after hot-reload
