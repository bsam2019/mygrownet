# GrowStart – Complete Development Specification

**Last Updated:** December 11, 2025  
**Status:** Sprint 1 Complete - Core Backend & Frontend  
**Source:** GROWSTART_CONCEPT.md  
**Stack:** Laravel 12 + Vue.js 3 + TypeScript

---

## Implementation Progress

### ✅ Completed (Sprint 1)

**Backend:**
- [x] Domain entities (StartupJourney, Stage, Task, UserTask)
- [x] Value objects (JourneyId, JourneyStatus, TaskStatus, StageSlug, JourneyProgress)
- [x] Repository interfaces and Eloquent implementations
- [x] Domain services (JourneyProgressService, TaskCompletionService)
- [x] Eloquent models (UserJourney, Stage, Task, UserTask, Industry, Country, Badge, Template, PartnerProvider)
- [x] Database migrations (9 tables)
- [x] Database seeders (Countries, Industries, Stages, Tasks, Badges)
- [x] Controllers (Dashboard, Journey, Stage, Task, Template, Provider, Badge)
- [x] Routes (Web + API)
- [x] Service provider registration

**Frontend:**
- [x] TypeScript types (growstart.ts)
- [x] Dashboard page
- [x] Onboarding wizard (3-step)
- [x] Stages index page
- [x] Stage detail page with tasks
- [x] Templates library page
- [x] Providers directory page
- [x] Badges page
- [x] Journey management page

**Configuration:**
- [x] Module registered in config/modules.php

### 🔄 In Progress (Sprint 2)

- [ ] Financial planning tools
- [ ] Collaboration features
- [ ] Badge award automation
- [ ] PWA setup
- [ ] Offline support

---

## Table of Contents

1. [System Architecture](#a-system-architecture)
2. [Development Tasks](#b-development-tasks)
3. [Sprint Plan](#c-sprint-plan)
4. [Feature Breakdown](#d-feature-breakdown)
5. [UI/UX Flows](#e-uiux-flows)
6. [Developer Notes](#f-developer-notes)

---

# A. System Architecture

## A.1 Backend Structure (Laravel)

### Directory Structure

```
app/
├── Domain/
│   └── GrowStart/
│       ├── Entities/
│       │   ├── StartupJourney.php
│       │   ├── Stage.php
│       │   ├── Task.php
│       │   ├── Milestone.php
│       │   ├── Industry.php
│       │   └── Badge.php
│       ├── ValueObjects/
│       │   ├── JourneyProgress.php
│       │   ├── TaskStatus.php
│       │   └── StageCompletion.php
│       ├── Services/
│       │   ├── JourneyProgressService.php
│       │   ├── RoadmapGeneratorService.php
│       │   ├── CountryPackService.php
│       │   ├── TaskCompletionService.php
│       │   ├── BadgeAwardService.php
│       │   ├── FinancialPlanningService.php
│       │   └── CollaborationService.php
│       ├── Repositories/
│       │   ├── JourneyRepositoryInterface.php
│       │   ├── TaskRepositoryInterface.php
│       │   ├── StageRepositoryInterface.php
│       │   └── ProviderRepositoryInterface.php
│       └── Events/
│           ├── JourneyStarted.php
│           ├── StageCompleted.php
│           ├── TaskCompleted.php
│           ├── MilestoneAchieved.php
│           └── BadgeEarned.php
│
├── Infrastructure/
│   └── GrowStart/
│       ├── Persistence/
│       │   ├── Eloquent/
│       │   │   ├── UserJourney.php
│       │   │   ├── StartupStage.php
│       │   │   ├── Task.php
│       │   │   ├── UserTask.php
│       │   │   ├── Industry.php
│       │   │   ├── Template.php
│       │   │   ├── Resource.php
│       │   │   ├── PartnerProvider.php
│       │   │   ├── Badge.php
│       │   │   ├── UserBadge.php
│       │   │   ├── Country.php
│       │   │   ├── CountryPack.php
│       │   │   ├── JourneyCollaborator.php
│       │   │   └── FinancialPlan.php
│       │   └── Repositories/
│       │       ├── EloquentJourneyRepository.php
│       │       ├── EloquentTaskRepository.php
│       │       ├── EloquentStageRepository.php
│       │       └── EloquentProviderRepository.php
│       ├── CountryPacks/
│       │   ├── CountryPackLoader.php
│       │   ├── Zambia/
│       │   │   ├── ZambiaPackProvider.php
│       │   │   ├── regulatory_steps.json
│       │   │   ├── licenses.json
│       │   │   ├── templates/
│       │   │   └── providers.json
│       │   └── BaseCountryPack.php
│       └── Integrations/
│           ├── GrowFinanceIntegration.php
│           ├── BizBoostIntegration.php
│           └── GrowBizIntegration.php
│
├── Application/
│   └── GrowStart/
│       ├── UseCases/
│       │   ├── StartJourneyUseCase.php
│       │   ├── CompleteTaskUseCase.php
│       │   ├── GenerateRoadmapUseCase.php
│       │   ├── GetProgressUseCase.php
│       │   ├── InviteCollaboratorUseCase.php
│       │   ├── DownloadTemplateUseCase.php
│       │   └── CalculateFinancialsUseCase.php
│       ├── DTOs/
│       │   ├── JourneyDTO.php
│       │   ├── TaskDTO.php
│       │   ├── StageDTO.php
│       │   ├── ProgressDTO.php
│       │   └── ProviderDTO.php
│       └── Commands/
│           ├── SyncCountryPacksCommand.php
│           ├── AwardBadgesCommand.php
│           └── SendProgressRemindersCommand.php
│
└── Presentation/
    └── Http/
        └── Controllers/
            └── GrowStart/
                ├── DashboardController.php
                ├── JourneyController.php
                ├── StageController.php
                ├── TaskController.php
                ├── RoadmapController.php
                ├── TemplateController.php
                ├── ProviderController.php
                ├── BadgeController.php
                ├── CollaboratorController.php
                ├── FinancialPlanController.php
                └── OfflinePackController.php
        └── Requests/
            └── GrowStart/
                ├── StartJourneyRequest.php
                ├── UpdateTaskRequest.php
                ├── InviteCollaboratorRequest.php
                └── FinancialPlanRequest.php
```

### Models (Eloquent)

```php
// app/Infrastructure/GrowStart/Persistence/Eloquent/

// 1. UserJourney.php - User's startup journey
class UserJourney extends Model
{
    protected $fillable = [
        'user_id', 'industry_id', 'country_id', 'business_name',
        'current_stage_id', 'started_at', 'target_launch_date',
        'status', 'is_premium'
    ];
    
    // Relationships
    public function user(): BelongsTo;
    public function industry(): BelongsTo;
    public function country(): BelongsTo;
    public function currentStage(): BelongsTo;
    public function tasks(): HasManyThrough;
    public function userTasks(): HasMany;
    public function collaborators(): HasMany;
    public function financialPlan(): HasOne;
    public function badges(): BelongsToMany;
}

// 2. StartupStage.php - 8 journey stages
class StartupStage extends Model
{
    protected $fillable = [
        'name', 'slug', 'description', 'order', 'icon',
        'estimated_days', 'is_active'
    ];
    
    // Stages: idea, validation, planning, registration, launch, accounting, marketing, growth
}

// 3. Task.php - Tasks per stage/industry
class Task extends Model
{
    protected $fillable = [
        'stage_id', 'industry_id', 'country_id', 'title',
        'description', 'instructions', 'external_link',
        'estimated_hours', 'order', 'is_required', 'is_premium'
    ];
}

// 4. UserTask.php - User's task completion
class UserTask extends Model
{
    protected $fillable = [
        'user_journey_id', 'task_id', 'status', 'completed_at',
        'notes', 'attachments'
    ];
    
    // Status: pending, in_progress, completed, skipped
}

// 5. Industry.php - Business categories
class Industry extends Model
{
    protected $fillable = [
        'name', 'slug', 'description', 'icon', 'is_active',
        'estimated_startup_cost_min', 'estimated_startup_cost_max'
    ];
}

// 6. Template.php - Document templates
class Template extends Model
{
    protected $fillable = [
        'name', 'description', 'category', 'file_path',
        'industry_id', 'country_id', 'is_premium', 'download_count'
    ];
}

// 7. PartnerProvider.php - Local service providers
class PartnerProvider extends Model
{
    protected $fillable = [
        'name', 'category', 'description', 'contact_phone',
        'contact_email', 'website', 'province', 'city',
        'country_id', 'is_featured', 'is_verified', 'rating'
    ];
}

// 8. Badge.php - Achievement badges
class Badge extends Model
{
    protected $fillable = [
        'name', 'slug', 'description', 'icon', 'criteria_type',
        'criteria_value', 'points'
    ];
}

// 9. Country.php - Supported countries
class Country extends Model
{
    protected $fillable = [
        'name', 'code', 'currency', 'currency_symbol',
        'is_active', 'pack_version'
    ];
}

// 10. JourneyCollaborator.php - Team members
class JourneyCollaborator extends Model
{
    protected $fillable = [
        'user_journey_id', 'user_id', 'email', 'role',
        'invited_at', 'accepted_at', 'status'
    ];
    
    // Roles: co_founder, mentor, advisor
}

// 11. FinancialPlan.php - User's financial planning
class FinancialPlan extends Model
{
    protected $fillable = [
        'user_journey_id', 'startup_budget', 'monthly_expenses',
        'pricing_strategy', 'break_even_units', 'break_even_months',
        'funding_sources', 'notes'
    ];
    
    protected $casts = [
        'funding_sources' => 'array'
    ];
}
```

### Services

```php
// app/Domain/GrowStart/Services/

// 1. JourneyProgressService.php
class JourneyProgressService
{
    public function calculateOverallProgress(UserJourney $journey): float;
    public function calculateStageProgress(UserJourney $journey, StartupStage $stage): float;
    public function getNextTasks(UserJourney $journey, int $limit = 5): Collection;
    public function canAdvanceToStage(UserJourney $journey, StartupStage $stage): bool;
    public function getWeeklyGoals(UserJourney $journey): array;
    public function getTimelineStatus(UserJourney $journey): array;
}

// 2. RoadmapGeneratorService.php
class RoadmapGeneratorService
{
    public function generateForIndustry(Industry $industry, Country $country): array;
    public function customizeRoadmap(UserJourney $journey, array $customizations): void;
    public function getEstimatedTimeline(UserJourney $journey): array;
}

// 3. CountryPackService.php
class CountryPackService
{
    public function loadPack(Country $country): CountryPack;
    public function getRegulatorySteps(Country $country, string $category): array;
    public function getLicenses(Country $country, Industry $industry): array;
    public function getTemplates(Country $country, ?Industry $industry = null): Collection;
    public function syncPack(Country $country): void;
}

// 4. BadgeAwardService.php
class BadgeAwardService
{
    public function checkAndAwardBadges(UserJourney $journey): array;
    public function awardBadge(UserJourney $journey, Badge $badge): UserBadge;
    public function getEligibleBadges(UserJourney $journey): Collection;
}

// 5. CollaborationService.php
class CollaborationService
{
    public function inviteCollaborator(UserJourney $journey, string $email, string $role): JourneyCollaborator;
    public function acceptInvitation(JourneyCollaborator $collaborator, User $user): void;
    public function getCollaboratorPermissions(string $role): array;
}

// 6. FinancialPlanningService.php
class FinancialPlanningService
{
    public function calculateStartupBudget(Industry $industry, Country $country): array;
    public function calculateBreakEven(FinancialPlan $plan): array;
    public function suggestPricing(Industry $industry, array $costs): array;
}
```

---

## A.2 Frontend Structure (Vue.js)

### Directory Structure

```
resources/js/
├── Pages/
│   └── GrowStart/
│       ├── Dashboard.vue              # Main dashboard
│       ├── Onboarding/
│       │   ├── Index.vue              # Onboarding wizard
│       │   ├── SelectIndustry.vue     # Step 1: Choose industry
│       │   ├── BusinessDetails.vue    # Step 2: Business info
│       │   └── SetGoals.vue           # Step 3: Timeline goals
│       ├── Journey/
│       │   ├── Index.vue              # Journey overview
│       │   ├── Stage.vue              # Single stage view
│       │   ├── TaskList.vue           # Tasks for a stage
│       │   └── TaskDetail.vue         # Task details modal
│       ├── Roadmap/
│       │   ├── Index.vue              # Visual roadmap
│       │   ├── Timeline.vue           # Timeline view
│       │   └── Customize.vue          # Customize roadmap
│       ├── Financial/
│       │   ├── Index.vue              # Financial planning hub
│       │   ├── Budget.vue             # Startup budget
│       │   ├── Pricing.vue            # Pricing calculator
│       │   └── BreakEven.vue          # Break-even analysis
│       ├── Templates/
│       │   ├── Index.vue              # Template library
│       │   └── Preview.vue            # Template preview
│       ├── Directory/
│       │   ├── Index.vue              # Provider directory
│       │   └── ProviderDetail.vue     # Provider profile
│       ├── Collaboration/
│       │   ├── Index.vue              # Team management
│       │   └── Invite.vue             # Invite modal
│       ├── Badges/
│       │   └── Index.vue              # Achievements
│       ├── Regulatory/
│       │   ├── Index.vue              # Regulatory guides
│       │   └── Guide.vue              # Single guide view
│       └── Settings/
│           └── Index.vue              # Journey settings
│
├── Components/
│   └── GrowStart/
│       ├── Dashboard/
│       │   ├── ProgressCard.vue       # Overall progress
│       │   ├── StageProgress.vue      # Stage completion
│       │   ├── NextTasks.vue          # Upcoming tasks
│       │   ├── WeeklyGoals.vue        # Weekly targets
│       │   ├── RecentBadges.vue       # Latest badges
│       │   └── QuickActions.vue       # Action buttons
│       ├── Journey/
│       │   ├── StageCard.vue          # Stage summary card
│       │   ├── TaskItem.vue           # Task list item
│       │   ├── TaskCheckbox.vue       # Completion checkbox
│       │   ├── TaskNotes.vue          # Task notes editor
│       │   └── StageTimeline.vue      # Visual timeline
│       ├── Roadmap/
│       │   ├── RoadmapVisual.vue      # Interactive roadmap
│       │   ├── StageNode.vue          # Stage node
│       │   └── ProgressLine.vue       # Connection lines
│       ├── Financial/
│       │   ├── BudgetTable.vue        # Budget breakdown
│       │   ├── PricingCalculator.vue  # Pricing tool
│       │   └── BreakEvenChart.vue     # Break-even chart
│       ├── Templates/
│       │   ├── TemplateCard.vue       # Template preview
│       │   └── DownloadButton.vue     # Download action
│       ├── Directory/
│       │   ├── ProviderCard.vue       # Provider listing
│       │   ├── ProviderFilter.vue     # Filter controls
│       │   └── ContactModal.vue       # Contact form
│       ├── Badges/
│       │   ├── BadgeCard.vue          # Badge display
│       │   └── BadgeProgress.vue      # Progress to badge
│       ├── Collaboration/
│       │   ├── CollaboratorList.vue   # Team members
│       │   └── InviteForm.vue         # Invitation form
│       └── Common/
│           ├── ProgressBar.vue        # Progress indicator
│           ├── StageIcon.vue          # Stage icons
│           ├── IndustryIcon.vue       # Industry icons
│           ├── EmptyState.vue         # Empty states
│           └── LoadingState.vue       # Loading states
│
├── Composables/
│   └── GrowStart/
│       ├── useJourney.ts              # Journey state
│       ├── useProgress.ts             # Progress calculations
│       ├── useTasks.ts                # Task management
│       ├── useRoadmap.ts              # Roadmap data
│       ├── useTemplates.ts            # Template downloads
│       ├── useProviders.ts            # Provider directory
│       ├── useBadges.ts               # Badge system
│       └── useFinancial.ts            # Financial tools
│
└── types/
    └── growstart.ts                   # TypeScript interfaces
```

### TypeScript Interfaces

```typescript
// resources/js/types/growstart.ts

export interface Journey {
  id: number;
  userId: number;
  industryId: number;
  countryId: number;
  businessName: string;
  currentStageId: number;
  startedAt: string;
  targetLaunchDate: string | null;
  status: 'active' | 'paused' | 'completed';
  isPremium: boolean;
  progress: JourneyProgress;
  currentStage: Stage;
  industry: Industry;
  country: Country;
}

export interface JourneyProgress {
  overall: number;
  stageProgress: Record<number, number>;
  tasksCompleted: number;
  totalTasks: number;
  daysActive: number;
  estimatedDaysRemaining: number;
}

export interface Stage {
  id: number;
  name: string;
  slug: StageSlug;
  description: string;
  order: number;
  icon: string;
  estimatedDays: number;
  isActive: boolean;
  tasks?: Task[];
  progress?: number;
}

export type StageSlug = 
  | 'idea' 
  | 'validation' 
  | 'planning' 
  | 'registration' 
  | 'launch' 
  | 'accounting' 
  | 'marketing' 
  | 'growth';

export interface Task {
  id: number;
  stageId: number;
  industryId: number | null;
  countryId: number | null;
  title: string;
  description: string;
  instructions: string | null;
  externalLink: string | null;
  estimatedHours: number;
  order: number;
  isRequired: boolean;
  isPremium: boolean;
  userTask?: UserTask;
}

export interface UserTask {
  id: number;
  userJourneyId: number;
  taskId: number;
  status: TaskStatus;
  completedAt: string | null;
  notes: string | null;
  attachments: string[];
}

export type TaskStatus = 'pending' | 'in_progress' | 'completed' | 'skipped';

export interface Industry {
  id: number;
  name: string;
  slug: string;
  description: string;
  icon: string;
  isActive: boolean;
  estimatedStartupCostMin: number;
  estimatedStartupCostMax: number;
}

export interface Country {
  id: number;
  name: string;
  code: string;
  currency: string;
  currencySymbol: string;
  isActive: boolean;
}

export interface Template {
  id: number;
  name: string;
  description: string;
  category: TemplateCategory;
  filePath: string;
  industryId: number | null;
  countryId: number | null;
  isPremium: boolean;
  downloadCount: number;
}

export type TemplateCategory = 
  | 'business_plan' 
  | 'financial' 
  | 'marketing' 
  | 'legal' 
  | 'operations';

export interface Provider {
  id: number;
  name: string;
  category: ProviderCategory;
  description: string;
  contactPhone: string;
  contactEmail: string;
  website: string | null;
  province: string;
  city: string;
  countryId: number;
  isFeatured: boolean;
  isVerified: boolean;
  rating: number;
}

export type ProviderCategory = 
  | 'accountant' 
  | 'designer' 
  | 'pacra_agent' 
  | 'marketing' 
  | 'legal' 
  | 'supplier';

export interface Badge {
  id: number;
  name: string;
  slug: string;
  description: string;
  icon: string;
  criteriaType: BadgeCriteria;
  criteriaValue: number;
  points: number;
  earnedAt?: string;
}

export type BadgeCriteria = 
  | 'stage_complete' 
  | 'tasks_complete' 
  | 'streak_days' 
  | 'journey_complete';

export interface Collaborator {
  id: number;
  userJourneyId: number;
  userId: number | null;
  email: string;
  role: CollaboratorRole;
  invitedAt: string;
  acceptedAt: string | null;
  status: 'pending' | 'accepted' | 'declined';
  user?: {
    id: number;
    name: string;
    email: string;
  };
}

export type CollaboratorRole = 'co_founder' | 'mentor' | 'advisor';

export interface FinancialPlan {
  id: number;
  userJourneyId: number;
  startupBudget: number;
  monthlyExpenses: number;
  pricingStrategy: string;
  breakEvenUnits: number;
  breakEvenMonths: number;
  fundingSources: FundingSource[];
  notes: string | null;
}

export interface FundingSource {
  name: string;
  amount: number;
  type: 'savings' | 'loan' | 'investment' | 'grant' | 'other';
}

export interface WeeklyGoal {
  id: number;
  title: string;
  taskId: number | null;
  isCompleted: boolean;
  dueDate: string;
}

export interface RegulatoryGuide {
  id: string;
  title: string;
  category: string;
  steps: RegulatoryStep[];
  fees: Fee[];
  links: ExternalLink[];
}

export interface RegulatoryStep {
  order: number;
  title: string;
  description: string;
  documents: string[];
  estimatedTime: string;
}

export interface Fee {
  name: string;
  amount: number;
  currency: string;
  notes: string | null;
}

export interface ExternalLink {
  title: string;
  url: string;
  type: 'official' | 'guide' | 'form';
}
```

---

## A.3 API Endpoints

### Journey Management

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/growstart/journey` | Get user's current journey |
| POST | `/api/growstart/journey` | Start new journey |
| PUT | `/api/growstart/journey/{id}` | Update journey details |
| DELETE | `/api/growstart/journey/{id}` | Delete/archive journey |
| GET | `/api/growstart/journey/{id}/progress` | Get progress summary |
| POST | `/api/growstart/journey/{id}/pause` | Pause journey |
| POST | `/api/growstart/journey/{id}/resume` | Resume journey |

### Stages & Tasks

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/growstart/stages` | List all stages |
| GET | `/api/growstart/stages/{slug}` | Get stage details |
| GET | `/api/growstart/stages/{slug}/tasks` | Get tasks for stage |
| GET | `/api/growstart/tasks/{id}` | Get task details |
| POST | `/api/growstart/tasks/{id}/complete` | Mark task complete |
| POST | `/api/growstart/tasks/{id}/skip` | Skip task |
| PUT | `/api/growstart/tasks/{id}/notes` | Update task notes |
| POST | `/api/growstart/tasks/{id}/attachments` | Upload attachment |

### Roadmap

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/growstart/roadmap` | Get user's roadmap |
| POST | `/api/growstart/roadmap/generate` | Generate new roadmap |
| PUT | `/api/growstart/roadmap/customize` | Customize roadmap |
| GET | `/api/growstart/roadmap/timeline` | Get timeline view |

### Templates & Resources

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/growstart/templates` | List templates |
| GET | `/api/growstart/templates/{id}` | Get template details |
| GET | `/api/growstart/templates/{id}/download` | Download template |
| GET | `/api/growstart/resources` | List resources |
| GET | `/api/growstart/offline-packs` | List offline packs |
| GET | `/api/growstart/offline-packs/{id}/download` | Download pack |

### Providers Directory

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/growstart/providers` | List providers |
| GET | `/api/growstart/providers/{id}` | Get provider details |
| GET | `/api/growstart/providers/categories` | List categories |
| POST | `/api/growstart/providers/{id}/contact` | Send contact request |

### Badges & Achievements

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/growstart/badges` | List all badges |
| GET | `/api/growstart/badges/earned` | Get earned badges |
| GET | `/api/growstart/badges/progress` | Get badge progress |

### Collaboration

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/growstart/collaborators` | List collaborators |
| POST | `/api/growstart/collaborators/invite` | Invite collaborator |
| DELETE | `/api/growstart/collaborators/{id}` | Remove collaborator |
| POST | `/api/growstart/collaborators/{id}/accept` | Accept invitation |
| PUT | `/api/growstart/collaborators/{id}/role` | Update role |

### Financial Planning

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/growstart/financial` | Get financial plan |
| POST | `/api/growstart/financial` | Create/update plan |
| GET | `/api/growstart/financial/budget-estimate` | Get budget estimate |
| POST | `/api/growstart/financial/break-even` | Calculate break-even |
| GET | `/api/growstart/financial/pricing-suggestions` | Get pricing suggestions |

### Regulatory Content

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/growstart/regulatory` | List regulatory guides |
| GET | `/api/growstart/regulatory/{category}` | Get guide by category |
| GET | `/api/growstart/licenses` | List required licenses |

### Industries & Countries

| Method | Endpoint | Description |
|--------|----------|-------------|
| GET | `/api/growstart/industries` | List industries |
| GET | `/api/growstart/industries/{slug}` | Get industry details |
| GET | `/api/growstart/countries` | List supported countries |
| GET | `/api/growstart/countries/{code}` | Get country details |

---

## A.4 Database Schema

### Complete Migration Files

```php
// Migration 1: create_growstart_countries_table
Schema::create('growstart_countries', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('code', 3)->unique();
    $table->string('currency', 10);
    $table->string('currency_symbol', 5);
    $table->boolean('is_active')->default(false);
    $table->string('pack_version')->nullable();
    $table->timestamps();
});

// Migration 2: create_growstart_industries_table
Schema::create('growstart_industries', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('slug')->unique();
    $table->text('description')->nullable();
    $table->string('icon')->nullable();
    $table->boolean('is_active')->default(true);
    $table->decimal('estimated_startup_cost_min', 12, 2)->nullable();
    $table->decimal('estimated_startup_cost_max', 12, 2)->nullable();
    $table->timestamps();
});

// Migration 3: create_growstart_stages_table
Schema::create('growstart_stages', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('slug')->unique();
    $table->text('description')->nullable();
    $table->integer('order');
    $table->string('icon')->nullable();
    $table->integer('estimated_days')->default(7);
    $table->boolean('is_active')->default(true);
    $table->timestamps();
});

// Migration 4: create_growstart_tasks_table
Schema::create('growstart_tasks', function (Blueprint $table) {
    $table->id();
    $table->foreignId('stage_id')->constrained('growstart_stages')->cascadeOnDelete();
    $table->foreignId('industry_id')->nullable()->constrained('growstart_industries')->nullOnDelete();
    $table->foreignId('country_id')->nullable()->constrained('growstart_countries')->nullOnDelete();
    $table->string('title');
    $table->text('description')->nullable();
    $table->text('instructions')->nullable();
    $table->string('external_link')->nullable();
    $table->integer('estimated_hours')->default(1);
    $table->integer('order')->default(0);
    $table->boolean('is_required')->default(true);
    $table->boolean('is_premium')->default(false);
    $table->timestamps();
    
    $table->index(['stage_id', 'industry_id', 'country_id']);
});
```

```php
// Migration 5: create_growstart_user_journeys_table
Schema::create('growstart_user_journeys', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    $table->foreignId('industry_id')->constrained('growstart_industries');
    $table->foreignId('country_id')->constrained('growstart_countries');
    $table->string('business_name');
    $table->foreignId('current_stage_id')->constrained('growstart_stages');
    $table->timestamp('started_at');
    $table->date('target_launch_date')->nullable();
    $table->enum('status', ['active', 'paused', 'completed'])->default('active');
    $table->boolean('is_premium')->default(false);
    $table->timestamps();
    
    $table->index(['user_id', 'status']);
});

// Migration 6: create_growstart_user_tasks_table
Schema::create('growstart_user_tasks', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_journey_id')->constrained('growstart_user_journeys')->cascadeOnDelete();
    $table->foreignId('task_id')->constrained('growstart_tasks')->cascadeOnDelete();
    $table->enum('status', ['pending', 'in_progress', 'completed', 'skipped'])->default('pending');
    $table->timestamp('completed_at')->nullable();
    $table->text('notes')->nullable();
    $table->json('attachments')->nullable();
    $table->timestamps();
    
    $table->unique(['user_journey_id', 'task_id']);
    $table->index(['user_journey_id', 'status']);
});

// Migration 7: create_growstart_badges_table
Schema::create('growstart_badges', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('slug')->unique();
    $table->text('description')->nullable();
    $table->string('icon');
    $table->enum('criteria_type', ['stage_complete', 'tasks_complete', 'streak_days', 'journey_complete']);
    $table->integer('criteria_value');
    $table->integer('points')->default(10);
    $table->timestamps();
});

// Migration 8: create_growstart_user_badges_table
Schema::create('growstart_user_badges', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_journey_id')->constrained('growstart_user_journeys')->cascadeOnDelete();
    $table->foreignId('badge_id')->constrained('growstart_badges')->cascadeOnDelete();
    $table->timestamp('earned_at');
    $table->timestamps();
    
    $table->unique(['user_journey_id', 'badge_id']);
});

// Migration 9: create_growstart_templates_table
Schema::create('growstart_templates', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->text('description')->nullable();
    $table->enum('category', ['business_plan', 'financial', 'marketing', 'legal', 'operations']);
    $table->string('file_path');
    $table->foreignId('industry_id')->nullable()->constrained('growstart_industries')->nullOnDelete();
    $table->foreignId('country_id')->nullable()->constrained('growstart_countries')->nullOnDelete();
    $table->boolean('is_premium')->default(false);
    $table->integer('download_count')->default(0);
    $table->timestamps();
});

// Migration 10: create_growstart_partner_providers_table
Schema::create('growstart_partner_providers', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->enum('category', ['accountant', 'designer', 'pacra_agent', 'marketing', 'legal', 'supplier']);
    $table->text('description')->nullable();
    $table->string('contact_phone')->nullable();
    $table->string('contact_email')->nullable();
    $table->string('website')->nullable();
    $table->string('province');
    $table->string('city');
    $table->foreignId('country_id')->constrained('growstart_countries');
    $table->boolean('is_featured')->default(false);
    $table->boolean('is_verified')->default(false);
    $table->decimal('rating', 2, 1)->default(0);
    $table->timestamps();
    
    $table->index(['country_id', 'category', 'province']);
});

// Migration 11: create_growstart_journey_collaborators_table
Schema::create('growstart_journey_collaborators', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_journey_id')->constrained('growstart_user_journeys')->cascadeOnDelete();
    $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
    $table->string('email');
    $table->enum('role', ['co_founder', 'mentor', 'advisor']);
    $table->timestamp('invited_at');
    $table->timestamp('accepted_at')->nullable();
    $table->enum('status', ['pending', 'accepted', 'declined'])->default('pending');
    $table->timestamps();
    
    $table->unique(['user_journey_id', 'email']);
});

// Migration 12: create_growstart_financial_plans_table
Schema::create('growstart_financial_plans', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_journey_id')->constrained('growstart_user_journeys')->cascadeOnDelete();
    $table->decimal('startup_budget', 15, 2)->default(0);
    $table->decimal('monthly_expenses', 15, 2)->default(0);
    $table->string('pricing_strategy')->nullable();
    $table->integer('break_even_units')->nullable();
    $table->integer('break_even_months')->nullable();
    $table->json('funding_sources')->nullable();
    $table->text('notes')->nullable();
    $table->timestamps();
    
    $table->unique('user_journey_id');
});

// Migration 13: create_growstart_resources_table
Schema::create('growstart_resources', function (Blueprint $table) {
    $table->id();
    $table->string('title');
    $table->text('description')->nullable();
    $table->enum('type', ['guide', 'video', 'pdf', 'link']);
    $table->string('url')->nullable();
    $table->string('file_path')->nullable();
    $table->foreignId('stage_id')->nullable()->constrained('growstart_stages')->nullOnDelete();
    $table->foreignId('industry_id')->nullable()->constrained('growstart_industries')->nullOnDelete();
    $table->foreignId('country_id')->nullable()->constrained('growstart_countries')->nullOnDelete();
    $table->boolean('is_premium')->default(false);
    $table->integer('view_count')->default(0);
    $table->timestamps();
});

// Migration 14: create_growstart_offline_packs_table
Schema::create('growstart_offline_packs', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->text('description')->nullable();
    $table->string('file_path');
    $table->bigInteger('file_size'); // bytes
    $table->string('version');
    $table->foreignId('country_id')->constrained('growstart_countries');
    $table->boolean('is_premium')->default(false);
    $table->integer('download_count')->default(0);
    $table->timestamps();
});

// Migration 15: create_growstart_activity_logs_table
Schema::create('growstart_activity_logs', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_journey_id')->constrained('growstart_user_journeys')->cascadeOnDelete();
    $table->string('action'); // task_completed, stage_advanced, badge_earned, etc.
    $table->string('subject_type')->nullable();
    $table->unsignedBigInteger('subject_id')->nullable();
    $table->json('metadata')->nullable();
    $table->timestamps();
    
    $table->index(['user_journey_id', 'created_at']);
});
```

```

### Entity Relationship Diagram

```
┌──────────────────┐     ┌─────────────────────┐     ┌────────────────────┐
│ growstart_users  │────<│ growstart_user_     │>────│ growstart_         │
│ (users table)    │     │ journeys            │     │ industries         │
└──────────────────┘     └─────────────────────┘     └────────────────────┘
                                   │
                    ┌──────────────┼──────────────┐
                    ↓              ↓              ↓
          ┌─────────────────┐ ┌─────────────┐ ┌─────────────────────┐
          │ growstart_user_ │ │ growstart_  │ │ growstart_journey_  │
          │ tasks           │ │ user_badges │ │ collaborators       │
          └─────────────────┘ └─────────────┘ └─────────────────────┘
                    │              │
                    ↓              ↓
          ┌─────────────────┐ ┌─────────────┐
          │ growstart_tasks │ │ growstart_  │
          └─────────────────┘ │ badges      │
                    │         └─────────────┘
                    ↓
          ┌─────────────────┐
          │ growstart_      │
          │ stages          │
          └─────────────────┘
```

---

## A.5 Country Pack Architecture

### Directory Structure

```
app/Infrastructure/GrowStart/CountryPacks/
├── BaseCountryPack.php           # Abstract base class
├── CountryPackLoader.php         # Pack loading service
├── CountryPackInterface.php      # Interface contract
└── Zambia/
    ├── ZambiaPackProvider.php    # Main provider class
    ├── config.json               # Country configuration
    ├── regulatory/
    │   ├── pacra.json           # PACRA registration steps
    │   ├── zra.json             # ZRA tax registration
    │   ├── napsa.json           # NAPSA registration
    │   └── licenses.json        # Industry licenses
    ├── templates/
    │   ├── business_plan.docx
    │   ├── invoice_template.xlsx
    │   └── budget_template.xlsx
    ├── providers/
    │   └── providers.json       # Local service providers
    └── content/
        ├── startup_costs.json   # Industry startup costs
        └── bank_accounts.json   # Bank account requirements
```

### Country Pack Interface

```php
// app/Infrastructure/GrowStart/CountryPacks/CountryPackInterface.php
interface CountryPackInterface
{
    public function getCode(): string;
    public function getName(): string;
    public function getCurrency(): string;
    public function getCurrencySymbol(): string;
    public function getRegulatorySteps(string $category): array;
    public function getLicenses(?string $industry = null): array;
    public function getTemplates(?string $category = null): array;
    public function getProviders(?string $category = null): array;
    public function getStartupCosts(string $industry): array;
    public function getBankRequirements(): array;
}
```

### Zambia Pack Implementation

```php
// app/Infrastructure/GrowStart/CountryPacks/Zambia/ZambiaPackProvider.php
class ZambiaPackProvider implements CountryPackInterface
{
    private array $config;
    private string $basePath;
    
    public function __construct()
    {
        $this->basePath = __DIR__;
        $this->config = json_decode(
            file_get_contents($this->basePath . '/config.json'), 
            true
        );
    }
    
    public function getCode(): string { return 'ZMB'; }
    public function getName(): string { return 'Zambia'; }
    public function getCurrency(): string { return 'ZMW'; }
    public function getCurrencySymbol(): string { return 'K'; }
    
    public function getRegulatorySteps(string $category): array
    {
        $file = $this->basePath . "/regulatory/{$category}.json";
        return file_exists($file) 
            ? json_decode(file_get_contents($file), true) 
            : [];
    }
}
```

### Future Country Expansion

```
Phase 2 Countries (Malawi, Botswana):
├── Malawi/
│   ├── MalawiPackProvider.php
│   ├── regulatory/
│   │   ├── registrar_general.json
│   │   └── mra.json              # Malawi Revenue Authority
│   └── ...
└── Botswana/
    ├── BotswanaPackProvider.php
    ├── regulatory/
    │   ├── cipa.json             # Companies & IP Authority
    │   └── burs.json             # Revenue Service
    └── ...
```

---

## A.6 Integration Points

### GrowFinance Integration

```php
// app/Infrastructure/GrowStart/Integrations/GrowFinanceIntegration.php
class GrowFinanceIntegration
{
    // Sync financial plan to GrowFinance
    public function syncFinancialPlan(FinancialPlan $plan): void;
    
    // Import expense categories
    public function getExpenseCategories(): array;
    
    // Create initial chart of accounts
    public function setupChartOfAccounts(UserJourney $journey): void;
    
    // Link to GrowFinance dashboard
    public function getDashboardLink(User $user): string;
}
```

### BizBoost Integration

```php
// app/Infrastructure/GrowStart/Integrations/BizBoostIntegration.php
class BizBoostIntegration
{
    // Create marketing campaign from template
    public function createCampaignFromTemplate(string $template, UserJourney $journey): void;
    
    // Get branding materials
    public function getBrandingTemplates(): array;
    
    // Sync business profile
    public function syncBusinessProfile(UserJourney $journey): void;
    
    // Link to BizBoost dashboard
    public function getDashboardLink(User $user): string;
}
```

### GrowBiz Integration

```php
// app/Infrastructure/GrowStart/Integrations/GrowBizIntegration.php
class GrowBizIntegration
{
    // Create business profile
    public function createBusinessProfile(UserJourney $journey): void;
    
    // Sync tasks to GrowBiz task manager
    public function syncTasks(UserJourney $journey): void;
    
    // Get operational templates
    public function getOperationalTemplates(): array;
    
    // Link to GrowBiz dashboard
    public function getDashboardLink(User $user): string;
}
```

---

# B. Development Tasks

## B.1 Backend Tasks

### B.1.1 Database & Models (Priority: Critical)

| ID | Task | Description | Expected Output | Dependencies | Difficulty |
|----|------|-------------|-----------------|--------------|------------|
| BE-001 | Create migrations | All 15 migration files for GrowStart tables | Migration files in database/migrations/ | None | Medium |
| BE-002 | Create Eloquent models | 15 model classes with relationships | Models in app/Infrastructure/GrowStart/Persistence/Eloquent/ | BE-001 | Medium |
| BE-003 | Create model factories | Factories for testing | Factories in database/factories/GrowStart/ | BE-002 | Easy |
| BE-004 | Create seeders | Seed stages, industries, badges, Zambia data | Seeders in database/seeders/GrowStart/ | BE-002 | Medium |
| BE-005 | Create repository interfaces | Repository contracts in domain layer | Interfaces in app/Domain/GrowStart/Repositories/ | None | Easy |
| BE-006 | Implement repositories | Eloquent repository implementations | Classes in app/Infrastructure/GrowStart/Persistence/Repositories/ | BE-002, BE-005 | Medium |

### B.1.2 Domain Services (Priority: High)

| ID | Task | Description | Expected Output | Dependencies | Difficulty |
|----|------|-------------|-----------------|--------------|------------|
| BE-007 | JourneyProgressService | Calculate progress, next tasks, timeline | Service class with unit tests | BE-006 | Hard |
| BE-008 | RoadmapGeneratorService | Generate industry-specific roadmaps | Service class with unit tests | BE-006 | Hard |
| BE-009 | CountryPackService | Load and manage country packs | Service class with unit tests | BE-010 | Medium |
| BE-010 | Zambia country pack | Complete Zambia regulatory content | JSON files and provider class | None | Medium |
| BE-011 | TaskCompletionService | Handle task completion logic | Service class with unit tests | BE-006 | Medium |
| BE-012 | BadgeAwardService | Check and award badges | Service class with unit tests | BE-006 | Medium |
| BE-013 | CollaborationService | Invite/manage collaborators | Service class with unit tests | BE-006 | Medium |
| BE-014 | FinancialPlanningService | Budget, pricing, break-even calculations | Service class with unit tests | BE-006 | Hard |

### B.1.3 Controllers & API (Priority: High)

| ID | Task | Description | Expected Output | Dependencies | Difficulty |
|----|------|-------------|-----------------|--------------|------------|
| BE-015 | DashboardController | Main dashboard data endpoint | Controller with tests | BE-007 | Medium |
| BE-016 | JourneyController | CRUD for user journeys | Controller with tests | BE-007, BE-008 | Medium |
| BE-017 | StageController | Stage listing and details | Controller with tests | BE-006 | Easy |
| BE-018 | TaskController | Task management endpoints | Controller with tests | BE-011 | Medium |
| BE-019 | RoadmapController | Roadmap generation/customization | Controller with tests | BE-008 | Medium |
| BE-020 | TemplateController | Template listing/download | Controller with tests | BE-006 | Easy |
| BE-021 | ProviderController | Provider directory endpoints | Controller with tests | BE-006 | Easy |
| BE-022 | BadgeController | Badge listing/progress | Controller with tests | BE-012 | Easy |
| BE-023 | CollaboratorController | Collaboration management | Controller with tests | BE-013 | Medium |
| BE-024 | FinancialPlanController | Financial planning endpoints | Controller with tests | BE-014 | Medium |
| BE-025 | OfflinePackController | Offline pack downloads | Controller with tests | BE-006 | Easy |

### B.1.4 Integration Tasks (Priority: Medium)

| ID | Task | Description | Expected Output | Dependencies | Difficulty |
|----|------|-------------|-----------------|--------------|------------|
| BE-026 | GrowFinance integration | Connect to GrowFinance module | Integration service | BE-014 | Medium |
| BE-027 | BizBoost integration | Connect to BizBoost module | Integration service | BE-006 | Medium |
| BE-028 | GrowBiz integration | Connect to GrowBiz module | Integration service | BE-006 | Medium |
| BE-029 | Notification system | Email/push notifications | Notification classes | BE-011, BE-012 | Medium |
| BE-030 | Activity logging | Log user actions | Event listeners | BE-006 | Easy |


---

## B.2 Frontend Tasks

### B.2.1 Core Pages (Priority: Critical)

| ID | Task | Description | Expected Output | Dependencies | Difficulty |
|----|------|-------------|-----------------|--------------|------------|
| FE-001 | GrowStartLayout | Main layout component with navigation | Layout component | None | Medium |
| FE-002 | Dashboard page | Main dashboard with progress overview | Dashboard.vue | FE-001 | Hard |
| FE-003 | Onboarding wizard | 3-step onboarding flow | Onboarding/Index.vue + steps | FE-001 | Hard |
| FE-004 | Journey overview | Stage cards with progress | Journey/Index.vue | FE-001 | Medium |
| FE-005 | Stage detail page | Tasks list for a stage | Journey/Stage.vue | FE-004 | Medium |
| FE-006 | Task detail modal | Task instructions and completion | Journey/TaskDetail.vue | FE-005 | Medium |

### B.2.2 Feature Pages (Priority: High)

| ID | Task | Description | Expected Output | Dependencies | Difficulty |
|----|------|-------------|-----------------|--------------|------------|
| FE-007 | Visual roadmap | Interactive journey visualization | Roadmap/Index.vue | FE-004 | Hard |
| FE-008 | Timeline view | Projected vs actual timeline | Roadmap/Timeline.vue | FE-007 | Medium |
| FE-009 | Template library | Browse and download templates | Templates/Index.vue | FE-001 | Medium |
| FE-010 | Provider directory | Search local providers | Directory/Index.vue | FE-001 | Medium |
| FE-011 | Provider detail | Provider profile page | Directory/ProviderDetail.vue | FE-010 | Easy |
| FE-012 | Badges page | Achievements and progress | Badges/Index.vue | FE-001 | Medium |
| FE-013 | Collaboration page | Team management | Collaboration/Index.vue | FE-001 | Medium |
| FE-014 | Regulatory guides | Zambia compliance content | Regulatory/Index.vue | FE-001 | Medium |

### B.2.3 Financial Planning Pages (Priority: High)

| ID | Task | Description | Expected Output | Dependencies | Difficulty |
|----|------|-------------|-----------------|--------------|------------|
| FE-015 | Financial hub | Financial planning overview | Financial/Index.vue | FE-001 | Medium |
| FE-016 | Budget calculator | Startup budget tool | Financial/Budget.vue | FE-015 | Hard |
| FE-017 | Pricing calculator | Pricing strategy tool | Financial/Pricing.vue | FE-015 | Medium |
| FE-018 | Break-even analysis | Break-even calculator | Financial/BreakEven.vue | FE-015 | Medium |

### B.2.4 Components (Priority: High)

| ID | Task | Description | Expected Output | Dependencies | Difficulty |
|----|------|-------------|-----------------|--------------|------------|
| FE-019 | ProgressCard | Overall progress display | Dashboard/ProgressCard.vue | None | Easy |
| FE-020 | StageProgress | Stage completion indicator | Dashboard/StageProgress.vue | None | Easy |
| FE-021 | NextTasks | Upcoming tasks widget | Dashboard/NextTasks.vue | None | Easy |
| FE-022 | WeeklyGoals | Weekly targets widget | Dashboard/WeeklyGoals.vue | None | Easy |
| FE-023 | StageCard | Stage summary card | Journey/StageCard.vue | None | Easy |
| FE-024 | TaskItem | Task list item | Journey/TaskItem.vue | None | Easy |
| FE-025 | TaskCheckbox | Completion checkbox | Journey/TaskCheckbox.vue | None | Easy |
| FE-026 | RoadmapVisual | Interactive roadmap | Roadmap/RoadmapVisual.vue | None | Hard |
| FE-027 | BadgeCard | Badge display | Badges/BadgeCard.vue | None | Easy |
| FE-028 | ProviderCard | Provider listing card | Directory/ProviderCard.vue | None | Easy |
| FE-029 | TemplateCard | Template preview card | Templates/TemplateCard.vue | None | Easy |
| FE-030 | BudgetTable | Budget breakdown table | Financial/BudgetTable.vue | None | Medium |
| FE-031 | BreakEvenChart | Break-even visualization | Financial/BreakEvenChart.vue | None | Medium |

### B.2.5 Composables & State (Priority: Medium)

| ID | Task | Description | Expected Output | Dependencies | Difficulty |
|----|------|-------------|-----------------|--------------|------------|
| FE-032 | useJourney | Journey state management | Composables/GrowStart/useJourney.ts | None | Medium |
| FE-033 | useProgress | Progress calculations | Composables/GrowStart/useProgress.ts | None | Medium |
| FE-034 | useTasks | Task management | Composables/GrowStart/useTasks.ts | None | Medium |
| FE-035 | useRoadmap | Roadmap data | Composables/GrowStart/useRoadmap.ts | None | Medium |
| FE-036 | useFinancial | Financial tools | Composables/GrowStart/useFinancial.ts | None | Medium |
| FE-037 | TypeScript types | All GrowStart interfaces | types/growstart.ts | None | Medium |


---

## B.3 UI/UX Tasks

| ID | Task | Description | Expected Output | Dependencies | Difficulty |
|----|------|-------------|-----------------|--------------|------------|
| UX-001 | Design system | GrowStart color palette, typography | Design tokens | None | Medium |
| UX-002 | Onboarding wireframes | 3-step wizard flow | Figma/wireframes | None | Medium |
| UX-003 | Dashboard wireframes | Desktop and mobile layouts | Figma/wireframes | None | Medium |
| UX-004 | Journey flow wireframes | Stage navigation design | Figma/wireframes | None | Medium |
| UX-005 | Roadmap visualization | Interactive roadmap design | Figma/wireframes | None | Hard |
| UX-006 | Mobile-first responsive | Ensure mobile responsiveness | Responsive CSS | FE-001 | Medium |
| UX-007 | Empty states | Design empty state illustrations | SVG illustrations | None | Easy |
| UX-008 | Loading states | Skeleton loaders | Loading components | None | Easy |
| UX-009 | Badge icons | Design 6 achievement badges | SVG icons | None | Medium |
| UX-010 | Stage icons | Design 8 stage icons | SVG icons | None | Easy |

---

## B.4 Infrastructure Tasks

| ID | Task | Description | Expected Output | Dependencies | Difficulty |
|----|------|-------------|-----------------|--------------|------------|
| INF-001 | Route definitions | GrowStart routes | routes/growstart.php | None | Easy |
| INF-002 | Service provider | GrowStart service provider | GrowStartServiceProvider.php | None | Easy |
| INF-003 | Config file | GrowStart configuration | config/growstart.php | None | Easy |
| INF-004 | PWA manifest | GrowStart PWA setup | public/growstart-manifest.json | None | Easy |
| INF-005 | Service worker | Offline capability | public/growstart-sw.js | None | Medium |
| INF-006 | File storage | Template/resource storage | Storage configuration | None | Easy |
| INF-007 | Queue jobs | Background processing | Job classes | None | Medium |
| INF-008 | Scheduled tasks | Weekly reminders, badge checks | Console commands | BE-029 | Easy |

---

## B.5 Testing Tasks

| ID | Task | Description | Expected Output | Dependencies | Difficulty |
|----|------|-------------|-----------------|--------------|------------|
| TEST-001 | Unit tests - Services | Test domain services | Pest test files | BE-007 to BE-014 | Medium |
| TEST-002 | Unit tests - Models | Test model relationships | Pest test files | BE-002 | Easy |
| TEST-003 | Feature tests - API | Test all API endpoints | Pest test files | BE-015 to BE-025 | Medium |
| TEST-004 | Feature tests - Journey | Test journey workflows | Pest test files | BE-016 | Medium |
| TEST-005 | Integration tests | Test integrations | Pest test files | BE-026 to BE-028 | Medium |
| TEST-006 | Browser tests | E2E testing | Dusk test files | All FE tasks | Hard |

---

# C. Sprint Plan

## Sprint Overview

| Sprint | Duration | Focus | Key Deliverables |
|--------|----------|-------|------------------|
| Sprint 1 | 2 weeks | Foundation | Database, models, basic API, layout |
| Sprint 2 | 2 weeks | Core Journey | Onboarding, journey, tasks, progress |
| Sprint 3 | 2 weeks | Features | Roadmap, templates, providers, badges |
| Sprint 4 | 2 weeks | Financial & Collab | Financial tools, collaboration |
| Sprint 5 | 2 weeks | Integration & Polish | App integrations, PWA, testing |
| Sprint 6 | 1 week | Launch Prep | Bug fixes, performance, documentation |

---

## Sprint 1: Foundation (Weeks 1-2)

### Objectives
- Set up database schema and models
- Create basic API structure
- Build main layout and navigation
- Implement Zambia country pack

### Tasks
| Category | Task IDs | Description |
|----------|----------|-------------|
| Backend | BE-001 to BE-006 | Migrations, models, repositories |
| Backend | BE-010 | Zambia country pack |
| Backend | INF-001 to INF-003 | Routes, provider, config |
| Frontend | FE-001, FE-037 | Layout, TypeScript types |
| UI/UX | UX-001 | Design system |

### Deliverables
- [ ] All 15 database tables created
- [ ] All Eloquent models with relationships
- [ ] Basic API routes registered
- [ ] GrowStart layout component
- [ ] Zambia regulatory content (PACRA, ZRA, NAPSA)

### Demo Ready
- Database schema complete
- Basic navigation working
- Zambia content viewable

---

## Sprint 2: Core Journey (Weeks 3-4)

### Objectives
- Complete onboarding wizard
- Build journey and task management
- Implement progress tracking
- Create dashboard

### Tasks
| Category | Task IDs | Description |
|----------|----------|-------------|
| Backend | BE-007, BE-008, BE-011 | Progress, roadmap, task services |
| Backend | BE-015 to BE-018 | Dashboard, journey, stage, task controllers |
| Frontend | FE-002 to FE-006 | Dashboard, onboarding, journey pages |
| Frontend | FE-019 to FE-025 | Dashboard and journey components |
| Frontend | FE-032 to FE-034 | Journey, progress, tasks composables |
| UI/UX | UX-002 to UX-004, UX-010 | Wireframes, stage icons |

### Deliverables
- [ ] Working onboarding wizard (3 steps)
- [ ] Journey overview with 8 stages
- [ ] Task completion functionality
- [ ] Progress tracking dashboard
- [ ] Industry-specific roadmap generation

### Demo Ready
- User can complete onboarding
- User can view journey stages
- User can complete tasks
- Progress updates in real-time

---

## Sprint 3: Features (Weeks 5-6)

### Objectives
- Build visual roadmap
- Implement template library
- Create provider directory
- Add badge system

### Tasks
| Category | Task IDs | Description |
|----------|----------|-------------|
| Backend | BE-009, BE-012 | Country pack service, badge service |
| Backend | BE-019 to BE-022 | Roadmap, template, provider, badge controllers |
| Frontend | FE-007 to FE-012 | Roadmap, templates, directory, badges pages |
| Frontend | FE-026 to FE-029 | Roadmap, badge, provider, template components |
| Frontend | FE-035 | Roadmap composable |
| UI/UX | UX-005, UX-007 to UX-009 | Roadmap design, empty states, badge icons |

### Deliverables
- [ ] Interactive visual roadmap
- [ ] Timeline view with projections
- [ ] Template library with downloads
- [ ] Provider directory with search/filter
- [ ] Badge system with 6 achievements

### Demo Ready
- Visual roadmap navigation
- Template downloads working
- Provider search functional
- Badges awarded on completion

---

## Sprint 4: Financial & Collaboration (Weeks 7-8)

### Objectives
- Build financial planning tools
- Implement collaboration features
- Add regulatory guides
- Create offline packs

### Tasks
| Category | Task IDs | Description |
|----------|----------|-------------|
| Backend | BE-013, BE-014 | Collaboration, financial services |
| Backend | BE-023 to BE-025 | Collaborator, financial, offline controllers |
| Frontend | FE-013 to FE-018 | Collaboration, regulatory, financial pages |
| Frontend | FE-030, FE-031 | Budget table, break-even chart |
| Frontend | FE-036 | Financial composable |
| UI/UX | UX-006 | Mobile responsiveness |

### Deliverables
- [ ] Startup budget calculator
- [ ] Pricing strategy tool
- [ ] Break-even analysis with chart
- [ ] Collaborator invitation system
- [ ] Regulatory guides (PACRA, ZRA, NAPSA)
- [ ] Offline pack downloads

### Demo Ready
- Financial tools functional
- Invite collaborators via email
- View Zambia regulatory guides
- Download offline resources

---

## Sprint 5: Integration & Polish (Weeks 9-10)

### Objectives
- Integrate with GrowFinance, BizBoost, GrowBiz
- Implement PWA features
- Add notifications
- Complete testing

### Tasks
| Category | Task IDs | Description |
|----------|----------|-------------|
| Backend | BE-026 to BE-030 | Integrations, notifications, logging |
| Infrastructure | INF-004 to INF-008 | PWA, storage, queues, scheduled tasks |
| Testing | TEST-001 to TEST-005 | Unit, feature, integration tests |
| UI/UX | UX-008 | Loading states |

### Deliverables
- [ ] GrowFinance integration (sync financial plan)
- [ ] BizBoost integration (marketing templates)
- [ ] GrowBiz integration (business profile)
- [ ] PWA with offline capability
- [ ] Email notifications (reminders, badges)
- [ ] 80%+ test coverage

### Demo Ready
- Cross-app navigation working
- PWA installable
- Notifications sending
- All tests passing

---

## Sprint 6: Launch Prep (Week 11)

### Objectives
- Bug fixes and polish
- Performance optimization
- Documentation
- Launch preparation

### Tasks
| Category | Task IDs | Description |
|----------|----------|-------------|
| Testing | TEST-006 | Browser/E2E tests |
| All | - | Bug fixes from testing |
| All | - | Performance optimization |
| All | - | Documentation updates |

### Deliverables
- [ ] All critical bugs fixed
- [ ] Performance optimized (< 3s load time)
- [ ] User documentation complete
- [ ] Admin documentation complete
- [ ] Launch checklist complete

### Demo Ready
- Production-ready application
- Full user journey testable
- All features working

---

# D. Feature Breakdown

## D.1 Startup Journey Map

### Overview
8-stage journey from idea to growth with customized tasks per industry.

### Backend Logic
```php
// Services involved
JourneyProgressService::calculateOverallProgress()
JourneyProgressService::calculateStageProgress()
JourneyProgressService::canAdvanceToStage()
RoadmapGeneratorService::generateForIndustry()

// Key endpoints
GET  /api/growstart/journey           // Get current journey
POST /api/growstart/journey           // Start new journey
GET  /api/growstart/journey/progress  // Get progress summary
```

### Frontend Components
- `Journey/Index.vue` - Stage overview grid
- `Journey/Stage.vue` - Single stage with tasks
- `Journey/StageCard.vue` - Stage summary card
- `Journey/StageTimeline.vue` - Visual timeline

### Data Needed
- 8 stages (seeded): idea, validation, planning, registration, launch, accounting, marketing, growth
- Tasks per stage (50-100 total)
- Industry-specific task variations

### Workflow
```
1. User completes onboarding → Journey created
2. User views Journey/Index → See all 8 stages
3. User clicks stage → View tasks for that stage
4. User completes tasks → Progress updates
5. All tasks done → Stage marked complete
6. User advances to next stage
```

### API Structure
```typescript
// GET /api/growstart/journey
{
  journey: Journey,
  stages: Stage[],
  progress: {
    overall: number,
    byStage: Record<string, number>,
    tasksCompleted: number,
    totalTasks: number
  }
}
```

---

## D.2 Industry Templates

### Overview
Pre-built roadmaps for 8+ industries with Zambia-specific content.

### Backend Logic
```php
// Services
RoadmapGeneratorService::generateForIndustry()
RoadmapGeneratorService::customizeRoadmap()
CountryPackService::getStartupCosts()

// Key endpoints
GET  /api/growstart/industries              // List industries
GET  /api/growstart/industries/{slug}       // Industry details
POST /api/growstart/roadmap/generate        // Generate roadmap
PUT  /api/growstart/roadmap/customize       // Customize roadmap
```

### Frontend Components
- `Onboarding/SelectIndustry.vue` - Industry selection
- `Roadmap/Generator.vue` - Roadmap generation
- `Roadmap/Customize.vue` - Customization interface

### Data Needed
```json
// Industries (seeded)
[
  { "slug": "agriculture", "name": "Agriculture", "icon": "🌾" },
  { "slug": "retail", "name": "Retail", "icon": "🏪" },
  { "slug": "writing", "name": "Writing & Academic Services", "icon": "✍️" },
  { "slug": "transport", "name": "Transport", "icon": "🚗" },
  { "slug": "beauty", "name": "Beauty & Fashion", "icon": "💄" },
  { "slug": "construction", "name": "Construction", "icon": "🏗️" },
  { "slug": "fintech", "name": "Mobile Money & Fintech", "icon": "💳" },
  { "slug": "online", "name": "Online Businesses", "icon": "💻" }
]
```

### Workflow
```
1. User selects industry in onboarding
2. System generates industry-specific roadmap
3. User can customize tasks (add/remove/reorder)
4. Roadmap saved to user journey
```

---

## D.3 Milestones & Tasks

### Overview
Actionable checklists with completion tracking and notes.

### Backend Logic
```php
// Services
TaskCompletionService::completeTask()
TaskCompletionService::skipTask()
TaskCompletionService::addNotes()
BadgeAwardService::checkAndAwardBadges()

// Key endpoints
GET  /api/growstart/stages/{slug}/tasks  // Tasks for stage
POST /api/growstart/tasks/{id}/complete  // Complete task
POST /api/growstart/tasks/{id}/skip      // Skip task
PUT  /api/growstart/tasks/{id}/notes     // Update notes
```

### Frontend Components
- `Journey/TaskList.vue` - Task list view
- `Journey/TaskItem.vue` - Single task row
- `Journey/TaskCheckbox.vue` - Completion toggle
- `Journey/TaskDetail.vue` - Task modal with instructions
- `Journey/TaskNotes.vue` - Notes editor

### Data Needed
```php
// Task structure
[
  'stage_id' => 4, // Registration
  'industry_id' => null, // All industries
  'country_id' => 1, // Zambia
  'title' => 'Register business with PACRA',
  'description' => 'Register your business name and company',
  'instructions' => 'Step-by-step PACRA guide...',
  'external_link' => 'https://www.pacra.org.zm',
  'estimated_hours' => 4,
  'is_required' => true
]
```

### Workflow
```
1. User views stage tasks
2. User clicks task → See details/instructions
3. User marks task complete → Progress updates
4. System checks for badge eligibility
5. Badge awarded if criteria met
```

---

## D.4 Progress Tracking Dashboard

### Overview
Visual dashboard showing overall progress, stage completion, and weekly goals.

### Backend Logic
```php
// Services
JourneyProgressService::calculateOverallProgress()
JourneyProgressService::getWeeklyGoals()
JourneyProgressService::getTimelineStatus()
JourneyProgressService::getNextTasks()

// Key endpoints
GET /api/growstart/journey/progress     // Full progress data
GET /api/growstart/journey/weekly-goals // Weekly targets
GET /api/growstart/journey/timeline     // Timeline status
```

### Frontend Components
- `Dashboard.vue` - Main dashboard page
- `Dashboard/ProgressCard.vue` - Overall progress circle
- `Dashboard/StageProgress.vue` - Stage completion bars
- `Dashboard/NextTasks.vue` - Upcoming tasks widget
- `Dashboard/WeeklyGoals.vue` - Weekly targets
- `Dashboard/RecentBadges.vue` - Latest achievements
- `Dashboard/QuickActions.vue` - Action buttons

### Data Needed
```typescript
interface DashboardData {
  journey: Journey;
  progress: {
    overall: number;        // 0-100
    byStage: StageProgress[];
    tasksCompleted: number;
    totalTasks: number;
    daysActive: number;
    estimatedDaysRemaining: number;
  };
  weeklyGoals: WeeklyGoal[];
  nextTasks: Task[];
  recentBadges: Badge[];
  timeline: {
    startDate: string;
    targetDate: string;
    projectedDate: string;
    isOnTrack: boolean;
  };
}
```

### Workflow
```
1. User opens GrowStart → Dashboard loads
2. See overall progress percentage
3. See stage-by-stage completion
4. View weekly goals and next tasks
5. Click task → Navigate to task detail
6. Click stage → Navigate to stage view
```

---

## D.5 Zambia Regulatory Module

### Overview
Comprehensive Zambia-specific business registration and compliance guides.

### Backend Logic
```php
// Services
CountryPackService::loadPack('ZMB')
CountryPackService::getRegulatorySteps('pacra')
CountryPackService::getLicenses('retail')

// Key endpoints
GET /api/growstart/regulatory           // List all guides
GET /api/growstart/regulatory/pacra     // PACRA guide
GET /api/growstart/regulatory/zra       // ZRA guide
GET /api/growstart/regulatory/napsa     // NAPSA guide
GET /api/growstart/licenses             // Industry licenses
```

### Frontend Components
- `Regulatory/Index.vue` - Guide listing
- `Regulatory/Guide.vue` - Single guide view
- `Regulatory/StepList.vue` - Step-by-step instructions
- `Regulatory/FeeTable.vue` - Fee breakdown
- `Regulatory/DocumentList.vue` - Required documents

### Data Needed (Zambia Pack)
```json
// regulatory/pacra.json
{
  "title": "PACRA Business Registration",
  "description": "Register your business with Patents and Companies Registration Agency",
  "steps": [
    {
      "order": 1,
      "title": "Name Search",
      "description": "Search for available business names",
      "documents": ["NRC copy", "Application form"],
      "estimatedTime": "1-2 days",
      "fee": { "amount": 50, "currency": "ZMW" }
    }
  ],
  "fees": [
    { "name": "Name reservation", "amount": 50, "currency": "ZMW" },
    { "name": "Company registration", "amount": 550, "currency": "ZMW" }
  ],
  "links": [
    { "title": "PACRA Website", "url": "https://www.pacra.org.zm", "type": "official" }
  ]
}
```

### Workflow
```
1. User views regulatory guides
2. Select guide (PACRA, ZRA, NAPSA)
3. View step-by-step instructions
4. See fees and required documents
5. Click external links for official sites
```

---

## D.6 Financial & Resource Planning

### Overview
Built-in tools for startup budgeting, pricing, and break-even analysis.

### Backend Logic
```php
// Services
FinancialPlanningService::calculateStartupBudget()
FinancialPlanningService::calculateBreakEven()
FinancialPlanningService::suggestPricing()

// Key endpoints
GET  /api/growstart/financial                  // Get plan
POST /api/growstart/financial                  // Save plan
GET  /api/growstart/financial/budget-estimate  // Industry estimate
POST /api/growstart/financial/break-even       // Calculate break-even
GET  /api/growstart/financial/pricing          // Pricing suggestions
```

### Frontend Components
- `Financial/Index.vue` - Financial hub
- `Financial/Budget.vue` - Budget calculator
- `Financial/Pricing.vue` - Pricing tool
- `Financial/BreakEven.vue` - Break-even analysis
- `Financial/BudgetTable.vue` - Budget breakdown
- `Financial/BreakEvenChart.vue` - Visual chart

### Data Needed
```typescript
interface FinancialPlan {
  startupBudget: number;
  monthlyExpenses: number;
  pricingStrategy: 'cost_plus' | 'market_based' | 'value_based';
  breakEvenUnits: number;
  breakEvenMonths: number;
  fundingSources: FundingSource[];
  budgetItems: BudgetItem[];
}

interface BudgetItem {
  category: string;
  item: string;
  amount: number;
  isOneTime: boolean;
}
```

### Workflow
```
1. User opens Financial hub
2. Start with budget calculator
3. Enter startup costs by category
4. System suggests industry averages
5. Move to pricing calculator
6. Enter costs, desired margin
7. System calculates suggested prices
8. Run break-even analysis
9. See chart with break-even point
```

---

## D.7 App Integrations

### Overview
Seamless integration with GrowFinance, BizBoost, and GrowBiz.

### Backend Logic
```php
// Integration services
GrowFinanceIntegration::syncFinancialPlan()
GrowFinanceIntegration::setupChartOfAccounts()
BizBoostIntegration::createCampaignFromTemplate()
BizBoostIntegration::syncBusinessProfile()
GrowBizIntegration::createBusinessProfile()
GrowBizIntegration::syncTasks()

// Key endpoints
POST /api/growstart/integrations/growfinance/sync
POST /api/growstart/integrations/bizboost/sync
POST /api/growstart/integrations/growbiz/sync
GET  /api/growstart/integrations/status
```

### Frontend Components
- `Dashboard/IntegrationCards.vue` - Integration status
- `Settings/Integrations.vue` - Integration settings
- Quick action buttons to navigate to other apps

### Data Needed
```typescript
interface IntegrationStatus {
  growfinance: {
    connected: boolean;
    lastSync: string | null;
    features: string[];
  };
  bizboost: {
    connected: boolean;
    lastSync: string | null;
    features: string[];
  };
  growbiz: {
    connected: boolean;
    lastSync: string | null;
    features: string[];
  };
}
```

### Workflow
```
1. User reaches Accounting stage
2. Prompt to connect GrowFinance
3. Sync financial plan to GrowFinance
4. User reaches Marketing stage
5. Prompt to connect BizBoost
6. Access marketing templates
7. Throughout journey, sync to GrowBiz
```

---

## D.8 Collaboration/Mentor Sharing

### Overview
Invite co-founders, mentors, and advisors to view/contribute to journey.

### Backend Logic
```php
// Services
CollaborationService::inviteCollaborator()
CollaborationService::acceptInvitation()
CollaborationService::getCollaboratorPermissions()

// Key endpoints
GET    /api/growstart/collaborators           // List collaborators
POST   /api/growstart/collaborators/invite    // Send invitation
DELETE /api/growstart/collaborators/{id}      // Remove collaborator
POST   /api/growstart/collaborators/{id}/accept
PUT    /api/growstart/collaborators/{id}/role
```

### Frontend Components
- `Collaboration/Index.vue` - Team management
- `Collaboration/Invite.vue` - Invitation modal
- `Collaboration/CollaboratorList.vue` - Team list
- `Collaboration/InviteForm.vue` - Invitation form

### Data Needed
```typescript
interface Collaborator {
  id: number;
  email: string;
  role: 'co_founder' | 'mentor' | 'advisor';
  status: 'pending' | 'accepted' | 'declined';
  permissions: {
    canEdit: boolean;
    canComment: boolean;
    canViewFinancials: boolean;
  };
}
```

### Workflow
```
1. User opens Collaboration page
2. Click "Invite" → Enter email, select role
3. System sends invitation email
4. Invitee clicks link → Accepts invitation
5. Collaborator can view journey
6. Co-founders can edit, mentors can comment
```

---

## D.9 Local Service Provider Directory

### Overview
Curated list of affordable local service providers in Zambia.

### Backend Logic
```php
// Services
CountryPackService::getProviders()

// Key endpoints
GET  /api/growstart/providers              // List with filters
GET  /api/growstart/providers/{id}         // Provider details
GET  /api/growstart/providers/categories   // Category list
POST /api/growstart/providers/{id}/contact // Send inquiry
```

### Frontend Components
- `Directory/Index.vue` - Provider listing
- `Directory/ProviderDetail.vue` - Provider profile
- `Directory/ProviderCard.vue` - Listing card
- `Directory/ProviderFilter.vue` - Filter controls
- `Directory/ContactModal.vue` - Contact form

### Data Needed
```json
// providers.json (Zambia)
[
  {
    "name": "ABC Accounting Services",
    "category": "accountant",
    "description": "Small business accounting and tax services",
    "contact_phone": "+260 97X XXX XXX",
    "contact_email": "info@example.com",
    "province": "Lusaka",
    "city": "Lusaka",
    "is_verified": true,
    "rating": 4.5
  }
]
```

### Workflow
```
1. User opens Provider Directory
2. Filter by category, province
3. Browse provider cards
4. Click provider → View details
5. Click "Contact" → Send inquiry
```

---

## D.10 Offline Packs

### Overview
Downloadable resources for offline access.

### Backend Logic
```php
// Key endpoints
GET /api/growstart/offline-packs              // List packs
GET /api/growstart/offline-packs/{id}/download // Download pack
```

### Frontend Components
- `Templates/OfflinePacks.vue` - Pack listing
- `Templates/DownloadButton.vue` - Download action

### Data Needed
```typescript
interface OfflinePack {
  id: number;
  name: string;
  description: string;
  fileSize: number; // bytes
  version: string;
  contents: string[]; // List of included items
  isPremium: boolean;
}
```

### Packs Available
1. Starter Guide (PDF) - Free
2. Business Plan Templates (DOCX) - Free
3. Marketing Basics Handbook (PDF) - Free
4. Bookkeeping Spreadsheets (XLSX) - Premium
5. Complete Zambia Compliance Pack - Premium

---

# E. UI/UX Flows

## E.1 User Onboarding Flow

```
┌─────────────────────────────────────────────────────────────────┐
│                        ONBOARDING WIZARD                         │
├─────────────────────────────────────────────────────────────────┤
│                                                                  │
│  Step 1: Select Industry                                        │
│  ┌──────────┐ ┌──────────┐ ┌──────────┐ ┌──────────┐           │
│  │ 🌾       │ │ 🏪       │ │ ✍️       │ │ 🚗       │           │
│  │Agriculture│ │ Retail   │ │ Writing  │ │Transport │           │
│  └──────────┘ └──────────┘ └──────────┘ └──────────┘           │
│  ┌──────────┐ ┌──────────┐ ┌──────────┐ ┌──────────┐           │
│  │ 💄       │ │ 🏗️       │ │ 💳       │ │ 💻       │           │
│  │ Beauty   │ │Construct.│ │ Fintech  │ │ Online   │           │
│  └──────────┘ └──────────┘ └──────────┘ └──────────┘           │
│                                                                  │
│  [Back]                                            [Next →]      │
├─────────────────────────────────────────────────────────────────┤
│                                                                  │
│  Step 2: Business Details                                       │
│  ┌─────────────────────────────────────────────────────────┐   │
│  │ Business Name: [________________________]                │   │
│  │ Country: [Zambia ▼]                                     │   │
│  │ Province: [Lusaka ▼]                                    │   │
│  │ Brief Description: [________________________]           │   │
│  └─────────────────────────────────────────────────────────┘   │
│                                                                  │
│  [← Back]                                          [Next →]      │
├─────────────────────────────────────────────────────────────────┤
│                                                                  │
│  Step 3: Set Goals                                              │
│  ┌─────────────────────────────────────────────────────────┐   │
│  │ Target Launch Date: [📅 Select Date]                    │   │
│  │                                                          │   │
│  │ What's your current stage?                              │   │
│  │ ○ Just an idea                                          │   │
│  │ ○ Planning phase                                        │   │
│  │ ○ Ready to register                                     │   │
│  │ ○ Already registered, need guidance                     │   │
│  └─────────────────────────────────────────────────────────┘   │
│                                                                  │
│  [← Back]                                    [Start Journey →]   │
└─────────────────────────────────────────────────────────────────┘
```

---

## E.2 Settings Page Flow

```
┌─────────────────────────────────────────────────────────────────┐
│  ⚙️ Journey Settings                                            │
├─────────────────────────────────────────────────────────────────┤
│                                                                  │
│  Business Information                                           │
│  ┌─────────────────────────────────────────────────────────┐   │
│  │ Business Name: [My Retail Shop        ] [Edit]          │   │
│  │ Industry: Retail                                        │   │
│  │ Country: Zambia                                         │   │
│  │ Started: December 10, 2025                              │   │
│  └─────────────────────────────────────────────────────────┘   │
│                                                                  │
│  Timeline                                                       │
│  ┌─────────────────────────────────────────────────────────┐   │
│  │ Target Launch: [📅 March 15, 2026    ] [Update]         │   │
│  │ Current Stage: Registration                             │   │
│  │ Days Active: 45                                         │   │
│  └─────────────────────────────────────────────────────────┘   │
│                                                                  │
│  Journey Actions                                                │
│  ┌─────────────────────────────────────────────────────────┐   │
│  │ [⏸️ Pause Journey]  [🔄 Reset Progress]  [🗑️ Delete]    │   │
│  └─────────────────────────────────────────────────────────┘   │
│                                                                  │
│  Notifications                                                  │
│  ┌─────────────────────────────────────────────────────────┐   │
│  │ ☑️ Weekly progress reminders                            │   │
│  │ ☑️ Badge earned notifications                           │   │
│  │ ☑️ Collaborator activity                                │   │
│  └─────────────────────────────────────────────────────────┘   │
│                                                                  │
└─────────────────────────────────────────────────────────────────┘
```

---

## E.3 Roadmap View Flow

```
┌─────────────────────────────────────────────────────────────────┐
│  🗺️ Your Startup Roadmap                          [Timeline ▼]  │
├─────────────────────────────────────────────────────────────────┤
│                                                                  │
│  ┌─────┐     ┌─────┐     ┌─────┐     ┌─────┐                   │
│  │ 💡  │────▶│ ✓   │────▶│ 📋  │────▶│ 📝  │                   │
│  │IDEA │     │VALID│     │PLAN │     │ REG │ ◀── You are here  │
│  │100% │     │100% │     │100% │     │ 45% │                   │
│  └─────┘     └─────┘     └─────┘     └─────┘                   │
│                                          │                      │
│                                          ▼                      │
│  ┌─────┐     ┌─────┐     ┌─────┐     ┌─────┐                   │
│  │ 📈  │◀────│ 📣  │◀────│ 💰  │◀────│ 🚀  │                   │
│  │GROW │     │MKTG │     │ACCT │     │LNCH │                   │
│  │ 0%  │     │ 0%  │     │ 0%  │     │ 0%  │                   │
│  └─────┘     └─────┘     └─────┘     └─────┘                   │
│                                                                  │
│  ─────────────────────────────────────────────────────────────  │
│                                                                  │
│  📊 Timeline View                                               │
│  ┌─────────────────────────────────────────────────────────┐   │
│  │ Dec 2025  │  Jan 2026  │  Feb 2026  │  Mar 2026        │   │
│  │ ████████  │  ████████  │  ████░░░░  │  ░░░░░░░░        │   │
│  │ Idea+Val  │  Planning  │  Register  │  Launch          │   │
│  │           │            │  ◀─ Today  │  ◀─ Target       │   │
│  └─────────────────────────────────────────────────────────┘   │
│                                                                  │
│  ⚠️ You're 3 days behind schedule. [Adjust Timeline]            │
│                                                                  │
└─────────────────────────────────────────────────────────────────┘
```

---

## E.4 Milestone Editing Flow

```
┌─────────────────────────────────────────────────────────────────┐
│  📝 Registration Stage                              45% Complete │
├─────────────────────────────────────────────────────────────────┤
│                                                                  │
│  Required Tasks                                                 │
│  ┌─────────────────────────────────────────────────────────┐   │
│  │ ☑️ Register business with PACRA                    ✓    │   │
│  │    Completed Dec 15, 2025                               │   │
│  ├─────────────────────────────────────────────────────────┤   │
│  │ ☑️ Obtain TPIN from ZRA                            ✓    │   │
│  │    Completed Dec 18, 2025                               │   │
│  ├─────────────────────────────────────────────────────────┤   │
│  │ ☐ Register for NAPSA                               →    │   │
│  │    Est. 2 hours • Required                              │   │
│  ├─────────────────────────────────────────────────────────┤   │
│  │ ☐ Open business bank account                       →    │   │
│  │    Est. 4 hours • Required                              │   │
│  └─────────────────────────────────────────────────────────┘   │
│                                                                  │
│  Optional Tasks                                                 │
│  ┌─────────────────────────────────────────────────────────┐   │
│  │ ☐ Register domain name                             →    │   │
│  │    Est. 1 hour • Optional                               │   │
│  └─────────────────────────────────────────────────────────┘   │
│                                                                  │
│  [+ Add Custom Task]                                            │
│                                                                  │
└─────────────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────────────┐
│  Task Detail Modal                                    [✕ Close] │
├─────────────────────────────────────────────────────────────────┤
│                                                                  │
│  📝 Register for NAPSA                                          │
│                                                                  │
│  Register your business and employees with the National         │
│  Pension Scheme Authority for social security contributions.    │
│                                                                  │
│  ─────────────────────────────────────────────────────────────  │
│                                                                  │
│  📋 Instructions                                                │
│  1. Visit NAPSA office or online portal                        │
│  2. Complete employer registration form                         │
│  3. Submit NRC copies of directors                             │
│  4. Submit PACRA certificate                                   │
│  5. Receive NAPSA registration number                          │
│                                                                  │
│  📎 Required Documents                                          │
│  • PACRA Certificate                                           │
│  • Directors' NRC copies                                       │
│  • Company resolution                                          │
│                                                                  │
│  🔗 [Visit NAPSA Website →]                                     │
│                                                                  │
│  ─────────────────────────────────────────────────────────────  │
│                                                                  │
│  📝 My Notes                                                    │
│  ┌─────────────────────────────────────────────────────────┐   │
│  │ Need to get company resolution signed first...          │   │
│  │                                                          │   │
│  └─────────────────────────────────────────────────────────┘   │
│                                                                  │
│  [Skip Task]                              [✓ Mark as Complete]  │
│                                                                  │
└─────────────────────────────────────────────────────────────────┘
```

---

## E.5 Progress Dashboard Flow

```
┌─────────────────────────────────────────────────────────────────┐
│  👋 Welcome back, Sarah!                                        │
│  My Retail Shop • Retail                                        │
├─────────────────────────────────────────────────────────────────┤
│                                                                  │
│  ┌─────────────────────┐  ┌─────────────────────────────────┐  │
│  │                     │  │  Stage Progress                  │  │
│  │    ╭───────────╮    │  │  ┌────────────────────────────┐ │  │
│  │    │           │    │  │  │ Idea        ████████████ ✓│ │  │
│  │    │    45%    │    │  │  │ Validation  ████████████ ✓│ │  │
│  │    │           │    │  │  │ Planning    ████████████ ✓│ │  │
│  │    ╰───────────╯    │  │  │ Registration████████░░░░  │ │  │
│  │                     │  │  │ Launch      ░░░░░░░░░░░░  │ │  │
│  │  Overall Progress   │  │  │ Accounting  ░░░░░░░░░░░░  │ │  │
│  │  18/40 tasks done   │  │  │ Marketing   ░░░░░░░░░░░░  │ │  │
│  │                     │  │  │ Growth      ░░░░░░░░░░░░  │ │  │
│  └─────────────────────┘  │  └────────────────────────────┘ │  │
│                           └─────────────────────────────────┘  │
│                                                                  │
│  ┌─────────────────────────────┐  ┌─────────────────────────┐  │
│  │  📋 Next Tasks              │  │  🎯 Weekly Goals        │  │
│  │  ┌───────────────────────┐  │  │  ┌───────────────────┐  │  │
│  │  │ ☐ Register for NAPSA  │  │  │  │ ☑️ Complete PACRA │  │  │
│  │  │ ☐ Open bank account   │  │  │  │ ☑️ Get TPIN       │  │  │
│  │  │ ☐ Get council permit  │  │  │  │ ☐ NAPSA register  │  │  │
│  │  └───────────────────────┘  │  │  └───────────────────┘  │  │
│  │  [View All Tasks →]         │  │  2/3 completed          │  │
│  └─────────────────────────────┘  └─────────────────────────┘  │
│                                                                  │
│  ┌─────────────────────────────┐  ┌─────────────────────────┐  │
│  │  🏆 Recent Badges           │  │  ⚡ Quick Actions       │  │
│  │  ┌─────┐ ┌─────┐ ┌─────┐   │  │  ┌─────────────────────┐│  │
│  │  │ 🎯  │ │ 📋  │ │ ✅  │   │  │  │ [📋 View Roadmap]  ││  │
│  │  │Idea │ │Plan │ │Reg  │   │  │  │ [📄 Templates]     ││  │
│  │  └─────┘ └─────┘ └─────┘   │  │  │ [👥 Providers]     ││  │
│  │  [View All Badges →]        │  │  └─────────────────────┘│  │
│  └─────────────────────────────┘  └─────────────────────────┘  │
│                                                                  │
└─────────────────────────────────────────────────────────────────┘
```

---

# F. Developer Notes

## F.1 Recommended Libraries

### Backend (PHP/Laravel)

| Library | Purpose | Version |
|---------|---------|---------|
| `spatie/laravel-permission` | Role/permission management | ^6.0 |
| `spatie/laravel-activitylog` | Activity logging | ^4.0 |
| `maatwebsite/excel` | Excel exports | ^3.1 |
| `barryvdh/laravel-dompdf` | PDF generation | ^2.0 |
| `intervention/image` | Image processing | ^3.0 |
| `laravel/sanctum` | API authentication | ^4.0 |

### Frontend (Vue/TypeScript)

| Library | Purpose | Version |
|---------|---------|---------|
| `@vueuse/core` | Vue composition utilities | ^10.0 |
| `chart.js` | Charts and graphs | ^4.0 |
| `vue-chartjs` | Vue Chart.js wrapper | ^5.0 |
| `@heroicons/vue` | Icons | ^2.0 |
| `lucide-vue-next` | Additional icons | ^0.300 |
| `sweetalert2` | Alerts and modals | ^11.0 |
| `date-fns` | Date utilities | ^3.0 |

---

## F.2 Component Naming Conventions

### Vue Components

```
Pattern: [Domain][Feature][Type].vue

Examples:
- GrowStartDashboard.vue          # Page
- GrowStartLayout.vue             # Layout
- JourneyStageCard.vue            # Component
- TaskCompletionCheckbox.vue      # Component
- ProgressCircleChart.vue         # Component
- ProviderFilterDropdown.vue      # Component
```

### File Organization

```
resources/js/
├── Pages/GrowStart/              # Page components (routed)
│   └── [Feature]/[Page].vue
├── Components/GrowStart/         # Reusable components
│   └── [Feature]/[Component].vue
├── Composables/GrowStart/        # Composition functions
│   └── use[Feature].ts
└── types/
    └── growstart.ts              # TypeScript interfaces
```

### Naming Rules

1. Pages: PascalCase, match route name
2. Components: PascalCase, descriptive
3. Composables: camelCase, prefix with `use`
4. Types: PascalCase for interfaces, camelCase for type aliases
5. Props: camelCase
6. Events: kebab-case with `@` prefix

---

## F.3 Country Module Structure

### Adding a New Country

```php
// 1. Create country pack directory
app/Infrastructure/GrowStart/CountryPacks/[CountryName]/

// 2. Implement provider class
class [CountryName]PackProvider implements CountryPackInterface
{
    // Implement all interface methods
}

// 3. Create content files
[CountryName]/
├── config.json           # Country configuration
├── regulatory/           # Regulatory guides
│   ├── [agency1].json
│   └── [agency2].json
├── templates/            # Document templates
├── providers/            # Service providers
│   └── providers.json
└── content/              # Additional content
    └── startup_costs.json

// 4. Register in service provider
$this->app->bind(
    CountryPackInterface::class,
    fn() => new [CountryName]PackProvider()
);

// 5. Seed country data
php artisan db:seed --class=GrowStart[CountryName]Seeder
```

### Country Pack JSON Schema

```json
// config.json
{
  "code": "ZMB",
  "name": "Zambia",
  "currency": "ZMW",
  "currency_symbol": "K",
  "languages": ["en"],
  "regulatory_agencies": ["pacra", "zra", "napsa", "zda"],
  "provinces": ["Lusaka", "Copperbelt", "..."],
  "version": "1.0.0"
}
```

---

## F.4 Scalability Guidelines

### Database Optimization

```php
// 1. Use eager loading
$journey = UserJourney::with([
    'industry',
    'country',
    'currentStage',
    'userTasks.task'
])->find($id);

// 2. Index frequently queried columns
$table->index(['user_id', 'status']);
$table->index(['stage_id', 'industry_id', 'country_id']);

// 3. Use chunking for large datasets
Task::chunk(100, function ($tasks) {
    // Process tasks
});
```

### Caching Strategy

```php
// 1. Cache country packs (rarely change)
Cache::remember("country_pack_{$code}", 86400, fn() => 
    $this->countryPackService->loadPack($code)
);

// 2. Cache user progress (invalidate on task completion)
Cache::tags(['journey', "user_{$userId}"])
    ->remember("progress_{$journeyId}", 3600, fn() =>
        $this->progressService->calculate($journey)
    );

// 3. Cache static content
Cache::rememberForever('industries', fn() =>
    Industry::where('is_active', true)->get()
);
```

### Queue Jobs

```php
// 1. Badge checking (after task completion)
dispatch(new CheckBadgeEligibility($journey));

// 2. Notification sending
dispatch(new SendProgressReminder($user));

// 3. Integration syncing
dispatch(new SyncToGrowFinance($financialPlan));
```

---

## F.5 API Response Standards

### Success Response

```json
{
  "success": true,
  "data": {
    "journey": { ... },
    "progress": { ... }
  },
  "meta": {
    "timestamp": "2025-12-10T12:00:00Z"
  }
}
```

### Error Response

```json
{
  "success": false,
  "error": {
    "code": "JOURNEY_NOT_FOUND",
    "message": "No active journey found for this user",
    "details": null
  },
  "meta": {
    "timestamp": "2025-12-10T12:00:00Z"
  }
}
```

### Pagination Response

```json
{
  "success": true,
  "data": [ ... ],
  "meta": {
    "current_page": 1,
    "per_page": 15,
    "total": 45,
    "last_page": 3
  }
}
```

---

## F.6 Testing Guidelines

### Unit Tests (Services)

```php
// tests/Unit/GrowStart/JourneyProgressServiceTest.php
it('calculates overall progress correctly', function () {
    $journey = UserJourney::factory()
        ->has(UserTask::factory()->count(10)->state(['status' => 'completed']))
        ->has(UserTask::factory()->count(10)->state(['status' => 'pending']))
        ->create();
    
    $service = new JourneyProgressService();
    $progress = $service->calculateOverallProgress($journey);
    
    expect($progress)->toBe(50.0);
});
```

### Feature Tests (API)

```php
// tests/Feature/GrowStart/JourneyApiTest.php
it('creates a new journey', function () {
    $user = User::factory()->create();
    
    $response = $this->actingAs($user)
        ->postJson('/api/growstart/journey', [
            'industry_id' => 1,
            'country_id' => 1,
            'business_name' => 'My Test Business'
        ]);
    
    $response->assertStatus(201)
        ->assertJsonPath('data.journey.business_name', 'My Test Business');
});
```

---

## F.7 Security Considerations

### Authorization

```php
// 1. Use policies for journey access
class JourneyPolicy
{
    public function view(User $user, UserJourney $journey): bool
    {
        return $user->id === $journey->user_id 
            || $journey->collaborators()->where('user_id', $user->id)->exists();
    }
}

// 2. Validate collaborator permissions
public function canEditJourney(User $user, UserJourney $journey): bool
{
    $collaborator = $journey->collaborators()
        ->where('user_id', $user->id)
        ->first();
    
    return $collaborator?->role === 'co_founder';
}
```

### Input Validation

```php
// Use Form Requests
class StartJourneyRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'industry_id' => 'required|exists:growstart_industries,id',
            'country_id' => 'required|exists:growstart_countries,id',
            'business_name' => 'required|string|max:255',
            'target_launch_date' => 'nullable|date|after:today',
        ];
    }
}
```

---

## F.8 Performance Targets

| Metric | Target | Measurement |
|--------|--------|-------------|
| Dashboard load time | < 2s | Time to interactive |
| API response time | < 500ms | 95th percentile |
| Task completion | < 300ms | API response |
| Roadmap render | < 1s | First contentful paint |
| Offline pack download | < 30s | For 10MB pack |
| Mobile performance | > 80 | Lighthouse score |

---

## F.9 Deployment Checklist

### Pre-Launch

- [ ] All migrations run successfully
- [ ] Seeders populate required data
- [ ] Zambia country pack complete
- [ ] All API endpoints tested
- [ ] PWA manifest configured
- [ ] Service worker registered
- [ ] SSL certificate installed
- [ ] Environment variables set
- [ ] Queue worker running
- [ ] Scheduled tasks configured

### Post-Launch Monitoring

- [ ] Error tracking (Sentry/Bugsnag)
- [ ] Performance monitoring
- [ ] User analytics
- [ ] API usage metrics
- [ ] Database query monitoring

---

## Changelog

### December 10, 2025
- Initial development specification created
- Complete architecture documented
- All tasks defined and organized into sprints
- Feature breakdowns completed
- UI/UX flows designed
- Developer notes added
