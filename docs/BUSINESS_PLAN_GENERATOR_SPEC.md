# MyGrowNet Business Plan Generator - Implementation Specification

**Last Updated:** November 21, 2025
**Status:** In Development
**Version:** 2.0 (Complete Rebuild)

## Overview

A comprehensive, AI-powered business plan generator that guides users through creating professional business plans with automatic financial calculations, industry templates, and multiple export formats.

## Key Features

### 1. 10-Step Guided Process
1. Business Information
2. Problem & Solution
3. Products/Services
4. Market Research
5. Marketing & Sales Strategy
6. Operations Plan
7. Financial Plan (Auto-calculated)
8. Risk Analysis
9. Implementation Roadmap
10. Final Output & Export

### 2. AI-Assisted Writing
- "Generate with AI" button on all text fields
- Industry-specific content generation
- Text improvement suggestions
- Auto-fill based on templates

### 3. Industry Templates
Pre-built templates for:
- Agriculture
- Retail
- Transport
- ICT
- Manufacturing
- Education
- Hospitality
- Real Estate
- Construction
- Freelancing/Services

### 4. Financial Calculators
- Sales Forecast Calculator
- Profit Margin Calculator
- Break-even Calculator
- Salary & Staff Cost Calculator
- Inventory/Supply Cost Calculator

### 5. Export Options
- **Free**: Editable MyGrowNet template
- **Premium**: PDF export
- **Premium**: Word export
- **Premium Add-on**: Pitch Deck

### 6. Cloud Storage
- Save and resume plans
- Version history
- Plan library
- Template purchases

## Monetization

### Free Features
- Basic templates
- Basic text generation
- Editable MyGrowNet plan
- Save drafts

### Premium Features
- PDF export (ZMW 49)
- Word export (ZMW 49)
- Pitch deck export (ZMW 79)
- Advanced AI writing
- Industry data packs (ZMW 60-150)
- Financial deep calculators
- Premium templates
- Branding features

### Subscription
- ZMW 79/month - Business Tools Subscription
  - Unlimited exports
  - All premium features
  - Priority AI generation
  - Advanced calculators

## Technical Implementation

### Database Schema
```sql
-- Business plans table
business_plans
- id
- user_id
- title
- industry
- status (draft/completed/published)
- template_id
- data (JSON)
- created_at
- updated_at

-- Plan versions (for history)
business_plan_versions
- id
- business_plan_id
- version_number
- data (JSON)
- created_at

-- Templates
business_plan_templates
- id
- name
- industry
- description
- price
- is_premium
- template_data (JSON)
- created_at

-- Exports
business_plan_exports
- id
- business_plan_id
- user_id
- export_type (pdf/word/pitch_deck)
- file_path
- created_at
```

### API Endpoints
```
GET  /tools/business-plan - List user's plans
GET  /tools/business-plan/create - Create new plan
GET  /tools/business-plan/{id} - View/edit plan
POST /tools/business-plan - Save plan
PUT  /tools/business-plan/{id} - Update plan
POST /tools/business-plan/{id}/export - Export plan
POST /tools/business-plan/ai-generate - AI content generation
GET  /tools/business-plan/templates - List templates
POST /tools/business-plan/templates/{id}/purchase - Purchase template
```

## Phase 1 Implementation (Current)
- Complete 10-step form
- Industry templates
- Financial calculators
- Save/resume functionality
- Basic export (MyGrowNet template)

## Phase 2 (Future)
- AI integration
- PDF/Word export
- Pitch deck generator
- Premium templates marketplace
- Team collaboration

## Phase 3 (Long-term)
- Compliance checker
- Accounting tool integration
- Industry research marketplace
- Mobile app

## Changelog

### November 21, 2025
- Initial specification document created
- Full feature set defined
- Monetization model established
