/**
 * GrowStart TypeScript Type Definitions
 */

export interface Journey {
    id: number;
    user_id: number;
    industry_id: number;
    country_id: number;
    business_name: string;
    business_description: string | null;
    current_stage_id: number;
    started_at: string;
    target_launch_date: string | null;
    status: JourneyStatus;
    is_premium: boolean;
    province: string | null;
    city: string | null;
    days_active: number;
    is_on_track: boolean;
    created_at: string;
    updated_at: string;
}

export type JourneyStatus = 'active' | 'paused' | 'completed' | 'archived';

export interface JourneyProgress {
    overall: number;
    stage_progress: StageProgress[];
    tasks_completed: number;
    total_tasks: number;
    days_active: number;
    estimated_days_remaining: number | null;
}

export interface StageProgress {
    stage_id: number;
    slug: StageSlug;
    name: string;
    completed: number;
    total: number;
    percentage: number;
}

export interface Stage {
    id: number;
    name: string;
    slug: StageSlug;
    description: string | null;
    order: number;
    icon: string | null;
    color: string | null;
    estimated_days: number;
    is_active: boolean;
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
    stage_id: number;
    industry_id: number | null;
    country_id: number | null;
    title: string;
    description: string | null;
    instructions: string | null;
    external_link: string | null;
    estimated_hours: number;
    order: number;
    is_required: boolean;
    is_premium: boolean;
    user_task?: UserTask;
}

export interface UserTask {
    id: number;
    user_journey_id: number;
    task_id: number;
    status: TaskStatus;
    started_at: string | null;
    completed_at: string | null;
    notes: string | null;
    attachments: string[];
    duration_hours: number | null;
    created_at: string;
    updated_at: string;
}

export type TaskStatus = 'pending' | 'in_progress' | 'completed' | 'skipped';

export interface Industry {
    id: number;
    name: string;
    slug: string;
    description: string | null;
    icon: string | null;
    is_active: boolean;
    estimated_startup_cost_min: number | null;
    estimated_startup_cost_max: number | null;
    sort_order: number;
}

export interface Country {
    id: number;
    name: string;
    code: string;
    currency: string;
    currency_symbol: string;
    is_active: boolean;
    pack_version: string | null;
}

export interface Badge {
    id: number;
    name: string;
    slug: string;
    description: string | null;
    icon: string;
    criteria_type: BadgeCriteria;
    criteria_value: string | null;
    points: number;
    earned?: boolean;
    earned_at?: string;
}

export type BadgeCriteria =
    | 'stage_complete'
    | 'tasks_complete'
    | 'streak_days'
    | 'journey_complete';

export interface Template {
    id: number;
    name: string;
    description: string | null;
    category: TemplateCategory;
    file_path: string;
    file_type: string | null;
    industry_id: number | null;
    country_id: number | null;
    is_premium: boolean;
    download_count: number;
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
    description: string | null;
    contact_phone: string | null;
    contact_email: string | null;
    website: string | null;
    province: string;
    city: string;
    country_id: number;
    is_featured: boolean;
    is_verified: boolean;
    rating: number;
    review_count: number;
}

export type ProviderCategory =
    | 'accountant'
    | 'designer'
    | 'pacra_agent'
    | 'marketing'
    | 'legal'
    | 'supplier'
    | 'consultant'
    | 'other';

export interface Timeline {
    start_date: string;
    target_date: string | null;
    days_active: number;
    estimated_days_remaining: number | null;
    is_on_track: boolean;
    projected_completion_date: string | null;
}

export interface WeeklyGoal {
    id: number;
    title: string;
    task_id: number | null;
    is_completed: boolean;
    due_date: string;
}

// Onboarding form data
export interface OnboardingData {
    industry_id: number;
    country_id: number;
    business_name: string;
    business_description?: string;
    target_launch_date?: string;
    province?: string;
    city?: string;
}

// Dashboard props
export interface DashboardProps {
    journey: Journey;
    progress: JourneyProgress;
    timeline: Timeline;
    nextTasks: UserTask[];
    weeklyGoals: WeeklyGoal[];
    stages: Stage[];
    recentBadges: Badge[];
}

// Stage page props
export interface StagePageProps {
    journey: Journey;
    stage: Stage;
    tasks: Task[];
    progress: number;
}
