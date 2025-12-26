# GrowBuilder - Implementation Plan

**Last Updated:** December 26, 2025  
**Status:** Phase 5A & 5B Complete - Editor Polish & Media Integration Done  
**Timeline:** 6-8 months to full launch

---

## Competitive Analysis & World-Class Roadmap

### Current State Assessment

**What We Have (Strong Foundation):**
- ✅ Modern drag-and-drop editor with 20+ section types
- ✅ Professional page templates (12 templates)
- ✅ E-commerce with MoMo/Airtel Money payments
- ✅ Site user authentication system (members area)
- ✅ Analytics dashboard
- ✅ Dark mode editor
- ✅ Responsive preview (mobile/tablet/desktop)
- ✅ Media library
- ✅ SEO settings
- ✅ Custom domains support

### Gap Analysis vs World-Class Builders (Wix, Squarespace, Webflow)

#### 🔴 Critical Gaps (Must Have for Launch)

| Feature | Status | Priority | Effort |
|---------|--------|----------|--------|
| **Undo/Redo** | ✅ Done | P0 | Medium |
| **Auto-save** | ✅ Done | P0 | Low |
| **Section Copy/Paste** | Missing | P0 | Low |
| **Rich Text Editor** | ✅ Done | P0 | Medium |
| **Image Cropping/Editing** | ✅ Done | P0 | Medium |
| **Form Builder** | Basic | P0 | High |
| **Mobile-specific editing** | Missing | P0 | High |
| **Loading States** | Partial | P0 | Low |
| **Error Handling** | Basic | P0 | Medium |

#### 🟡 Important Gaps (Competitive Advantage)

| Feature | Status | Priority | Effort |
|---------|--------|----------|--------|
| **AI Content Generation** | Missing | P1 | High |
| **Animation/Transitions** | Missing | P1 | Medium |
| **Custom CSS/Code Injection** | Missing | P1 | Low |
| **Multi-language Support** | Missing | P1 | High |
| **Blog/CMS System** | Partial | P1 | High |
| **Booking/Appointments** | Missing | P1 | High |
| **Email Marketing Integration** | Missing | P1 | Medium |
| **WhatsApp Business Integration** | Partial | P1 | Medium |
| **SSL Certificates** | Missing | P1 | Medium |
| **Site Backup/Restore** | Missing | P1 | Medium |

#### 🟢 Nice to Have (Differentiation)

| Feature | Status | Priority | Effort |
|---------|--------|----------|--------|
| **Collaborative Editing** | Missing | P2 | Very High |
| **Version History** | Missing | P2 | High |
| **A/B Testing** | Missing | P2 | High |
| **Advanced Analytics** | Basic | P2 | Medium |
| **Membership/Subscriptions** | Partial | P2 | High |
| **Course/LMS Builder** | Missing | P2 | Very High |
| **Marketplace/Plugins** | Missing | P2 | Very High |

---

### Zambia-Specific Opportunities (Competitive Moat)

These features would make GrowBuilder THE choice for Zambian businesses:

1. **Local Payment Integration** ✅ (Done)
   - MTN MoMo, Airtel Money
   - Bank transfers (Zanaco, Stanbic, FNB)
   
2. **WhatsApp-First Commerce** 🟡 (Partial)
   - WhatsApp catalog sync
   - Order notifications via WhatsApp
   - WhatsApp Business API integration
   - Click-to-chat buttons everywhere

3. **Offline-First Design** 🔴 (Missing)
   - PWA support for sites
   - Offline order capture
   - Low-bandwidth optimizations
   - Image compression/lazy loading

4. **Local Business Templates** 🟡 (Partial)
   - Zambian restaurant menus (Nshima, etc.)
   - Church/Ministry templates
   - School/Tutor templates
   - Real estate (Zambian market)
   - Agriculture/Farming
   - Transport/Logistics

5. **Local SEO** 🔴 (Missing)
   - Google My Business integration
   - Local schema markup
   - Zambian business directories

6. **SMS Integration** 🔴 (Missing)
   - Order confirmations via SMS
   - Marketing SMS campaigns
   - OTP verification

---

### Recommended Implementation Phases

#### Phase 5A: Editor Polish (1-2 weeks) - ✅ COMPLETE
```
Priority: Make the editor feel complete and professional
```

- [x] **Undo/Redo System** - Command pattern with history stack (useHistory composable)
- [x] **Auto-save with Indicator** - Save every 30 seconds, show status (useAutoSave composable)
- [x] **Section Copy/Paste** - Clipboard support across pages (useClipboard composable)
- [x] **Rich Text Editor** - Custom RichTextEditor component with formatting toolbar
- [x] **Better Loading States** - LoadingOverlay component with spinner
- [x] **Error Boundaries** - ErrorBoundary component with graceful error handling
- [x] **Keyboard Navigation** - Arrow keys, Ctrl+Z/Y, Ctrl+S, Ctrl+P, Delete, Escape, Ctrl+C/X/V
- [x] **Context Menu** - Right-click menu for quick actions (ContextMenu component)

#### Phase 5B: Media & Content (2-3 weeks) - COMPLETE
```
Priority: Professional content creation tools
```

- [x] **Image Editor** - Crop, resize, brightness/contrast/saturation, export quality (ImageEditorModal)
- [x] **Stock Photos Integration** - Unsplash API with search and categories (MediaLibraryModal tabs)
- [x] **Icon Library** - Searchable Heroicons picker with categories (IconPickerModal)
- [x] **Video Backgrounds** - YouTube/Vimeo URL parser with autoplay/loop options (VideoEmbedModal)
- [x] **Image Optimization** - Auto-compress, WebP conversion (ImageOptimizationService)
- [x] **Drag-to-Upload** - useDragUpload composable for canvas drops

#### Phase 5C: AI Features (2-3 weeks)
```
Priority: Differentiation through AI assistance
```

- [ ] **AI Content Writer** - Generate section content from prompts
- [ ] **AI Image Suggestions** - Recommend images based on content
- [ ] **AI Color Palette** - Generate color schemes from logo
- [ ] **AI SEO Optimizer** - Auto-generate meta descriptions
- [ ] **AI Translation** - Translate content to local languages
- [ ] **Smart Templates** - AI-powered template recommendations

#### Phase 5D: Commerce Enhancement (2-3 weeks)
```
Priority: Complete e-commerce solution
```

- [ ] **Product Variants** - Size, color, etc.
- [ ] **Inventory Management** - Stock alerts, low stock warnings
- [ ] **Discount Codes** - Percentage, fixed amount, free shipping
- [ ] **Shipping Zones** - Lusaka, Copperbelt, other provinces
- [ ] **Order Tracking** - Status updates, delivery tracking
- [ ] **Invoice Generation** - PDF invoices with ZRA compliance
- [ ] **WhatsApp Order Notifications** - Instant alerts to seller

#### Phase 5E: Marketing & Growth (3-4 weeks)
```
Priority: Help users grow their businesses
```

- [ ] **Email Collection** - Popup forms, exit intent
- [ ] **WhatsApp Marketing** - Broadcast lists, templates
- [ ] **Social Media Integration** - Auto-post to Facebook/Instagram
- [ ] **Google Analytics Integration** - One-click setup
- [ ] **Facebook Pixel** - E-commerce tracking
- [ ] **SEO Audit Tool** - Check and fix SEO issues
- [ ] **Performance Score** - PageSpeed insights integration

#### Phase 5F: Advanced Features (4-6 weeks)
```
Priority: Enterprise-ready features
```

- [ ] **Blog/CMS** - Full blogging platform
- [ ] **Booking System** - Appointments, calendars
- [ ] **Membership Areas** - Gated content, subscriptions
- [ ] **Multi-language** - Translate sites to Bemba, Nyanja, etc.
- [ ] **Custom Forms** - Drag-and-drop form builder
- [ ] **Integrations** - Zapier, webhooks, API

---

### Quick Wins (Can Implement Today)

1. **Undo/Redo** - ✅ Implemented with useHistory composable
2. **Copy Section** - ✅ Implemented with useClipboard composable
3. **Better Empty States** - Guide users when starting
4. **Keyboard Shortcuts Help** - Already have modal, expand it
5. **Section Reordering Animation** - Smooth drag animations
6. **Auto-save Indicator** - ✅ Implemented with useAutoSave composable
7. **Preview in New Tab** - Full preview without iframe
8. **Duplicate Page** - Copy entire page with sections

---

### Success Metrics

| Metric | Current | Target (6 months) |
|--------|---------|-------------------|
| Sites Created | - | 1,000+ |
| Published Sites | - | 500+ |
| Monthly Active Users | - | 2,000+ |
| E-commerce Orders | - | 5,000+ |
| Revenue (MRR) | K0 | K50,000+ |
| NPS Score | - | 50+ |

---

## Changelog

### December 26, 2025 (Phase 5B - Advanced Features)
- ✅ **Section Copy/Paste** - Full clipboard support for sections
  - Copy sections with Ctrl+C
  - Cut sections with Ctrl+X
  - Paste sections with Ctrl+V
  - Cross-page clipboard (persists in localStorage for 24 hours)
  - Toast notifications for clipboard actions
- ✅ **Context Menu** - Right-click menu for quick section actions
  - Edit Section, Edit Style options
  - Copy, Cut, Paste, Duplicate
  - Move Up, Move Down
  - Delete with confirmation
  - Shows clipboard status
  - Keyboard shortcut hints
- ✅ **Image Optimization Service** - Auto-compress and WebP conversion
  - Automatic image compression on upload
  - WebP version generated for all images
  - Thumbnail generation
  - Size savings reported to user
  - Max dimensions: 1920x1080
  - Quality: JPEG 85%, WebP 80%
- ✅ **Overlay Gradient Customization** - Full control over overlay gradients
  - Custom from/to color pickers
  - 8 gradient presets
  - Color balance slider (midpoint control)
  - Real-time preview

#### Files Created
- `app/Services/GrowBuilder/ImageOptimizationService.php` - Image optimization service
- `resources/js/pages/GrowBuilder/Editor/composables/useClipboard.ts` - Clipboard composable
- `resources/js/pages/GrowBuilder/Editor/components/common/ContextMenu.vue` - Context menu component

#### Files Modified
- `app/Http/Controllers/GrowBuilder/MediaController.php` - Image optimization integration
- `app/Infrastructure/GrowBuilder/Models/GrowBuilderMedia.php` - WebP URL accessor
- `resources/js/pages/GrowBuilder/Editor/Index.vue` - Clipboard and context menu integration
- `resources/js/pages/GrowBuilder/Editor/components/inspectors/SectionInspector.vue` - Overlay gradient controls
- `resources/js/pages/GrowBuilder/Editor/components/sections/HeroSection.vue` - Gradient midpoint support

### December 26, 2025 (Phase 5B - Enhancements)
- ✅ **Stock Photo Editing** - Stock photos can now be edited/cropped before use
  - Click stock photo to open image editor
  - Direct download button for quick use without editing
  - Same editing capabilities as uploaded images
- ✅ **Gradient Backgrounds** - Added gradient support to all sections
  - Solid/Gradient toggle in Style tab
  - 8 beautiful gradient presets (Ocean, Sunset, Forest, Fire, Sky, Purple, Midnight, Coral)
  - Custom gradient colors with color pickers
  - 4 direction options (→, ↓, ↘, ↗)
  - Live preview of gradient
  - Fixed: Switching to gradient now sets default colors automatically
- ✅ **Overlay Controls** - Adjustable overlay for Hero sections with backgrounds
  - Overlay color options: Dark, Light, Gradient
  - Opacity slider (0-90%)
  - Works with both image and video backgrounds
  - Real-time preview

#### Files Modified
- `components/modals/MediaLibraryModal.vue` - Stock photo editing support
- `components/inspectors/SectionInspector.vue` - Gradient background controls, overlay controls
- `components/SectionRenderer.vue` - Gradient rendering support
- `components/sections/HeroSection.vue` - Dynamic overlay rendering

### December 25, 2025 (Phase 5B Complete - Media & Content Integration)
- ✅ **Icon Picker Integration** - Added to SectionInspector for services/features
  - Icon selection button for each item in services/features sections
  - Shows current icon name or placeholder
  - Stores icon name and style (outline/solid) in item data
- ✅ **Video Background for Hero** - Added to SectionInspector
  - Background type toggle (Image/Video)
  - Video embed modal integration
  - Thumbnail preview with play indicator
  - Stores video URL, thumbnail, and platform info
- ✅ **Drag-to-Upload Integration** - Added to Index.vue canvas
  - Global drop zone with visual overlay
  - Upload progress indicator
  - Auto-applies to selected section (hero, about, gallery)
  - Falls back to media library if no section selected
  - File type and size validation (10MB max)

#### Files Modified
- `components/inspectors/SectionInspector.vue` - Added IconPickerModal, VideoEmbedModal integration
- `Index.vue` - Added useDragUpload composable, drop zone overlay, upload handler

### December 25, 2025 (Phase 5B - Media & Content)
- ✅ **Enhanced Image Editor** - Added brightness, contrast, saturation, blur adjustments
  - Export format selection (JPEG, PNG, WebP)
  - Quality slider (10-100%)
  - Scale slider for resolution control (10-200%)
  - Compact dark UI with maximized editing area
- ✅ **Stock Photos Integration** - Unsplash API in MediaLibraryModal
  - Tabbed interface: My Media / Stock Photos
  - Search with categories (business, technology, nature, etc.)
  - One-click selection with attribution
- ✅ **Icon Picker Modal** - Searchable Heroicons library
  - 200+ icons organized by category
  - Outline/Solid style toggle
  - Search and filter functionality
- ✅ **Video Embed Modal** - YouTube/Vimeo background videos
  - URL parsing for both platforms
  - Thumbnail preview
  - Autoplay, muted, loop options for backgrounds
- ✅ **Drag-to-Upload Composable** - useDragUpload.ts
  - File type validation
  - Size limit checking
  - Global or element-specific drop zones

#### Files Created
- `components/modals/StockPhotosModal.vue`
- `components/modals/IconPickerModal.vue`
- `components/modals/VideoEmbedModal.vue`
- `composables/useDragUpload.ts`

#### Files Modified
- `components/modals/MediaLibraryModal.vue` - Added Stock Photos tab
- `components/modals/ImageEditorModal.vue` - Enhanced with adjustments
- `components/modals/index.ts` - Added new exports
- `Index.vue` - Added stock photo handler

### December 25, 2025 (Phase 5A Complete - Editor Polish)
- ✅ **Undo/Redo System** - useHistory composable with 50-state history stack
  - Tracks sections, navigation, and footer changes
  - Ctrl+Z to undo, Ctrl+Shift+Z or Ctrl+Y to redo
  - Visual indicators in toolbar (disabled when no history)
- ✅ **Auto-save System** - useAutoSave composable with 30-second delay
  - Dirty state tracking
  - Automatic save after inactivity
  - Error handling with toast notifications
  - Reset on manual save
- ✅ **Rich Text Editor** - Custom RichTextEditor component
  - Bold, italic, underline, strikethrough formatting
  - Headings (H1, H2, H3)
  - Lists (bullet, numbered)
  - Text alignment (left, center, right, justify)
  - Links with URL input
  - Keyboard shortcuts (Ctrl+B/I/U)
  - Dark mode support
  - Integrated into SectionInspector for text sections
- ✅ **Image Editor Modal** - ImageEditorModal component
  - Crop with draggable selection area
  - Aspect ratio presets (Free, 1:1, 16:9, 4:3, 3:2, 2:3)
  - Resize handles (corners and edges)
  - Grid overlay for composition
  - Real-time output dimensions display
  - Canvas-based image export
  - Integrated into MediaLibraryModal
- ✅ **Loading Overlay** - LoadingOverlay component
  - Spinner animation
  - Optional message text
  - Backdrop blur effect
- ✅ **Error Boundary** - ErrorBoundary component
  - Graceful error catching
  - User-friendly error display
  - Retry functionality
  - Error details toggle
- ✅ **MediaLibraryModal Enhanced**
  - Image cropping before selection (allowCrop prop)
  - Delete image functionality
  - File name tooltips on hover
  - Action buttons overlay (crop, select, delete)

#### Files Created
- `resources/js/pages/GrowBuilder/Editor/composables/useHistory.ts`
- `resources/js/pages/GrowBuilder/Editor/composables/useAutoSave.ts`
- `resources/js/pages/GrowBuilder/Editor/components/common/RichTextEditor.vue`
- `resources/js/pages/GrowBuilder/Editor/components/common/LoadingOverlay.vue`
- `resources/js/pages/GrowBuilder/Editor/components/common/ErrorBoundary.vue`
- `resources/js/pages/GrowBuilder/Editor/components/modals/ImageEditorModal.vue`

#### Files Modified
- `resources/js/pages/GrowBuilder/Editor/Index.vue` - Integrated history and auto-save
- `resources/js/pages/GrowBuilder/Editor/components/inspectors/SectionInspector.vue` - RichTextEditor for text sections
- `resources/js/pages/GrowBuilder/Editor/components/modals/MediaLibraryModal.vue` - Image editor integration

### December 25, 2025 (Visual Enhancements - Custom Scrollbars)
- ✅ **Custom Scrollbar Styling** - Thin 6px scrollbars with rounded corners
- ✅ **Dark Mode Scrollbars** - Separate styling for dark mode
- ✅ **Applied to All Scrollable Areas**:
  - Canvas area
  - WidgetPalette (left sidebar)
  - PagesList (left sidebar)
  - SectionInspector (right sidebar)
  - NavigationInspector (right sidebar)
  - FooterInspector (right sidebar)
  - MediaLibraryModal
  - CreatePageModal
  - ApplyTemplateModal
- ✅ **Dark Mode Props** - Added darkMode prop to NavigationInspector and FooterInspector

### December 25, 2025 (Visual Enhancements - Builder UI Polish)
- ✅ **Full-Width Preview Mode** - In-page preview with hidden sidebars
- ✅ **Interactive Preview** - Iframe mode with clickable links
- ✅ **Static Preview** - Component-based preview (faster)
- ✅ **Responsive Breakpoints** - Mobile (375px), Tablet (768px), Laptop (1024px), Desktop (1440px)
- ✅ **Draggable Resize Handles** - Manually adjust preview width
- ✅ **Keyboard Shortcuts** - Ctrl+S (save), Ctrl+P (preview), Ctrl+D (duplicate), Delete, Escape
- ✅ **Shortcuts Modal** - Ctrl+/ to show all shortcuts
- ✅ **Top Toolbar Polish** - Gradient background, site logo, auto-save indicator, zoom controls
- ✅ **Canvas Background** - Dot grid pattern for professional look
- ✅ **Zoom Controls** - 50%, 75%, 100%, 125% zoom levels
- ✅ **Section Labels** - Type badge on hover (dark pill showing section type)
- ✅ **Widget Search** - Search/filter widgets in palette
- ✅ **Widget Tooltips** - Hover tooltips with descriptions
- ✅ **Tablet Preview Mode** - Added tablet breakpoint to preview toggle
- ✅ **Better Widget Cards** - Improved hover states with gradients
- ✅ **Toast Notifications** - Success/error feedback for save, page create/update/delete
- ✅ **Dark Mode** - Full dark mode support for entire editor interface
- ✅ **Site Logo Display** - Shows site logo in toolbar (falls back to navigation logo or initial)

#### Files Modified/Created
- `resources/js/pages/GrowBuilder/Editor/Index.vue` - Dark mode, canvas zoom, dot grid, section labels, toast integration
- `resources/js/pages/GrowBuilder/Editor/components/sidebar/EditorToolbar.vue` - Dark mode, site logo, gradient, zoom, auto-save
- `resources/js/pages/GrowBuilder/Editor/components/sidebar/WidgetPalette.vue` - Dark mode, search, tooltips, better styling
- `resources/js/pages/GrowBuilder/Editor/components/ToastContainer.vue` - New toast notification component
- `resources/js/pages/GrowBuilder/Editor/composables/useToast.ts` - New toast composable
- `resources/js/pages/GrowBuilder/Editor/types/index.ts` - Added tablet to PreviewMode, logo/favicon to Site

### December 25, 2025 (Site Users Architecture - Implementation)
- ✅ Created database migrations for site users system
  - `site_users` table with role support
  - `site_user_sessions` for multi-device tracking
  - `site_user_password_resets` for password recovery
  - `site_roles` and `site_permissions` for RBAC
  - `site_posts`, `site_post_categories`, `site_comments` for content
- ✅ Created Eloquent models: SiteUser, SiteRole, SitePermission, SiteUserSession, SitePost, SitePostCategory, SiteComment
- ✅ Created controllers: SiteAuthController, SiteMemberController, SitePostController, SiteUserManagementController
- ✅ Created middleware: SiteUserAuth, SiteUserPermission
- ✅ Created SiteRoleService for default role management
- ✅ Created SitePermissionsSeeder with 26 default permissions
- ✅ Created Vue pages for site member area:
  - Auth: Login, Register, ForgotPassword, ResetPassword
  - Member: Dashboard, Profile/Index, Orders/Index, Orders/Show
- ✅ Added site user routes in growbuilder.php
- ✅ Registered middleware aliases in Kernel.php
- ✅ Updated GrowBuilderOrder model with site_user_id support
- ✅ Updated GrowBuilderSite model with site users relationships

### December 25, 2025 (Site Users Architecture - Design)
- ✅ Designed independent site user authentication system
- ✅ Separate `site_users` table for site-specific members
- ✅ Site-branded login/register pages architecture
- ✅ Auth buttons in navigation inspector
- ✅ Member CTA section for promoting signups
- ✅ PageLinkSelector with Auth tab for easy linking

### December 20, 2025 (Modern Builder UI - Phase 4)
- ✅ Complete redesign of Editor/Index.vue with modern, elegant UI
- ✅ Implemented true drag-and-drop using vuedraggable
- ✅ Canvas-first editing with live preview
- ✅ Left sidebar with pages list and draggable section blocks
- ✅ Right sidebar with inspector panel (Content/Style/Advanced tabs)
- ✅ Section hover actions (move up/down, duplicate, delete)
- ✅ Add Section modal with categorized section blocks
- ✅ Desktop/Mobile preview toggle
- ✅ Visual polish: softer shadows, rounded corners, modern buttons
- ✅ Empty state guidance for new pages
- ✅ Enhanced SectionPreview component with all 11 section types
- ✅ Color picker with theme palette for section styling
- ✅ Padding controls in Advanced tab
- ✅ Items editor for services, features, testimonials, pricing sections
- ✅ **Widget Palette** - Tabbed left sidebar with Widgets/Pages tabs
- ✅ **Drag-from-Palette** - Drag widgets directly from palette to canvas
- ✅ **Collapsible Categories** - Widget categories expand/collapse for organization
- ✅ **Visual Drop Zone** - Canvas shows drop indicator when dragging widgets
- ✅ **Clone on Drag** - Widgets clone from palette (source stays in place)
- ✅ **Inline Editing** - Click any text element to edit directly on canvas
- ✅ **Pencil Icon Indicator** - Shows on hover to indicate editable content
- ✅ **Keyboard Support** - Enter to save, Escape to cancel inline edits
- ✅ **All Sections Editable** - Hero, About, Services, Features, Gallery, Testimonials, Pricing, Contact, CTA, Text, Products

### December 20, 2025 (World-Class Enhancements)
- ✅ Enhanced Dashboard with stats cards (page views, orders, revenue)
- ✅ Added site thumbnails and quick action overlays on hover
- ✅ Created comprehensive Site Settings page with 4 tabs (General, Theme, SEO, Social)
- ✅ Added live theme preview in settings
- ✅ Created Analytics page with charts, device breakdown, top pages
- ✅ Enhanced SiteController with stats aggregation
- ✅ Added analytics route and controller method
- ✅ Updated Site entity with description, logo, favicon update methods

### December 20, 2025 (Phase 2 Implementation)
- ✅ Created commerce database migration (products, orders, payments, invoices, coupons, shipping)
- ✅ Ran commerce migration successfully
- ✅ Created Domain Entities: Product, Order
- ✅ Created Value Objects: Money, OrderStatus, OrderId, ProductId
- ✅ Created Payment Gateway interfaces and implementations (MoMo, Airtel Money)
- ✅ Created Eloquent models: GrowBuilderProduct, GrowBuilderOrder, GrowBuilderPayment, GrowBuilderPaymentSettings, GrowBuilderInvoice
- ✅ Created Controllers: ProductController, OrderController, PaymentSettingsController, CheckoutController
- ✅ Created Vue pages: Products/Index, Products/Create, Products/Edit, Orders/Index, Orders/Show, Settings/Payments
- ✅ Created product catalog section template for rendered sites
- ✅ Created shopping cart component with WhatsApp ordering support
- ✅ Created checkout page with multiple payment methods
- ✅ Updated RenderController with e-commerce support and checkout routing
- ✅ Updated routes with commerce endpoints

### December 20, 2025 (Editor Enhancements)
- ✅ Created SectionPreview component with visual previews for all 11 section types
- ✅ Enhanced Editor with Content/Style tabs for section editing
- ✅ Added color picker with quick color palette for styling
- ✅ Added items editor for services, features, testimonials sections
- ✅ Added desktop/mobile preview toggle

### December 20, 2025 (Phase 1 Implementation)
- ✅ Created database migration with all core tables
- ✅ Implemented DDD structure (Domain, Infrastructure, Application layers)
- ✅ Created Domain Entities: Site, Page, Template
- ✅ Created Value Objects: SiteId, PageId, TemplateId, Subdomain, SiteStatus, SitePlan, Theme, PageContent, TemplateCategory
- ✅ Created Repository Interfaces and Eloquent implementations
- ✅ Created Use Cases: CreateSite, UpdateSite, SavePageContent, PublishSite
- ✅ Created Controllers: SiteController, EditorController, MediaController, RenderController
- ✅ Created Vue pages: Dashboard, Sites/Create, Editor/Index
- ✅ Created Blade templates for site rendering (10 section types)
- ✅ Created template seeder with 5 starter templates
- ✅ Registered routes and service provider

### December 20, 2025 (Initial)
- Initial implementation plan created
- 5-phase development roadmap defined
- Technical architecture outlined
- Team and budget estimated

---

## Phase 1 Implementation Status

### ✅ Completed

#### Database
- `growbuilder_templates` - Template definitions
- `growbuilder_sites` - User websites
- `growbuilder_pages` - Page content (JSON)
- `growbuilder_section_types` - Reusable section definitions
- `growbuilder_media` - Media library
- `growbuilder_forms` - Contact forms
- `growbuilder_form_submissions` - Form submissions
- `growbuilder_page_views` - Basic analytics

#### Domain Layer (DDD)
- **Entities:** Site, Page, Template
- **Value Objects:** SiteId, PageId, TemplateId, Subdomain, SiteStatus, SitePlan, Theme, PageContent, TemplateCategory
- **Repository Interfaces:** SiteRepositoryInterface, PageRepositoryInterface, TemplateRepositoryInterface

#### Infrastructure Layer
- **Eloquent Models:** GrowBuilderSite, GrowBuilderPage, GrowBuilderTemplate, GrowBuilderMedia, GrowBuilderForm, GrowBuilderFormSubmission, GrowBuilderPageView
- **Repository Implementations:** EloquentSiteRepository, EloquentPageRepository, EloquentTemplateRepository

#### Application Layer
- **Use Cases:** CreateSiteUseCase, UpdateSiteUseCase, SavePageContentUseCase, PublishSiteUseCase
- **DTOs:** CreateSiteDTO, UpdateSiteDTO, SavePageContentDTO

#### Presentation Layer
- **Controllers:** SiteController, EditorController, MediaController, RenderController
- **Vue Pages:** Dashboard, Sites/Create, Editor/Index
- **Blade Templates:** render.blade.php + 10 section templates

#### Templates (5 Starter)
1. Business Pro (business)
2. Restaurant Starter (restaurant)
3. Church Community (church)
4. Tutor Pro (tutor)
5. Portfolio Minimal (portfolio)

### ⏳ Remaining for Phase 1

- [x] Run migration: `php artisan migrate`
- [x] Seed templates: `php artisan db:seed --class=GrowBuilderTemplateSeeder`
- [ ] Test site creation flow
- [ ] Test page editor
- [ ] Test site rendering
- [ ] Add subdomain routing (Nginx config)
- [ ] Image upload testing

---

## Phase 2 Implementation Status

### ✅ Completed

#### Database (Commerce)
- `growbuilder_products` - Product catalog
- `growbuilder_product_categories` - Product categories
- `growbuilder_orders` - Customer orders
- `growbuilder_payments` - Payment transactions
- `growbuilder_invoices` - Invoice generation
- `growbuilder_coupons` - Discount codes
- `growbuilder_payment_settings` - Per-site payment config
- `growbuilder_shipping_settings` - Shipping options
- `growbuilder_notifications` - Order alerts

#### Domain Layer
- **Entities:** Product, Order
- **Value Objects:** Money, OrderStatus, OrderId, ProductId
- **Services:** PaymentGatewayInterface, PaymentResult

#### Infrastructure Layer
- **Eloquent Models:** GrowBuilderProduct, GrowBuilderOrder, GrowBuilderPayment, GrowBuilderPaymentSettings, GrowBuilderInvoice
- **Payment Gateways:** MoMoPaymentGateway, AirtelMoneyPaymentGateway

#### Presentation Layer
- **Controllers:** ProductController, OrderController, PaymentSettingsController, CheckoutController
- **Vue Pages:** Products/Index, Products/Create, Products/Edit, Orders/Index, Orders/Show, Settings/Payments
- **Blade Templates:** products.blade.php section, cart.blade.php, cart-scripts.blade.php

### ⏳ Remaining for Phase 2

- [ ] Test product CRUD operations
- [ ] Test order creation flow
- [ ] Test MoMo payment integration (sandbox)
- [ ] Test Airtel Money integration (sandbox)
- [ ] Create invoice PDF generation service
- [ ] Add SMS notifications for orders
- [ ] Test WhatsApp ordering flow
- [ ] Add product image upload functionality

---

## Phase 4 Implementation Status (Modern Builder UI)

### ✅ Completed

#### Editor Redesign
- **True Drag-and-Drop:** Using vuedraggable for section reordering
- **Canvas-First Editing:** Live preview in center canvas area
- **Left Sidebar:** Pages list (expandable) + Add Section button + Section list with drag handles
- **Right Sidebar:** Inspector panel with Content/Style/Advanced tabs
- **Section Hover Actions:** Move up/down, duplicate, delete buttons
- **Add Section Modal:** Categorized grid of 11 section types with icons and descriptions

#### Visual Polish
- Softer shadows (shadow-sm, shadow-lg, shadow-xl)
- Rounded corners (rounded-lg, rounded-xl, rounded-2xl)
- Modern button styles with transitions
- Consistent spacing and typography
- No hard borders - using subtle ring effects for selection
- Smooth transitions and hover states

#### Section Types (11 total)
1. **Hero** - Eye-catching header with title, subtitle, button
2. **About** - Company info with image
3. **Services** - Grid of service cards
4. **Features** - Feature list with icons
5. **Gallery** - Image grid
6. **Testimonials** - Customer reviews with stars
7. **Pricing** - Pricing plans with features
8. **Products** - Product catalog
9. **Contact** - Contact form
10. **CTA** - Call to action section
11. **Text** - Rich text content

#### Inspector Panel Features
- **Content Tab:** Section-specific content editing (title, description, items, etc.)
- **Style Tab:** Background color picker with theme palette, text color toggle
- **Advanced Tab:** Padding controls (top/bottom), duplicate/delete actions

#### Files Modified
- `resources/js/pages/GrowBuilder/Editor/Index.vue` - Complete rewrite
- `resources/js/components/GrowBuilder/SectionPreview.vue` - Enhanced with all section types

### ⏳ Remaining for Phase 4

- [ ] Test drag-and-drop functionality
- [ ] Test all section type editing
- [ ] Add image upload to sections
- [ ] Add undo/redo functionality
- [ ] Add keyboard shortcuts
- [ ] Add autosave feature

---

## Overview

This document outlines the complete development plan for **GrowBuilder**, MyGrowNet's website builder platform. The plan is divided into 5 phases, each building on the previous one.

**Total Timeline:** 6-8 months  
**Estimated Budget:** K80,000-150,000  
**Team Size:** 4-6 people

---

## Phase 1: Foundation (1-2 months)

### Objectives

✅ Build the base website builder engine  
✅ Provide a functional prototype for pilot customers  
✅ Establish core architecture

### Core Features

#### 1. User Management
- User registration/login (use existing MyGrowNet auth)
- Dashboard for managing websites
- Profile settings
- Subscription management

#### 2. Template System
- 5 starter templates:
  - **Business** - General business/shop
  - **Restaurant** - Food & beverage
  - **Church** - Religious organizations
  - **Tutor** - Education services
  - **Portfolio** - Personal/professional

#### 3. Page Builder Basics
- **Sections available:**
  - Hero (banner with image/text)
  - About (company info)
  - Services (grid of services)
  - Gallery (image grid)
  - Contact (form + map)
  - Footer

- **Editing capabilities:**
  - Text editable inline
  - Images uploadable
  - Colors customizable
  - Save page structure as JSON

#### 4. Hosting Model
- **Subdomains:** `username.mygrownet.com`
- Lightweight rendering engine
- Fast on low bandwidth
- CDN for images

### Technical Work

#### Backend (Laravel)
```
Database Tables:
- growbuilder_sites (id, user_id, subdomain, template_id, settings, is_active)
- growbuilder_pages (id, site_id, slug, title, content_json, is_published)
- growbuilder_templates (id, name, preview_image, structure_json)
- growbuilder_sections (id, template_id, type, default_content)
```

#### Frontend (Vue.js)
```
Components:
- SiteBuilder.vue (main editor)
- SectionEditor.vue (edit individual sections)
- TemplateSelector.vue (choose template)
- PreviewMode.vue (live preview)
```

#### Storage
- Images: AWS S3 or DigitalOcean Spaces
- Fallback: Local storage initially

### Deliverables

✅ Working builder prototype  
✅ Admin panel for managing users & sites  
✅ 5 functional templates  
✅ Basic hosting infrastructure

### People Needed

- 1 Full-stack developer (Laravel + Vue)
- 1 UI/UX designer (part-time)

### Budget: K35,000-50,000

---

## Phase 2: Local Commerce Integration (1-1.5 months)

### Objectives

✅ Introduce local payment systems  
✅ Add e-commerce basics  
✅ Release early commercial version

### Features

#### 1. Payments
- **MTN MoMo integration**
  - Payment collection API
  - Webhook handling
  - Transaction logging
  
- **Airtel Money integration**
  - Payment collection API
  - Webhook handling
  - Transaction logging

#### 2. E-Commerce
- **Product management:**
  - Add/edit/delete products
  - Product images (up to 5 per product)
  - Stock tracking
  - Categories

- **Shopping features:**
  - Product catalog page
  - Product detail page
  - Simple cart
  - WhatsApp checkout option
  - MoMo checkout option

#### 3. Business Tools
- **Invoice generator:**
  - PDF invoices
  - Email invoices
  - Invoice history

- **Contact forms:**
  - Form builder
  - SMS alerts (via Zamtel or third party)
  - WhatsApp alerts
  - Email notifications

### Technical Work

#### New Database Tables
```sql
- growbuilder_products (id, site_id, name, description, price, images, stock, category)
- growbuilder_orders (id, site_id, customer_name, customer_phone, items, total, status)
- growbuilder_payments (id, order_id, method, transaction_id, amount, status)
- growbuilder_invoices (id, site_id, customer_name, items, total, pdf_path)
```

#### Payment Integration
- MTN MoMo API wrapper
- Airtel Money API wrapper
- Payment verification service
- Webhook handlers

#### SMS Integration
- Zamtel SMS API
- Africa's Talking (backup)
- SMS queue system

### Deliverables

✅ Fully functioning business-ready builder  
✅ Payment processing system  
✅ E-commerce capabilities  
✅ Invoice generation

### People Needed

- 1 Backend developer (payments specialist)
- 1 QA tester

### Budget: K25,000-35,000

---

## Phase 3: Advanced Builder + Business Tools (1.5-2 months)

### Objectives

✅ Improve builder's power and flexibility  
✅ Introduce tools SMEs need most  
✅ Add ecosystem integration

### Features

#### 1. Builder Upgrades
- **Drag-and-drop blocks:**
  - Text blocks
  - Image blocks
  - Video embeds
  - Button blocks
  - Testimonial blocks
  - Pricing tables
  - FAQ accordions

- **Reusable sections:**
  - Save custom sections
  - Section library
  - Import/export sections

- **Theme customization:**
  - Color picker
  - Font selector (Google Fonts)
  - Spacing controls
  - Border radius controls

- **Blog/News feature:**
  - Blog post editor
  - Categories & tags
  - RSS feed
  - Social sharing

- **SEO basics:**
  - Page titles
  - Meta descriptions
  - Open Graph tags
  - Sitemap generation

#### 2. Business Tools

- **Booking/Scheduling system:**
  - Calendar integration
  - Time slot management
  - Email confirmations
  - SMS reminders
  - Payment collection

- **CRM-lite:**
  - Customer list
  - Contact history
  - Notes & tags
  - Email campaigns

- **Receipt generator:**
  - PDF receipts
  - Email receipts
  - Receipt history

- **Analytics dashboard:**
  - Page views
  - Visitor stats
  - Popular products
  - Conversion tracking

- **Social media posters:**
  - AI-generated graphics
  - Product promotion images
  - Event announcements
  - Auto-post to Facebook (optional)

#### 3. Ecosystem Integration

- **MyGrowNet Marketplace:**
  - Publish products to marketplace
  - Sync inventory
  - Unified order management

- **BizBoost integration:**
  - Import BizBoost products
  - Sync business info
  - Unified dashboard

- **MyGrowNet services:**
  - Access to training courses
  - Community forum
  - Support tickets

### Technical Work

#### New Components
```
- DragDropBuilder.vue (advanced editor)
- BlockLibrary.vue (block selector)
- ThemeCustomizer.vue (styling panel)
- BlogEditor.vue (blog post editor)
- BookingCalendar.vue (scheduling)
- CRMDashboard.vue (customer management)
- AnalyticsDashboard.vue (stats)
```

#### New Services
```php
- SEOService.php (meta tags, sitemap)
- BookingService.php (scheduling logic)
- AnalyticsService.php (tracking)
- SocialMediaService.php (poster generation)
- MarketplaceSyncService.php (product sync)
```

### Deliverables

✅ Full GrowBuilder 1.0  
✅ Advanced builder features  
✅ Complete business tool suite  
✅ Marketplace integration  
✅ Ready for mass rollout

### People Needed

- 2 Full-stack developers
- 1 Frontend specialist (Vue.js)
- 1 Designer
- 1 QA tester

### Budget: K40,000-60,000

---

## Phase 4: Commercial Rollout (1 month)

### Objectives

✅ Market aggressively  
✅ Onboard SMEs  
✅ Start partnerships  
✅ Launch ambassador program

### Activities

#### 1. Marketing Campaign
- **Digital marketing:**
  - Facebook ads targeting Zambian SMEs
  - Google ads (local keywords)
  - WhatsApp marketing
  - Email campaigns

- **Content marketing:**
  - YouTube tutorials
  - Blog posts
  - Success stories
  - Case studies

- **Partnerships:**
  - MTN partnership (featured app)
  - Airtel partnership
  - Lusaka SME associations
  - Co-operatives

#### 2. Ambassador Program
- **Recruit 20 ambassadors:**
  - Local influencers
  - Business coaches
  - Tech enthusiasts
  - MyGrowNet members

- **Incentives:**
  - K50 per referral
  - Free Pro plan
  - Exclusive training
  - Recognition badges

#### 3. Training & Support
- **MyGrowNet University courses:**
  - "Build Your Website in 1 Hour"
  - "E-commerce for Beginners"
  - "MoMo Payments Setup"
  - "SEO Basics for Zambian Businesses"

- **Support channels:**
  - WhatsApp support line
  - Email support
  - Video tutorials
  - Knowledge base

#### 4. Pricing Launch
- **Starter Plan:** K50-120/month
- **Business Plan:** K200-400/month
- **Pro Plan:** K500-800/month
- **Done-For-You:** K1,000-2,500 setup + monthly

- **Launch promotions:**
  - First month free
  - 50% off for 3 months
  - Free setup (normally K500)
  - Free domain for annual plans

### Deliverables

✅ 100+ active customers in month 1  
✅ 500+ active customers by month 3  
✅ Ambassador network established  
✅ Training content published  
✅ Partnership agreements signed

### People Needed

- 1 Marketing manager
- 2 Customer success reps
- 1 Content creator
- 5-10 ambassadors (commission-based)

### Budget: K30,000-50,000

---

## Phase 5: Expansion (2-6 months)

### Objectives

✅ Add mobile app  
✅ Add AI assistants  
✅ Expand templates  
✅ Improve builder intelligence

### Features

#### 1. Mobile App (Android)
- **Manage website from phone:**
  - Edit content
  - Add products
  - View orders
  - Respond to messages
  - Check analytics

- **Offline mode:**
  - Draft changes offline
  - Sync when online

#### 2. AI Features
- **AI page generator:**
  - "Create my website automatically"
  - Business type detection
  - Content generation
  - Image suggestions

- **AI content rewriter:**
  - Improve grammar
  - Translate to local languages
  - SEO optimization
  - Tone adjustment

- **AI image generator:**
  - Product photos
  - Social media graphics
  - Logo design
  - Banner images

#### 3. Advanced E-commerce
- **Inventory management:**
  - Stock alerts
  - Supplier management
  - Purchase orders
  - Barcode scanning

- **Delivery options:**
  - Courier integration (Postnet, DHL)
  - Pickup locations
  - Delivery tracking
  - Delivery zones & pricing

- **Advanced payments:**
  - Installment plans
  - Subscriptions
  - Loyalty points
  - Gift cards

#### 4. Template Expansion
- **20+ new templates:**
  - Industry-specific (salon, gym, clinic)
  - Event-specific (weddings, conferences)
  - Seasonal (Christmas, Easter)
  - Premium designs

### Technical Work

#### Mobile App
```
Technology: Flutter or React Native
Features:
- Native Android app
- Push notifications
- Offline sync
- Camera integration
```

#### AI Integration
```
Services:
- OpenAI API (GPT-4)
- Stability AI (image generation)
- Custom fine-tuned models
- Local language support
```

### Deliverables

✅ Android app published  
✅ AI features live  
✅ 25+ templates available  
✅ Advanced e-commerce features  
✅ 2,000+ active customers

### People Needed

- 1 Mobile developer (Flutter/React Native)
- 1 AI/ML specialist
- 2 Template designers
- 1 Backend developer

### Budget: K50,000-80,000

---

## Site Users Architecture (Independent Authentication)

### Overview

Each GrowBuilder site has its own independent user base, completely separate from MyGrowNet platform users. This enables:
- True site independence
- Site owners control their own members
- Same email can exist on different sites
- Sites can be transferred with their user data
- GDPR-compliant data isolation

### User Types

```
┌─────────────────────────────────────────────────────────┐
│                    User Architecture                     │
├─────────────────────────────────────────────────────────┤
│                                                          │
│  ┌──────────────────┐      ┌──────────────────┐        │
│  │  MyGrowNet Users │      │   Site Users     │        │
│  │  (Platform)      │      │   (Per Site)     │        │
│  ├──────────────────┤      ├──────────────────┤        │
│  │ - Platform login │      │ - Site-specific  │        │
│  │ - Create sites   │      │ - Own credentials│        │
│  │ - Manage builder │      │ - Member area    │        │
│  │ - Access courses │      │ - Site purchases │        │
│  └──────────────────┘      └──────────────────┘        │
│           │                         │                   │
│           │                         │                   │
│     users table              site_users table           │
│                                                          │
└─────────────────────────────────────────────────────────┘
```

### Database Schema

```sql
-- Site Users (independent from MyGrowNet users)
CREATE TABLE site_users (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    site_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(50) NULL,
    avatar VARCHAR(255) NULL,
    status ENUM('active', 'inactive', 'pending', 'suspended') DEFAULT 'pending',
    email_verified_at TIMESTAMP NULL,
    phone_verified_at TIMESTAMP NULL,
    last_login_at TIMESTAMP NULL,
    login_count INT UNSIGNED DEFAULT 0,
    metadata JSON NULL,
    remember_token VARCHAR(100) NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    FOREIGN KEY (site_id) REFERENCES growbuilder_sites(id) ON DELETE CASCADE,
    UNIQUE KEY unique_site_email (site_id, email),
    INDEX idx_site_status (site_id, status),
    INDEX idx_email (email)
);

-- Site User Sessions (for multi-device tracking)
CREATE TABLE site_user_sessions (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    site_user_id BIGINT UNSIGNED NOT NULL,
    token VARCHAR(255) NOT NULL UNIQUE,
    ip_address VARCHAR(45) NULL,
    user_agent TEXT NULL,
    last_activity_at TIMESTAMP NULL,
    expires_at TIMESTAMP NOT NULL,
    created_at TIMESTAMP NULL,
    
    FOREIGN KEY (site_user_id) REFERENCES site_users(id) ON DELETE CASCADE,
    INDEX idx_token (token),
    INDEX idx_expires (expires_at)
);

-- Site User Password Resets
CREATE TABLE site_user_password_resets (
    email VARCHAR(255) NOT NULL,
    site_id BIGINT UNSIGNED NOT NULL,
    token VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NULL,
    
    INDEX idx_site_email (site_id, email)
);
```

### Authentication Flow

```
┌─────────────────────────────────────────────────────────┐
│              Site User Authentication Flow               │
├─────────────────────────────────────────────────────────┤
│                                                          │
│  1. User visits: /sites/mybusiness/login                │
│                      │                                   │
│                      ▼                                   │
│  2. Site-branded login page loads                       │
│     (uses site's logo, colors, name)                    │
│                      │                                   │
│                      ▼                                   │
│  3. User submits credentials                            │
│                      │                                   │
│                      ▼                                   │
│  4. System checks site_users WHERE                      │
│     site_id = 'mybusiness' AND email = input            │
│                      │                                   │
│                      ▼                                   │
│  5. Create site-scoped session                          │
│     (stored in site_user_sessions)                      │
│                      │                                   │
│                      ▼                                   │
│  6. Redirect to /sites/mybusiness/member                │
│     or /sites/mybusiness/dashboard                      │
│                                                          │
└─────────────────────────────────────────────────────────┘
```

### Routes Structure

```php
// Site-specific auth routes
Route::prefix('sites/{subdomain}')->group(function () {
    // Public auth pages
    Route::get('/login', [SiteAuthController::class, 'showLogin']);
    Route::post('/login', [SiteAuthController::class, 'login']);
    Route::get('/register', [SiteAuthController::class, 'showRegister']);
    Route::post('/register', [SiteAuthController::class, 'register']);
    Route::post('/logout', [SiteAuthController::class, 'logout']);
    
    // Password reset
    Route::get('/forgot-password', [SiteAuthController::class, 'showForgotPassword']);
    Route::post('/forgot-password', [SiteAuthController::class, 'sendResetLink']);
    Route::get('/reset-password/{token}', [SiteAuthController::class, 'showResetPassword']);
    Route::post('/reset-password', [SiteAuthController::class, 'resetPassword']);
    
    // Protected member area
    Route::middleware('site.auth')->group(function () {
        Route::get('/member', [SiteMemberController::class, 'dashboard']);
        Route::get('/member/profile', [SiteMemberController::class, 'profile']);
        Route::put('/member/profile', [SiteMemberController::class, 'updateProfile']);
        Route::get('/member/orders', [SiteMemberController::class, 'orders']);
    });
});
```

### Model Structure

```php
// app/Models/GrowBuilder/SiteUser.php
class SiteUser extends Authenticatable
{
    protected $table = 'site_users';
    
    protected $fillable = [
        'site_id', 'name', 'email', 'password', 
        'phone', 'avatar', 'status', 'metadata'
    ];
    
    protected $hidden = ['password', 'remember_token'];
    
    protected $casts = [
        'email_verified_at' => 'datetime',
        'phone_verified_at' => 'datetime',
        'last_login_at' => 'datetime',
        'metadata' => 'array',
    ];
    
    // Relationships
    public function site(): BelongsTo
    {
        return $this->belongsTo(GrowBuilderSite::class, 'site_id');
    }
    
    public function orders(): HasMany
    {
        return $this->hasMany(GrowBuilderOrder::class, 'site_user_id');
    }
    
    // Scopes
    public function scopeForSite($query, $siteId)
    {
        return $query->where('site_id', $siteId);
    }
    
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
```

### Site Owner User Management

Site owners can manage their site's users through the GrowBuilder dashboard:

```
┌─────────────────────────────────────────────────────────┐
│           Site Owner User Management Features            │
├─────────────────────────────────────────────────────────┤
│                                                          │
│  📋 User List                                           │
│  ├── View all registered users                          │
│  ├── Search by name/email                               │
│  ├── Filter by status                                   │
│  └── Export user list (CSV)                             │
│                                                          │
│  👤 User Details                                        │
│  ├── View profile information                           │
│  ├── View order history                                 │
│  ├── View login activity                                │
│  └── Add notes                                          │
│                                                          │
│  ⚙️ User Actions                                        │
│  ├── Activate/Deactivate user                           │
│  ├── Reset password                                     │
│  ├── Send email                                         │
│  └── Delete user                                        │
│                                                          │
│  📊 User Analytics                                      │
│  ├── Total users                                        │
│  ├── New users (this month)                             │
│  ├── Active users                                       │
│  └── User growth chart                                  │
│                                                          │
└─────────────────────────────────────────────────────────┘
```

### Site Member Dashboard & Roles

Each site has its own member dashboard with role-based access control:

```
┌─────────────────────────────────────────────────────────┐
│              Site Member Role Hierarchy                  │
├─────────────────────────────────────────────────────────┤
│                                                          │
│  👑 Site Owner (MyGrowNet User)                         │
│  │   - Full control over site                           │
│  │   - Manage all roles and permissions                 │
│  │   - Access GrowBuilder editor                        │
│  │   - View all analytics                               │
│  │                                                       │
│  ├── 🛡️ Site Admin (Site User)                         │
│  │   │   - Manage other users (except owner)            │
│  │   │   - Create/edit content                          │
│  │   │   - Manage products & orders                     │
│  │   │   - View analytics                               │
│  │   │                                                   │
│  │   ├── ✍️ Site Editor (Site User)                    │
│  │   │   │   - Create/edit posts & content              │
│  │   │   │   - Upload media                             │
│  │   │   │   - Moderate comments                        │
│  │   │   │                                               │
│  │   │   └── 👤 Site Member (Site User)                │
│  │   │           - View member-only content             │
│  │   │           - Place orders                         │
│  │   │           - View own profile & orders            │
│  │   │           - Comment on posts                     │
│  │   │                                                   │
│  │   └── 🛒 Site Customer (Site User)                  │
│  │           - Place orders                             │
│  │           - View order history                       │
│  │           - Basic profile                            │
│  │                                                       │
└─────────────────────────────────────────────────────────┘
```

### Site Roles & Permissions Schema

```sql
-- Site Roles (predefined + custom)
CREATE TABLE site_roles (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    site_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(50) NOT NULL,
    slug VARCHAR(50) NOT NULL,
    description VARCHAR(255) NULL,
    is_system BOOLEAN DEFAULT FALSE,  -- System roles can't be deleted
    level INT UNSIGNED DEFAULT 0,      -- Higher = more permissions
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    FOREIGN KEY (site_id) REFERENCES growbuilder_sites(id) ON DELETE CASCADE,
    UNIQUE KEY unique_site_role (site_id, slug)
);

-- Site Permissions
CREATE TABLE site_permissions (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL UNIQUE,
    group_name VARCHAR(50) NOT NULL,  -- users, content, orders, settings
    description VARCHAR(255) NULL,
    created_at TIMESTAMP NULL
);

-- Role-Permission Pivot
CREATE TABLE site_role_permissions (
    site_role_id BIGINT UNSIGNED NOT NULL,
    site_permission_id BIGINT UNSIGNED NOT NULL,
    
    PRIMARY KEY (site_role_id, site_permission_id),
    FOREIGN KEY (site_role_id) REFERENCES site_roles(id) ON DELETE CASCADE,
    FOREIGN KEY (site_permission_id) REFERENCES site_permissions(id) ON DELETE CASCADE
);

-- User-Role Assignment
ALTER TABLE site_users ADD COLUMN site_role_id BIGINT UNSIGNED NULL;
ALTER TABLE site_users ADD FOREIGN KEY (site_role_id) REFERENCES site_roles(id) ON SET NULL;
```

### Default Permissions

```php
// Seeded permissions grouped by category
$permissions = [
    'users' => [
        'users.view'        => 'View user list',
        'users.create'      => 'Create new users',
        'users.edit'        => 'Edit user profiles',
        'users.delete'      => 'Delete users',
        'users.roles'       => 'Assign roles to users',
    ],
    'content' => [
        'posts.view'        => 'View all posts',
        'posts.create'      => 'Create new posts',
        'posts.edit'        => 'Edit posts',
        'posts.delete'      => 'Delete posts',
        'posts.publish'     => 'Publish/unpublish posts',
        'media.upload'      => 'Upload media files',
        'media.delete'      => 'Delete media files',
        'comments.moderate' => 'Moderate comments',
    ],
    'orders' => [
        'orders.view'       => 'View all orders',
        'orders.manage'     => 'Update order status',
        'orders.refund'     => 'Process refunds',
        'products.view'     => 'View products',
        'products.manage'   => 'Create/edit products',
    ],
    'settings' => [
        'settings.view'     => 'View site settings',
        'settings.edit'     => 'Edit site settings',
        'analytics.view'    => 'View analytics',
    ],
    'member' => [
        'member.access'     => 'Access member area',
        'member.content'    => 'View member-only content',
        'member.orders'     => 'Place orders',
        'member.profile'    => 'Edit own profile',
    ],
];
```

### Default Role Configurations

```php
// System roles created for each new site
$defaultRoles = [
    'admin' => [
        'name' => 'Administrator',
        'level' => 100,
        'is_system' => true,
        'permissions' => ['*'],  // All permissions
    ],
    'editor' => [
        'name' => 'Editor',
        'level' => 50,
        'is_system' => true,
        'permissions' => [
            'posts.*', 'media.*', 'comments.moderate',
            'member.access', 'member.content',
        ],
    ],
    'member' => [
        'name' => 'Member',
        'level' => 20,
        'is_system' => true,
        'permissions' => [
            'member.access', 'member.content', 
            'member.orders', 'member.profile',
        ],
    ],
    'customer' => [
        'name' => 'Customer',
        'level' => 10,
        'is_system' => true,
        'permissions' => [
            'member.orders', 'member.profile',
        ],
    ],
];
```

### Site Member Dashboard Structure

```
┌─────────────────────────────────────────────────────────┐
│         /sites/{subdomain}/member - Dashboard            │
├─────────────────────────────────────────────────────────┤
│                                                          │
│  ┌─────────────┐  ┌──────────────────────────────────┐ │
│  │  Sidebar    │  │  Main Content Area               │ │
│  │             │  │                                  │ │
│  │  🏠 Home    │  │  Welcome, {name}!                │ │
│  │  👤 Profile │  │                                  │ │
│  │  🛒 Orders  │  │  ┌────────┐ ┌────────┐          │ │
│  │  📝 Posts*  │  │  │ Orders │ │ Posts  │          │ │
│  │  📊 Stats*  │  │  │   12   │ │   5    │          │ │
│  │  👥 Users*  │  │  └────────┘ └────────┘          │ │
│  │  ⚙️ Settings│  │                                  │ │
│  │             │  │  Recent Activity                 │ │
│  │  * = Role   │  │  - Order #123 placed            │ │
│  │    based    │  │  - New comment on post          │ │
│  │             │  │  - Profile updated              │ │
│  └─────────────┘  └──────────────────────────────────┘ │
│                                                          │
└─────────────────────────────────────────────────────────┘
```

### Member Dashboard Routes

```php
// Site member dashboard routes
Route::prefix('sites/{subdomain}/member')->middleware('site.auth')->group(function () {
    // All members
    Route::get('/', [SiteMemberController::class, 'dashboard']);
    Route::get('/profile', [SiteMemberController::class, 'profile']);
    Route::put('/profile', [SiteMemberController::class, 'updateProfile']);
    Route::get('/orders', [SiteMemberController::class, 'orders']);
    Route::get('/orders/{id}', [SiteMemberController::class, 'orderDetail']);
    
    // Editors & above (content management)
    Route::middleware('site.permission:posts.view')->group(function () {
        Route::get('/posts', [SitePostController::class, 'index']);
        Route::get('/posts/create', [SitePostController::class, 'create']);
        Route::post('/posts', [SitePostController::class, 'store']);
        Route::get('/posts/{id}/edit', [SitePostController::class, 'edit']);
        Route::put('/posts/{id}', [SitePostController::class, 'update']);
        Route::delete('/posts/{id}', [SitePostController::class, 'destroy']);
    });
    
    // Admins only (user management)
    Route::middleware('site.permission:users.view')->group(function () {
        Route::get('/users', [SiteUserManagementController::class, 'index']);
        Route::get('/users/{id}', [SiteUserManagementController::class, 'show']);
        Route::put('/users/{id}', [SiteUserManagementController::class, 'update']);
        Route::put('/users/{id}/role', [SiteUserManagementController::class, 'updateRole']);
        Route::delete('/users/{id}', [SiteUserManagementController::class, 'destroy']);
    });
    
    // Admins only (analytics)
    Route::middleware('site.permission:analytics.view')->group(function () {
        Route::get('/analytics', [SiteAnalyticsController::class, 'index']);
    });
    
    // Admins only (settings)
    Route::middleware('site.permission:settings.view')->group(function () {
        Route::get('/settings', [SiteSettingsController::class, 'index']);
        Route::put('/settings', [SiteSettingsController::class, 'update']);
    });
});
```

### Site Posts System

```sql
-- Site Posts (blog/news for each site)
CREATE TABLE site_posts (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    site_id BIGINT UNSIGNED NOT NULL,
    author_id BIGINT UNSIGNED NOT NULL,  -- site_user who created
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL,
    excerpt TEXT NULL,
    content LONGTEXT NOT NULL,
    featured_image VARCHAR(255) NULL,
    status ENUM('draft', 'published', 'scheduled', 'archived') DEFAULT 'draft',
    visibility ENUM('public', 'members', 'private') DEFAULT 'public',
    published_at TIMESTAMP NULL,
    scheduled_at TIMESTAMP NULL,
    views_count INT UNSIGNED DEFAULT 0,
    comments_enabled BOOLEAN DEFAULT TRUE,
    metadata JSON NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    FOREIGN KEY (site_id) REFERENCES growbuilder_sites(id) ON DELETE CASCADE,
    FOREIGN KEY (author_id) REFERENCES site_users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_site_slug (site_id, slug),
    INDEX idx_site_status (site_id, status, published_at)
);

-- Site Post Categories
CREATE TABLE site_post_categories (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    site_id BIGINT UNSIGNED NOT NULL,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL,
    description TEXT NULL,
    parent_id BIGINT UNSIGNED NULL,
    sort_order INT DEFAULT 0,
    created_at TIMESTAMP NULL,
    
    FOREIGN KEY (site_id) REFERENCES growbuilder_sites(id) ON DELETE CASCADE,
    FOREIGN KEY (parent_id) REFERENCES site_post_categories(id) ON DELETE SET NULL,
    UNIQUE KEY unique_site_category (site_id, slug)
);

-- Post-Category Pivot
CREATE TABLE site_post_category (
    site_post_id BIGINT UNSIGNED NOT NULL,
    site_post_category_id BIGINT UNSIGNED NOT NULL,
    
    PRIMARY KEY (site_post_id, site_post_category_id),
    FOREIGN KEY (site_post_id) REFERENCES site_posts(id) ON DELETE CASCADE,
    FOREIGN KEY (site_post_category_id) REFERENCES site_post_categories(id) ON DELETE CASCADE
);

-- Site Comments
CREATE TABLE site_comments (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    site_id BIGINT UNSIGNED NOT NULL,
    post_id BIGINT UNSIGNED NOT NULL,
    site_user_id BIGINT UNSIGNED NULL,  -- NULL for guest comments
    parent_id BIGINT UNSIGNED NULL,      -- For replies
    author_name VARCHAR(100) NULL,       -- For guests
    author_email VARCHAR(255) NULL,      -- For guests
    content TEXT NOT NULL,
    status ENUM('pending', 'approved', 'spam', 'trash') DEFAULT 'pending',
    ip_address VARCHAR(45) NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    
    FOREIGN KEY (site_id) REFERENCES growbuilder_sites(id) ON DELETE CASCADE,
    FOREIGN KEY (post_id) REFERENCES site_posts(id) ON DELETE CASCADE,
    FOREIGN KEY (site_user_id) REFERENCES site_users(id) ON DELETE SET NULL,
    FOREIGN KEY (parent_id) REFERENCES site_comments(id) ON DELETE CASCADE,
    INDEX idx_post_status (post_id, status)
);
```

### Vue Pages Structure (Site Member Area)

```
resources/js/pages/SiteMember/
├── Dashboard.vue           # Member home
├── Profile/
│   ├── Index.vue          # View/edit profile
│   └── ChangePassword.vue # Change password
├── Orders/
│   ├── Index.vue          # Order list
│   └── Show.vue           # Order detail
├── Posts/                  # Editor+ only
│   ├── Index.vue          # Post list
│   ├── Create.vue         # Create post
│   └── Edit.vue           # Edit post
├── Users/                  # Admin only
│   ├── Index.vue          # User list
│   └── Show.vue           # User detail
├── Analytics/              # Admin only
│   └── Index.vue          # Site analytics
└── Settings/               # Admin only
    └── Index.vue          # Site settings
```

### Navigation Auth Buttons

The GrowBuilder editor includes auth button configuration:

```typescript
interface NavigationSettings {
    // ... existing fields
    showAuthButtons: boolean;      // Toggle auth buttons
    loginText: string;             // "Login" button text
    registerText: string;          // "Sign Up" button text
    loginStyle: 'link' | 'outline' | 'solid';
    registerStyle: 'link' | 'outline' | 'solid';
}
```

These buttons link to:
- Login: `/sites/{subdomain}/login`
- Register: `/sites/{subdomain}/register`

### Member CTA Section

A new section type for promoting membership:

```typescript
{
    type: 'member-cta',
    content: {
        title: 'Join Our Community',
        subtitle: 'Become a member and unlock exclusive benefits',
        description: 'Get access to premium content...',
        benefits: ['Exclusive content', 'Member discounts', 'Priority support'],
        registerText: 'Sign Up Now',
        loginText: 'Already a member? Login',
        registerButtonStyle: 'solid',
        showLoginLink: true,
        backgroundColor: '#1e40af',
        textColor: '#ffffff'
    }
}
```

### Security Considerations

1. **Password Hashing**: bcrypt with cost factor 12
2. **Rate Limiting**: 5 login attempts per minute per IP
3. **Session Security**: HTTP-only cookies, secure flag in production
4. **CSRF Protection**: Laravel's built-in CSRF tokens
5. **Email Verification**: Optional, configurable per site
6. **Password Requirements**: Minimum 8 characters, configurable

### Implementation Status

- [x] Create migration for `site_users` table
- [x] Create migration for `site_user_sessions` table
- [x] Create migration for `site_user_password_resets` table
- [x] Create migration for `site_roles` and `site_permissions` tables
- [x] Create migration for `site_posts` and `site_comments` tables
- [x] Create `SiteUser` model
- [x] Create `SiteRole` model
- [x] Create `SitePermission` model
- [x] Create `SitePost` model
- [x] Create `SiteComment` model
- [x] Create `SiteAuthController`
- [x] Create `SiteMemberController`
- [x] Create `SitePostController`
- [x] Create `SiteUserManagementController`
- [x] Create site-branded login page (Vue)
- [x] Create site-branded register page (Vue)
- [x] Create member dashboard page (Vue)
- [x] Create profile page (Vue)
- [x] Create orders pages (Vue)
- [x] Create `SiteRoleService` for default roles
- [x] Add `site.auth` middleware for site users
- [x] Add `site.permission` middleware for permission checking
- [x] Create `SitePermissionsSeeder`
- [x] Run migrations
- [x] Run permissions seeder (25 permissions created)
- [x] Hook up default role creation on site creation
- [ ] Test site user registration flow
- [ ] Test site user login flow
- [ ] Create posts management pages (Vue)
- [ ] Create user management pages (Vue)

---

## Technical Architecture

### System Overview

```
┌─────────────────────────────────────────────────────────┐
│                    GrowBuilder Platform                  │
├─────────────────────────────────────────────────────────┤
│                                                          │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐ │
│  │   Builder    │  │   Renderer   │  │   Hosting    │ │
│  │   (Vue.js)   │  │   (Laravel)  │  │   (Nginx)    │ │
│  └──────────────┘  └──────────────┘  └──────────────┘ │
│         │                  │                  │         │
│         └──────────────────┴──────────────────┘         │
│                           │                              │
│                  ┌────────▼────────┐                    │
│                  │    Database     │                    │
│                  │   (MySQL)       │                    │
│                  └────────┬────────┘                    │
│                           │                              │
│         ┌─────────────────┼─────────────────┐          │
│         │                 │                 │          │
│    ┌────▼────┐      ┌────▼────┐      ┌────▼────┐     │
│    │ Storage │      │Payments │      │   SMS   │     │
│    │  (S3)   │      │ (MoMo)  │      │(Zamtel) │     │
│    └─────────┘      └─────────┘      └─────────┘     │
│                                                          │
└─────────────────────────────────────────────────────────┘
```

### Frontend (Builder)

**Technology Stack:**
- Vue.js 3 (Composition API)
- TypeScript
- Tailwind CSS
- Vue Draggable (drag-drop)
- Pinia (state management)
- Vite (build tool)

**Key Components:**
```
resources/js/pages/GrowBuilder/
├── Dashboard.vue (site list)
├── Builder/
│   ├── Editor.vue (main editor)
│   ├── Sidebar.vue (section library)
│   ├── Canvas.vue (preview area)
│   ├── Inspector.vue (properties panel)
│   └── Toolbar.vue (actions)
├── Templates/
│   ├── Gallery.vue (template selector)
│   └── Preview.vue (template preview)
└── Settings/
    ├── General.vue (site settings)
    ├── Domain.vue (domain management)
    └── SEO.vue (SEO settings)
```

### Backend (Laravel)

**API Endpoints:**
```php
// Sites
GET    /api/growbuilder/sites
POST   /api/growbuilder/sites
GET    /api/growbuilder/sites/{id}
PUT    /api/growbuilder/sites/{id}
DELETE /api/growbuilder/sites/{id}

// Pages
GET    /api/growbuilder/sites/{id}/pages
POST   /api/growbuilder/sites/{id}/pages
PUT    /api/growbuilder/pages/{id}
DELETE /api/growbuilder/pages/{id}

// Templates
GET    /api/growbuilder/templates
GET    /api/growbuilder/templates/{id}

// Products
GET    /api/growbuilder/sites/{id}/products
POST   /api/growbuilder/sites/{id}/products
PUT    /api/growbuilder/products/{id}
DELETE /api/growbuilder/products/{id}

// Orders
GET    /api/growbuilder/sites/{id}/orders
GET    /api/growbuilder/orders/{id}
PUT    /api/growbuilder/orders/{id}/status

// Payments
POST   /api/growbuilder/payments/momo
POST   /api/growbuilder/payments/airtel
POST   /api/growbuilder/payments/webhook
```

**Services:**
```php
app/Services/GrowBuilder/
├── SiteService.php (site management)
├── PageService.php (page CRUD)
├── TemplateService.php (template logic)
├── RenderService.php (JSON → HTML)
├── PaymentService.php (MoMo/Airtel)
├── SMSService.php (notifications)
├── SEOService.php (meta tags, sitemap)
└── AnalyticsService.php (tracking)
```

### Rendering Engine

**How it works:**
1. Page content stored as JSON
2. Runtime renderer transforms JSON → HTML components
3. Server-side caching for speed
4. CDN for static assets

**Example JSON structure:**
```json
{
  "sections": [
    {
      "type": "hero",
      "content": {
        "title": "Welcome to My Business",
        "subtitle": "Quality products at great prices",
        "image": "/storage/hero.jpg",
        "button": {
          "text": "Shop Now",
          "link": "/products"
        }
      },
      "style": {
        "backgroundColor": "#2563eb",
        "textColor": "#ffffff"
      }
    },
    {
      "type": "services",
      "content": {
        "title": "Our Services",
        "items": [
          {
            "icon": "shopping-bag",
            "title": "Quality Products",
            "description": "Best products in town"
          }
        ]
      }
    }
  ]
}
```

### Hosting Infrastructure

**Subdomain Routing:**
```nginx
# Nginx configuration
server {
    server_name *.mygrownet.com;
    
    location / {
        proxy_pass http://localhost:8000;
        proxy_set_header Host $host;
        proxy_set_header X-Forwarded-For $remote_addr;
    }
}
```

**Laravel Route:**
```php
// Catch-all route for subdomains
Route::domain('{subdomain}.mygrownet.com')->group(function () {
    Route::get('/{any?}', [GrowBuilderController::class, 'render'])
        ->where('any', '.*');
});
```

### Database Schema

```sql
-- Sites
CREATE TABLE growbuilder_sites (
    id BIGINT PRIMARY KEY,
    user_id BIGINT,
    subdomain VARCHAR(255) UNIQUE,
    custom_domain VARCHAR(255) NULLABLE,
    template_id BIGINT,
    name VARCHAR(255),
    settings JSON,
    is_active BOOLEAN DEFAULT true,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);

-- Pages
CREATE TABLE growbuilder_pages (
    id BIGINT PRIMARY KEY,
    site_id BIGINT,
    slug VARCHAR(255),
    title VARCHAR(255),
    content_json JSON,
    meta_title VARCHAR(255),
    meta_description TEXT,
    is_published BOOLEAN DEFAULT false,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    UNIQUE(site_id, slug)
);

-- Templates
CREATE TABLE growbuilder_templates (
    id BIGINT PRIMARY KEY,
    name VARCHAR(255),
    category VARCHAR(255),
    preview_image VARCHAR(255),
    structure_json JSON,
    is_premium BOOLEAN DEFAULT false,
    price INT DEFAULT 0,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);

-- Products
CREATE TABLE growbuilder_products (
    id BIGINT PRIMARY KEY,
    site_id BIGINT,
    name VARCHAR(255),
    description TEXT,
    price INT,
    compare_price INT NULLABLE,
    images JSON,
    stock_quantity INT DEFAULT 0,
    category VARCHAR(255),
    is_active BOOLEAN DEFAULT true,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);

-- Orders
CREATE TABLE growbuilder_orders (
    id BIGINT PRIMARY KEY,
    site_id BIGINT,
    order_number VARCHAR(255) UNIQUE,
    customer_name VARCHAR(255),
    customer_phone VARCHAR(255),
    customer_email VARCHAR(255) NULLABLE,
    items JSON,
    subtotal INT,
    shipping_cost INT DEFAULT 0,
    total INT,
    status ENUM('pending', 'paid', 'processing', 'completed', 'cancelled'),
    payment_method VARCHAR(255),
    payment_transaction_id VARCHAR(255) NULLABLE,
    notes TEXT NULLABLE,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);

-- Payments
CREATE TABLE growbuilder_payments (
    id BIGINT PRIMARY KEY,
    order_id BIGINT,
    method ENUM('momo', 'airtel', 'cash'),
    transaction_id VARCHAR(255),
    amount INT,
    status ENUM('pending', 'completed', 'failed'),
    response_data JSON,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

---

## Team & Budget

### Minimum Team

| Role | Quantity | Rate (K/month) | Duration | Total |
|------|----------|----------------|----------|-------|
| **Full-stack Developer** | 1 | 15,000 | 6 months | 90,000 |
| **Frontend Specialist** | 1 | 12,000 | 4 months | 48,000 |
| **UI/UX Designer** | 1 | 8,000 | 4 months | 32,000 |
| **QA Tester** | 1 | 6,000 | 3 months | 18,000 |
| **Marketing Manager** | 1 | 10,000 | 2 months | 20,000 |
| **Content Creator** | 1 | 5,000 | 2 months | 10,000 |

**Total Labor:** K218,000

### Infrastructure Costs

| Item | Cost (K/month) | Duration | Total |
|------|----------------|----------|-------|
| **Cloud Hosting** | 2,000 | 6 months | 12,000 |
| **CDN** | 1,000 | 6 months | 6,000 |
| **Domain & SSL** | 500 | 6 months | 3,000 |
| **SMS Gateway** | 1,500 | 6 months | 9,000 |
| **Payment Gateway Fees** | 1,000 | 6 months | 6,000 |
| **AI API Credits** | 2,000 | 3 months | 6,000 |

**Total Infrastructure:** K42,000

### Marketing Budget

| Item | Cost (K) |
|------|----------|
| **Facebook Ads** | 20,000 |
| **Google Ads** | 15,000 |
| **Ambassador Program** | 10,000 |
| **Content Creation** | 8,000 |
| **Launch Event** | 5,000 |

**Total Marketing:** K58,000

### Grand Total

**MVP (Phase 1-2):** K80,000-100,000  
**Full Launch (Phase 1-4):** K200,000-300,000  
**With Expansion (Phase 1-5):** K300,000-400,000

---

## Success Metrics & KPIs

### Phase 1 (Foundation)
- ✅ 5 templates created
- ✅ 10 pilot users
- ✅ Builder functional
- ✅ 95% uptime

### Phase 2 (Commerce)
- ✅ 50 active sites
- ✅ 100 products listed
- ✅ K50,000 processed through MoMo
- ✅ 90% payment success rate

### Phase 3 (Advanced)
- ✅ 200 active sites
- ✅ 20 templates available
- ✅ 50 marketplace integrations
- ✅ 4.5/5 user rating

### Phase 4 (Rollout)
- ✅ 500 active sites
- ✅ K500,000 MRR
- ✅ 80% retention rate
- ✅ 20 ambassadors active

### Phase 5 (Expansion)
- ✅ 2,000 active sites
- ✅ K2,000,000 MRR
- ✅ Mobile app: 1,000 downloads
- ✅ Expand to 2 more countries

---

## Risk Management

### Technical Risks

| Risk | Probability | Impact | Mitigation |
|------|-------------|--------|------------|
| **Payment API downtime** | Medium | High | Fallback to WhatsApp orders |
| **Slow page load times** | Medium | High | Aggressive caching, CDN |
| **Security vulnerabilities** | Low | Critical | Regular audits, updates |
| **Data loss** | Low | Critical | Daily backups, redundancy |

### Business Risks

| Risk | Probability | Impact | Mitigation |
|------|-------------|--------|------------|
| **Low adoption** | Medium | High | Aggressive marketing, free trials |
| **Competition** | High | Medium | Focus on local features, pricing |
| **Payment fraud** | Medium | Medium | Verification, limits, monitoring |
| **Customer churn** | Medium | High | Excellent support, training |

---

## Next Steps

### Immediate (Week 1-2)
1. ✅ Finalize vision and implementation docs
2. ⏳ Assemble core team
3. ⏳ Set up development environment
4. ⏳ Create project roadmap in Jira/Trello
5. ⏳ Design database schema
6. ⏳ Create wireframes for builder

### Short-term (Month 1)
1. ⏳ Build authentication system
2. ⏳ Create site management dashboard
3. ⏳ Develop first template
4. ⏳ Build basic page editor
5. ⏳ Set up hosting infrastructure

### Medium-term (Month 2-3)
1. ⏳ Complete Phase 1 (Foundation)
2. ⏳ Start Phase 2 (Commerce)
3. ⏳ Pilot with 10 businesses
4. ⏳ Gather feedback
5. ⏳ Iterate and improve

---

## Conclusion

GrowBuilder represents a significant opportunity to become the leading website builder for Zambian SMEs. With a phased approach, local-first design, and integration with the MyGrowNet ecosystem, we're positioned to capture a large market share.

**Key Success Factors:**
1. **Simplicity** - Easier than WordPress
2. **Local payments** - MoMo & Airtel Money built-in
3. **Affordability** - Cheaper than international alternatives
4. **Support** - Local language, local context
5. **Ecosystem** - Part of MyGrowNet platform

**Timeline:** 6-8 months to full launch  
**Investment:** K200,000-300,000  
**Projected ROI:** K2,000,000 MRR by Year 2

Let's build the future of Zambian SME websites! 🚀

