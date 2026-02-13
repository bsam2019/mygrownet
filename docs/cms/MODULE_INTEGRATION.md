# CMS Module Integration Analysis

**Last Updated:** February 7, 2026  
**Status:** Integration Planning  
**Purpose:** Identify integration opportunities between CMS and existing modules

---

## Executive Summary

The new CMS (Company Management System) has **significant integration opportunities** with three existing MyGrowNet modules:

1. **GrowFinance** - Accounting & Financial Management
2. **GrowBiz** - Business Management & Operations  
3. **BizBoost** - Marketing & Growth

This document outlines specific integration points, shared entities, event-driven communication patterns, and implementation strategies.

---

## 1. Integration with GrowFinance

### 1.1 Overview

GrowFinance is a **fully implemented** accounting module (Phase 1-6 complete) with:
- Chart of accounts (double-entry bookkeeping)
- Invoice management
- Expense tracking
- Customer & vendor management
- Banking & reconciliation
- Financial reports (P&L, Balance Sheet, Cash Flow)

### 1.2 Shared Entities

| CMS Entity | GrowFinance Entity | Relationship |
|------------|-------------------|--------------|
| **Customer** | **GrowFinanceCustomerModel** | Same entity, different contexts |
| **Job** | **GrowFinanceInvoiceModel** | Job → Invoice (1:1 or 1:many) |
| **Payment** | **GrowFinancePaymentModel** | Same payment records |
| **Expense** | **GrowFinanceExpenseModel** | CMS expenses → GrowFinance expenses |

### 1.3 Integration Points

#### A. Job-to-Invoice Flow

**CMS Workflow:**
```
Job Created → Job Assigned → Job Completed → Invoice Generated
```

**Integration:**
```php
// CMS Domain Event
class JobCompleted
{
    public function __construct(
        public readonly JobId $jobId,
        public readonly CustomerId $customerId,
        public readonly Money $totalValue,
        public readonly array $lineItems
    ) {}
}

// GrowFinance Event Listener
class GenerateInvoiceFromJob
{
    public function handle(JobCompleted $event): void
    {
        // Create invoice in GrowFinance
        $invoice = $this->invoiceService->createFromJob(
            customerId: $event->customerId,
            jobReference: $event->jobId,
            lineItems: $event->lineItems,
            totalAmount: $event->totalValue
        );
        
        // Link invoice back to job
        $this->jobService->linkInvoice($event->jobId, $invoice->id);
    }
}
```

**Benefits:**
- ✅ Automatic invoice generation from completed jobs
- ✅ No manual data entry duplication
- ✅ Consistent customer records
- ✅ Accurate job costing

#### B. Expense Tracking Integration

**CMS Workflow:**
```
Job Created → Materials Assigned → Inventory Deducted → Expense Recorded
```

**Integration:**
```php
// CMS Domain Event
class MaterialsUsedOnJob
{
    public function __construct(
        public readonly JobId $jobId,
        public readonly array $materials, // [material_id, quantity, cost]
        public readonly Money $totalCost
    ) {}
}

// GrowFinance Event Listener
class RecordJobExpense
{
    public function handle(MaterialsUsedOnJob $event): void
    {
        // Create expense in GrowFinance
        $this->expenseService->create([
            'category' => 'Cost of Goods Sold',
            'description' => "Materials for Job #{$event->jobId}",
            'amount' => $event->totalCost,
            'reference' => "JOB-{$event->jobId}",
            'expense_date' => now()
        ]);
    }
}
```

**Benefits:**
- ✅ Automatic expense tracking for job costs
- ✅ Accurate profit margin calculations
- ✅ Real-time cost tracking

#### C. Customer Synchronization

**Strategy:** Use GrowFinance as the **single source of truth** for customer data.

```php
// CMS uses GrowFinance customer repository
class CmsCustomerService
{
    public function __construct(
        private GrowFinanceCustomerRepository $customerRepo
    ) {}
    
    public function getCustomer(CustomerId $id): Customer
    {
        // Fetch from GrowFinance
        return $this->customerRepo->find($id);
    }
}
```

**Benefits:**
- ✅ No duplicate customer records
- ✅ Consistent contact information
- ✅ Unified customer history

#### D. Payment Reconciliation

**CMS Workflow:**
```
Invoice Generated → Payment Received → Job Marked Paid → Commission Calculated
```

**Integration:**
```php
// GrowFinance Domain Event
class InvoicePaymentReceived
{
    public function __construct(
        public readonly InvoiceId $invoiceId,
        public readonly Money $amount,
        public readonly PaymentMethod $method
    ) {}
}

// CMS Event Listener
class UpdateJobPaymentStatus
{
    public function handle(InvoicePaymentReceived $event): void
    {
        // Find job linked to invoice
        $job = $this->jobService->findByInvoice($event->invoiceId);
        
        // Update job payment status
        $this->jobService->markAsPaid($job->id, $event->amount);
        
        // Trigger commission calculation
        event(new JobPaid($job->id, $event->amount));
    }
}
```

**Benefits:**
- ✅ Automatic job payment tracking
- ✅ Triggers commission calculations
- ✅ Real-time financial visibility

### 1.4 Financial Reporting Integration

**CMS Reports Enhanced by GrowFinance:**

| CMS Report | GrowFinance Data | Enhancement |
|------------|------------------|-------------|
| Job Profitability | Invoice revenue + Expense costs | Accurate profit margins |
| Worker Performance | Commission payments | Financial performance metrics |
| Project Costing | Material expenses + Labor costs | True project costs |
| Cash Flow | Payments received + Expenses paid | Real-time cash position |

### 1.5 Implementation Strategy

**Phase 1: Shared Customer Entity**
- Use `GrowFinanceCustomerModel` as the customer entity for CMS
- Add `company_id` (tenant) field to GrowFinance customers table
- Update CMS to reference GrowFinance customer IDs

**Phase 2: Job-to-Invoice Automation**
- Create `JobCompleted` domain event in CMS
- Create `GenerateInvoiceFromJob` listener in GrowFinance
- Add `job_id` reference field to `growfinance_invoices` table

**Phase 3: Expense Integration**
- Create `MaterialsUsedOnJob` domain event in CMS
- Create `RecordJobExpense` listener in GrowFinance
- Link expenses to jobs via reference field

**Phase 4: Payment Reconciliation**
- Create `InvoicePaymentReceived` event in GrowFinance
- Create `UpdateJobPaymentStatus` listener in CMS
- Implement commission calculation triggers

---

## 2. Integration with GrowBiz

### 2.1 Overview

GrowBiz is in **architecture planning stage** with focus on:
- Task & todo management
- Project management (Kanban, Gantt)
- Inventory tracking
- Appointments scheduling
- POS (Point of Sale)
- Mobile-first PWA design

### 2.2 Shared Entities

| CMS Entity | GrowBiz Entity | Relationship |
|------------|----------------|--------------|
| **Job** | **Project** | Job is a type of project |
| **Worker** | **Team Member** | Same entity |
| **Asset** | **Inventory Item** | Equipment/tools tracking |
| **Task** | **Task** | Job tasks = GrowBiz tasks |

### 2.3 Integration Points

#### A. Job-as-Project Management

**CMS Workflow:**
```
Job Created → Tasks Generated → Workers Assigned → Progress Tracked
```

**Integration:**
```php
// CMS Domain Event
class JobCreated
{
    public function __construct(
        public readonly JobId $jobId,
        public readonly string $jobName,
        public readonly CustomerId $customerId,
        public readonly array $tasks
    ) {}
}

// GrowBiz Event Listener
class CreateProjectFromJob
{
    public function handle(JobCreated $event): void
    {
        // Create project in GrowBiz
        $project = $this->projectService->create([
            'name' => $event->jobName,
            'type' => 'job',
            'reference_id' => $event->jobId,
            'customer_id' => $event->customerId
        ]);
        
        // Create tasks
        foreach ($event->tasks as $task) {
            $this->taskService->create([
                'project_id' => $project->id,
                'title' => $task['title'],
                'description' => $task['description'],
                'due_date' => $task['due_date']
            ]);
        }
    }
}
```

**Benefits:**
- ✅ Visual project management (Kanban boards)
- ✅ Task assignment and tracking
- ✅ Gantt chart for job timelines
- ✅ Team collaboration features

#### B. Inventory & Asset Management

**CMS Workflow:**
```
Asset Assigned to Job → Usage Tracked → Maintenance Logged
```

**Integration:**
```php
// Shared inventory system
class AssetService
{
    public function assignToJob(AssetId $assetId, JobId $jobId): void
    {
        // Update asset assignment in GrowBiz
        $this->growBizInventory->assign($assetId, $jobId);
        
        // Record in CMS
        $this->cmsAssetRepo->updateAssignment($assetId, $jobId);
    }
}
```

**Benefits:**
- ✅ Real-time asset tracking
- ✅ Maintenance scheduling
- ✅ Usage history
- ✅ Depreciation tracking

#### C. Team Management Integration

**Strategy:** Use GrowBiz as the **single source of truth** for team/worker data.

```php
// CMS uses GrowBiz team repository
class CmsWorkerService
{
    public function __construct(
        private GrowBizTeamRepository $teamRepo
    ) {}
    
    public function getWorker(WorkerId $id): Worker
    {
        return $this->teamRepo->findTeamMember($id);
    }
    
    public function assignToJob(WorkerId $workerId, JobId $jobId): void
    {
        // Assign in GrowBiz (creates task assignment)
        $this->teamRepo->assignToProject($workerId, $jobId);
        
        // Record in CMS
        $this->jobService->assignWorker($jobId, $workerId);
    }
}
```

**Benefits:**
- ✅ Unified team directory
- ✅ Consistent availability tracking
- ✅ Integrated time tracking

#### D. Appointment Scheduling for Jobs

**CMS Workflow:**
```
Job Created → Site Visit Scheduled → Worker Assigned → Customer Notified
```

**Integration:**
```php
// CMS Domain Event
class SiteVisitRequired
{
    public function __construct(
        public readonly JobId $jobId,
        public readonly CustomerId $customerId,
        public readonly DateTime $preferredDate
    ) {}
}

// GrowBiz Event Listener
class ScheduleSiteVisit
{
    public function handle(SiteVisitRequired $event): void
    {
        // Create appointment in GrowBiz
        $this->appointmentService->create([
            'type' => 'site_visit',
            'reference_id' => $event->jobId,
            'customer_id' => $event->customerId,
            'date' => $event->preferredDate,
            'status' => 'pending'
        ]);
    }
}
```

**Benefits:**
- ✅ Centralized scheduling
- ✅ Calendar integration
- ✅ Automated reminders
- ✅ Conflict detection

### 2.4 Implementation Strategy

**Phase 1: Shared Team Entity**
- Use GrowBiz team members as CMS workers
- Add `company_id` (tenant) field to GrowBiz team table
- Update CMS to reference GrowBiz team member IDs

**Phase 2: Job-to-Project Sync**
- Create `JobCreated` domain event in CMS
- Create `CreateProjectFromJob` listener in GrowBiz
- Implement bi-directional sync for job/project updates

**Phase 3: Inventory Integration**
- Share inventory/asset tables between CMS and GrowBiz
- Implement unified asset assignment logic
- Add maintenance tracking

**Phase 4: Appointment Integration**
- Create `SiteVisitRequired` event in CMS
- Create `ScheduleSiteVisit` listener in GrowBiz
- Implement calendar sync

---

## 3. Integration with BizBoost

### 3.1 Overview

BizBoost is in **Phase 5 development** (Advanced Analytics) with:
- AI content generation
- Social media scheduling & posting
- Campaign management
- Customer engagement tools
- Sales tracking
- Analytics & reporting

### 3.2 Shared Entities

| CMS Entity | BizBoost Entity | Relationship |
|------------|----------------|--------------|
| **Customer** | **BizBoostCustomerModel** | Same entity |
| **Job** | **Campaign** | Job completion → Marketing campaign |
| **Document** | **Media** | Marketing materials |

### 3.3 Integration Points

#### A. Customer Marketing Integration

**CMS Workflow:**
```
Job Completed → Customer Satisfied → Marketing Campaign Triggered
```

**Integration:**
```php
// CMS Domain Event
class JobCompleted
{
    public function __construct(
        public readonly JobId $jobId,
        public readonly CustomerId $customerId,
        public readonly string $jobType
    ) {}
}

// BizBoost Event Listener
class TriggerMarketingCampaign
{
    public function handle(JobCompleted $event): void
    {
        // Create testimonial request campaign
        $this->campaignService->create([
            'type' => 'testimonial_request',
            'customer_id' => $event->customerId,
            'reference_id' => $event->jobId,
            'objective' => 'collect_review'
        ]);
        
        // Generate social media post
        $this->postService->createFromTemplate([
            'template' => 'job_completion',
            'job_type' => $event->jobType,
            'customer_name' => $this->getCustomerName($event->customerId)
        ]);
    }
}
```

**Benefits:**
- ✅ Automated testimonial collection
- ✅ Social proof generation
- ✅ Customer engagement

#### B. Portfolio Building

**CMS Workflow:**
```
Job Completed → Photos Uploaded → Portfolio Updated → Social Media Posted
```

**Integration:**
```php
// CMS Domain Event
class JobPhotosUploaded
{
    public function __construct(
        public readonly JobId $jobId,
        public readonly array $photoUrls,
        public readonly string $jobDescription
    ) {}
}

// BizBoost Event Listener
class AddToPortfolio
{
    public function handle(JobPhotosUploaded $event): void
    {
        // Add to business portfolio
        $this->portfolioService->add([
            'job_id' => $event->jobId,
            'images' => $event->photoUrls,
            'description' => $event->jobDescription
        ]);
        
        // Create social media post
        $this->postService->create([
            'caption' => $this->aiService->generateCaption($event->jobDescription),
            'images' => $event->photoUrls,
            'platforms' => ['facebook', 'instagram'],
            'schedule_at' => now()->addHours(2)
        ]);
    }
}
```

**Benefits:**
- ✅ Automated portfolio updates
- ✅ Social media content generation
- ✅ Brand visibility

#### C. Customer Engagement Campaigns

**CMS Workflow:**
```
Job Completed → 30 Days Pass → Re-engagement Campaign
```

**Integration:**
```php
// Scheduled job in CMS
class TriggerReengagementCampaigns
{
    public function handle(): void
    {
        // Find jobs completed 30 days ago
        $jobs = $this->jobService->findCompletedDaysAgo(30);
        
        foreach ($jobs as $job) {
            // Trigger BizBoost campaign
            event(new CustomerReengagementDue(
                customerId: $job->customer_id,
                lastJobDate: $job->completed_at,
                jobType: $job->job_type
            ));
        }
    }
}

// BizBoost Event Listener
class CreateReengagementCampaign
{
    public function handle(CustomerReengagementDue $event): void
    {
        // Create automated campaign
        $this->campaignService->create([
            'type' => 'reengagement',
            'customer_id' => $event->customerId,
            'objective' => 'repeat_business',
            'messages' => $this->generateMessages($event->jobType)
        ]);
    }
}
```

**Benefits:**
- ✅ Automated customer retention
- ✅ Repeat business generation
- ✅ Customer lifetime value increase

#### D. Analytics Integration

**CMS Reports Enhanced by BizBoost:**

| CMS Metric | BizBoost Data | Enhancement |
|------------|---------------|-------------|
| Customer Acquisition | Marketing campaign performance | Attribution tracking |
| Job Conversion Rate | Social media engagement | Marketing effectiveness |
| Customer Retention | Re-engagement campaign success | Retention strategies |
| Brand Awareness | Social media reach & impressions | Market visibility |

### 3.4 Implementation Strategy

**Phase 1: Shared Customer Entity**
- Use unified customer table across CMS and BizBoost
- Add `company_id` (tenant) field
- Sync customer data bi-directionally

**Phase 2: Job Completion Marketing**
- Create `JobCompleted` event in CMS
- Create `TriggerMarketingCampaign` listener in BizBoost
- Implement testimonial collection automation

**Phase 3: Portfolio Integration**
- Create `JobPhotosUploaded` event in CMS
- Create `AddToPortfolio` listener in BizBoost
- Implement automated social posting

**Phase 4: Re-engagement Campaigns**
- Create scheduled job in CMS for re-engagement triggers
- Create `CreateReengagementCampaign` listener in BizBoost
- Implement campaign performance tracking

---

## 4. Event-Driven Architecture

### 4.1 Domain Events

**CMS Domain Events:**

```php
namespace App\Domain\CMS\Events;

// Job Events
class JobCreated { /* ... */ }
class JobAssigned { /* ... */ }
class JobCompleted { /* ... */ }
class JobPaid { /* ... */ }

// Material Events
class MaterialsUsedOnJob { /* ... */ }
class InventoryDeducted { /* ... */ }

// Commission Events
class CommissionCalculated { /* ... */ }
class CommissionPaid { /* ... */ }

// Customer Events
class CustomerCreated { /* ... */ }
class CustomerUpdated { /* ... */ }

// Document Events
class DocumentUploaded { /* ... */ }
class DocumentLinked { /* ... */ }
```

### 4.2 Event Listeners

**GrowFinance Listeners:**

```php
namespace App\Infrastructure\Events\GrowFinance;

class GenerateInvoiceFromJob { /* ... */ }
class RecordJobExpense { /* ... */ }
class UpdateJobPaymentStatus { /* ... */ }
```

**GrowBiz Listeners:**

```php
namespace App\Infrastructure\Events\GrowBiz;

class CreateProjectFromJob { /* ... */ }
class ScheduleSiteVisit { /* ... */ }
class AssignTeamMember { /* ... */ }
```

**BizBoost Listeners:**

```php
namespace App\Infrastructure\Events\BizBoost;

class TriggerMarketingCampaign { /* ... */ }
class AddToPortfolio { /* ... */ }
class CreateReengagementCampaign { /* ... */ }
```

### 4.3 Event Registration

```php
// app/Providers/EventServiceProvider.php

protected $listen = [
    // CMS → GrowFinance
    JobCompleted::class => [
        GenerateInvoiceFromJob::class,
    ],
    MaterialsUsedOnJob::class => [
        RecordJobExpense::class,
    ],
    
    // CMS → GrowBiz
    JobCreated::class => [
        CreateProjectFromJob::class,
    ],
    SiteVisitRequired::class => [
        ScheduleSiteVisit::class,
    ],
    
    // CMS → BizBoost
    JobCompleted::class => [
        TriggerMarketingCampaign::class,
    ],
    JobPhotosUploaded::class => [
        AddToPortfolio::class,
    ],
    
    // GrowFinance → CMS
    InvoicePaymentReceived::class => [
        UpdateJobPaymentStatus::class,
    ],
];
```

---

## 5. Data Sync Strategies

### 5.1 Shared Entities Strategy

**Option 1: Single Source of Truth (Recommended)**

```
Customer Entity:
- GrowFinance owns customer data
- CMS references GrowFinance customer IDs
- BizBoost references GrowFinance customer IDs

Worker/Team Entity:
- GrowBiz owns team member data
- CMS references GrowBiz team member IDs

Asset/Inventory Entity:
- GrowBiz owns inventory data
- CMS references GrowBiz asset IDs
```

**Benefits:**
- ✅ No data duplication
- ✅ Single update point
- ✅ Consistent data across modules

**Option 2: Event-Driven Sync**

```
Customer Created in CMS
→ Event: CustomerCreated
→ Listener: SyncToGrowFinance
→ Listener: SyncToBizBoost
```

**Benefits:**
- ✅ Module independence
- ✅ Eventual consistency
- ✅ Resilient to failures

### 5.2 Reference Fields

**Add reference fields to link entities:**

```php
// growfinance_invoices table
Schema::table('growfinance_invoices', function (Blueprint $table) {
    $table->foreignId('job_id')->nullable()->constrained('cms_jobs');
});

// growbiz_projects table
Schema::table('growbiz_projects', function (Blueprint $table) {
    $table->string('reference_type')->nullable(); // 'job', 'campaign', etc.
    $table->unsignedBigInteger('reference_id')->nullable();
});

// bizboost_campaigns table
Schema::table('bizboost_campaigns', function (Blueprint $table) {
    $table->foreignId('job_id')->nullable()->constrained('cms_jobs');
});
```

---

## 6. API Integration Points

### 6.1 Internal APIs

**CMS exposes APIs for other modules:**

```php
// routes/api.php

Route::prefix('cms/api')->group(function () {
    // Jobs
    Route::get('/jobs/{id}', [CmsApiController::class, 'getJob']);
    Route::get('/jobs/{id}/status', [CmsApiController::class, 'getJobStatus']);
    
    // Customers
    Route::get('/customers/{id}', [CmsApiController::class, 'getCustomer']);
    
    // Workers
    Route::get('/workers/{id}', [CmsApiController::class, 'getWorker']);
    Route::get('/workers/{id}/availability', [CmsApiController::class, 'getAvailability']);
});
```

**GrowFinance exposes APIs for CMS:**

```php
Route::prefix('growfinance/api')->group(function () {
    // Invoices
    Route::post('/invoices/from-job', [GrowFinanceApiController::class, 'createFromJob']);
    Route::get('/invoices/{id}/payment-status', [GrowFinanceApiController::class, 'getPaymentStatus']);
    
    // Expenses
    Route::post('/expenses', [GrowFinanceApiController::class, 'createExpense']);
});
```

### 6.2 Webhook Integration

**For real-time updates:**

```php
// CMS webhook endpoint
Route::post('/cms/webhooks/job-completed', [CmsWebhookController::class, 'jobCompleted']);

// GrowFinance webhook endpoint
Route::post('/growfinance/webhooks/payment-received', [GrowFinanceWebhookController::class, 'paymentReceived']);
```

---

## 7. Implementation Roadmap

### Phase 1: Foundation (Weeks 1-2)

**Tasks:**
- [ ] Define shared entity strategy
- [ ] Create domain events for CMS
- [ ] Set up event listeners in existing modules
- [ ] Add reference fields to database tables
- [ ] Create migration scripts

**Deliverables:**
- Event-driven architecture in place
- Database schema updated
- Basic event flow working

### Phase 2: GrowFinance Integration (Weeks 3-4)

**Tasks:**
- [ ] Implement job-to-invoice automation
- [ ] Implement expense tracking integration
- [ ] Implement customer synchronization
- [ ] Implement payment reconciliation
- [ ] Test end-to-end flows

**Deliverables:**
- Jobs automatically generate invoices
- Expenses tracked from jobs
- Payments update job status
- Commission calculations triggered

### Phase 3: GrowBiz Integration (Weeks 5-6)

**Tasks:**
- [ ] Implement job-to-project sync
- [ ] Implement team management integration
- [ ] Implement inventory/asset integration
- [ ] Implement appointment scheduling
- [ ] Test end-to-end flows

**Deliverables:**
- Jobs create projects in GrowBiz
- Team members shared across modules
- Assets tracked centrally
- Appointments scheduled automatically

### Phase 4: BizBoost Integration (Weeks 7-8)

**Tasks:**
- [ ] Implement customer marketing automation
- [ ] Implement portfolio building
- [ ] Implement re-engagement campaigns
- [ ] Implement analytics integration
- [ ] Test end-to-end flows

**Deliverables:**
- Job completions trigger marketing
- Portfolio auto-updates
- Re-engagement campaigns automated
- Unified analytics dashboard

### Phase 5: Testing & Optimization (Weeks 9-10)

**Tasks:**
- [ ] End-to-end integration testing
- [ ] Performance optimization
- [ ] Error handling & retry logic
- [ ] Documentation
- [ ] User acceptance testing

**Deliverables:**
- All integrations tested
- Performance benchmarks met
- Documentation complete
- Ready for production

---

## 8. Benefits Summary

### For Geopamu (Pilot Tenant)

| Benefit | Impact |
|---------|--------|
| **Automated Invoicing** | Save 2-3 hours/day on manual invoice creation |
| **Real-Time Financial Visibility** | Know profitability of each job instantly |
| **Unified Customer Records** | No duplicate data entry across systems |
| **Automated Marketing** | Generate social proof and testimonials automatically |
| **Project Visibility** | Track job progress with Kanban boards |
| **Team Coordination** | Centralized team scheduling and assignment |

### For Future SME Customers

| Benefit | Impact |
|---------|--------|
| **All-in-One Platform** | No need for multiple disconnected tools |
| **Data Consistency** | Single source of truth for business data |
| **Automation** | Reduce manual work by 60-70% |
| **Better Decision Making** | Unified analytics across operations, finance, and marketing |
| **Scalability** | System grows with the business |

---

## 9. Technical Considerations

### 9.1 Multi-Tenancy

**All modules must support multi-tenancy:**

```php
// Add company_id to all relevant tables
Schema::table('growfinance_customers', function (Blueprint $table) {
    $table->foreignId('company_id')->constrained('cms_companies');
});

Schema::table('growbiz_team_members', function (Blueprint $table) {
    $table->foreignId('company_id')->constrained('cms_companies');
});

Schema::table('bizboost_businesses', function (Blueprint $table) {
    $table->foreignId('company_id')->constrained('cms_companies');
});
```

### 9.2 Data Isolation

**Ensure strict data isolation per tenant:**

```php
// Global scope for all models
class TenantScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $builder->where('company_id', auth()->user()->company_id);
    }
}
```

### 9.3 Performance Optimization

**Strategies:**
- Use database indexes on foreign keys
- Cache frequently accessed data (Redis)
- Queue heavy operations (invoice generation, email sending)
- Use eager loading to prevent N+1 queries

### 9.4 Error Handling

**Resilient event handling:**

```php
class GenerateInvoiceFromJob
{
    public function handle(JobCompleted $event): void
    {
        try {
            // Create invoice
            $invoice = $this->invoiceService->createFromJob($event);
        } catch (\Exception $e) {
            // Log error
            Log::error('Failed to generate invoice from job', [
                'job_id' => $event->jobId,
                'error' => $e->getMessage()
            ]);
            
            // Queue for retry
            GenerateInvoiceFromJobJob::dispatch($event)->delay(now()->addMinutes(5));
        }
    }
}
```

---

## 10. Conclusion

The integration between CMS and existing modules (GrowFinance, GrowBiz, BizBoost) provides **significant value** through:

1. **Automation** - Reduce manual data entry by 60-70%
2. **Consistency** - Single source of truth for shared entities
3. **Visibility** - Unified view of operations, finance, and marketing
4. **Efficiency** - Streamlined workflows across modules
5. **Scalability** - System grows with the business

**Recommended Approach:**
- Use **event-driven architecture** for loose coupling
- Implement **single source of truth** for shared entities
- Build **incrementally** (Phase 1-5 over 10 weeks)
- Focus on **Geopamu pilot** first, then generalize for other SMEs

**Next Steps:**
1. Review this document with development team
2. Prioritize integration points based on Geopamu needs
3. Begin Phase 1 implementation (Foundation)
4. Test with Geopamu as pilot tenant
5. Refine and roll out to other SMEs

---

## 11. Future Extraction Strategy

### 11.1 Can CMS Be Extracted Later?

**YES - The architecture is designed for future extraction.** Here's how:

#### Decoupling Principles Used

| Principle | Implementation | Extraction Benefit |
|-----------|----------------|-------------------|
| **Event-Driven Communication** | Modules communicate via domain events, not direct calls | Events can be sent over HTTP/message queue |
| **Bounded Contexts** | Each module has its own domain logic | Clear separation of concerns |
| **Repository Pattern** | Data access abstracted behind interfaces | Easy to swap implementations |
| **No Direct Dependencies** | Modules don't import each other's classes | No code coupling |
| **API-First Design** | Internal APIs already defined | Can become external APIs |

### 11.2 Extraction Scenarios

#### Scenario 1: CMS as Standalone SaaS Product

**Current State:**
```
MyGrowNet Platform (Single Codebase)
├── CMS Module
├── GrowFinance Module
├── GrowBiz Module
└── BizBoost Module
```

**After Extraction:**
```
CMS SaaS (Separate Application)          MyGrowNet Platform
├── Own Database                         ├── GrowFinance Module
├── Own Domain                           ├── GrowBiz Module
├── REST API                             └── BizBoost Module
└── Webhook System                              ↕
                                         REST API / Webhooks
```

**Migration Steps:**

1. **Replace Event Bus with HTTP API**
```php
// BEFORE (Internal Events)
event(new JobCompleted($jobId, $customerId, $totalValue));

// AFTER (HTTP API Call)
Http::post('https://growfinance.api/webhooks/job-completed', [
    'job_id' => $jobId,
    'customer_id' => $customerId,
    'total_value' => $totalValue
]);
```

2. **Replace Repository with API Client**
```php
// BEFORE (Direct Database Access)
class CmsCustomerService
{
    public function __construct(
        private GrowFinanceCustomerRepository $customerRepo
    ) {}
    
    public function getCustomer(CustomerId $id): Customer
    {
        return $this->customerRepo->find($id);
    }
}

// AFTER (API Client)
class CmsCustomerService
{
    public function __construct(
        private GrowFinanceApiClient $apiClient
    ) {}
    
    public function getCustomer(CustomerId $id): Customer
    {
        return $this->apiClient->customers()->find($id);
    }
}
```

3. **Add Authentication Layer**
```php
// API authentication between systems
class GrowFinanceApiClient
{
    public function __construct(
        private string $apiUrl,
        private string $apiKey
    ) {}
    
    public function customers(): CustomerEndpoint
    {
        return new CustomerEndpoint(
            $this->apiUrl,
            $this->apiKey
        );
    }
}
```

#### Scenario 2: CMS Sold to External SMEs

**Use Case:** Geopamu wants to sell CMS to other printing companies.

**Architecture:**
```
CMS SaaS Platform (cms.mygrownet.com)
├── Multi-tenant database
├── Tenant: Geopamu
├── Tenant: ABC Printers
├── Tenant: XYZ Branding
└── Optional: Integration with MyGrowNet modules
```

**Integration Options for External Customers:**

| Customer Type | Integration Level | Implementation |
|--------------|-------------------|----------------|
| **MyGrowNet Members** | Full integration | Event-driven + API |
| **External SMEs (No MyGrowNet)** | Standalone CMS only | No integration needed |
| **External SMEs (Want Finance)** | Optional GrowFinance | API integration |

### 11.3 Technical Requirements for Extraction

#### A. Database Separation

**Current (Shared Database):**
```
mygrownet_database
├── cms_companies
├── cms_jobs
├── cms_workers
├── growfinance_invoices
├── growfinance_customers
└── growbiz_projects
```

**After Extraction (Separate Databases):**
```
cms_database                    growfinance_database
├── companies                   ├── customers
├── jobs                        ├── invoices
├── workers                     └── payments
└── customers (cached)          
```

**Migration Strategy:**
```sql
-- Export CMS tables
mysqldump mygrownet_database cms_* > cms_export.sql

-- Import to new database
mysql cms_database < cms_export.sql

-- Add API reference fields
ALTER TABLE cms_jobs 
ADD COLUMN growfinance_invoice_id VARCHAR(255),
ADD COLUMN growfinance_customer_id VARCHAR(255);
```

#### B. API Gateway Pattern

**Implement API Gateway for cross-system communication:**

```php
// app/Infrastructure/External/ApiGateway.php
class ApiGateway
{
    public function __construct(
        private GrowFinanceApiClient $growFinance,
        private GrowBizApiClient $growBiz,
        private BizBoostApiClient $bizBoost
    ) {}
    
    public function createInvoiceFromJob(Job $job): Invoice
    {
        return $this->growFinance->invoices()->createFromJob([
            'job_id' => $job->id,
            'customer_id' => $job->customer_id,
            'line_items' => $job->lineItems,
            'total_amount' => $job->totalValue
        ]);
    }
}
```

#### C. Event Bridge Pattern

**Use message queue for event distribution:**

```php
// BEFORE (Laravel Events)
Event::listen(JobCompleted::class, GenerateInvoiceFromJob::class);

// AFTER (Message Queue)
class JobCompletedEventPublisher
{
    public function publish(JobCompleted $event): void
    {
        // Publish to RabbitMQ/SQS/Redis
        Queue::connection('events')->push(new PublishDomainEvent(
            event: 'job.completed',
            payload: $event->toArray(),
            subscribers: ['growfinance', 'growbiz', 'bizboost']
        ));
    }
}
```

### 11.4 Extraction Roadmap

#### Phase 1: Prepare for Extraction (Weeks 1-2)

**Tasks:**
- [ ] Audit all direct dependencies between modules
- [ ] Create API contracts (OpenAPI specs)
- [ ] Implement API clients for each module
- [ ] Add feature flags for local vs. remote mode

**Deliverables:**
- Dependency map
- API documentation
- API client libraries

#### Phase 2: Implement Dual Mode (Weeks 3-4)

**Tasks:**
- [ ] Add configuration for local/remote mode
- [ ] Implement API gateway
- [ ] Add fallback mechanisms
- [ ] Test both modes

**Configuration:**
```php
// config/cms.php
return [
    'integration_mode' => env('CMS_INTEGRATION_MODE', 'local'), // 'local' or 'remote'
    
    'remote_apis' => [
        'growfinance' => [
            'url' => env('GROWFINANCE_API_URL'),
            'key' => env('GROWFINANCE_API_KEY'),
        ],
        'growbiz' => [
            'url' => env('GROWBIZ_API_URL'),
            'key' => env('GROWBIZ_API_KEY'),
        ],
        'bizboost' => [
            'url' => env('BIZBOOST_API_URL'),
            'key' => env('BIZBOOST_API_KEY'),
        ],
    ],
];
```

**Service Provider:**
```php
class CmsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Bind appropriate implementation based on mode
        if (config('cms.integration_mode') === 'local') {
            $this->app->bind(
                CustomerRepositoryInterface::class,
                GrowFinanceCustomerRepository::class
            );
        } else {
            $this->app->bind(
                CustomerRepositoryInterface::class,
                GrowFinanceApiCustomerRepository::class
            );
        }
    }
}
```

#### Phase 3: Database Migration (Weeks 5-6)

**Tasks:**
- [ ] Set up separate CMS database
- [ ] Migrate CMS tables
- [ ] Update connection configurations
- [ ] Implement data sync mechanisms
- [ ] Test data integrity

#### Phase 4: Deploy as Separate Service (Weeks 7-8)

**Tasks:**
- [ ] Set up separate server/container
- [ ] Configure API authentication
- [ ] Set up monitoring
- [ ] Implement rate limiting
- [ ] Deploy and test

**Deployment Architecture:**
```
Load Balancer
├── cms.mygrownet.com (CMS Service)
├── finance.mygrownet.com (GrowFinance Service)
├── biz.mygrownet.com (GrowBiz Service)
└── boost.mygrownet.com (BizBoost Service)
```

### 11.5 Cost-Benefit Analysis

#### Keeping CMS Integrated (Current)

**Pros:**
- ✅ Faster development (no API overhead)
- ✅ Simpler deployment (single codebase)
- ✅ Lower infrastructure costs
- ✅ Easier debugging (single application)

**Cons:**
- ❌ Harder to scale independently
- ❌ Can't sell CMS separately
- ❌ Tighter coupling over time
- ❌ Single point of failure

#### Extracting CMS (Future)

**Pros:**
- ✅ Can sell CMS as standalone product
- ✅ Independent scaling
- ✅ Technology flexibility (could rewrite in different language)
- ✅ Clear boundaries
- ✅ Multiple deployment options

**Cons:**
- ❌ Higher infrastructure costs (multiple servers)
- ❌ API latency overhead
- ❌ More complex deployment
- ❌ Distributed system challenges

### 11.6 Recommended Strategy

**Start Integrated, Extract When Needed:**

1. **Year 1 (Now):** Build CMS integrated with MyGrowNet
   - Faster time to market
   - Validate product-market fit with Geopamu
   - Lower costs

2. **Year 2:** Prepare for extraction
   - Implement dual-mode support
   - Create API contracts
   - Test with pilot external customer

3. **Year 3:** Full extraction
   - Deploy as separate service
   - Sell to external SMEs
   - Scale independently

**Trigger Points for Extraction:**

| Trigger | Action |
|---------|--------|
| **10+ external SMEs want CMS** | Begin extraction planning |
| **Performance bottlenecks** | Extract for independent scaling |
| **Different tech requirements** | Extract to use different stack |
| **Acquisition interest** | Extract to sell CMS separately |

### 11.7 Extraction Checklist

**Before Extraction:**
- [ ] All module communication via events/APIs (no direct calls)
- [ ] Repository pattern used everywhere (no direct Eloquent queries)
- [ ] API contracts documented (OpenAPI specs)
- [ ] Authentication/authorization abstracted
- [ ] Configuration externalized (no hardcoded values)
- [ ] Comprehensive test coverage (can verify behavior after extraction)

**During Extraction:**
- [ ] Separate database created and migrated
- [ ] API clients implemented and tested
- [ ] Event bridge/message queue configured
- [ ] Monitoring and logging set up
- [ ] Performance benchmarks met
- [ ] Security audit completed

**After Extraction:**
- [ ] Both systems running independently
- [ ] Integration tests passing
- [ ] Documentation updated
- [ ] Team trained on new architecture
- [ ] Rollback plan tested

---

## Related Documents

- `docs/cms/DEVELOPMENT_BRIEF.md` - CMS requirements
- `docs/cms/IMPLEMENTATION_PLAN.md` - CMS technical architecture
- `docs/growfinance/GROWFINANCE_MODULE.md` - GrowFinance documentation
- `docs/growbiz/GROWBIZ_MODULE_ARCHITECTURE.md` - GrowBiz architecture
- `docs/bizboost/BIZBOOST_MASTER_CONCEPT.md` - BizBoost concept

---

**Document Owner:** Development Team  
**Review Cycle:** Weekly during implementation  
**Next Review:** February 14, 2026
