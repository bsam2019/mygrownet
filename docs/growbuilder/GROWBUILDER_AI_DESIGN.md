# GrowBuilder AI Design System

**Last Updated:** December 21, 2025  
**Status:** Planning  
**Priority:** Phase 1 Feature

---

## Overview

AI-assisted website creation for GrowBuilder. AI generates structure and content, then hands over to the visual builder for refinement.

**Core Principle:** AI assists, it does not replace the builder.

---

## What AI Does

- Speed up website creation
- Reduce thinking for beginners
- Improve design quality
- Guide users step-by-step
- Generate structure and content

---

## Zambia-First Design Principles

### Local Market Optimization

1. **WhatsApp-First CTAs** - AI automatically generates WhatsApp click-to-chat links as primary CTAs (most Zambian customers prefer WhatsApp)
2. **Mobile-First Layouts** - AI optimizes all layouts for mobile viewing (most users browse on phones with limited data)
3. **Low-Bandwidth Friendly** - Generated sites are lightweight, fast-loading on 2G/3G networks
4. **Local Payment Integration** - AI suggests MTN MoMo and Airtel Money payment sections by default

### Multilingual Support

AI can generate content in:
- **English** (default)
- **Bemba** - Northern/Copperbelt regions
- **Nyanja** - Lusaka/Eastern regions  
- **Tonga** - Southern region

User selects preferred language during onboarding. AI adapts tone and expressions appropriately.

### Seasonal Awareness

AI is aware of Zambian calendar for timely content:
- **Independence Day** (October 24) - Patriotic themes
- **Christmas/New Year** - Holiday promotions
- **Farming seasons** - Agricultural business content
- **School terms** - Education-related businesses
- **Rainy/Dry seasons** - Weather-appropriate services

---

## Phase 1: AI MVP (Must Have)

### 1. AI Website Generator

**Entry Point:** "Create My Website with AI" button at onboarding or dashboard.

**Onboarding Questions (5-7 max):**
1. Business name
2. Business type (dropdown)
3. What do you offer? (brief description)
4. Target customers
5. Contact details (phone, WhatsApp, email)
6. Preferred language (English, Bemba, Nyanja, Tonga)
7. Preferred color (optional)
8. Logo upload (optional)
9. Competitor website URL (optional - for differentiation suggestions)

### 2. Industry-Specific Templates

Pre-trained prompts for common Zambian businesses:

| Industry | Optimized Sections | Default CTAs |
|----------|-------------------|--------------|
| **Salon/Barbershop** | Services, Gallery, Booking, Prices | WhatsApp booking |
| **Restaurant/Food** | Menu, Gallery, Location, Hours | WhatsApp order |
| **Church/NGO** | About, Events, Donations, Contact | MoMo donation |
| **Tutor/School** | Courses, About, Testimonials, Enroll | WhatsApp inquiry |
| **Mechanic/Auto** | Services, Gallery, Location, Emergency | Call Now button |
| **Retail/Shop** | Products, About, Location, Delivery | WhatsApp order |
| **Farm/Agri** | Products, About, Seasons, Bulk Orders | WhatsApp inquiry |
| **Freelancer** | Portfolio, Services, Testimonials, Hire | WhatsApp contact |

### 3. AI Layout Generator

AI generates page structure as builder-compatible JSON:

```json
[
  { 
    "type": "hero", 
    "content": {
      "title": "Welcome to [Business Name]",
      "subtitle": "Your trusted partner for...",
      "buttonText": "Get Started",
      "buttonLink": "#contact"
    }
  },
  { 
    "type": "services", 
    "content": {
      "title": "Our Services",
      "items": [
        { "title": "Service 1", "description": "..." },
        { "title": "Service 2", "description": "..." }
      ]
    }
  },
  { 
    "type": "about", 
    "content": {
      "title": "About Us",
      "description": "..."
    }
  },
  { 
    "type": "contact", 
    "content": {
      "title": "Contact Us",
      "showForm": true
    }
  }
]
```

**Critical Rule:** AI output must always produce valid builder blocks. Never raw HTML.

### 3. AI Content Writer

Generates:
- Headlines and taglines
- About text
- Service descriptions
- Call-to-action text

**Language Style:**
- Simple English
- Business-friendly
- Local tone (no US slang)
- Appropriate for Zambian market

**User Actions:**
- Accept generated content
- Rewrite with AI
- Edit inline manually

### 4. AI Theme Stylist

Suggests:
- Color palette (primary, secondary, accent)
- Font pairing
- Button style

Not full design control — just good defaults based on business type.

---

## Phase 2: AI Editor Assistant

### In-Editor AI Features

**A. "Improve This Section"**
- Rewrite text
- Make it shorter
- Make it more professional
- Add call-to-action

**B. "Generate More Sections"**
- Testimonials
- Pricing
- FAQ
- Gallery
- Team

**C. "Fix Design" (Smart Suggestions)**
- Increase spacing
- Improve contrast
- Balance layout

AI suggests, never forces.

---

## Phase 3: Advanced AI Features

### AI Image Support
- Suggest stock images by business type
- Auto-crop and optimize images
- Generate icon sets (SVG icons)

*Avoid full AI image generation initially — keep quality high.*

### AI SEO Assistant
- Page titles
- Meta descriptions
- Headings structure
- Keyword suggestions (basic)

*SEO should be simple and guided, not technical.*

### AI Learning (Future)
Learn from builder data:
- Which layouts convert better
- Popular templates by business type
- Best colors per industry
- Most used section combinations

---

## Technical Architecture

### Backend (Laravel)
- AI service layer (`app/Services/AI/`)
- Prompt templates stored in DB
- JSON schema validation for layouts
- Rate limiting per user/plan

### Frontend (Vue)
- AI modal/drawer component
- Preview before apply
- Undo/revert always available
- Loading states and progress

### API Structure

```
POST /api/ai/generate-website
POST /api/ai/generate-section
POST /api/ai/improve-content
POST /api/ai/suggest-theme
POST /api/ai/competitor-analysis
```

### JSON Schema for AI Layouts

```typescript
interface AIGeneratedLayout {
  sections: AISection[];
  theme: AITheme;
  meta: AIMeta;
  language: 'en' | 'bem' | 'nya' | 'ton';
}

interface AISection {
  type: 'hero' | 'about' | 'services' | 'features' | 'gallery' | 
        'testimonials' | 'pricing' | 'contact' | 'cta' | 'text' |
        'whatsapp-cta' | 'call-now' | 'momo-payment' | 'booking';
  content: Record<string, any>;
  style?: Record<string, any>;
}

interface AITheme {
  primaryColor: string;
  secondaryColor: string;
  accentColor: string;
  fontFamily: string;
  buttonStyle: 'rounded' | 'square' | 'pill';
}

interface AIMeta {
  title: string;
  description: string;
  keywords: string[];
  language: string;
}
```

---

## Reliability & Offline Support

### Fallback Content System

If AI generation fails, use pre-written templates per business type:

```typescript
const fallbackTemplates = {
  salon: { sections: [...], theme: {...} },
  restaurant: { sections: [...], theme: {...} },
  church: { sections: [...], theme: {...} },
  // ... other industries
};
```

**Fallback triggers:**
- AI API timeout (> 30 seconds)
- AI API error response
- Network failure
- Rate limit exceeded

### Content Caching

Cache successful AI generations for instant results:

```typescript
interface CachedGeneration {
  businessType: string;
  language: string;
  hash: string; // hash of input params
  result: AIGeneratedLayout;
  usageCount: number;
  createdAt: Date;
}
```

**Benefits:**
- Similar businesses get instant results
- Reduces AI API costs
- Works when AI is slow/unavailable

### Offline Draft Mode

For unreliable internet connections:

1. **Queue Generation Requests** - Store request locally when offline
2. **Process on Reconnect** - Auto-submit when connection returns
3. **Local Preview** - Show fallback template while waiting
4. **Sync Status** - Clear indicator of pending AI requests

```typescript
interface OfflineQueue {
  id: string;
  request: AIGenerationRequest;
  status: 'pending' | 'processing' | 'completed' | 'failed';
  createdAt: Date;
  retryCount: number;
}
```

### Progressive Generation

Show sections as they're generated (streaming):

```
User submits request
    ↓
Hero section appears (2 sec)
    ↓
Services section appears (4 sec)
    ↓
About section appears (6 sec)
    ↓
Contact section appears (8 sec)
    ↓
Full website ready
```

**Implementation:** Server-Sent Events (SSE) or WebSocket for real-time updates.

---

## Competitor Analysis Feature

### Optional Input

User can provide competitor website URL during onboarding.

### AI Analysis

AI analyzes competitor and suggests:
- **Differentiators** - What makes your business unique
- **Missing sections** - Features competitor has that you should add
- **Better headlines** - More compelling than competitor
- **Pricing positioning** - How to position against competitor

### Output Example

```json
{
  "competitorUrl": "https://competitor.com",
  "analysis": {
    "strengths": ["Good gallery", "Clear pricing"],
    "weaknesses": ["No WhatsApp", "Slow loading"],
    "opportunities": [
      "Add WhatsApp ordering (competitor doesn't have)",
      "Highlight faster delivery",
      "Show customer reviews"
    ]
  },
  "suggestedDifferentiators": [
    "24/7 WhatsApp support",
    "Same-day delivery in Lusaka",
    "MoMo payment accepted"
  ]
}
```

---

## User Flow

```
User clicks "Create with AI"
    ↓
AI asks 5-7 questions
    ↓
AI generates full website (JSON)
    ↓
Website opens in visual editor
    ↓
User edits visually
    ↓
AI becomes optional helper
```

---

## Competitive Advantages

### High-Impact Additions

**A. Smart Guided Builder**
Checklist mode inside builder:
- ✔ Business name added
- ✔ Contact details added
- ✔ Payment enabled
- ✔ WhatsApp connected
- ✔ Page published

**B. Conversion-First Blocks**
Native business blocks (not just design):
- "Call Now" block (mobile-first)
- WhatsApp Order block
- Booking CTA block
- Pricing comparison block
- Trust badges block
- Testimonial carousel

**C. Trust & Credibility System**
- Verified business badge (optional)
- Customer review widget
- Google Maps embed
- Business registration info section

**D. Post-Publish Growth Assistant**
After publishing, AI suggests:
- Add WhatsApp button
- Create a promo page
- Share on Facebook
- Add payment option
- Improve headline

**E. Reusable Sections & Presets**
- Save sections
- Reuse across pages
- Insert "popular layouts"

---

## What NOT to Add (Yet)

Avoid complexity:
- ❌ Advanced animations
- ❌ Custom CSS editors
- ❌ Plugin marketplaces
- ❌ Full app builder
- ❌ Overly advanced SEO tools

---

## Pricing & Limits

### AI Usage by Plan

| Plan | AI Website Gen | AI Rewrites/month | AI Sections |
|------|----------------|-------------------|-------------|
| Starter | 1 | 10 | 5 |
| Business | 3 | 50 | 20 |
| Pro | Unlimited | Unlimited | Unlimited |

---

## Prompt Templates

### Website Generation Prompt

```
You are a website content generator for small businesses in Zambia.

Business Details:
- Name: {business_name}
- Type: {business_type}
- Services: {services}
- Target: {target_customers}
- Contact: {contact_info}
- Language: {language}
- Competitor: {competitor_url} (optional)

Generate a professional website structure with:
1. Hero section with compelling headline and WhatsApp CTA
2. Services/products section with prices in Kwacha
3. About section
4. WhatsApp order/contact section
5. Contact section with MoMo payment option

Rules:
- Use simple, clear {language}
- Be professional but friendly
- Primary CTA should be WhatsApp click-to-chat
- Include MTN MoMo/Airtel Money payment mentions
- Output valid JSON matching the schema
- Optimize for mobile viewing
```

### Multilingual Content Prompt

```
Generate website content in {language}:

For Bemba:
- Use common Bemba business phrases
- Keep technical terms in English
- Friendly, community-focused tone

For Nyanja:
- Use Lusaka/Eastern dialect
- Mix with English where natural
- Professional but approachable

For Tonga:
- Use Southern Province expressions
- Agricultural/farming friendly terms
- Respectful, traditional tone
```

### Competitor Analysis Prompt

```
Analyze this competitor website: {competitor_url}

Business type: {business_type}
My business: {business_name}

Identify:
1. What they do well (to match or exceed)
2. What they're missing (opportunities for us)
3. How we can differentiate
4. Better headline suggestions
5. Missing trust elements

Focus on Zambian market context:
- Do they have WhatsApp ordering?
- Do they accept MoMo/Airtel Money?
- Is the site mobile-friendly?
- Do they have local testimonials?
```

### Seasonal Content Prompt

```
Current date: {current_date}
Business type: {business_type}
Location: Zambia

Check for upcoming events/seasons:
- Independence Day (Oct 24): Suggest patriotic themes
- Christmas (Dec): Holiday promotions
- Farming season (Nov-Apr): Agricultural content
- School term start: Education promotions
- Dry season (May-Oct): Weather-appropriate services

Generate timely promotional content if applicable.
```

### Content Improvement Prompt

```
Improve this website section text:
"{current_text}"

Business type: {business_type}
Goal: {improvement_goal}
Language: {language}

Make it:
- More professional
- Clearer and shorter
- Include a WhatsApp call-to-action
- Appropriate for Zambian audience
- Mobile-friendly (short paragraphs)
```

---

## Implementation Phases

### Phase A - AI MVP (Week 1-2)
- [ ] AI website generation endpoint
- [ ] AI content writing
- [ ] AI color + font suggestion
- [ ] Basic onboarding flow

### Phase B - AI Editor Assistant (Week 3-4)
- [ ] Section rewrite feature
- [ ] Layout suggestions
- [ ] Add missing sections
- [ ] Inline AI improvements

### Phase C - AI Growth Tools (Week 5-6)
- [ ] SEO assistant
- [ ] Conversion improvement tips
- [ ] Performance suggestions
- [ ] Post-publish guidance

---

## Success Metrics

- **Time to first website:** < 5 minutes with AI
- **AI acceptance rate:** > 70% of generated content accepted
- **User satisfaction:** 4.5/5 for AI features
- **Conversion:** 30% higher signup-to-publish rate
- **Fallback usage:** < 10% of generations use fallback
- **Offline queue success:** > 95% of queued requests complete
- **Cache hit rate:** > 40% for similar businesses

---

## Changelog

### December 21, 2025
- Initial AI design specification created
- Defined 3-phase rollout plan
- Documented JSON schema for AI layouts
- Added prompt templates
- Defined competitive advantages
- Added Zambia-first design principles
- Added multilingual support (English, Bemba, Nyanja, Tonga)
- Added industry-specific templates for 8 business types
- Added WhatsApp-first CTA strategy
- Added seasonal awareness for Zambian calendar
- Added competitor analysis feature
- Added fallback content system for reliability
- Added content caching for performance
- Added offline draft mode for unreliable internet
- Added progressive generation (streaming)
- Added mobile-first optimization requirements
