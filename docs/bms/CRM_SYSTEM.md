# CRM System

**Last Updated:** February 12, 2026  
**Status:** Production Ready

## Overview

Complete Customer Relationship Management system with lead management, opportunity tracking, sales pipeline, communication history, follow-up reminders, customer segmentation, marketing campaigns, and customer lifetime value tracking.

## Features

### Lead Management
- Create and track leads from multiple sources
- Lead status workflow (new → contacted → qualified → won/lost)
- Lead assignment and prioritization
- Convert leads to customers
- Lead source tracking
- Estimated value and probability

### Opportunity Tracking
- Create opportunities from leads or customers
- Track opportunity stages (prospecting → closed won/lost)
- Probability-based weighted pipeline value
- Expected and actual close dates
- Opportunity assignment
- Loss reason tracking

### Sales Pipeline
- Visual pipeline by stage
- Total and weighted values per stage
- Opportunity count per stage
- Pipeline velocity tracking
- Win/loss analysis

### Communication History
- Log all customer interactions (calls, emails, meetings, notes)
- Polymorphic - works with customers, leads, opportunities
- Track communication direction (inbound/outbound)
- Duration tracking for calls/meetings
- Attachment support
- Automatic last contacted date updates

### Follow-up Reminders
- Create follow-up tasks for any entity
- Due date and priority tracking
- Assignment to team members
- Overdue reminder detection
- Completion tracking with notes
- Pending/completed/cancelled status

### Customer Segmentation
- Create custom segments with criteria
- Dynamic customer count calculation
- Segment-based targeting
- Active/inactive segments
- Criteria-based filtering

### Marketing Campaigns
- Multi-channel campaigns (email, SMS, social, events)
- Segment or custom targeting
- Campaign scheduling
- Budget and goal tracking
- Performance metrics (sent, opened, clicked, converted)
- ROI tracking

### Customer Lifetime Value
- Automatic CLV calculation
- Average order value
- Purchase frequency
- First and last purchase dates
- Days since last purchase
- Total revenue and profit
- Profit margin calculation
- Customer tier assignment (bronze/silver/gold/platinum)
- Churn risk assessment (low/medium/high)

## Database Schema

### cms_leads
- Lead information and contact details
- Status workflow and source tracking
- Assignment and segmentation
- Conversion tracking

### cms_opportunities
- Opportunity details and amounts
- Pipeline stage management
- Customer/lead relationships
- Close date tracking

### cms_communications
- Polymorphic communication log
- Type and direction tracking
- Content and attachments
- Duration tracking

### cms_follow_ups
- Polymorphic follow-up tasks
- Due date and priority
- Assignment and completion
- Status tracking

### cms_customer_segments
- Segment definitions
- Criteria storage (JSON)
- Customer count tracking

### cms_campaigns
- Campaign details and targeting
- Schedule and budget
- Performance metrics
- ROI tracking

### cms_customer_metrics
- Lifetime value calculations
- Purchase behavior metrics
- Profitability tracking
- Tier and churn risk

## Implementation Files

### Backend
- `database/migrations/2026_02_12_170000_create_cms_crm_tables.php`
- `app/Infrastructure/Persistence/Eloquent/CMS/LeadModel.php`
- `app/Infrastructure/Persistence/Eloquent/CMS/OpportunityModel.php`
- `app/Infrastructure/Persistence/Eloquent/CMS/CommunicationModel.php`
- `app/Infrastructure/Persistence/Eloquent/CMS/FollowUpModel.php`
- `app/Infrastructure/Persistence/Eloquent/CMS/CampaignModel.php`
- `app/Infrastructure/Persistence/Eloquent/CMS/CustomerSegmentModel.php`
- `app/Infrastructure/Persistence/Eloquent/CMS/CustomerMetricModel.php`
- `app/Domain/CMS/Core/Services/CrmService.php`
- `app/Http/Controllers/CMS/CrmController.php`

### Routes
- `routes/cms.php` (CRM routes)

## Usage

### Creating a Lead
```php
POST /cms/crm/leads
{
  "name": "John Doe",
  "email": "john@example.com",
  "phone": "+260971234567",
  "company_name": "ABC Ltd",
  "source": "website",
  "estimated_value": 50000,
  "assigned_to": 1
}
```

### Converting Lead to Customer
```php
POST /cms/crm/leads/{lead}/convert
```

### Creating an Opportunity
```php
POST /cms/crm/opportunities
{
  "customer_id": 1,
  "name": "New Construction Project",
  "amount": 100000,
  "probability": 75,
  "expected_close_date": "2026-03-15",
  "assigned_to": 1
}
```

### Logging Communication
```php
POST /cms/crm/communications
{
  "communicable_type": "App\\Infrastructure\\Persistence\\Eloquent\\CMS\\LeadModel",
  "communicable_id": 1,
  "type": "call",
  "direction": "outbound",
  "subject": "Follow-up call",
  "content": "Discussed project requirements",
  "communicated_at": "2026-02-12 14:30:00",
  "duration_minutes": 15
}
```

### Creating Follow-up
```php
POST /cms/crm/follow-ups
{
  "followable_type": "App\\Infrastructure\\Persistence\\Eloquent\\CMS\\OpportunityModel",
  "followable_id": 1,
  "title": "Send proposal",
  "due_date": "2026-02-15",
  "priority": "high",
  "assigned_to": 1
}
```

### Calculating Customer Metrics
```php
POST /cms/crm/customers/{customer}/calculate-metrics
```

## Business Rules

1. **Lead Conversion**: Leads can only be converted once
2. **Opportunity Weighting**: Weighted amount = amount × probability / 100
3. **Customer Tiers**: Based on lifetime value (Bronze < K20k, Silver < K50k, Gold < K100k, Platinum ≥ K100k)
4. **Churn Risk**: Based on days since last purchase (Low < 90, Medium < 180, High ≥ 180)
5. **Follow-up Status**: Automatically marked overdue if past due date
6. **Communication Tracking**: Updates last contacted date on related entities
7. **Segment Count**: Automatically updated when criteria changes

## Security

- Company-scoped queries (all queries filtered by company_id)
- Authorization policies for sensitive actions
- Audit trail for all CRM activities
- Validation of relationships and assignments

## Future Enhancements

- Email integration for automatic communication logging
- Calendar integration for meetings
- Lead scoring algorithm
- Predictive analytics for churn
- AI-powered lead qualification
- Automated campaign workflows
- A/B testing for campaigns
- Advanced reporting and dashboards
- Mobile app for field sales
- Integration with third-party CRM tools

## Changelog

### February 12, 2026
- Initial implementation
- Lead management
- Opportunity tracking
- Sales pipeline
- Communication history
- Follow-up reminders
- Customer segmentation
- Marketing campaigns
- Customer lifetime value tracking
