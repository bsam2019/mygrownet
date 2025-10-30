# Venture Builder - Access URLs

**Last Updated:** October 29, 2025

---

## Admin URLs (Requires Admin Role)

### Main Pages (Currently Built)

1. **Dashboard**
   - URL: `/admin/ventures/dashboard`
   - Route: `admin.ventures.dashboard`
   - Description: Overview with stats, recent ventures, and investments

2. **All Ventures**
   - URL: `/admin/ventures`
   - Route: `admin.ventures.index`
   - Description: List all ventures with search and filters

3. **Create Venture**
   - URL: `/admin/ventures/create`
   - Route: `admin.ventures.create`
   - Description: Form to create a new venture

4. **Edit Venture**
   - URL: `/admin/ventures/{id}/edit`
   - Route: `admin.ventures.edit`
   - Description: Edit existing venture (page not yet created)

### Venture Management

5. **Approve Venture**
   - Method: POST
   - URL: `/admin/ventures/{id}/approve`
   - Route: `admin.ventures.approve`

6. **Launch Funding**
   - Method: POST
   - URL: `/admin/ventures/{id}/launch-funding`
   - Route: `admin.ventures.launch-funding`

7. **Close Funding**
   - Method: POST
   - URL: `/admin/ventures/{id}/close-funding`
   - Route: `admin.ventures.close-funding`

8. **Activate Venture**
   - Method: POST
   - URL: `/admin/ventures/{id}/activate`
   - Route: `admin.ventures.activate`

### Investment Management

9. **View Investments**
   - URL: `/admin/ventures/{id}/investments`
   - Route: `admin.ventures.investments`

10. **Confirm Investment**
    - Method: POST
    - URL: `/admin/ventures/investments/{id}/confirm`
    - Route: `admin.ventures.investments.confirm`

11. **Refund Investment**
    - Method: POST
    - URL: `/admin/ventures/investments/{id}/refund`
    - Route: `admin.ventures.investments.refund`

### Shareholder Management

12. **View Shareholders**
    - URL: `/admin/ventures/{id}/shareholders`
    - Route: `admin.ventures.shareholders`

13. **Register Shareholders**
    - Method: POST
    - URL: `/admin/ventures/{id}/register-shareholders`
    - Route: `admin.ventures.register-shareholders`

### Document Management

14. **View Documents**
    - URL: `/admin/ventures/{id}/documents`
    - Route: `admin.ventures.documents`

15. **Upload Document**
    - Method: POST
    - URL: `/admin/ventures/{id}/documents`
    - Route: `admin.ventures.documents.upload`

16. **Delete Document**
    - Method: DELETE
    - URL: `/admin/ventures/documents/{id}`
    - Route: `admin.ventures.documents.delete`

### Updates Management

17. **View Updates**
    - URL: `/admin/ventures/{id}/updates`
    - Route: `admin.ventures.updates`

18. **Create Update**
    - Method: POST
    - URL: `/admin/ventures/{id}/updates`
    - Route: `admin.ventures.updates.create`

19. **Edit Update**
    - Method: PUT
    - URL: `/admin/ventures/updates/{id}`
    - Route: `admin.ventures.updates.update`

20. **Delete Update**
    - Method: DELETE
    - URL: `/admin/ventures/updates/{id}`
    - Route: `admin.ventures.updates.delete`

### Dividend Management

21. **View Dividends**
    - URL: `/admin/ventures/{id}/dividends`
    - Route: `admin.ventures.dividends`

22. **Declare Dividend**
    - Method: POST
    - URL: `/admin/ventures/{id}/dividends/declare`
    - Route: `admin.ventures.dividends.declare`

23. **Process Dividend**
    - Method: POST
    - URL: `/admin/ventures/dividends/{id}/process`
    - Route: `admin.ventures.dividends.process`

### Category Management

24. **View Categories**
    - URL: `/admin/ventures/categories`
    - Route: `admin.ventures.categories`

25. **Create Category**
    - Method: POST
    - URL: `/admin/ventures/categories`
    - Route: `admin.ventures.categories.store`

26. **Update Category**
    - Method: PUT
    - URL: `/admin/ventures/categories/{id}`
    - Route: `admin.ventures.categories.update`

### Analytics

27. **Analytics Dashboard**
    - URL: `/admin/ventures/analytics`
    - Route: `admin.ventures.analytics`

---

## Member URLs (Requires Authentication)

### Marketplace

1. **Browse Ventures**
   - URL: `/mygrownet/ventures`
   - Route: `mygrownet.ventures.index`
   - Description: Browse available ventures for investment

2. **View Venture Details**
   - URL: `/mygrownet/ventures/{slug}`
   - Route: `mygrownet.ventures.show`
   - Description: View detailed information about a venture

3. **Make Investment**
   - Method: POST
   - URL: `/mygrownet/ventures/{id}/invest`
   - Route: `mygrownet.ventures.invest`

### My Investments

4. **My Investments**
   - URL: `/mygrownet/my-investments`
   - Route: `mygrownet.ventures.my-investments`
   - Description: View all your investments

5. **Investment Details**
   - URL: `/mygrownet/my-investments/{id}`
   - Route: `mygrownet.ventures.investment-details`
   - Description: View details of a specific investment

### Portfolio

6. **My Portfolio**
   - URL: `/mygrownet/portfolio`
   - Route: `mygrownet.ventures.portfolio`
   - Description: Overview of all your venture investments

7. **Dividend History**
   - URL: `/mygrownet/dividends`
   - Route: `mygrownet.ventures.dividends`
   - Description: View all dividend payments received

### Documents

8. **Download Document**
   - URL: `/mygrownet/ventures/{venture_id}/documents/{document_id}/download`
   - Route: `mygrownet.ventures.documents.download`

---

## Quick Access (Local Development)

### Start Development Server
```bash
# Terminal 1: Laravel server
php artisan serve

# Terminal 2: Vite dev server
npm run dev
```

### Access URLs
- **Admin Dashboard**: http://localhost:8000/admin/ventures/dashboard
- **All Ventures**: http://localhost:8000/admin/ventures
- **Create Venture**: http://localhost:8000/admin/ventures/create
- **Member Marketplace**: http://localhost:8000/mygrownet/ventures

---

## Testing Checklist

### Admin Testing
- [ ] Access admin dashboard
- [ ] View ventures list
- [ ] Create a new venture
- [ ] Edit a venture
- [ ] Approve a venture
- [ ] Launch funding
- [ ] View investments
- [ ] Upload documents

### Member Testing
- [ ] Browse venture marketplace
- [ ] View venture details
- [ ] Make an investment
- [ ] View my investments
- [ ] View portfolio
- [ ] Download documents

---

## Notes

- All admin routes require the `admin` role
- All member routes require authentication
- Some routes are API endpoints (POST, PUT, DELETE) and don't have pages
- Pages marked "not yet created" have routes but no Vue components yet

---

## Sidebar Navigation

The Venture Builder menu appears in the admin sidebar with these items:
1. Dashboard → `/admin/ventures/dashboard`
2. All Ventures → `/admin/ventures`
3. Create Venture → `/admin/ventures/create`
4. Investments → `/admin/ventures/investments` (placeholder)
5. Categories → `/admin/ventures/categories` (placeholder)
6. Analytics → `/admin/ventures/analytics` (placeholder)

---

**Status**: Phase 1 Complete, Phase 2 In Progress
