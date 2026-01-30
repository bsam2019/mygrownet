# Geopamu Website Documentation

**Last Updated:** January 28, 2026  
**Status:** Production Ready

## Overview

Geopamu is a professional printing and branding business website integrated into the MyGrowNet platform. The site features a modern, elegant design with **blue and red color scheme** (matching the Geopamu logo), providing a complete corporate presence for the printing business.

All images are sourced from **Unsplash** for professional, high-quality photography.

## Accessing the Site

### Local Development
```bash
# Start the development server
npm run dev

# In another terminal, start Laravel
php artisan serve

# Check which port Laravel is using (usually 8000 or 8001)
# Access the site at the correct port:
http://localhost:8000/geopamu
# OR
http://localhost:8001/geopamu
```

**Note:** If you get a 404 error, check which port your Laravel server is running on. Look at the terminal output when you run `php artisan serve` - it will show something like "Server running on [http://127.0.0.1:8001]".

### Production
```
https://your-domain.com/geopamu
```

### Available Routes
- `/geopamu` - Home page
- `/geopamu/services` - Services listing
- `/geopamu/portfolio` - Project gallery
- `/geopamu/about` - About us
- `/geopamu/contact` - Contact form

## Features

### Pages
1. **Home** (`/geopamu`) - Hero section with services overview, featured work, and CTA
2. **Services** (`/geopamu/services`) - Detailed service offerings with process workflow
3. **Portfolio** (`/geopamu/portfolio`) - Filterable project gallery with testimonials
4. **Blog** (`/geopamu/blog`) - Blog listing with categories and search
5. **Blog Post** (`/geopamu/blog/{slug}`) - Individual blog post with related articles
6. **About** (`/geopamu/about`) - Company story, values, team, and statistics
7. **Contact** (`/geopamu/contact`) - Contact form, information, and map

### Admin Dashboard
- **Blog Management** (`/geopamu/admin/blog`) - Manage blog posts
- **Create Post** (`/geopamu/admin/blog/create`) - Write new blog posts
- **Edit Post** (`/geopamu/admin/blog/{id}/edit`) - Update existing posts
- **Statistics** - View total posts, published, drafts, and views
- **WYSIWYG Editor** - HTML support for rich content

## Design System

**Logo:**
- Located at: `/geopamu-assets/logo.png`
- Used in navigation and footer
- Circular display with object-contain for proper aspect ratio

**Color Palette (Based on Logo):**
- Primary Blue: `#1e40af` to `#1e3a8a` (blue-700 to blue-900)
- Accent Red: `#dc2626` to `#b91c1c` (red-600 to red-700)
- White: `#ffffff`
- Gray Scale: gray-50 to gray-900
- Gradients: Blue to Red, Blue to Gray-900

**Images:**
- All images from Unsplash API
- High-quality professional photography
- Optimized for web performance
- Relevant to printing and branding industry

**Typography:**
- Headings: Bold, large sizes (3xl-6xl)
- Body: Regular weight, readable sizes
- Emphasis: Semibold for important text

**Components:**
- Gradient backgrounds for hero sections
- Rounded corners (lg, xl, 2xl)
- Shadow effects (sm, lg, xl, 2xl)
- Hover transitions on interactive elements
- Responsive grid layouts

### Services Offered

1. **Digital & Offset Printing**
   - Business Cards
   - Flyers & Brochures
   - Posters & Banners
   - Catalogs & Magazines

2. **Brand Identity Design**
   - Logo Design
   - Brand Guidelines
   - Visual Identity
   - Brand Strategy

3. **Promotional Products**
   - T-Shirts & Apparel
   - Mugs & Drinkware
   - Bags & Accessories
   - Corporate Gifts

4. **Marketing Materials**
   - Brochures
   - Presentation Folders
   - Sales Sheets
   - Direct Mail

5. **Packaging Design**
   - Product Packaging
   - Label Design
   - Box Design
   - Custom Solutions

6. **Signage & Display**
   - Storefront Signs
   - Vehicle Wraps
   - Trade Show Displays
   - Window Graphics

## Technical Implementation

### File Structure

```
resources/js/
├── Layouts/
│   └── GeopamuLayout.vue          # Main layout with nav and footer
├── Pages/
│   └── Geopamu/
│       ├── Home.vue               # Landing page
│       ├── Services.vue           # Services listing
│       ├── Portfolio.vue          # Project gallery
│       ├── About.vue              # Company information
│       └── Contact.vue            # Contact form
└── Components/
    └── Geopamu/
        ├── GeopamuNavigation.vue  # Header navigation
        ├── GeopamuFooter.vue      # Footer with links
        ├── PageHeader.vue         # Page title sections
        ├── HeroSection.vue        # Home hero
        ├── ServicesOverview.vue   # Services grid
        ├── ServiceCard.vue        # Individual service
        ├── FeaturedWork.vue       # Portfolio preview
        ├── PortfolioGrid.vue      # Project grid
        ├── PortfolioFilter.vue    # Category filter
        ├── WhyChooseUs.vue        # Features section
        └── CallToAction.vue       # CTA sections
```

### Routes

```php
// routes/web.php
Route::prefix('geopamu')->name('geopamu.')->group(function () {
    Route::get('/', [GeopamuController::class, 'home'])->name('home');
    Route::get('/services', [GeopamuController::class, 'services'])->name('services');
    Route::get('/portfolio', [GeopamuController::class, 'portfolio'])->name('portfolio');
    Route::get('/about', [GeopamuController::class, 'about'])->name('about');
    Route::get('/contact', [GeopamuController::class, 'contact'])->name('contact');
});
```

### Controller

```php
// app/Http/Controllers/GeopamuController.php
class GeopamuController extends Controller
{
    public function home(): Response
    public function services(): Response
    public function portfolio(): Response
    public function about(): Response
    public function contact(): Response
}
```

## Content Placeholders

All content uses placeholders that can be easily replaced:

- **Images**: Gray boxes with descriptive text
- **Text**: Lorem ipsum style professional content
- **Contact Info**: `+260 XXX XXX XXX`, `info@geopamu.com`
- **Statistics**: `500+ Projects`, `200+ Clients`, `10+ Years`
- **Team Members**: Generic avatars and titles
- **Testimonials**: Sample client quotes

## Responsive Design

- **Mobile**: Single column, stacked navigation
- **Tablet**: 2-column grids, collapsible menu
- **Desktop**: 3-4 column grids, full navigation

## Accessibility

- Semantic HTML structure
- ARIA labels on icon-only buttons
- `aria-hidden="true"` on decorative icons
- Keyboard navigation support
- High contrast text
- Focus states on interactive elements

## Future Enhancements

1. **Backend Integration**
   - Contact form submission
   - Portfolio management system
   - Quote request system
   - Client testimonials management

2. **Advanced Features**
   - Image galleries with lightbox
   - Service booking system
   - Online quote calculator
   - Client portal for orders

3. **SEO Optimization**
   - Meta tags and descriptions
   - Structured data markup
   - Sitemap generation
   - Social media integration

4. **Performance**
   - Image optimization
   - Lazy loading
   - CDN integration
   - Caching strategy

## Domain Setup

The site is designed to be accessible via its own domain name (e.g., `geopamu.com`) while being hosted within the MyGrowNet platform infrastructure.

**Recommended Setup:**
1. Point domain to MyGrowNet server
2. Configure web server to route `/geopamu` paths
3. Set up SSL certificate
4. Configure CDN for static assets

## Maintenance

- Update placeholder content with real information
- Add actual project images to portfolio
- Configure contact form email delivery
- Set up analytics tracking
- Regular content updates

## Changelog

### January 28, 2026 - Complete Modernization
- **REDESIGNED**: PageHeader component for all pages
  - **Stunning visual design** befitting a printing/graphic design company:
    - Full-width background images specific to each page
    - Gradient overlay (blue to red) matching brand colors
    - Animated diagonal pattern overlay
    - Floating animated shapes (pulsing orbs)
    - Gradient text effect on titles
    - Decorative lines above and below content
    - Bottom wave SVG transition to page content
    - Fade-in and slide-up animations on load
  - **Page-specific images:**
    - Services: Modern signage and displays
    - Portfolio: Brand identity design work
    - About: Team collaboration
    - Contact: Creative workspace
    - Blog: Writing and content creation
  - Taller, more impactful headers (py-24 to py-32)
  - Large, bold typography (text-7xl on desktop)
  - Professional and inspiring design

- **ENHANCED**: Hero section with animated 4-slide slideshow
  - Slide 1: Quality Printing focus
  - Slide 2: Brand Identity focus
  - Slide 3: Custom Merchandise focus
  - Slide 4: Professional Signage focus
  - Ken Burns zoom effect on background images
  - Smooth slide transitions (7-second intervals)
  - Elegant navigation dots with gradient backdrop
  - Large circular arrow buttons with hover effects
  - Staggered content animations (badge, title, description, buttons, stats)
  - Fixed hero height from 700px/750px to 600px/650px for better proportions

- **ENHANCED**: All homepage components with scroll animations
  - **ServicesOverview**: Fade-in and slide-up effects, staggered card animations, card hover effects (lift, shadow, icon rotation)
  - **FeaturedWork**: Scroll-triggered animations, image zoom on hover, enhanced overlay transitions, staggered card reveals
  - **WhyChooseUs**: Fade-in animations, icon scale and rotate effects, staggered feature reveals
  - **CallToAction**: Slide-up animation, button hover effects (scale, shadow), icon animations

- **ENHANCED**: Services page (`/geopamu/services`) with professional images
  - **ServiceCard component redesigned** with image-first layout:
    - Beautiful header images for each service (Unsplash)
    - Image zoom effect on hover
    - Gradient overlay on images
    - Floating icon badge over image
    - Icon badge hover effects (scale, rotate)
    - Card hover effects (shadow, title color change)
  - Service cards with scroll-triggered fade-in animations
  - Staggered card reveals (100ms delay between each)
  - Process section with animated step numbers
  - Step number hover effects (scale, rotate, color change)
  - All sections responsive with smooth transitions
  - **Images used:**
    - Digital Printing: Professional printing press
    - Brand Identity: Design workspace with sketches
    - Promotional Products: Branded merchandise display
    - Marketing Materials: Elegant brochure layouts
    - Packaging Design: Product packaging mockups
    - Signage & Display: Modern storefront signage

- **ENHANCED**: Portfolio page (`/geopamu/portfolio`)
  - Testimonials section with scroll animations
  - Staggered testimonial card reveals (150ms delay)
  - Card hover effects (scale, shadow)
  - Gradient avatar placeholders with hover rotation

- **ENHANCED**: About page (`/geopamu/about`)
  - Story section with slide-in animation
  - Image hover effects (scale, shadow)
  - Stats section with scale-in animations
  - Stats hover effects (scale up)
  - Values section with scroll-triggered reveals
  - Icon hover effects (scale, rotate)
  - Team section with staggered member reveals
  - Team avatar hover effects (scale, rotate)

- **ENHANCED**: Contact page (`/geopamu/contact`)
  - Form section with slide-in from left
  - Contact info with slide-in from right
  - Staggered contact info item reveals
  - Icon hover effects (scale, rotate)
  - Form input focus animations
  - Button hover effects (scale, shadow)
  - Map image hover effects (scale, shadow)
  - Social media button hover effects (scale, translate-y)
  - CTA section with slide-up animation

- **IMPROVED**: Consistent animation patterns across all pages
  - Intersection Observer for scroll-triggered animations
  - Smooth transitions (300ms-700ms duration)
  - Staggered delays for sequential reveals
  - Transform effects (translate, scale, rotate)
  - Enhanced hover states on all interactive elements

- **RESULT**: Professional, elegant, and modern website with comprehensive animations and beautiful imagery throughout all pages and components

### January 7, 2026
- Initial website structure created
- All 5 pages implemented with Unsplash images
- Color scheme updated to match Geopamu logo (blue and red)
- Reusable component library built
- Responsive design completed
- Professional photography integrated
- Fixed: Logo now uses actual Geopamu logo from `/geopamu-assets/logo.png`
- Fixed: Reduced hero section spacing for better visual balance
- Fixed: Resolved 404 error caused by conflicting `public/geopamu` folder


## Troubleshooting

### 404 Not Found Error
**Most Common Issue:** Conflicting folder in public directory!

1. **Check for conflicting folders:**
   - If you have a folder named `geopamu` in the `public` directory, it will override the route
   - Remove or rename any `public/geopamu` folder
   - The route should work after removing the folder

2. **Check which port Laravel is running on:**
   - Look at your terminal where you ran `php artisan serve`
   - It will show: "Server running on [http://127.0.0.1:XXXX]"
   - Use that exact port number

2. **Try both common ports:**
   - `http://localhost:8000/geopamu`
   - `http://localhost:8001/geopamu`

3. **Clear caches:**
   ```bash
   php artisan route:clear
   php artisan config:clear
   ```

4. **Verify routes are registered:**
   ```bash
   php artisan route:list --path=geopamu
   ```
   You should see 5 routes listed.

### Images Not Loading
- Check internet connection (Unsplash requires internet)
- Images load from CDN, may take a moment on first load

### Styling Issues
- Run `npm run build` to rebuild assets
- Clear Laravel cache: `php artisan cache:clear`
- Make sure `npm run dev` is running


## Blog System

### Public Blog Features
- **Blog Listing** - Paginated blog posts with category filtering
- **Blog Post View** - Full post with related articles
- **Category Pages** - Filter posts by category
- **View Tracking** - Automatic view count increment
- **Reading Time** - Calculated based on word count
- **Author Attribution** - Display post author information

### Admin Dashboard Features
- **Post Management** - Create, edit, delete blog posts
- **Draft/Publish** - Save as draft or publish immediately
- **Rich Content** - HTML support for formatting
- **Featured Images** - Add images via URL
- **Categories** - Organize posts (general, tips, news, tutorials, case-studies)
- **Tags** - Add multiple tags per post
- **Statistics** - View total posts, published, drafts, and total views
- **Authentication** - Protected admin routes (requires login + admin role or manage-geopamu permission)

### Admin Access

**Security Model:**
- Geopamu admin access is **completely separate** from MyGrowNet admin
- Users with `manage-geopamu` permission can ONLY access Geopamu admin
- They CANNOT access MyGrowNet admin dashboard
- This ensures proper separation of concerns

**Who Can Access:**
- Only users with `manage-geopamu` permission
- Admin role does NOT automatically grant access (by design)

**How to Grant Access:**

**Option 1: Admin UI (Easiest - Recommended)**
1. Login as MyGrowNet admin
2. Go to `/admin/geopamu-admins`
3. Select user from dropdown
4. Click "Assign Access"

**Option 2: Artisan Command (Production/SSH)**
```bash
php artisan geopamu:assign-admin user@example.com
```

**Option 3: Laravel Tinker (Advanced)**
```bash
php artisan tinker
$user = User::where('email', 'user@example.com')->first();
$user->givePermissionTo('manage-geopamu');
exit
```

**To Access Admin Dashboard:**
1. Login to MyGrowNet at `/login`
2. Navigate to `/geopamu/admin/blog`
3. If you don't have permission, you'll see a 403 error

**To Remove Access:**
- Use Admin UI at `/admin/geopamu-admins` and click "Revoke"
- Or via code: `$user->revokePermissionTo('manage-geopamu');`

### Database Schema
```sql
geopamu_blog_posts
- id
- title
- slug (unique, auto-generated)
- excerpt
- content (HTML)
- featured_image (URL)
- category
- tags (JSON)
- status (draft/published)
- published_at
- author_id (foreign key to users)
- views_count
- timestamps
```

### Access URLs
- Public Blog: `/geopamu/blog`
- Blog Post: `/geopamu/blog/{slug}`
- Category: `/geopamu/blog/category/{category}`
- Admin Dashboard: `/geopamu/admin/blog` (requires authentication)
- Create Post: `/geopamu/admin/blog/create`
- Edit Post: `/geopamu/admin/blog/{id}/edit`
