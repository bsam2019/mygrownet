# Ubumi - Family Lineage & Health Check-In Platform

**Status:** In Development  
**Target Market:** Zambia (Pilot)  
**Last Updated:** January 23, 2026

---

## What is Ubumi?

Ubumi (meaning "life") is a mobile-first digital platform that enables Zambian families to:

- 📱 **Document family lineage** - Build and maintain digital family trees
- 💚 **Stay connected** - Simple wellness check-ins for family members
- 🔔 **Receive alerts** - Get notified when someone needs support
- 🤝 **Coordinate care** - Help families respond quickly to health needs

**Important:** Ubumi is NOT a medical system. It's a family communication and wellness awareness tool.

---

## Current Implementation Status

### ✅ Completed Features

1. **Mobile-First Design**
   - Purple/indigo gradient color scheme
   - Bottom navigation (Home, People, Add, More)
   - Touch-friendly interface
   - Profile modal with slide-up animation

2. **Family Management**
   - Create and manage families
   - Human-readable URLs (slugs instead of UUIDs)
   - Family dashboard with statistics
   - Family tree visualization

3. **Person Management**
   - Add family members with flexible data entry
   - Hierarchical tree display (parents on top, children below)
   - Person profiles with photos
   - Gender badges and deceased indicators

4. **Relationships**
   - Define relationships between family members
   - Relationship types (parent, child, spouse, sibling)
   - Visual relationship mapping

5. **Duplicate Detection**
   - 3-layer approach (Prevention, Detection, Resolution)
   - Smart duplicate detection before creation
   - Family-centered merge workflow

6. **URL Structure**
   - Slug-based URLs for better UX
   - Example: `/ubumi/families/smith-family` instead of `/ubumi/families/uuid`
   - Automatic slug generation with uniqueness handling

### 🚧 In Progress

- Vue component updates for slug-based routing
- PersonController updates for slug support
- Testing and validation

### ⏳ Planned

- Wellness check-in system
- Notification system
- SMS integration
- Offline functionality

---

## Documentation

### Core Documents

1. **[UBUMI_CONCEPT.md](./UBUMI_CONCEPT.md)**
   - Complete concept overview
   - Problem statement and target users
   - Core objectives and principles
   - Vision and success metrics

2. **[IMPLEMENTATION_PROCESS.md](./IMPLEMENTATION_PROCESS.md)**
   - 4-phase implementation roadmap
   - Feature specifications
   - Technical stack recommendations
   - Resource requirements and timeline
   - Monetization strategy

3. **[DUPLICATE_HANDLING_TECHNICAL_SPEC.md](./DUPLICATE_HANDLING_TECHNICAL_SPEC.md)**
   - Technical solution for duplicate records
   - 3-layer architecture (Prevention, Detection, Resolution)
   - Database schema and API endpoints
   - User experience guidelines

4. **[IMPLEMENTATION_STATUS.md](./IMPLEMENTATION_STATUS.md)**
   - Current development status
   - Completed features
   - Known issues and fixes

5. **[SEPARATION_STRATEGY.md](./SEPARATION_STRATEGY.md)**
   - Technical separation from MyGrowNet
   - Domain-driven design approach
   - Migration strategy

---

## Technical Implementation

### Current Stack

**Backend:**
- Laravel 12 (PHP 8.2+)
- Domain-Driven Design architecture
- MySQL database
- Slug-based routing for human-readable URLs

**Frontend:**
- Vue 3 with TypeScript
- Inertia.js for SPA experience
- Tailwind CSS with purple/indigo theme
- Mobile-first responsive design

**Key Features:**
- Domain entities with rich business logic
- Value objects for type safety
- Repository pattern for data access
- Service layer for complex operations
- Slug generation for SEO-friendly URLs

### URL Structure

**Families:**
- List: `/ubumi/families`
- View: `/ubumi/families/smith-family`
- Edit: `/ubumi/families/smith-family/edit`

**Persons:**
- List: `/ubumi/persons`
- View: `/ubumi/families/smith-family/persons/john-doe`
- Edit: `/ubumi/families/smith-family/persons/john-doe/edit`

### Database Schema

**Families:**
- `id` (UUID)
- `name` (string)
- `slug` (string, unique)
- `admin_user_id` (foreign key)

**Persons:**
- `id` (UUID)
- `family_id` (foreign key)
- `name` (string)
- `slug` (string, unique within family)
- `photo_url` (nullable)
- `date_of_birth` (nullable)
- `approximate_age` (nullable)
- `gender` (nullable)
- `is_deceased` (boolean)

**Relationships:**
- `id` (UUID)
- `person_id` (foreign key)
- `related_person_id` (foreign key)
- `relationship_type` (enum)

---

## Changelog

### January 23, 2026
- Implemented slug-based URLs for families and persons
- Created Slug value object for URL-friendly identifiers
- Added SlugGeneratorService for unique slug generation
- Updated route model binding to use slugs
- Updated domain entities to include slug support
- Updated repositories with slug lookup methods
- Created migration to add slug columns

### January 21, 2026
- Implemented mobile-first design with bottom navigation
- Added purple/indigo gradient color scheme
- Created hierarchical tree visualization
- Added profile modal with slide-up animation
- Implemented dashboard with statistics

---

## Quick Overview

### The Problem

In Zambia, families are geographically dispersed, making it difficult to:
- Track family lineage digitally
- Stay informed about family members' wellbeing
- Respond quickly when someone needs help

### The Solution

A simple, trusted platform where families can:
- Build their family tree together
- Check in regularly ("I am well" / "I need help")
- Receive alerts when action is needed
- Coordinate care for vulnerable members

### Key Principles

- **Family-controlled** - Families own their data
- **Privacy-first** - No data sharing without consent
- **Culturally sensitive** - Respects African family dynamics
- **Offline-first** - Works in low-connectivity areas (planned)
- **Simple & accessible** - Easy for all ages and literacy levels

---

## Implementation Phases

### Phase 1: Family Tree & Identity (Current)
- ✅ Family account creation
- ✅ Family tree builder
- ✅ Member profiles and relationships
- ✅ Mobile-first interface
- ✅ Human-readable URLs
- ⏳ Offline functionality

### Phase 2: Wellness Check-In System (Planned)
- Simple check-in interface
- Notification system
- SMS integration
- Legal disclaimers

### Phase 3: Alerts & Care Coordination (Planned)
- Missed check-in alerts
- Caregiver designation
- Emergency contact escalation
- Care coordination tools

### Phase 4: Institutional Integration (Planned)
- Community health worker integration
- NGO partnerships
- Aggregated analytics
- Research capabilities (anonymized)

---

## Duplicate Handling

One of the most critical features is handling duplicate family member records.

### The Challenge
Family members may add the same person multiple times due to:
- Different name spellings
- Unknown dates of birth
- Cultural naming variations

### The Solution: 3-Layer Approach

1. **Prevention** - Soft warnings before creation (non-blocking)
2. **Detection** - Background scanning for potential duplicates
3. **Resolution** - Family-centered merge workflow

**Key Principle:** Never block creation. Detect intelligently. Resolve collaboratively.

See [DUPLICATE_HANDLING_TECHNICAL_SPEC.md](./DUPLICATE_HANDLING_TECHNICAL_SPEC.md) for full details.

---

## Pilot Plan

### Location
- **Urban**: Lusaka
- **Rural**: One district (for contrast)

### Scale
- 100-300 families
- 3-6 months duration

### Success Metrics
- 60%+ weekly check-in rate
- <2 hour average alert response time
- 70%+ monthly retention
- 80%+ would recommend to others

---

## Resource Requirements

### Team
- Product Manager (1 FT)
- Backend Developer (1-2 FT)
- Mobile Developer (1-2 FT)
- UI/UX Designer (1 PT)
- QA Tester (1 PT)
- Community Liaison (1 PT)

### Budget (Year 1)
- Development: $30,000 - $50,000
- Infrastructure: $500 - $1,000/month
- Legal/Compliance: $5,000 - $10,000
- Pilot Operations: $10,000 - $15,000
- **Total: $60,000 - $90,000**

### Timeline
- MVP Development: 3 months
- Testing & Refinement: 1 month
- Pilot Preparation: 1 month
- Pilot Execution: 6 months
- **Total: 11-12 months**

---

## Monetization Strategy

### Short-Term (Years 1-2)
- Free core features
- Grant funding
- Pilot partnerships

### Medium-Term (Years 3-4)
- Premium family plans ($2-5/month)
- Institutional licensing ($50-200/month)

### Long-Term (Years 5+)
- White-label solutions
- API access for health systems
- Data insights (aggregated, anonymized)

**Important:** No ads. No sale of personal data. Ever.

---

## Next Steps

### Immediate
1. ✅ Complete slug implementation
2. ⏳ Update Vue components for slug-based routing
3. ⏳ Test slug generation and uniqueness
4. ⏳ Update all navigation links to use slugs

### Short-Term
1. Complete Phase 1 features
2. User testing with pilot families
3. Performance optimization
4. Offline functionality

### Medium-Term
1. Wellness check-in system
2. Notification infrastructure
3. SMS integration
4. Pilot launch

---

## Key Documents Summary

| Document | Purpose | Audience |
|----------|---------|----------|
| UBUMI_CONCEPT.md | High-level concept and vision | Stakeholders, investors |
| IMPLEMENTATION_PROCESS.md | Detailed implementation plan | Product team, developers |
| DUPLICATE_HANDLING_TECHNICAL_SPEC.md | Technical solution for duplicates | Developers, architects |
| IMPLEMENTATION_STATUS.md | Current development status | Development team |
| SEPARATION_STRATEGY.md | Technical architecture | Developers, architects |
| README.md (this file) | Quick reference and navigation | Everyone |

---

## Contact & Collaboration

This project is part of the **MyGrowNet** ecosystem, leveraging existing technical infrastructure and expertise.

For questions or collaboration:
- Review the concept documents
- Provide feedback on implementation approach
- Suggest pilot partners or stakeholders
- Contribute to technical design

---

## Vision Statement

> "Not surveillance. Not diagnosis. But care, connection, and continuity."

Ubumi aims to strengthen African families through technology that respects culture, protects privacy, and enables care across distances.

---

## Document Status

- ✅ Concept validated
- ✅ Implementation process defined
- ✅ Technical specifications drafted
- ✅ Phase 1 development in progress
- ✅ Mobile-first design implemented
- ✅ Slug-based URLs implemented
- ⏳ Stakeholder validation pending
- ⏳ Pilot planning pending

---

**Last Updated:** January 23, 2026  
**Version:** 1.1  
**Status:** Active Development - Phase 1
