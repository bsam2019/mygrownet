# GrowBuilder Implementation Analysis

**Last Updated:** January 19, 2026
**Status:** Complete Analysis
**Purpose:** Comprehensive overview of GrowBuilder architecture, AI capabilities, and infrastructure for future development

---

## Executive Summary

GrowBuilder is a sophisticated website builder platform built with **Domain-Driven Design (DDD)** principles. The system features:

- **Multi-tier architecture** with Domain, Application, Infrastructure, and Presentation layers
- **Comprehensive AI integration** with support for multiple AI providers (OpenAI, Groq, Gemini, Ollama, Grok/XAI)
- **Flexible section-based page builder** with 25+ section types
- **Smart AI assistant** with intent classification, content generation, and learning from user feedback
- **Usage tracking and tier-based restrictions** for AI features
- **Payment gateway integration** for e-commerce functionality
- **Site management** with templates, pages, media, products, and forms

---

## Architecture Overview

### 1. Domain Layer (`app/Domain/GrowBuilder/`)

**Purpose:** Core business logic and domain models

**Structure:**
```
Domain/GrowBuilder/
├── Entities/              # Rich domain models
│   ├── Site.php          # Website entity
│   ├── Page.php          # Page entity
│   ├── Template.php      # Template entity
│   ├── Product.php       # Product entity
│   └── Order.php         # Order entity
├── ValueObjects/         # Immutable value objects
│   ├── SiteId.php
│   ├── PageId.php
│   ├── Subdomain.php
│   ├── SiteStatus.php
│   ├── SitePlan.php
│   ├── Theme.php
│   ├── Money.php
│   ├── OrderStatus.php
│   └── TemplateCategory.php
├── Repositories/         # Repository interfaces
│   ├── SiteRepositoryInterface.php
│   ├── PageRepositoryInterface.php
│   └── TemplateRepositoryInterface.php
├── Services/             # Domain services
│   ├── PaymentGatewayInterface.php
│   └── PaymentResult.php
└── Payment/              # Payment subdomain
    ├── Contracts/
    ├── DTOs/
    ├── Enums/
    ├── Gateways/         # Payment gateway implementations
    └── Services/
```

**Key Entities:**

- **Site**: Represents a website with subdomain, custom domain, settings, theme, SEO settings
- **Page**: Represents a page within a site with content sections, metadata, navigation settings
- **Template**: Represents a reusable website template with structure and default styles
- **Product**: E-commerce products for sale on the site
- **Order**: Customer orders with payment tracking

### 2. Infrastructure Layer (`app/Infrastructure/GrowBuilder/`)

**Purpose:** Technical implementations and data persistence

**Models:**
```
Infrastructure/GrowBuilder/Models/
├── GrowBuilderSite.php           # Eloquent model for sites
├── GrowBuilderPage.php           # Eloquent model for pages
├── GrowBuilderTemplate.php       # Eloquent model for templates
├── GrowBuilderProduct.php        # Eloquent model for products
├── GrowBuilderMedia.php          # Eloquent model for media files
├── GrowBuilderForm.php           # Eloquent model for forms
├── GrowBuilderFormSubmission.php # Eloquent model for form submissions
├── GrowBuilderOrder.php          # Eloquent model for orders
├── GrowBuilderInvoice.php        # Eloquent model for invoices
├── GrowBuilderPayment.php        # Eloquent model for payments
├── GrowBuilderPaymentSettings.php # Eloquent model for payment config
├── GrowBuilderPageView.php       # Eloquent model for analytics
└── AIFeedback.php                # Eloquent model for AI feedback
```

**Database Tables:**
- `growbuilder_sites` - Website records
- `growbuilder_pages` - Page records
- `growbuilder_templates` - Template records
- `growbuilder_products` - Product records
- `growbuilder_media` - Media files
- `growbuilder_forms` - Contact forms
- `growbuilder_form_submissions` - Form submissions
- `growbuilder_orders` - Orders
- `growbuilder_invoices` - Invoices
- `growbuilder_payments` - Payment records
- `growbuilder_payment_settings` - Payment gateway configuration
- `growbuilder_page_views` - Page view analytics
- `growbuilder_ai_usage` - AI usage tracking
- `growbuilder_ai_feedback` - AI feedback for learning

### 3. Application Layer (`app/Application/GrowBuilder/`)

**Purpose:** Use cases and application services

**Use Cases:**
```
Application/GrowBuilder/UseCases/
├── CreateSiteUseCase.php         # Create new website
├── UpdateSiteUseCase.php         # Update website settings
├── ApplySiteTemplateUseCase.php  # Apply template to site
├── SavePageContentUseCase.php    # Save page content
├── PublishSiteUseCase.php        # Publish website
└── Product/
    └── CreateProductUseCase.php  # Create product
```

**DTOs:**
```
Application/GrowBuilder/DTOs/
├── CreateSiteDTO.php
├── UpdateSiteDTO.php
└── SavePageContentDTO.php
```

### 4. Presentation Layer (`app/Http/Controllers/GrowBuilder/`)

**Purpose:** HTTP request handling and responses

**Controllers:**
```
Http/Controllers/GrowBuilder/
├── SiteController.php              # Site CRUD operations
├── EditorController.php            # Page editor operations
├── AIController.php                # AI content generation
├── MediaController.php             # Media management
├── ProductController.php           # Product management
├── OrderController.php             # Order management
├── FormSubmissionController.php    # Form handling
├── PaymentSettingsController.php   # Payment configuration
├── PaymentConfigController.php     # Payment gateway config
├── PaymentWebhookController.php    # Payment webhooks
├── SiteTemplateController.php      # Template management
├── SiteAuthController.php          # Site member authentication
├── SiteMemberController.php        # Member dashboard
├── SiteContactController.php       # Contact form handling
├── SitePostController.php          # Blog post management
├── SiteProductController.php       # Member product management
├── SiteUserManagementController.php # Member user management
├── SiteBlogController.php          # Blog display
├── CheckoutController.php          # Checkout process
├── SubscriptionController.php      # Subscription management
└── RenderController.php            # Site rendering
```

---

## AI Services Architecture

### 1. AIContentService (`app/Services/GrowBuilder/AIContentService.php`)

**Purpose:** Core AI content generation and intent classification

**Key Features:**

#### Multi-Provider Support
```php
Supported Providers:
- OpenAI (gpt-3.5-turbo, gpt-4)
- Groq (llama-3.3-70b-versatile) - FREE
- Google Gemini (gemini-pro) - FREE tier
- Ollama (local, free)
- Grok/XAI (grok-beta)
```

#### Core Methods

1. **classifyIntent()** - Analyzes user message to determine action
   - Returns: `{ intent, confidence, params }`
   - Intents: create_page, add_section, edit_content, change_style, generate_content, navigation, footer, seo, colors, help, multi_step, clarify, unknown
   - Uses conversation history for context
   - Supports multi-step action detection

2. **smartChat()** - AI-first approach to handle any user request
   - Analyzes intent
   - Generates appropriate content/response
   - Returns structured data for frontend application
   - Actions: generate_content, create_page, change_style, update_navigation, update_footer, generate_seo, chat, clarify, analyze_page

3. **buildSmartChatSystemPrompt()** - Constructs AI system prompt with:
   - Business context (site name, type, industry)
   - Current page structure
   - Existing sections and pages
   - Site color scheme
   - Section schemas
   - Industry-specific knowledge
   - Creativity mode instructions
   - Learning from user feedback

#### Creativity Modes
```php
- 'guided': Follows best practices closely, conventional layouts
- 'balanced': (Default) Flexible with guidelines, adapts to user
- 'creative': Maximum freedom, experimental layouts
```

#### Learning System
- Tracks user feedback (accepted/rejected suggestions)
- Records feedback to `growbuilder_ai_feedback` table
- AI receives feedback context in prompts
- Adapts behavior based on acceptance rates

#### Section Schema Integration
- Integrates with `SectionTemplateService`
- Validates generated content against section schemas
- Ensures AI generates valid section structures

### 2. AIUsageService (`app/Services/GrowBuilder/AIUsageService.php`)

**Purpose:** Track and enforce AI usage limits based on user tier

**Key Features:**

#### Tier-Based Limits
```php
Default Limits:
- free: 5 prompts/month
- starter: 100 prompts/month
- business: unlimited
- agency: unlimited
```

#### Tier-Based Features
```php
- free: ['content']
- starter: ['content', 'section']
- business: ['content', 'seo', 'section']
- agency: ['content', 'seo', 'section', 'priority']
```

#### Core Methods

1. **getLimit(User)** - Get AI limit for user's tier
2. **getUsageCount(User)** - Get current month's usage
3. **getRemainingPrompts(User)** - Get remaining prompts
4. **canUseAI(User)** - Check if user can use AI
5. **hasFeatureAccess(User, feature)** - Check feature access
6. **hasUnlimitedAI(User)** - Check if unlimited
7. **hasPriorityProcessing(User)** - Check priority status
8. **recordUsage()** - Record AI usage
9. **getUsageStats()** - Get usage statistics
10. **getAvailableFeatures()** - Get available features for user

#### Usage Tracking
- Records: user_id, site_id, prompt_type, prompt, tokens_used, month_year, model, is_priority
- Cached for 5 minutes
- Integrates with TierConfigurationService for database-driven limits

### 3. SectionTemplateService (`app/Services/GrowBuilder/SectionTemplateService.php`)

**Purpose:** Define and validate section types and schemas

**25+ Section Types:**

| Type | Name | Category | Required Fields |
|------|------|----------|-----------------|
| hero | Hero | structure | title |
| page-header | Page Header | structure | title |
| about | About | content | title, description |
| services | Services | content | title, items |
| features | Features | content | title, items |
| testimonials | Testimonials | social-proof | title, items |
| pricing | Pricing | commerce | title, plans |
| contact | Contact | forms | title |
| faq | FAQ | content | title, items |
| team | Team | content | title, items |
| stats | Stats | social-proof | items |
| cta | Call to Action | structure | title, buttonText |
| gallery | Gallery | media | title |
| member-cta | Member Signup | structure | title |
| blog | Blog | content | title |
| products | Products | commerce | title |
| product-search | Product Search | commerce | (none) |
| video | Video | media | title |
| map | Map | forms | title |
| text | Text Block | content | content |
| divider | Divider | structure | (none) |

**Key Methods:**

1. **getTemplates()** - Get all section templates with schemas
2. **getTemplate(type)** - Get specific template
3. **getByCategory()** - Get templates by category
4. **generateAISchemaDoc()** - Generate schema documentation for AI prompts
5. **validateContent(type, content)** - Validate section content
6. **getDefaults(type)** - Get default content
7. **getDefaultStyle(type)** - Get default styling

**Schema Structure:**
```php
[
    'type' => 'hero',
    'name' => 'Hero',
    'description' => '...',
    'category' => 'structure',
    'required' => ['title'],
    'optional' => ['subtitle', 'buttonText', ...],
    'layouts' => ['centered', 'split-right', ...],
    'defaults' => [...],
    'style' => ['backgroundColor' => '#...', ...],
    'aiHints' => 'AI guidance for generating this section'
]
```

---

## API Endpoints

### AI Endpoints

```php
// AI Status and Usage
GET    /growbuilder/ai/status                          # Check AI availability
GET    /growbuilder/sites/{siteId}/ai/feedback-stats   # Get feedback statistics

// AI Content Generation
POST   /growbuilder/sites/{siteId}/ai/smart-chat       # Smart chat (main endpoint)
POST   /growbuilder/sites/{siteId}/ai/classify-intent  # Classify user intent
POST   /growbuilder/sites/{siteId}/ai/generate-content # Generate section content
POST   /growbuilder/sites/{siteId}/ai/generate-meta    # Generate SEO metadata
POST   /growbuilder/sites/{siteId}/ai/suggest-colors   # Suggest color palettes
POST   /growbuilder/sites/{siteId}/ai/improve-text     # Improve text content
POST   /growbuilder/sites/{siteId}/ai/translate        # Translate content
POST   /growbuilder/sites/{siteId}/ai/suggest-images   # Suggest images
POST   /growbuilder/sites/{siteId}/ai/generate-testimonials # Generate testimonials
POST   /growbuilder/sites/{siteId}/ai/generate-faqs    # Generate FAQs
POST   /growbuilder/sites/{siteId}/ai/generate-page    # Generate complete page
POST   /growbuilder/sites/{siteId}/ai/generate-page-detailed # Generate page with details

// AI Feedback
POST   /growbuilder/sites/{siteId}/ai/feedback         # Record user feedback
```

### Site Management Endpoints

```php
// Sites
GET    /growbuilder/                                   # List sites
GET    /growbuilder/sites/create                       # Create site form
POST   /growbuilder/sites                              # Store new site
GET    /growbuilder/sites/{id}                         # Show site
GET    /growbuilder/sites/{id}/settings                # Site settings
GET    /growbuilder/sites/{id}/analytics               # Site analytics
PUT    /growbuilder/sites/{id}                         # Update site
DELETE /growbuilder/sites/{id}                         # Delete site
POST   /growbuilder/sites/{id}/restore                 # Restore deleted site
POST   /growbuilder/sites/{id}/publish                 # Publish site
POST   /growbuilder/sites/{id}/unpublish               # Unpublish site

// Pages
GET    /growbuilder/editor/{siteId}                    # Editor
GET    /growbuilder/editor/{siteId}/page/{pageId}      # Edit page
POST   /growbuilder/editor/{siteId}/pages              # Create page
POST   /growbuilder/editor/{siteId}/pages/{pageId}/save # Save page content
PUT    /growbuilder/editor/{siteId}/pages/{pageId}     # Update page metadata
DELETE /growbuilder/editor/{siteId}/pages/{pageId}     # Delete page

// Media
GET    /growbuilder/sites/{siteId}/media               # List media
POST   /growbuilder/sites/{siteId}/media               # Upload media
POST   /growbuilder/sites/{siteId}/media/base64        # Upload base64 media
DELETE /growbuilder/sites/{siteId}/media/{mediaId}     # Delete media

// Products
GET    /growbuilder/sites/{siteId}/products            # List products
POST   /growbuilder/sites/{siteId}/products            # Create product
GET    /growbuilder/sites/{siteId}/products/{id}/edit  # Edit product
PUT    /growbuilder/sites/{siteId}/products/{id}       # Update product
DELETE /growbuilder/sites/{siteId}/products/{id}       # Delete product

// Orders
GET    /growbuilder/sites/{siteId}/orders              # List orders
GET    /growbuilder/sites/{siteId}/orders/{id}         # Show order
PUT    /growbuilder/sites/{siteId}/orders/{id}/status  # Update order status

// Forms
GET    /growbuilder/sites/{siteId}/form-submissions    # List submissions
GET    /growbuilder/sites/{siteId}/form-submissions/{id} # Show submission
POST   /growbuilder/sites/{siteId}/form-submissions/{id}/toggle-read # Mark read
DELETE /growbuilder/sites/{siteId}/form-submissions/{id} # Delete submission
```

---

## Frontend Integration

### Vue Components

**Editor Components:**
```
resources/js/pages/GrowBuilder/Editor/
├── components/
│   ├── inspectors/
│   │   ├── WhatsAppInspector.vue
│   │   └── [other inspectors]
│   ├── sections/
│   │   ├── WhatsAppSection.vue
│   │   └── [other sections]
│   └── modals/
│       ├── AIAssistantModal.vue
│       ├── ImageEditorModal.vue
│       └── WebsiteGeneratorModal.vue
├── composables/
│   └── useAIContext.ts
└── [editor pages]
```

### AI Context Composable

**`useAIContext.ts`** - Manages AI interaction state:
- Conversation history
- Site context (name, type, business info)
- Current page/section context
- Creativity level (guided/balanced/creative)
- Feedback tracking

---

## Database Schema

### Core Tables

#### growbuilder_sites
```sql
- id (PK)
- user_id (FK)
- template_id (FK)
- name
- subdomain (unique)
- custom_domain
- description
- logo
- favicon
- settings (JSON)
- theme (JSON)
- social_links (JSON)
- contact_info (JSON)
- business_hours (JSON)
- seo_settings (JSON)
- status (draft|published|suspended)
- plan (starter|business|agency)
- storage_used
- storage_limit
- published_at
- plan_expires_at
- scheduled_deletion_at
- timestamps
```

#### growbuilder_pages
```sql
- id (PK)
- site_id (FK)
- title
- slug
- content_json (JSON) - Array of sections
- meta_title
- meta_description
- og_image
- is_homepage
- is_published
- show_in_nav
- nav_order
- timestamps
```

#### growbuilder_templates
```sql
- id (PK)
- name
- slug
- category
- description
- preview_image
- thumbnail
- structure_json (JSON)
- default_styles (JSON)
- is_premium
- price
- is_active
- usage_count
- timestamps
```

#### growbuilder_ai_usage
```sql
- id (PK)
- user_id (FK)
- site_id (FK)
- prompt_type
- prompt (text, truncated)
- tokens_used
- month_year
- model
- is_priority
- created_at
```

#### growbuilder_ai_feedback
```sql
- id (PK)
- user_id (FK)
- site_id (FK)
- action_type
- section_type
- business_type
- applied (boolean)
- user_message
- ai_response (text, truncated)
- context (JSON)
- created_at
```

---

## Key Features Implemented

### 1. Smart AI Assistant
- **Intent Classification**: Understands user requests in natural language
- **Multi-Step Actions**: Detects and handles compound requests
- **Conversation Memory**: Uses recent messages for context
- **Creativity Modes**: Guided, Balanced, Creative
- **Learning System**: Adapts based on user feedback

### 2. Content Generation
- **Section Generation**: Creates complete section content
- **Page Generation**: Generates multi-section pages
- **SEO Optimization**: Generates meta titles, descriptions
- **Color Suggestions**: Recommends color palettes
- **Text Improvement**: Enhances existing content
- **Testimonials**: Generates realistic testimonials
- **FAQs**: Creates FAQ sections

### 3. Usage Management
- **Tier-Based Limits**: Different limits per subscription tier
- **Feature Access Control**: Some features only on higher tiers
- **Monthly Tracking**: Resets usage each month
- **Priority Processing**: Premium users get priority
- **Usage Statistics**: Detailed usage reporting

### 4. Section System
- **25+ Section Types**: Comprehensive section library
- **Flexible Layouts**: Multiple layout options per section
- **Schema Validation**: Ensures valid content structure
- **Default Styles**: Consistent styling
- **AI Hints**: Guidance for AI generation

### 5. Payment Integration
- **Multiple Gateways**: MTN MoMo, Airtel Money, DPO, Flutterwave, etc.
- **Payment Configuration**: Per-site gateway setup
- **Order Management**: Track customer orders
- **Invoice Generation**: Automatic invoicing
- **Webhook Handling**: Payment confirmation

### 6. Site Management
- **Templates**: Pre-built website templates
- **Subdomain Support**: Custom subdomains
- **Custom Domains**: Support for custom domains
- **Storage Tracking**: Monitor storage usage
- **Publishing**: Draft/Published states
- **Soft Deletes**: Scheduled deletion with recovery

### 7. Member Features
- **Site Authentication**: Member login/registration
- **Role-Based Access**: Admin, Editor, Viewer roles
- **Member Dashboard**: Member area
- **Blog Posts**: Member-created content
- **Product Management**: Member product listings
- **Analytics**: Member activity tracking

---

## Configuration

### Environment Variables

```env
# AI Provider Configuration
SERVICES_AI_PROVIDER=openai|groq|gemini|ollama|grok
SERVICES_AI_OPENAI_KEY=sk-...
SERVICES_AI_OPENAI_MODEL=gpt-3.5-turbo|gpt-4
SERVICES_AI_GROQ_KEY=...
SERVICES_AI_GROQ_MODEL=llama-3.3-70b-versatile
SERVICES_AI_GEMINI_KEY=...
SERVICES_AI_GEMINI_MODEL=gemini-pro
SERVICES_AI_OLLAMA_URL=http://localhost:11434/api
SERVICES_AI_OLLAMA_MODEL=llama3
SERVICES_AI_GROK_KEY=...
SERVICES_AI_GROK_MODEL=grok-beta
```

### Configuration Files

**`config/growbuilder.php`** - GrowBuilder configuration
**`config/marketplace.php`** - Marketplace configuration
**`config/rewards.php`** - Rewards system configuration

---

## Existing AI Features

### Phase 1: Layout Generation (COMPLETE)
- ✅ Removed over-constraints from AI rules
- ✅ Changed "ALWAYS/NEVER" to "Prefer/Avoid"
- ✅ AI respects user requests for unconventional layouts

### Phase 2: Creativity Modes (COMPLETE)
- ✅ Added creativity level UI toggle
- ✅ Creativity level persists to localStorage
- ✅ Backend uses creativity level in prompts
- ✅ Three modes: Guided, Balanced, Creative

### Phase 3: Learning System (COMPLETE)
- ✅ Rejection tracking with "Dismiss" button
- ✅ Feedback recording (accepted/rejected)
- ✅ AI receives feedback context in prompts
- ✅ AI adapts behavior based on acceptance rates
- ✅ Industry-specific learning integration

### Planned: AI Website Generator (DESIGN DOCUMENT)
- Design document exists: `docs/growbuilder/AI_WEBSITE_GENERATOR.md`
- Planned phases: Basic generation, Multi-page, Advanced features, Refinement
- Estimated cost: $0.002-0.03 per website generation

---

## Infrastructure & Services

### Service Providers

**`app/Providers/GrowBuilderServiceProvider.php`**
- Registers GrowBuilder services
- Binds repository interfaces
- Configures service container

### Repositories

**Domain Repositories (Interfaces):**
- `SiteRepositoryInterface`
- `PageRepositoryInterface`
- `TemplateRepositoryInterface`
- `ProductRepositoryInterface`

**Infrastructure Repositories (Implementations):**
- Eloquent-based implementations
- Located in Infrastructure layer

### Payment Gateways

**Supported Gateways:**
- MTN MoMo
- Airtel Money
- DPO
- Flutterwave
- MoneyUnify
- Pawapay
- Zamtel Kwacha

**Gateway Interface:**
```php
interface PaymentGatewayInterface {
    public function charge(PaymentRequest $request): PaymentResponse;
    public function refund(RefundRequest $request): RefundResponse;
    public function verify(string $transactionId): PaymentResponse;
}
```

---

## Performance Considerations

### Caching
- AI usage stats cached for 5 minutes
- Section templates cached
- Site data cached

### Database Optimization
- Indexes on frequently queried columns
- Soft deletes for data retention
- JSON columns for flexible data

### Storage Management
- Per-site storage limits
- Storage usage tracking
- Scheduled deletion for cleanup

---

## Security Features

### Access Control
- User authentication required
- Site ownership verification
- Role-based permissions for site members
- Tier-based feature access

### Data Protection
- Soft deletes for data recovery
- Audit logging for changes
- Payment data encryption
- Webhook signature verification

### Rate Limiting
- AI usage limits per tier
- Monthly reset of usage counters
- Priority processing for premium users

---

## Testing Infrastructure

### Test Files
- Unit tests for services
- Feature tests for endpoints
- Integration tests for payment gateways

### Test Database
- SQLite for testing
- Migrations run before tests
- Seeders for test data

---

## Deployment

### Docker Support
- Dockerfile for containerization
- Docker Compose for local development
- Nginx configuration for production

### Deployment Scripts
- `deployment/deploy-with-assets.sh`
- `deployment/deploy-with-seeder.sh`
- CORS configuration scripts

---

## Related Documentation

- **AI Layout Generation Fix**: `docs/growbuilder/AI_LAYOUT_GENERATION_FIX.md`
- **AI Website Generator**: `docs/growbuilder/AI_WEBSITE_GENERATOR.md`
- **Image Processing**: `docs/marketplace/IMAGE_PROCESSING.md`
- **Social Sharing**: `docs/marketplace/SOCIAL_SHARING.md`

---

## Next Steps for Development

### Immediate Priorities
1. Implement AI Website Generator (design document ready)
2. Enhance section templates with more layout options
3. Improve image suggestion and optimization
4. Add more payment gateway integrations

### Medium-Term
1. Advanced analytics and reporting
2. A/B testing for generated content
3. Multi-language support
4. Advanced SEO optimization

### Long-Term
1. AI-powered design recommendations
2. Automated content updates
3. Performance optimization suggestions
4. Competitor analysis integration

---

## Changelog

### January 19, 2026
- Created comprehensive implementation analysis
- Documented all layers and services
- Listed all endpoints and features
- Included database schema
- Added configuration details
- Documented existing AI features
- Provided next steps for development

