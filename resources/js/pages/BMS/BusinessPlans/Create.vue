<script setup lang="ts">
import { Head, router, usePage } from '@inertiajs/vue3';
import CMSLayout from '@/Layouts/CMSLayout.vue';
import { ref, computed, watch, nextTick } from 'vue';
import { useToast } from '@/composables/useToast';
import { useAlert } from '@/composables/useAlert';

const { toast } = useToast();
const { confirm } = useAlert();

const props = defineProps<{ existingPlan: any | null; userTier: string }>();
const page = usePage();

const totalSteps = 20;
const currentStep = ref(props.existingPlan?.current_step || 1);
const saving = ref(false);
const autoSaving = ref(false);
const lastSavedAt = ref<string | null>(null);
const generatingField = ref<string | null>(null);
const generatingAll = ref(false);
const generatedContent = ref<string | null>(null);
const currentAIField = ref<string | null>(null);
let autoSaveTimer: ReturnType<typeof setTimeout> | null = null;

// Fields that have an AI button (text content fields, not numbers / metadata)
const aiFields: string[] = [
    'tagline', 'mission_statement', 'vision_statement', 'core_values', 'business_objectives',
    'company_history', 'long_term_goals', 'success_factors', 'background',
    'industry_trends', 'regulations', 'technology_changes',
    'problem_statement', 'existing_alternatives', 'why_existing_fail', 'solution_description',
    'competitive_advantage', 'swot_strengths', 'swot_weaknesses', 'swot_opportunities', 'swot_threats',
    'customer_pain_points', 'product_description', 'delivery_method', 'product_lifecycle',
    'future_improvements', 'structured_products', 'product_features', 'pricing_strategy',
    'revenue_streams', 'cost_structure', 'customer_relationships', 'channels', 'key_activities',
    'key_resources', 'key_partners', 'business_model_canvas', 'unique_selling_points',
    'production_process', 'resource_requirements', 'target_market', 'customer_demographics',
    'customer_personas', 'market_size', 'surveys_data', 'interviews_data',
    'competitor_pricing_data', 'customer_feedback_information', 'swot_from_research',
    'competitors', 'structured_competitors', 'competitive_analysis', 'marketing_channels',
    'promotion_channels', 'branding_approach', 'brand_voice', 'sales_funnel', 'sales_channels',
    'customer_retention', 'sales_process', 'sales_targets', 'crm_process',
    'daily_operations', 'facilities', 'technology_stack', 'quality_control',
    'staff_roles', 'organizational_chart_data', 'departments_data', 'key_staff',
    'hiring_plan', 'recruitment_strategy', 'employee_benefits', 'training_plan',
    'performance_management', 'equipment_tools', 'supplier_list', 'operational_workflow',
    'financial_projections', 'break_even_analysis', 'funding_requirements',
    'profit_loss_projection', 'cash_flow_projection', 'balance_sheet_projection',
    'financial_ratios', 'scenario_planning_data', 'risks', 'structured_risks',
    'mitigation_strategies', 'timeline', 'milestones', 'exit_strategy_details', 'appendices',
];

// Wizard state
const wizardStep = ref(1);
const wizardErrors = ref<Record<string, string>>({});
const wizGoal = ref('');
const stages = [
    { val: 'idea', label: 'Idea' },
    { val: 'startup', label: 'Startup' },
    { val: 'growth', label: 'Growth' },
    { val: 'expansion', label: 'Expansion' },
];

const STORAGE_KEY = 'bizplan_draft_' + (props.existingPlan?.id || 'new');

const saveToLocalStorage = () => {
    try {
        const data = { ...form.value, _savedAt: Date.now() };
        localStorage.setItem(STORAGE_KEY, JSON.stringify(data));
    } catch {}
};

const loadFromLocalStorage = () => {
    try {
        const raw = localStorage.getItem(STORAGE_KEY);
        if (!raw) return;
        const stored = JSON.parse(raw);
        if (!stored || !stored.business_name) return;
        // For new plans, clear stale data and start fresh
        if (!props.existingPlan) {
            clearLocalStorage();
            return;
        }
        const serverTime = props.existingPlan?.updated_at ? new Date(props.existingPlan.updated_at).getTime() : 0;
        if (stored._savedAt > serverTime) {
            Object.assign(form.value, stored);
            if (stored.current_step) currentStep.value = stored.current_step;
        }
    } catch {}
};

const clearLocalStorage = () => {
    try { localStorage.removeItem(STORAGE_KEY); } catch {}
};

loadFromLocalStorage();

const industries = ['Agriculture', 'Construction', 'Education', 'Energy', 'Fashion', 'Finance', 'Food & Beverage', 'Healthcare', 'Hospitality', 'Information Technology', 'Manufacturing', 'Media & Entertainment', 'Mining', 'Real Estate', 'Retail', 'Telecommunications', 'Transportation & Logistics', 'Tourism', 'Other'];

const steps = [
    { num: 1, label: 'Business Profile', short: 'Profile', icon: 'Building' },
    { num: 2, label: 'Company Description', short: 'Company', icon: 'FileText' },
    { num: 3, label: 'Problem Statement', short: 'Problem', icon: 'AlertTriangle' },
    { num: 4, label: 'Products & Services', short: 'Products', icon: 'Package' },
    { num: 5, label: 'Product Features & Production', short: 'Features', icon: 'Settings' },
    { num: 6, label: 'Pricing & Business Model', short: 'Pricing', icon: 'DollarSign' },
    { num: 7, label: 'Market Analysis', short: 'Market', icon: 'TrendingUp' },
    { num: 8, label: 'Market Research', short: 'Research', icon: 'Search' },
    { num: 9, label: 'Competitor Analysis', short: 'Competitors', icon: 'Users' },
    { num: 10, label: 'SWOT Analysis', short: 'SWOT', icon: 'Grid' },
    { num: 11, label: 'Unique Selling Points', short: 'USP', icon: 'Star' },
    { num: 12, label: 'Marketing Strategy', short: 'Marketing', icon: 'Megaphone' },
    { num: 13, label: 'Sales Strategy', short: 'Sales', icon: 'CreditCard' },
    { num: 14, label: 'Operations Plan', short: 'Ops', icon: 'Tool' },
    { num: 15, label: 'Organization & HR', short: 'HR', icon: 'Briefcase' },
    { num: 16, label: 'Risk Analysis', short: 'Risks', icon: 'Shield' },
    { num: 17, label: 'Implementation Roadmap', short: 'Roadmap', icon: 'Calendar' },
    { num: 18, label: 'Financial Plan', short: 'Finance', icon: 'BarChart' },
    { num: 19, label: 'Advanced Financials & Funding', short: 'Funding', icon: 'PieChart' },
    { num: 20, label: 'Exit Strategy & Review', short: 'Exit', icon: 'CheckCircle' },
];

const form = ref({
    id: props.existingPlan?.id || null,
    business_name: props.existingPlan?.business_name || '',
    tagline: props.existingPlan?.tagline || '',
    business_stage: props.existingPlan?.business_stage || '',
    industry: props.existingPlan?.industry || '',
    industry_size: props.existingPlan?.industry_size || '',
    growth_rate: props.existingPlan?.growth_rate || '',
    industry_trends: props.existingPlan?.industry_trends || '',
    regulations: props.existingPlan?.regulations || '',
    technology_changes: props.existingPlan?.technology_changes || '',
    country: props.existingPlan?.country || '',
    province: props.existingPlan?.province || '',
    city: props.existingPlan?.city || '',
    website: props.existingPlan?.website || '',
    date_established: props.existingPlan?.date_established || '',
    legal_structure: props.existingPlan?.legal_structure || '',
    registration_status: props.existingPlan?.registration_status || '',
    mission_statement: props.existingPlan?.mission_statement || '',
    vision_statement: props.existingPlan?.vision_statement || '',
    core_values: props.existingPlan?.core_values || '',
    business_objectives: props.existingPlan?.business_objectives || '',
    company_history: props.existingPlan?.company_history || '',
    long_term_goals: props.existingPlan?.long_term_goals || '',
    success_factors: props.existingPlan?.success_factors || '',
    background: props.existingPlan?.background || '',
    business_description: props.existingPlan?.business_description || '',
    wizard_completed: props.existingPlan?.wizard_completed ?? false,
    problem_statement: props.existingPlan?.problem_statement || '',
    existing_alternatives: props.existingPlan?.existing_alternatives || '',
    why_existing_fail: props.existingPlan?.why_existing_fail || '',
    solution_description: props.existingPlan?.solution_description || '',
    competitive_advantage: props.existingPlan?.competitive_advantage || '',
    swot_strengths: props.existingPlan?.swot_strengths || '',
    swot_weaknesses: props.existingPlan?.swot_weaknesses || '',
    swot_opportunities: props.existingPlan?.swot_opportunities || '',
    swot_threats: props.existingPlan?.swot_threats || '',
    customer_pain_points: props.existingPlan?.customer_pain_points || '',
    product_description: props.existingPlan?.product_description || '',
    delivery_method: props.existingPlan?.delivery_method || '',
    product_lifecycle: props.existingPlan?.product_lifecycle || '',
    future_improvements: props.existingPlan?.future_improvements || '',
    structured_products: props.existingPlan?.structured_products || '',
    product_features: props.existingPlan?.product_features || '',
    pricing_strategy: props.existingPlan?.pricing_strategy || '',
    revenue_streams: props.existingPlan?.revenue_streams || '',
    cost_structure: props.existingPlan?.cost_structure || '',
    customer_relationships: props.existingPlan?.customer_relationships || '',
    channels: props.existingPlan?.channels || '',
    key_activities: props.existingPlan?.key_activities || '',
    key_resources: props.existingPlan?.key_resources || '',
    key_partners: props.existingPlan?.key_partners || '',
    business_model_canvas: props.existingPlan?.business_model_canvas || '',
    unique_selling_points: props.existingPlan?.unique_selling_points || '',
    production_process: props.existingPlan?.production_process || '',
    resource_requirements: props.existingPlan?.resource_requirements || '',
    target_market: props.existingPlan?.target_market || '',
    customer_demographics: props.existingPlan?.customer_demographics || '',
    customer_personas: props.existingPlan?.customer_personas || '',
    market_size: props.existingPlan?.market_size || '',
    surveys_data: props.existingPlan?.surveys_data || '',
    interviews_data: props.existingPlan?.interviews_data || '',
    competitor_pricing_data: props.existingPlan?.competitor_pricing_data || '',
    customer_feedback_information: props.existingPlan?.customer_feedback_information || '',
    swot_from_research: props.existingPlan?.swot_from_research || '',
    competitors: props.existingPlan?.competitors || '',
    structured_competitors: props.existingPlan?.structured_competitors || '',
    competitive_analysis: props.existingPlan?.competitive_analysis || '',
    marketing_channels: props.existingPlan?.marketing_channels || '',
    promotion_channels: props.existingPlan?.promotion_channels || '',
    branding_approach: props.existingPlan?.branding_approach || '',
    brand_voice: props.existingPlan?.brand_voice || '',
    sales_funnel: props.existingPlan?.sales_funnel || '',
    sales_channels: props.existingPlan?.sales_channels || '',
    customer_retention: props.existingPlan?.customer_retention || '',
    sales_process: props.existingPlan?.sales_process || '',
    sales_targets: props.existingPlan?.sales_targets || '',
    crm_process: props.existingPlan?.crm_process || '',
    daily_operations: props.existingPlan?.daily_operations || '',
    facilities: props.existingPlan?.facilities || '',
    technology_stack: props.existingPlan?.technology_stack || '',
    quality_control: props.existingPlan?.quality_control || '',
    staff_roles: props.existingPlan?.staff_roles || '',
    organizational_chart_data: props.existingPlan?.organizational_chart_data || '',
    departments_data: props.existingPlan?.departments_data || '',
    key_staff: props.existingPlan?.key_staff || '',
    hiring_plan: props.existingPlan?.hiring_plan || '',
    recruitment_strategy: props.existingPlan?.recruitment_strategy || '',
    employee_benefits: props.existingPlan?.employee_benefits || '',
    training_plan: props.existingPlan?.training_plan || '',
    performance_management: props.existingPlan?.performance_management || '',
    equipment_tools: props.existingPlan?.equipment_tools || '',
    supplier_list: props.existingPlan?.supplier_list || '',
    operational_workflow: props.existingPlan?.operational_workflow || '',
    startup_costs: props.existingPlan?.startup_costs || null,
    monthly_operating_costs: props.existingPlan?.monthly_operating_costs || null,
    expected_monthly_revenue: props.existingPlan?.expected_monthly_revenue || null,
    price_per_unit: props.existingPlan?.price_per_unit || null,
    expected_sales_volume: props.existingPlan?.expected_sales_volume || null,
    staff_salaries: props.existingPlan?.staff_salaries || null,
    inventory_costs: props.existingPlan?.inventory_costs || null,
    utilities_costs: props.existingPlan?.utilities_costs || null,
    financial_projections: props.existingPlan?.financial_projections || '',
    break_even_analysis: props.existingPlan?.break_even_analysis || '',
    funding_requirements: props.existingPlan?.funding_requirements || '',
    profit_loss_projection: props.existingPlan?.profit_loss_projection || '',
    cash_flow_projection: props.existingPlan?.cash_flow_projection || '',
    balance_sheet_projection: props.existingPlan?.balance_sheet_projection || '',
    financial_ratios: props.existingPlan?.financial_ratios || '',
    scenario_planning_data: props.existingPlan?.scenario_planning_data || '',
    risks: props.existingPlan?.risks || '',
    structured_risks: props.existingPlan?.structured_risks || '',
    mitigation_strategies: props.existingPlan?.mitigation_strategies || '',
    timeline: props.existingPlan?.timeline || '',
    milestones: props.existingPlan?.milestones || '',
    exit_strategy_type: props.existingPlan?.exit_strategy_type || '',
    exit_strategy_details: props.existingPlan?.exit_strategy_details || '',
    appendices: props.existingPlan?.appendices || '',
    current_step: props.existingPlan?.current_step || 1,
    status: props.existingPlan?.status || 'draft',
    user_ai_instructions: '',
});

const progressPercent = computed(() => Math.round((currentStep.value / totalSteps) * 100));
const isPremium = computed(() => props.userTier === 'premium');
const monthlyProfit = computed(() => (form.value.expected_monthly_revenue || 0) - (form.value.monthly_operating_costs || 0) - (form.value.staff_salaries || 0));

watch(() => (page.props as any).flash?.generatedContent, (val: string | null) => {
    if (val) { generatedContent.value = val; }
});

watch(() => (page.props as any).flash?.businessPlan, (val: any | null) => {
    if (val?.id) {
        form.value.id = val.id;
        const newKey = 'bizplan_draft_' + val.id;
        if (STORAGE_KEY !== newKey) {
            const raw = localStorage.getItem(STORAGE_KEY);
            if (raw) { localStorage.setItem(newKey, raw); localStorage.removeItem(STORAGE_KEY); }
        }
    }
});

const triggerAutoSave = () => {
    saveToLocalStorage();
    if (autoSaveTimer) clearTimeout(autoSaveTimer);
    autoSaveTimer = setTimeout(async () => {
        if (!form.value.business_name) return;
        autoSaving.value = true;
        form.value.current_step = currentStep.value;
        try {
            const res = await fetch(route('cms.business-plans.save'), {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': (document.querySelector('meta[name=csrf-token]') as HTMLMetaElement)?.content ?? '' },
                body: JSON.stringify(form.value),
            });
            const data = await res.json();
            if (data.success && data.business_plan_id && data.business_plan_id !== form.value.id) {
                form.value.id = data.business_plan_id;
                router.replace(route('cms.business-plans.edit', form.value.id));
            }
            lastSavedAt.value = new Date().toLocaleTimeString();
        } catch {}
        autoSaving.value = false;
    }, 2000);
};

watch(form, triggerAutoSave, { deep: true });

const goToStep = (step: number) => {
    if (step >= 1 && step <= totalSteps) currentStep.value = step;
};

const wizardNext = (screen: number) => {
    const errs: Record<string, string> = {};
    if (screen === 1) {
        if (!form.value.business_name?.trim()) errs.business_name = 'Business name is required';
        if (!form.value.industry) errs.industry = 'Industry is required';
    } else if (screen === 2) {
        if (!form.value.legal_structure) errs.legal_structure = 'Legal structure is required';
    } else if (screen === 3) {
        if (!form.value.business_description?.trim()) errs.business_description = 'Business snapshot helps AI generate better content';
    }
    wizardErrors.value = errs;
    if (Object.keys(errs).length === 0) wizardStep.value = screen + 1;
};

const completeWizard = async () => {
    if (wizGoal.value?.trim()) form.value.business_objectives = wizGoal.value.trim();
    form.value.wizard_completed = true;
    await saveDraft();
    wizardStep.value = 1;
    wizardErrors.value = {};
    currentStep.value = 2;
};

const saveDraft = async () => {
    saving.value = true;
    saveToLocalStorage();
    form.value.current_step = currentStep.value;
    try {
        const res = await fetch(route('cms.business-plans.save'), {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': (document.querySelector('meta[name=csrf-token]') as HTMLMetaElement)?.content ?? '' },
            body: JSON.stringify(form.value),
        });
        const data = await res.json();
        if (data.success && data.business_plan_id) {
            if (data.business_plan_id !== form.value.id) {
                form.value.id = data.business_plan_id;
                router.replace(route('cms.business-plans.edit', form.value.id));
            }
        }
    } catch {}
    saving.value = false;
};

const nextStep = () => {
    if (currentStep.value < totalSteps) {
        saveDraft();
        currentStep.value++;
    }
};

const prevStep = () => {
    if (currentStep.value > 1) currentStep.value--;
};

const completePlan = () => {
    if (!form.value.id) return;
    clearLocalStorage();
    router.post(route('cms.business-plans.complete'), { id: form.value.id });
};

const generateAIContent = async (field: string) => {
    if (generatingField.value === field) return;
    generatingField.value = field;
    generatedContent.value = null;
    currentAIField.value = field;
    const ctx = { business_name: form.value.business_name, industry: form.value.industry, ...form.value };
    try {
        const res = await fetch(route('cms.business-plans.generate-ai'), {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': (document.querySelector('meta[name=csrf-token]') as HTMLMetaElement)?.content ?? '', 'X-Requested-With': 'XMLHttpRequest' },
            body: JSON.stringify({
                business_plan_id: form.value.id,
                field,
                context: ctx,
            }),
        });
        if (!res.ok) throw new Error('AI request failed (' + res.status + ')');
        const data = await res.json();
        if (data.success && data.content) {
            generatedContent.value = data.content;
            (form.value as any)[field] = data.content;
        } else if (data.error) {
            alert('AI error: ' + data.error);
        }
    } catch (e: any) {
        alert('Could not reach AI service: ' + (e?.message || 'unknown error'));
    } finally {
        generatingField.value = null;
    }
};

const generateAllFields = async () => {
    if (generatingAll.value) return;
    const confirmed = await confirm({
        title: 'Generate all empty fields?',
        message: 'This will call the AI for every empty content field. It may take a few minutes. Continue?',
        type: 'question',
    });
    if (!confirmed) return;
    generatingAll.value = true;
    const empty = aiFields.filter(f => !(form.value as any)[f] || (form.value as any)[f].toString().trim() === '');
    for (const f of empty) {
        try {
            const ctx = { business_name: form.value.business_name, industry: form.value.industry, ...form.value };
            const res = await fetch(route('cms.business-plans.generate-ai'), {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': (document.querySelector('meta[name=csrf-token]') as HTMLMetaElement)?.content ?? '', 'X-Requested-With': 'XMLHttpRequest' },
                body: JSON.stringify({ business_plan_id: form.value.id, field: f, context: ctx }),
            });
            const data = await res.json();
            if (data.success && data.content) {
                (form.value as any)[f] = data.content;
            }
        } catch {}
    }
    generatingAll.value = false;
};

const applyGenerated = (field: string) => {
    if (generatedContent.value) {
        (form.value as any)[field] = generatedContent.value;
        generatedContent.value = null;
        currentAIField.value = null;
    }
};

const clearField = async (field: string) => {
    const confirmed = await confirm({
        title: 'Clear field?',
        message: `This will erase the content of "${field.replace(/_/g, ' ')}".`,
        type: 'warning',
    });
    if (!confirmed) return;
    (form.value as any)[field] = '';
    saveToLocalStorage();
    form.value.current_step = currentStep.value;
    try {
        const res = await fetch(route('cms.business-plans.save'), {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': (document.querySelector('meta[name=csrf-token]') as HTMLMetaElement)?.content ?? '' },
            body: JSON.stringify(form.value),
        });
        const data = await res.json();
        if (data.success && data.business_plan_id) {
            form.value.id = data.business_plan_id;
        }
    } catch {}
};

const formatCurrency = (v: number | null) => v != null ? `K${Number(v).toLocaleString()}` : '-';

const chatMessages = ref<{role: string, content: string, field?: string}[]>([]);
const chatInput = ref('');
const chatLoading = ref(false);
const showChat = ref(false);

const sendChatMessage = () => {
    if (!chatInput.value.trim() || chatLoading.value) return;
    const msg = chatInput.value.trim();
    chatInput.value = '';
    chatMessages.value.push({ role: 'user', content: msg });
    chatLoading.value = true;
    router.post(route('cms.business-plans.chat'), {
        message: msg,
        context: { business_name: form.value.business_name, industry: form.value.industry, current_step: currentStep.value, current_step_label: steps[currentStep.value - 1]?.label || '', total_steps: totalSteps, ...form.value },
    }, {
        preserveScroll: true,
        preserveState: true,
        onSuccess: (res: any) => {
            chatLoading.value = false;
            const val = res.props?.flash?.chatResponse;
            if (!val) return;
            if (val.type === 'field' && val.field && val.content) {
                chatMessages.value.push({ role: 'assistant', content: val.content, field: val.field });
            } else {
                chatMessages.value.push({ role: 'assistant', content: val.content || 'Could you be more specific?' });
            }
        },
        onError: () => { chatLoading.value = false; chatMessages.value.push({ role: 'assistant', content: 'Request failed. Please try again.' }); },
        onFinish: () => { chatLoading.value = false; },
    });
};

const applyChatField = (field: string, content: string) => {
    (form.value as any)[field] = content;
    chatMessages.value.push({ role: 'assistant', content: `Applied to "${field.replace(/_/g, ' ')}"!`, field: undefined });
};

const handleChatKeydown = (e: KeyboardEvent) => {
    if (e.key === 'Enter' && !e.shiftKey) { e.preventDefault(); sendChatMessage(); }
};

const chatListRef = ref<HTMLElement | null>(null);
watch(chatMessages, () => {
    nextTick(() => { chatListRef.value?.scrollTo({ top: chatListRef.value.scrollHeight, behavior: 'smooth' }); });
}, { deep: true });

const showPreview = ref(false);

const currencyFields = new Set([
    'startup_costs', 'monthly_operating_costs', 'expected_monthly_revenue',
    'price_per_unit', 'expected_sales_volume', 'staff_salaries',
    'inventory_costs', 'utilities_costs',
]);

const stepFields: Record<number, { key: string; label: string }[]> = {
    1: [
        { key: 'business_name', label: 'Business Name' },
        { key: 'tagline', label: 'Tagline' },
        { key: 'business_stage', label: 'Business Stage' },
        { key: 'industry', label: 'Industry' },
        { key: 'business_description', label: 'Business Description' },
        { key: 'country', label: 'Country' },
        { key: 'province', label: 'Province' },
        { key: 'city', label: 'City' },
        { key: 'website', label: 'Website' },
        { key: 'date_established', label: 'Date Established' },
        { key: 'legal_structure', label: 'Legal Structure' },
        { key: 'registration_status', label: 'Registration Status' },
        { key: 'mission_statement', label: 'Mission Statement' },
        { key: 'vision_statement', label: 'Vision Statement' },
        { key: 'core_values', label: 'Core Values' },
        { key: 'business_objectives', label: 'Business Objectives' },
        { key: 'company_history', label: 'Company History' },
        { key: 'long_term_goals', label: 'Long-term Goals' },
        { key: 'success_factors', label: 'Success Factors' },
        { key: 'background', label: 'Background' },
        { key: 'industry_size', label: 'Industry Size' },
        { key: 'growth_rate', label: 'Growth Rate' },
        { key: 'industry_trends', label: 'Industry Trends' },
        { key: 'regulations', label: 'Regulations' },
        { key: 'technology_changes', label: 'Technology Changes' },
    ],
    2: [
        { key: 'problem_statement', label: 'Problem Statement' },
        { key: 'existing_alternatives', label: 'Existing Alternatives' },
        { key: 'why_existing_fail', label: 'Why Existing Solutions Fail' },
        { key: 'solution_description', label: 'Solution Description' },
        { key: 'competitive_advantage', label: 'Competitive Advantage' },
        { key: 'swot_strengths', label: 'SWOT - Strengths' },
        { key: 'swot_weaknesses', label: 'SWOT - Weaknesses' },
        { key: 'swot_opportunities', label: 'SWOT - Opportunities' },
        { key: 'swot_threats', label: 'SWOT - Threats' },
        { key: 'customer_pain_points', label: 'Customer Pain Points' },
    ],
    3: [
        { key: 'product_description', label: 'Product Description' },
        { key: 'delivery_method', label: 'Delivery Method' },
        { key: 'product_lifecycle', label: 'Product Lifecycle' },
        { key: 'future_improvements', label: 'Future Improvements' },
        { key: 'structured_products', label: 'Products Catalog' },
        { key: 'product_features', label: 'Product Features' },
        { key: 'pricing_strategy', label: 'Pricing Strategy' },
        { key: 'revenue_streams', label: 'Revenue Streams' },
        { key: 'cost_structure', label: 'Cost Structure' },
    ],
    4: [
        { key: 'customer_relationships', label: 'Customer Relationships' },
        { key: 'channels', label: 'Channels' },
        { key: 'key_activities', label: 'Key Activities' },
        { key: 'key_resources', label: 'Key Resources' },
        { key: 'key_partners', label: 'Key Partners' },
        { key: 'business_model_canvas', label: 'Business Model Canvas' },
        { key: 'unique_selling_points', label: 'Unique Selling Points' },
        { key: 'production_process', label: 'Production Process' },
        { key: 'resource_requirements', label: 'Resource Requirements' },
    ],
    5: [
        { key: 'target_market', label: 'Target Market' },
        { key: 'customer_demographics', label: 'Customer Demographics' },
        { key: 'customer_personas', label: 'Customer Personas' },
        { key: 'market_size', label: 'Market Size' },
        { key: 'surveys_data', label: 'Surveys Data' },
        { key: 'interviews_data', label: 'Interviews Data' },
        { key: 'competitor_pricing_data', label: 'Competitor Pricing Data' },
        { key: 'customer_feedback_information', label: 'Customer Feedback' },
        { key: 'swot_from_research', label: 'SWOT From Research' },
    ],
    6: [
        { key: 'competitors', label: 'Competitors' },
        { key: 'structured_competitors', label: 'Competitor Analysis' },
        { key: 'competitive_analysis', label: 'Competitive Analysis' },
        { key: 'marketing_channels', label: 'Marketing Channels' },
        { key: 'promotion_channels', label: 'Promotion Channels' },
        { key: 'branding_approach', label: 'Branding Approach' },
        { key: 'brand_voice', label: 'Brand Voice' },
    ],
    7: [
        { key: 'sales_funnel', label: 'Sales Funnel' },
        { key: 'sales_channels', label: 'Sales Channels' },
        { key: 'customer_retention', label: 'Customer Retention' },
        { key: 'sales_process', label: 'Sales Process' },
        { key: 'sales_targets', label: 'Sales Targets' },
        { key: 'crm_process', label: 'CRM Process' },
    ],
    8: [
        { key: 'daily_operations', label: 'Daily Operations' },
        { key: 'facilities', label: 'Facilities' },
        { key: 'technology_stack', label: 'Technology Stack' },
        { key: 'quality_control', label: 'Quality Control' },
    ],
    9: [
        { key: 'staff_roles', label: 'Staff Roles' },
        { key: 'organizational_chart_data', label: 'Organizational Chart' },
        { key: 'departments_data', label: 'Departments' },
        { key: 'key_staff', label: 'Key Staff' },
        { key: 'hiring_plan', label: 'Hiring Plan' },
        { key: 'recruitment_strategy', label: 'Recruitment Strategy' },
        { key: 'employee_benefits', label: 'Employee Benefits' },
        { key: 'training_plan', label: 'Training Plan' },
        { key: 'performance_management', label: 'Performance Management' },
    ],
    10: [
        { key: 'equipment_tools', label: 'Equipment & Tools' },
        { key: 'supplier_list', label: 'Supplier List' },
        { key: 'operational_workflow', label: 'Operational Workflow' },
    ],
    11: [], // USP — merged into step 4
    12: [
        { key: 'marketing_channels', label: 'Marketing Channels' },
        { key: 'promotion_channels', label: 'Promotion Channels' },
        { key: 'branding_approach', label: 'Branding Approach' },
        { key: 'brand_voice', label: 'Brand Voice' },
    ],
    13: [
        { key: 'sales_funnel', label: 'Sales Funnel' },
        { key: 'sales_channels', label: 'Sales Channels' },
        { key: 'customer_retention', label: 'Customer Retention' },
        { key: 'sales_process', label: 'Sales Process' },
        { key: 'sales_targets', label: 'Sales Targets' },
        { key: 'crm_process', label: 'CRM Process' },
    ],
    14: [
        { key: 'daily_operations', label: 'Daily Operations' },
        { key: 'facilities', label: 'Facilities' },
        { key: 'technology_stack', label: 'Technology Stack' },
        { key: 'quality_control', label: 'Quality Control' },
    ],
    15: [
        { key: 'staff_roles', label: 'Staff Roles' },
        { key: 'organizational_chart_data', label: 'Organizational Chart' },
        { key: 'departments_data', label: 'Departments' },
        { key: 'key_staff', label: 'Key Staff' },
        { key: 'hiring_plan', label: 'Hiring Plan' },
        { key: 'recruitment_strategy', label: 'Recruitment Strategy' },
        { key: 'employee_benefits', label: 'Employee Benefits' },
        { key: 'training_plan', label: 'Training Plan' },
        { key: 'performance_management', label: 'Performance Management' },
    ],
    16: [
        { key: 'risks', label: 'Risks' },
        { key: 'structured_risks', label: 'Structured Risks' },
        { key: 'mitigation_strategies', label: 'Mitigation Strategies' },
    ],
    17: [
        { key: 'timeline', label: 'Timeline' },
        { key: 'milestones', label: 'Milestones' },
    ],
    18: [
        { key: 'startup_costs', label: 'Startup Costs' },
        { key: 'monthly_operating_costs', label: 'Monthly Operating Costs' },
        { key: 'expected_monthly_revenue', label: 'Expected Monthly Revenue' },
        { key: 'price_per_unit', label: 'Price per Unit' },
        { key: 'expected_sales_volume', label: 'Expected Sales Volume' },
        { key: 'staff_salaries', label: 'Staff Salaries' },
        { key: 'inventory_costs', label: 'Inventory Costs' },
        { key: 'utilities_costs', label: 'Utilities Costs' },
        { key: 'financial_projections', label: 'Financial Projections' },
        { key: 'break_even_analysis', label: 'Break-even Analysis' },
    ],
    19: [
        { key: 'funding_requirements', label: 'Funding Requirements' },
        { key: 'profit_loss_projection', label: 'Profit & Loss Projection' },
        { key: 'cash_flow_projection', label: 'Cash Flow Projection' },
        { key: 'balance_sheet_projection', label: 'Balance Sheet Projection' },
        { key: 'financial_ratios', label: 'Financial Ratios' },
        { key: 'scenario_planning_data', label: 'Scenario Planning' },
    ],
    20: [
        { key: 'exit_strategy_type', label: 'Exit Strategy Type' },
        { key: 'exit_strategy_details', label: 'Exit Strategy Details' },
        { key: 'appendices', label: 'Appendices' },
    ],
};

const fieldLabel = (key: string): string => {
    for (const arr of Object.values(stepFields)) {
        const found = arr.find(f => f.key === key);
        if (found) return found.label;
    }
    return key.replace(/_/g, ' ').replace(/\b\w/g, c => c.toUpperCase());
};

const formatPreviewValue = (key: string, value: any): string => {
    if (value === null || value === undefined || value === '') return '';
    if (currencyFields.has(key) && typeof value === 'number') {
        return new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD', maximumFractionDigits: 0 }).format(value);
    }
    if (typeof value === 'boolean') return value ? 'Yes' : 'No';
    if (typeof value === 'object') {
        try { return JSON.stringify(value, null, 2); } catch { return String(value); }
    }
    return String(value);
};

const escHtml = (s: string): string => s
    .replace(/&/g, '&')
    .replace(/</g, '<')
    .replace(/>/g, '>')
    .replace(/"/g, '"');

const previewAsHtml = (key: string, value: any): string => {
    const raw = formatPreviewValue(key, value);
    if (!raw) return '<span style="color:#9ca3af;font-style:italic;">Not filled in yet</span>';
    const escaped = escHtml(raw);
    if (raw.includes('\n')) {
        return escaped
            .split('\n')
            .map(line => {
                const t = line.trim();
                if (t.startsWith('- ') || t.startsWith('* ')) {
                    return '<div style="padding-left:1rem;">&bull; ' + t.slice(2) + '</div>';
                }
                return '<div>' + (t || '&nbsp;') + '</div>';
            })
            .join('');
    }
    return '<div>' + escaped + '</div>';
};
</script>

<template>
    <Head title="Business Plan Generator" />
    <CMSLayout>
        <div class="p-6 max-w-5xl mx-auto">
            <div v-if="$page.props.flash?.error" class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg text-sm text-red-700">{{ $page.props.flash.error }}</div>
            <div v-if="$page.props.flash?.success" class="mb-4 p-3 bg-green-50 border border-green-200 rounded-lg text-sm text-green-700">{{ $page.props.flash.success }}</div>
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Business Plan Generator</h1>
                    <p class="text-sm text-gray-500 mt-1">Create a comprehensive 20-module business plan <span v-if="lastSavedAt" class="text-xs text-green-600 ml-2">&middot; Saved {{ lastSavedAt }}</span></p>
                </div>
                <div class="flex items-center gap-2">
                    <span v-if="autoSaving" class="text-xs text-gray-400">Auto-saving...</span>
                    <button @click="generateAllFields" :disabled="generatingAll" class="px-4 py-2 bg-purple-600 text-white text-sm rounded-lg hover:bg-purple-700 disabled:opacity-50">
                        {{ generatingAll ? 'Generating all...' : 'AI: Fill All' }}
                    </button>
                    <button @click="showPreview = true" class="px-4 py-2 bg-indigo-600 text-white text-sm rounded-lg hover:bg-indigo-700">
                        Preview Plan
                    </button>
                    <button v-if="form.id" @click="saveDraft" :disabled="saving" class="px-4 py-2 bg-gray-600 text-white text-sm rounded-lg hover:bg-gray-700 disabled:opacity-50">
                        {{ saving ? 'Saving...' : 'Save Draft' }}
                    </button>
                </div>
            </div>

            <!-- Step Navigator -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 mb-6">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-medium text-gray-600">Step {{ currentStep }} of {{ totalSteps }} &mdash; {{ steps[currentStep-1].label }}</span>
                    <span class="text-xs text-gray-500">{{ progressPercent }}%</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2"><div class="bg-blue-600 h-2 rounded-full transition-all duration-300" :style="{ width: progressPercent + '%' }"></div></div>
                <div class="mt-3 flex gap-1 overflow-x-auto pb-1">
                    <div v-for="s in steps" :key="s.num" @click="goToStep(s.num)" class="flex-shrink-0 w-5 h-1.5 rounded-full cursor-pointer transition-colors"
                        :class="currentStep > s.num ? 'bg-green-400' : currentStep === s.num ? 'bg-blue-600' : 'bg-gray-200'" :title="`${s.num}. ${s.label}`"></div>
                </div>
            </div>

            <!-- AI Processing Indicator -->
            <div v-if="generatingField || generatingAll" class="mb-4 p-4 bg-purple-50 rounded-lg border border-purple-200 flex items-center gap-3">
                <svg class="animate-spin h-5 w-5 text-purple-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                </svg>
                <div>
                    <p class="text-sm font-semibold text-purple-700">Generating AI content<span v-if="generatingField"> for {{ generatingField.replace(/_/g, ' ') }}</span>...</p>
                    <p class="text-xs text-purple-500" v-if="generatingAll">Filling all empty fields. This may take a few minutes.</p>
                </div>
            </div>

            <!-- AI Generated Content Banner -->
            <div v-if="generatedContent" class="mb-4 p-4 bg-purple-50 rounded-lg border border-purple-200">
                <div class="flex items-start justify-between">
                    <div class="flex-1">
                        <p class="text-xs font-semibold text-purple-700 uppercase tracking-wider mb-1">AI Generated Content</p>
                        <p class="text-sm text-purple-900 whitespace-pre-wrap">{{ generatedContent }}</p>
                    </div>
                    <button @click="generatedContent = null; currentAIField = null" class="ml-3 text-purple-400 hover:text-purple-600">&times;</button>
                </div>
                <div class="mt-2 flex gap-2">
                    <button v-if="currentAIField" @click="applyGenerated(currentAIField)" class="px-3 py-1 text-xs bg-purple-600 text-white rounded hover:bg-purple-700 font-medium">Apply to {{ currentAIField?.replace(/_/g, ' ') }}</button>
                    <button @click="generateAIContent(currentAIField!)" class="px-3 py-1 text-xs bg-purple-100 text-purple-700 rounded hover:bg-purple-200 font-medium">Regenerate</button>
                    <button @click="generatedContent = null; currentAIField = null" class="px-3 py-1 text-xs border border-purple-300 text-purple-700 rounded hover:bg-purple-100">Dismiss</button>
                </div>
            </div>

            <!-- Step 1: Quick-Start Wizard (first visit) or full grid (returning) -->
            <div v-if="currentStep === 1" class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <template v-if="!form.wizard_completed && !props.existingPlan">
                    <div class="flex items-center justify-between mb-6">
                        <div>
                            <h2 class="text-lg font-bold text-gray-900">Quick Start</h2>
                            <p class="text-xs text-gray-500">Let's get to know your business</p>
                        </div>
                        <div class="flex items-center gap-3">
                            <div class="flex gap-1.5">
                                <div v-for="i in 4" :key="i" class="w-8 h-1.5 rounded-full" :class="i <= wizardStep ? 'bg-purple-600' : 'bg-gray-200'"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Screen 1: Identity -->
                    <div v-if="wizardStep === 1" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">What's your business called? <span class="text-red-500">*</span></label>
                            <input v-model="form.business_name" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="e.g. MyGrowNet Success Hub" />
                            <p v-if="wizardErrors.business_name" class="text-xs text-red-500 mt-1">{{ wizardErrors.business_name }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Tagline</label>
                            <input v-model="form.tagline" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="A short, memorable phrase" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Industry <span class="text-red-500">*</span></label>
                            <select v-model="form.industry" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                                <option value="">Select industry...</option><option v-for="ind in industries" :key="ind" :value="ind">{{ ind }}</option>
                            </select>
                            <p v-if="wizardErrors.industry" class="text-xs text-red-500 mt-1">{{ wizardErrors.industry }}</p>
                        </div>
                        <div class="pt-2">
                            <button @click="wizardNext(1)" class="px-5 py-2 bg-purple-600 text-white text-sm rounded-lg hover:bg-purple-700 font-medium">Next</button>
                        </div>
                    </div>

                    <!-- Screen 2: Location & Legal -->
                    <div v-if="wizardStep === 2" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Country</label>
                            <input v-model="form.country" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="Zambia" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Province</label>
                            <input v-model="form.province" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="Lusaka" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">City</label>
                            <input v-model="form.city" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="Lusaka" />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Legal Structure <span class="text-red-500">*</span></label>
                            <select v-model="form.legal_structure" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                                <option value="">Select structure...</option><option value="sole_trader">Sole Trader</option><option value="partnership">Partnership</option><option value="company">Company</option><option value="cooperative">Cooperative</option>
                            </select>
                            <p v-if="wizardErrors.legal_structure" class="text-xs text-red-500 mt-1">{{ wizardErrors.legal_structure }}</p>
                        </div>
                        <div class="pt-2 flex gap-2">
                            <button @click="wizardStep--" class="px-4 py-2 border border-gray-300 text-sm rounded-lg hover:bg-gray-50 font-medium">Back</button>
                            <button @click="wizardNext(2)" class="px-5 py-2 bg-purple-600 text-white text-sm rounded-lg hover:bg-purple-700 font-medium">Next</button>
                        </div>
                    </div>

                    <!-- Screen 3: Snapshot & Goal -->
                    <div v-if="wizardStep === 3" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Business Snapshot <span class="text-red-500">*</span></label>
                            <textarea v-model="form.business_description" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="Describe what your business does in 1-2 sentences..."></textarea>
                            <p v-if="wizardErrors.business_description" class="text-xs text-red-500 mt-1">{{ wizardErrors.business_description }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">What stage is your business in?</label>
                            <select v-model="form.business_stage" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                                <option value="">Select stage...</option>
                                <option v-for="s in stages" :key="s.val" :value="s.val">{{ s.label }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Your main goal right now</label>
                            <input v-model="wizGoal" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="e.g. Secure funding, refine the idea, plan for launch" />
                        </div>
                        <div class="pt-2 flex gap-2">
                            <button @click="wizardStep--" class="px-4 py-2 border border-gray-300 text-sm rounded-lg hover:bg-gray-50 font-medium">Back</button>
                            <button @click="wizardNext(3)" class="px-5 py-2 bg-purple-600 text-white text-sm rounded-lg hover:bg-purple-700 font-medium">Next</button>
                        </div>
                    </div>

                    <!-- Screen 4: Review & Launch -->
                    <div v-if="wizardStep === 4" class="space-y-4">
                        <p class="text-sm text-gray-600">Review what you've entered before we start building your plan.</p>
                        <div class="bg-gray-50 rounded-lg p-4 space-y-2 text-sm">
                            <div><span class="font-medium">Business:</span> {{ form.business_name || '—' }}</div>
                            <div><span class="font-medium">Industry:</span> {{ form.industry || '—' }}</div>
                            <div><span class="font-medium">Country:</span> {{ form.country || '—' }}</div>
                            <div><span class="font-medium">Legal Structure:</span> {{ form.legal_structure ? form.legal_structure.replace(/_/g, ' ') : '—' }}</div>
                            <div><span class="font-medium">Tagline:</span> {{ form.tagline || '—' }}</div>
                            <div><span class="font-medium">Snapshot:</span> {{ form.business_description ? form.business_description.substring(0, 100) + '...' : '—' }}</div>
                        </div>
                        <div class="pt-2 flex gap-2">
                            <button @click="wizardStep--" class="px-4 py-2 border border-gray-300 text-sm rounded-lg hover:bg-gray-50 font-medium">Back</button>
                            <button @click="completeWizard" class="px-5 py-2 bg-purple-600 text-white text-sm rounded-lg hover:bg-purple-700 font-medium">Start Building &rarr;</button>
                        </div>
                    </div>
                </template>

                <!-- Existing full Step 1 grid for returning users -->
                <template v-else>
                    <h2 class="text-lg font-bold text-gray-900 mb-1">Business Profile</h2>
                    <p class="text-sm text-gray-500 mb-6">Basic information about your business</p>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div><label class="block text-sm font-medium text-gray-700 mb-1">Business Name <span class="text-red-500">*</span></label>
                            <input v-model="form.business_name" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="e.g. MyGrowNet Success Hub" /></div>
                        <div><div class="flex items-center justify-between mb-1"><label class="text-sm font-medium text-gray-700">Tagline</label><div class="flex gap-1"><button @click="generateAIContent('tagline')" class="text-xs bg-purple-100 text-purple-700 px-2 py-0.5 rounded hover:bg-purple-200 font-medium cursor-pointer" :disabled="!!generatingField || generatingAll">{{ (generatingField || generatingAll) ? '...' : 'AI' }}</button><button @click="clearField('tagline')" class="text-xs text-gray-400 hover:text-red-500 px-1.5 py-0.5 rounded hover:bg-red-50 font-medium cursor-pointer" title="Clear">&times;</button></div></div>
                            <input v-model="form.tagline" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="A short, memorable phrase" /></div>
                        <div><label class="block text-sm font-medium text-gray-700 mb-1">Industry <span class="text-red-500">*</span></label>
                            <select v-model="form.industry" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                                <option value="">Select industry...</option><option v-for="ind in industries" :key="ind" :value="ind">{{ ind }}</option>
                            </select></div>
                        <div><label class="block text-sm font-medium text-gray-700 mb-1">Business Stage</label>
                            <select v-model="form.business_stage" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                                <option value="">Select stage...</option><option value="idea">Idea</option><option value="startup">Startup</option><option value="growth">Growth</option><option value="expansion">Expansion</option>
                            </select></div>
                        <div><label class="block text-sm font-medium text-gray-700 mb-1">Country</label><input v-model="form.country" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="Zambia" /></div>
                        <div><label class="block text-sm font-medium text-gray-700 mb-1">Province</label><input v-model="form.province" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="Lusaka" /></div>
                        <div><label class="block text-sm font-medium text-gray-700 mb-1">City</label><input v-model="form.city" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="Lusaka" /></div>
                        <div><label class="block text-sm font-medium text-gray-700 mb-1">Website</label><input v-model="form.website" type="url" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="https://example.com" /></div>
                        <div><label class="block text-sm font-medium text-gray-700 mb-1">Legal Structure <span class="text-red-500">*</span></label>
                            <select v-model="form.legal_structure" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                                <option value="">Select structure...</option><option value="sole_trader">Sole Trader</option><option value="partnership">Partnership</option><option value="company">Company</option><option value="cooperative">Cooperative</option>
                            </select></div>
                        <div><label class="block text-sm font-medium text-gray-700 mb-1">Registration Status</label>
                            <input v-model="form.registration_status" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="e.g. Registered with PACRA" /></div>
                        <div><label class="block text-sm font-medium text-gray-700 mb-1">Date Established</label>
                            <input v-model="form.date_established" type="date" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" /></div>
                    </div>
                </template>
            </div>

            <!-- Step 2: Company Description -->
            <div v-if="currentStep === 2" class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h2 class="text-lg font-bold text-gray-900 mb-1">Company Description</h2>
                <p class="text-sm text-gray-500 mb-6">Define your business purpose, values, and direction</p>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <div class="flex items-center justify-between mb-1"><label class="text-sm font-medium text-gray-700">Mission Statement</label><div class="flex gap-1"><button @click="generateAIContent('mission_statement')" class="text-xs bg-purple-100 text-purple-700 px-2 py-0.5 rounded hover:bg-purple-200 font-medium cursor-pointer" :disabled="!!generatingField || generatingAll">{{ (generatingField || generatingAll) ? '...' : 'AI' }}</button><button @click="clearField('mission_statement')" class="text-xs text-gray-400 hover:text-red-500 px-1.5 py-0.5 rounded hover:bg-red-50 font-medium cursor-pointer" title="Clear">&times;</button></div></div>
                        <textarea v-model="form.mission_statement" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="Our mission is to..."></textarea>
                    </div>
                    <div>
                        <div class="flex items-center justify-between mb-1"><label class="text-sm font-medium text-gray-700">Vision Statement</label><div class="flex gap-1"><button @click="generateAIContent('vision_statement')" class="text-xs bg-purple-100 text-purple-700 px-2 py-0.5 rounded hover:bg-purple-200 font-medium cursor-pointer" :disabled="!!generatingField || generatingAll">{{ (generatingField || generatingAll) ? '...' : 'AI' }}</button><button @click="clearField('vision_statement')" class="text-xs text-gray-400 hover:text-red-500 px-1.5 py-0.5 rounded hover:bg-red-50 font-medium cursor-pointer" title="Clear">&times;</button></div></div>
                        <textarea v-model="form.vision_statement" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="To become the leading..."></textarea>
                    </div>
                    <div><div class="flex items-center justify-between mb-1"><label class="text-sm font-medium text-gray-700">Core Values (JSON array)</label><div class="flex gap-1"><button @click="generateAIContent('core_values')" class="text-xs bg-purple-100 text-purple-700 px-2 py-0.5 rounded hover:bg-purple-200 font-medium cursor-pointer" :disabled="!!generatingField || generatingAll">{{ (generatingField || generatingAll) ? '...' : 'AI' }}</button><button @click="clearField('core_values')" class="text-xs text-gray-400 hover:text-red-500 px-1.5 py-0.5 rounded hover:bg-red-50 font-medium cursor-pointer" title="Clear">&times;</button></div></div>
                        <textarea v-model="form.core_values" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder='["Integrity","Innovation","Customer Focus"]'></textarea></div>
                    <div><div class="flex items-center justify-between mb-1"><label class="text-sm font-medium text-gray-700">Business Objectives</label><div class="flex gap-1"><button @click="generateAIContent('business_objectives')" class="text-xs bg-purple-100 text-purple-700 px-2 py-0.5 rounded hover:bg-purple-200 font-medium cursor-pointer" :disabled="!!generatingField || generatingAll">{{ (generatingField || generatingAll) ? '...' : 'AI' }}</button><button @click="clearField('business_objectives')" class="text-xs text-gray-400 hover:text-red-500 px-1.5 py-0.5 rounded hover:bg-red-50 font-medium cursor-pointer" title="Clear">&times;</button></div></div>
                        <textarea v-model="form.business_objectives" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="What you aim to achieve..."></textarea></div>
                    <div><div class="flex items-center justify-between mb-1"><label class="text-sm font-medium text-gray-700">Company History</label><div class="flex gap-1"><button @click="generateAIContent('company_history')" class="text-xs bg-purple-100 text-purple-700 px-2 py-0.5 rounded hover:bg-purple-200 font-medium cursor-pointer" :disabled="!!generatingField || generatingAll">{{ (generatingField || generatingAll) ? '...' : 'AI' }}</button><button @click="clearField('company_history')" class="text-xs text-gray-400 hover:text-red-500 px-1.5 py-0.5 rounded hover:bg-red-50 font-medium cursor-pointer" title="Clear">&times;</button></div></div>
                        <textarea v-model="form.company_history" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="How and when the business started..."></textarea></div>
                    <div><div class="flex items-center justify-between mb-1"><label class="text-sm font-medium text-gray-700">Long-Term Goals</label><div class="flex gap-1"><button @click="generateAIContent('long_term_goals')" class="text-xs bg-purple-100 text-purple-700 px-2 py-0.5 rounded hover:bg-purple-200 font-medium cursor-pointer" :disabled="!!generatingField || generatingAll">{{ (generatingField || generatingAll) ? '...' : 'AI' }}</button><button @click="clearField('long_term_goals')" class="text-xs text-gray-400 hover:text-red-500 px-1.5 py-0.5 rounded hover:bg-red-50 font-medium cursor-pointer" title="Clear">&times;</button></div></div>
                        <textarea v-model="form.long_term_goals" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="Where do you see the business in 5-10 years?"></textarea></div>
                </div>
                <div class="mt-5">
                    <div class="flex items-center justify-between mb-1"><label class="text-sm font-medium text-gray-700">Business Background</label><div class="flex gap-1"><button @click="generateAIContent('background')" class="text-xs bg-purple-100 text-purple-700 px-2 py-0.5 rounded hover:bg-purple-200 font-medium cursor-pointer" :disabled="!!generatingField || generatingAll">{{ (generatingField || generatingAll) ? '...' : 'AI' }}</button><button @click="clearField('background')" class="text-xs text-gray-400 hover:text-red-500 px-1.5 py-0.5 rounded hover:bg-red-50 font-medium cursor-pointer" title="Clear">&times;</button></div></div>
                    <textarea v-model="form.background" rows="4" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="Tell the full story of your business..."></textarea>
                </div>
                <div class="mt-5"><div class="flex items-center justify-between mb-1"><label class="text-sm font-medium text-gray-700">Key Success Factors</label><div class="flex gap-1"><button @click="generateAIContent('success_factors')" class="text-xs bg-purple-100 text-purple-700 px-2 py-0.5 rounded hover:bg-purple-200 font-medium cursor-pointer" :disabled="!!generatingField || generatingAll">{{ (generatingField || generatingAll) ? '...' : 'AI' }}</button><button @click="clearField('success_factors')" class="text-xs text-gray-400 hover:text-red-500 px-1.5 py-0.5 rounded hover:bg-red-50 font-medium cursor-pointer" title="Clear">&times;</button></div></div>
                    <textarea v-model="form.success_factors" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="What will make your business successful?"></textarea></div>
            </div>

            <!-- Step 3: Problem Statement -->
            <div v-if="currentStep === 3" class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h2 class="text-lg font-bold text-gray-900 mb-1">Problem Statement</h2>
                <p class="text-sm text-gray-500 mb-6">Define the problem your business solves</p>
                <div class="space-y-5">
                    <div>
                        <div class="flex items-center justify-between mb-1"><label class="text-sm font-medium text-gray-700">Problem Statement</label><div class="flex gap-1"><button @click="generateAIContent('problem_statement')" class="text-xs bg-purple-100 text-purple-700 px-2 py-0.5 rounded hover:bg-purple-200 font-medium cursor-pointer" :disabled="!!generatingField || generatingAll">{{ (generatingField || generatingAll) ? '...' : 'AI' }}</button><button @click="clearField('problem_statement')" class="text-xs text-gray-400 hover:text-red-500 px-1.5 py-0.5 rounded hover:bg-red-50 font-medium cursor-pointer" title="Clear">&times;</button></div></div>
                        <textarea v-model="form.problem_statement" rows="4" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="What specific problem are you solving for your customers?"></textarea>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div><div class="flex items-center justify-between mb-1"><label class="text-sm font-medium text-gray-700">Existing Alternatives</label><div class="flex gap-1"><button @click="generateAIContent('existing_alternatives')" class="text-xs bg-purple-100 text-purple-700 px-2 py-0.5 rounded hover:bg-purple-200 font-medium cursor-pointer" :disabled="!!generatingField || generatingAll">{{ (generatingField || generatingAll) ? '...' : 'AI' }}</button><button @click="clearField('existing_alternatives')" class="text-xs text-gray-400 hover:text-red-500 px-1.5 py-0.5 rounded hover:bg-red-50 font-medium cursor-pointer" title="Clear">&times;</button></div></div>
                            <textarea v-model="form.existing_alternatives" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="How are customers currently solving this problem?"></textarea></div>
                        <div><div class="flex items-center justify-between mb-1"><label class="text-sm font-medium text-gray-700">Why Existing Alternatives Fail</label><div class="flex gap-1"><button @click="generateAIContent('why_existing_fail')" class="text-xs bg-purple-100 text-purple-700 px-2 py-0.5 rounded hover:bg-purple-200 font-medium cursor-pointer" :disabled="!!generatingField || generatingAll">{{ (generatingField || generatingAll) ? '...' : 'AI' }}</button><button @click="clearField('why_existing_fail')" class="text-xs text-gray-400 hover:text-red-500 px-1.5 py-0.5 rounded hover:bg-red-50 font-medium cursor-pointer" title="Clear">&times;</button></div></div>
                            <textarea v-model="form.why_existing_fail" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="Why aren't current solutions good enough?"></textarea></div>
                    </div>
                    <div>
                        <div class="flex items-center justify-between mb-1"><label class="text-sm font-medium text-gray-700">Solution Description</label><div class="flex gap-1"><button @click="generateAIContent('solution_description')" class="text-xs bg-purple-100 text-purple-700 px-2 py-0.5 rounded hover:bg-purple-200 font-medium cursor-pointer" :disabled="!!generatingField || generatingAll">{{ (generatingField || generatingAll) ? '...' : 'AI' }}</button><button @click="clearField('solution_description')" class="text-xs text-gray-400 hover:text-red-500 px-1.5 py-0.5 rounded hover:bg-red-50 font-medium cursor-pointer" title="Clear">&times;</button></div></div>
                        <textarea v-model="form.solution_description" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="How does your product/service solve this problem?"></textarea>
                    </div>
                </div>
            </div>

            <!-- Step 4: Products & Services -->
            <div v-if="currentStep === 4" class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h2 class="text-lg font-bold text-gray-900 mb-1">Products &amp; Services</h2>
                <p class="text-sm text-gray-500 mb-6">Describe what you offer and how you deliver it</p>
                <div class="space-y-5">
                    <div>
                        <div class="flex items-center justify-between mb-1"><label class="text-sm font-medium text-gray-700">Product/Service Description</label><div class="flex gap-1"><button @click="generateAIContent('product_description')" class="text-xs bg-purple-100 text-purple-700 px-2 py-0.5 rounded hover:bg-purple-200 font-medium cursor-pointer" :disabled="!!generatingField || generatingAll">{{ (generatingField || generatingAll) ? '...' : 'AI' }}</button><button @click="clearField('product_description')" class="text-xs text-gray-400 hover:text-red-500 px-1.5 py-0.5 rounded hover:bg-red-50 font-medium cursor-pointer" title="Clear">&times;</button></div></div>
                        <textarea v-model="form.product_description" rows="4" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="Describe your products or services in detail..."></textarea>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div><div class="flex items-center justify-between mb-1"><label class="text-sm font-medium text-gray-700">Delivery Method</label><div class="flex gap-1"><button @click="generateAIContent('delivery_method')" class="text-xs bg-purple-100 text-purple-700 px-2 py-0.5 rounded hover:bg-purple-200 font-medium cursor-pointer" :disabled="!!generatingField || generatingAll">{{ (generatingField || generatingAll) ? '...' : 'AI' }}</button><button @click="clearField('delivery_method')" class="text-xs text-gray-400 hover:text-red-500 px-1.5 py-0.5 rounded hover:bg-red-50 font-medium cursor-pointer" title="Clear">&times;</button></div></div>
                            <textarea v-model="form.delivery_method" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="How do you deliver? (online, physical, hybrid...)"></textarea></div>
                        <div><div class="flex items-center justify-between mb-1"><label class="text-sm font-medium text-gray-700">Product Lifecycle</label><div class="flex gap-1"><button @click="generateAIContent('product_lifecycle')" class="text-xs bg-purple-100 text-purple-700 px-2 py-0.5 rounded hover:bg-purple-200 font-medium cursor-pointer" :disabled="!!generatingField || generatingAll">{{ (generatingField || generatingAll) ? '...' : 'AI' }}</button><button @click="clearField('product_lifecycle')" class="text-xs text-gray-400 hover:text-red-500 px-1.5 py-0.5 rounded hover:bg-red-50 font-medium cursor-pointer" title="Clear">&times;</button></div></div>
                            <textarea v-model="form.product_lifecycle" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="Development, growth, maturity, decline stages..."></textarea></div>
                    </div>
                    <div><div class="flex items-center justify-between mb-1"><label class="text-sm font-medium text-gray-700">Future Improvements &amp; Roadmap</label><div class="flex gap-1"><button @click="generateAIContent('future_improvements')" class="text-xs bg-purple-100 text-purple-700 px-2 py-0.5 rounded hover:bg-purple-200 font-medium cursor-pointer" :disabled="!!generatingField || generatingAll">{{ (generatingField || generatingAll) ? '...' : 'AI' }}</button><button @click="clearField('future_improvements')" class="text-xs text-gray-400 hover:text-red-500 px-1.5 py-0.5 rounded hover:bg-red-50 font-medium cursor-pointer" title="Clear">&times;</button></div></div>
                        <textarea v-model="form.future_improvements" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="What enhancements do you plan?"></textarea></div>
                    <div><label class="block text-sm font-medium text-gray-700 mb-1">Structured Products (JSON array of product objects)</label>
                        <textarea v-model="form.structured_products" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm font-mono text-xs" placeholder='[{"name":"Product A","description":"...","price":100,"type":"physical"}]'></textarea></div>
                </div>
            </div>

            <!-- Step 5: Product Features & Production -->
            <div v-if="currentStep === 5" class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h2 class="text-lg font-bold text-gray-900 mb-1">Product Features &amp; Production</h2>
                <p class="text-sm text-gray-500 mb-6">Detail your features and how your products are made</p>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div><div class="flex items-center justify-between mb-1"><label class="text-sm font-medium text-gray-700">Key Features (JSON array)</label><div class="flex gap-1"><button @click="generateAIContent('product_features')" class="text-xs bg-purple-100 text-purple-700 px-2 py-0.5 rounded hover:bg-purple-200 font-medium cursor-pointer" :disabled="!!generatingField || generatingAll">{{ (generatingField || generatingAll) ? '...' : 'AI' }}</button><button @click="clearField('product_features')" class="text-xs text-gray-400 hover:text-red-500 px-1.5 py-0.5 rounded hover:bg-red-50 font-medium cursor-pointer" title="Clear">&times;</button></div></div>
                        <textarea v-model="form.product_features" rows="4" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder='["Feature 1","Feature 2"]'></textarea></div>
                    <div><div class="flex items-center justify-between mb-1"><label class="text-sm font-medium text-gray-700">Production Process</label><div class="flex gap-1"><button @click="generateAIContent('production_process')" class="text-xs bg-purple-100 text-purple-700 px-2 py-0.5 rounded hover:bg-purple-200 font-medium cursor-pointer" :disabled="!!generatingField || generatingAll">{{ (generatingField || generatingAll) ? '...' : 'AI' }}</button><button @click="clearField('production_process')" class="text-xs text-gray-400 hover:text-red-500 px-1.5 py-0.5 rounded hover:bg-red-50 font-medium cursor-pointer" title="Clear">&times;</button></div></div>
                        <textarea v-model="form.production_process" rows="4" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="How are your products made or services delivered?"></textarea></div>
                </div>
                <div class="mt-5"><label class="block text-sm font-medium text-gray-700 mb-1">Resource Requirements (JSON)</label>
                    <textarea v-model="form.resource_requirements" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder='{"raw_materials":"...","equipment":"...","technology":"...","labor":"..."}'></textarea></div>
            </div>

            <!-- Step 6: Pricing & Business Model -->
            <div v-if="currentStep === 6" class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h2 class="text-lg font-bold text-gray-900 mb-1">Pricing &amp; Business Model</h2>
                <p class="text-sm text-gray-500 mb-6">How you make money and create value</p>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div><div class="flex items-center justify-between mb-1"><label class="text-sm font-medium text-gray-700">Pricing Strategy</label><div class="flex gap-1"><button @click="generateAIContent('pricing_strategy')" class="text-xs bg-purple-100 text-purple-700 px-2 py-0.5 rounded hover:bg-purple-200 font-medium cursor-pointer" :disabled="!!generatingField || generatingAll">{{ (generatingField || generatingAll) ? '...' : 'AI' }}</button><button @click="clearField('pricing_strategy')" class="text-xs text-gray-400 hover:text-red-500 px-1.5 py-0.5 rounded hover:bg-red-50 font-medium cursor-pointer" title="Clear">&times;</button></div></div>
                        <textarea v-model="form.pricing_strategy" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="Competitive, value-based, penetration, premium?"></textarea></div>
                    <div><div class="flex items-center justify-between mb-1"><label class="text-sm font-medium text-gray-700">Revenue Streams</label><div class="flex gap-1"><button @click="generateAIContent('revenue_streams')" class="text-xs bg-purple-100 text-purple-700 px-2 py-0.5 rounded hover:bg-purple-200 font-medium cursor-pointer" :disabled="!!generatingField || generatingAll">{{ (generatingField || generatingAll) ? '...' : 'AI' }}</button><button @click="clearField('revenue_streams')" class="text-xs text-gray-400 hover:text-red-500 px-1.5 py-0.5 rounded hover:bg-red-50 font-medium cursor-pointer" title="Clear">&times;</button></div></div>
                        <textarea v-model="form.revenue_streams" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="Direct sales, subscriptions, advertising, commissions..."></textarea></div>
                    <div><div class="flex items-center justify-between mb-1"><label class="text-sm font-medium text-gray-700">Cost Structure</label><div class="flex gap-1"><button @click="generateAIContent('cost_structure')" class="text-xs bg-purple-100 text-purple-700 px-2 py-0.5 rounded hover:bg-purple-200 font-medium cursor-pointer" :disabled="!!generatingField || generatingAll">{{ (generatingField || generatingAll) ? '...' : 'AI' }}</button><button @click="clearField('cost_structure')" class="text-xs text-gray-400 hover:text-red-500 px-1.5 py-0.5 rounded hover:bg-red-50 font-medium cursor-pointer" title="Clear">&times;</button></div></div>
                        <textarea v-model="form.cost_structure" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="Fixed costs, variable costs, economies of scale..."></textarea></div>
                    <div><div class="flex items-center justify-between mb-1"><label class="text-sm font-medium text-gray-700">Customer Relationships</label><div class="flex gap-1"><button @click="generateAIContent('customer_relationships')" class="text-xs bg-purple-100 text-purple-700 px-2 py-0.5 rounded hover:bg-purple-200 font-medium cursor-pointer" :disabled="!!generatingField || generatingAll">{{ (generatingField || generatingAll) ? '...' : 'AI' }}</button><button @click="clearField('customer_relationships')" class="text-xs text-gray-400 hover:text-red-500 px-1.5 py-0.5 rounded hover:bg-red-50 font-medium cursor-pointer" title="Clear">&times;</button></div></div>
                        <textarea v-model="form.customer_relationships" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="Personal assistance, self-service, communities..."></textarea></div>
                    <div><div class="flex items-center justify-between mb-1"><label class="text-sm font-medium text-gray-700">Channels</label><div class="flex gap-1"><button @click="generateAIContent('channels')" class="text-xs bg-purple-100 text-purple-700 px-2 py-0.5 rounded hover:bg-purple-200 font-medium cursor-pointer" :disabled="!!generatingField || generatingAll">{{ (generatingField || generatingAll) ? '...' : 'AI' }}</button><button @click="clearField('channels')" class="text-xs text-gray-400 hover:text-red-500 px-1.5 py-0.5 rounded hover:bg-red-50 font-medium cursor-pointer" title="Clear">&times;</button></div></div>
                        <textarea v-model="form.channels" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="How do you reach customers?"></textarea></div>
                    <div><div class="flex items-center justify-between mb-1"><label class="text-sm font-medium text-gray-700">Key Activities</label><div class="flex gap-1"><button @click="generateAIContent('key_activities')" class="text-xs bg-purple-100 text-purple-700 px-2 py-0.5 rounded hover:bg-purple-200 font-medium cursor-pointer" :disabled="!!generatingField || generatingAll">{{ (generatingField || generatingAll) ? '...' : 'AI' }}</button><button @click="clearField('key_activities')" class="text-xs text-gray-400 hover:text-red-500 px-1.5 py-0.5 rounded hover:bg-red-50 font-medium cursor-pointer" title="Clear">&times;</button></div></div>
                        <textarea v-model="form.key_activities" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="Most important actions to make business model work"></textarea></div>
                    <div><div class="flex items-center justify-between mb-1"><label class="text-sm font-medium text-gray-700">Key Resources</label><div class="flex gap-1"><button @click="generateAIContent('key_resources')" class="text-xs bg-purple-100 text-purple-700 px-2 py-0.5 rounded hover:bg-purple-200 font-medium cursor-pointer" :disabled="!!generatingField || generatingAll">{{ (generatingField || generatingAll) ? '...' : 'AI' }}</button><button @click="clearField('key_resources')" class="text-xs text-gray-400 hover:text-red-500 px-1.5 py-0.5 rounded hover:bg-red-50 font-medium cursor-pointer" title="Clear">&times;</button></div></div>
                        <textarea v-model="form.key_resources" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="Physical, intellectual, human, financial assets"></textarea></div>
                    <div><div class="flex items-center justify-between mb-1"><label class="text-sm font-medium text-gray-700">Key Partners</label><div class="flex gap-1"><button @click="generateAIContent('key_partners')" class="text-xs bg-purple-100 text-purple-700 px-2 py-0.5 rounded hover:bg-purple-200 font-medium cursor-pointer" :disabled="!!generatingField || generatingAll">{{ (generatingField || generatingAll) ? '...' : 'AI' }}</button><button @click="clearField('key_partners')" class="text-xs text-gray-400 hover:text-red-500 px-1.5 py-0.5 rounded hover:bg-red-50 font-medium cursor-pointer" title="Clear">&times;</button></div></div>
                        <textarea v-model="form.key_partners" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="Suppliers, distributors, strategic alliances"></textarea></div>
                </div>
                <div class="mt-5"><label class="block text-sm font-medium text-gray-700 mb-1">Business Model Canvas (JSON)</label>
                    <textarea v-model="form.business_model_canvas" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm font-mono text-xs" placeholder='{"value_proposition":"...","customer_segments":"...","revenue_streams":"...","cost_structure":"..."}'></textarea></div>
            </div>

            <!-- Step 7: Market Analysis -->
            <div v-if="currentStep === 7" class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h2 class="text-lg font-bold text-gray-900 mb-1">Market Analysis</h2>
                <p class="text-sm text-gray-500 mb-6">Understand your industry and target market</p>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                    <div><label class="block text-sm font-medium text-gray-700 mb-1">Industry Size</label><input v-model="form.industry_size" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="e.g. K5 billion" /></div>
                    <div><label class="block text-sm font-medium text-gray-700 mb-1">Growth Rate</label><input v-model="form.growth_rate" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="e.g. 12% annually" /></div>
                    <div><div class="flex items-center justify-between mb-1"><label class="text-sm font-medium text-gray-700">Target Market</label><div class="flex gap-1"><button @click="generateAIContent('target_market')" class="text-xs bg-purple-100 text-purple-700 px-2 py-0.5 rounded hover:bg-purple-200 font-medium cursor-pointer" :disabled="!!generatingField || generatingAll">{{ (generatingField || generatingAll) ? '...' : 'AI' }}</button><button @click="clearField('target_market')" class="text-xs text-gray-400 hover:text-red-500 px-1.5 py-0.5 rounded hover:bg-red-50 font-medium cursor-pointer" title="Clear">&times;</button></div></div>
                        <textarea v-model="form.target_market" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="Who are your target customers?"></textarea></div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-5 mt-5">
                    <div><div class="flex items-center justify-between mb-1"><label class="text-sm font-medium text-gray-700">Industry Trends</label><div class="flex gap-1"><button @click="generateAIContent('industry_trends')" class="text-xs bg-purple-100 text-purple-700 px-2 py-0.5 rounded hover:bg-purple-200 font-medium cursor-pointer" :disabled="!!generatingField || generatingAll">{{ (generatingField || generatingAll) ? '...' : 'AI' }}</button><button @click="clearField('industry_trends')" class="text-xs text-gray-400 hover:text-red-500 px-1.5 py-0.5 rounded hover:bg-red-50 font-medium cursor-pointer" title="Clear">&times;</button></div></div>
                        <textarea v-model="form.industry_trends" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="Current trends shaping the industry"></textarea></div>
                    <div><div class="flex items-center justify-between mb-1"><label class="text-sm font-medium text-gray-700">Regulations</label><div class="flex gap-1"><button @click="generateAIContent('regulations')" class="text-xs bg-purple-100 text-purple-700 px-2 py-0.5 rounded hover:bg-purple-200 font-medium cursor-pointer" :disabled="!!generatingField || generatingAll">{{ (generatingField || generatingAll) ? '...' : 'AI' }}</button><button @click="clearField('regulations')" class="text-xs text-gray-400 hover:text-red-500 px-1.5 py-0.5 rounded hover:bg-red-50 font-medium cursor-pointer" title="Clear">&times;</button></div></div>
                        <textarea v-model="form.regulations" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="Laws, licenses, compliance requirements"></textarea></div>
                    <div><div class="flex items-center justify-between mb-1"><label class="text-sm font-medium text-gray-700">Technology Changes</label><div class="flex gap-1"><button @click="generateAIContent('technology_changes')" class="text-xs bg-purple-100 text-purple-700 px-2 py-0.5 rounded hover:bg-purple-200 font-medium cursor-pointer" :disabled="!!generatingField || generatingAll">{{ (generatingField || generatingAll) ? '...' : 'AI' }}</button><button @click="clearField('technology_changes')" class="text-xs text-gray-400 hover:text-red-500 px-1.5 py-0.5 rounded hover:bg-red-50 font-medium cursor-pointer" title="Clear">&times;</button></div></div>
                        <textarea v-model="form.technology_changes" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="Tech advancements affecting your industry"></textarea></div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-5 mt-5">
                    <div><label class="block text-sm font-medium text-gray-700 mb-1">Customer Demographics (JSON)</label>
                        <textarea v-model="form.customer_demographics" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder='{"age":"25-45","income":"K5k-K15k","location":"Lusaka"}'></textarea></div>
                    <div><label class="block text-sm font-medium text-gray-700 mb-1">Customer Personas (JSON)</label>
                        <textarea v-model="form.customer_personas" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder='[{"name":"Persona A","age":30,"needs":"..."}]'></textarea></div>
                    <div><label class="block text-sm font-medium text-gray-700 mb-1">Market Size</label>
                        <input v-model="form.market_size" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="e.g. 500,000 potential customers" /></div>
                </div>
            </div>

            <!-- Step 8: Market Research -->
            <div v-if="currentStep === 8" class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h2 class="text-lg font-bold text-gray-900 mb-1">Market Research</h2>
                <p class="text-sm text-gray-500 mb-6">Data and insights gathered from your research</p>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div><div class="flex items-center justify-between mb-1"><label class="text-sm font-medium text-gray-700">Survey Data &amp; Findings</label><div class="flex gap-1"><button @click="generateAIContent('surveys_data')" class="text-xs bg-purple-100 text-purple-700 px-2 py-0.5 rounded hover:bg-purple-200 font-medium cursor-pointer" :disabled="!!generatingField || generatingAll">{{ (generatingField || generatingAll) ? '...' : 'AI' }}</button><button @click="clearField('surveys_data')" class="text-xs text-gray-400 hover:text-red-500 px-1.5 py-0.5 rounded hover:bg-red-50 font-medium cursor-pointer" title="Clear">&times;</button></div></div>
                        <textarea v-model="form.surveys_data" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="Key findings from customer surveys..."></textarea></div>
                    <div><div class="flex items-center justify-between mb-1"><label class="text-sm font-medium text-gray-700">Interview Insights</label><div class="flex gap-1"><button @click="generateAIContent('interviews_data')" class="text-xs bg-purple-100 text-purple-700 px-2 py-0.5 rounded hover:bg-purple-200 font-medium cursor-pointer" :disabled="!!generatingField || generatingAll">{{ (generatingField || generatingAll) ? '...' : 'AI' }}</button><button @click="clearField('interviews_data')" class="text-xs text-gray-400 hover:text-red-500 px-1.5 py-0.5 rounded hover:bg-red-50 font-medium cursor-pointer" title="Clear">&times;</button></div></div>
                        <textarea v-model="form.interviews_data" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="Insights from customer interviews..."></textarea></div>
                    <div><div class="flex items-center justify-between mb-1"><label class="text-sm font-medium text-gray-700">Competitor Pricing Data</label><div class="flex gap-1"><button @click="generateAIContent('competitor_pricing_data')" class="text-xs bg-purple-100 text-purple-700 px-2 py-0.5 rounded hover:bg-purple-200 font-medium cursor-pointer" :disabled="!!generatingField || generatingAll">{{ (generatingField || generatingAll) ? '...' : 'AI' }}</button><button @click="clearField('competitor_pricing_data')" class="text-xs text-gray-400 hover:text-red-500 px-1.5 py-0.5 rounded hover:bg-red-50 font-medium cursor-pointer" title="Clear">&times;</button></div></div>
                        <textarea v-model="form.competitor_pricing_data" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="Pricing analysis of competitors..."></textarea></div>
                    <div><div class="flex items-center justify-between mb-1"><label class="text-sm font-medium text-gray-700">Customer Feedback</label><div class="flex gap-1"><button @click="generateAIContent('customer_feedback_information')" class="text-xs bg-purple-100 text-purple-700 px-2 py-0.5 rounded hover:bg-purple-200 font-medium cursor-pointer" :disabled="!!generatingField || generatingAll">{{ (generatingField || generatingAll) ? '...' : 'AI' }}</button><button @click="clearField('customer_feedback_information')" class="text-xs text-gray-400 hover:text-red-500 px-1.5 py-0.5 rounded hover:bg-red-50 font-medium cursor-pointer" title="Clear">&times;</button></div></div>
                        <textarea v-model="form.customer_feedback_information" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="What customers are saying about existing solutions..."></textarea></div>
                </div>
                <div class="mt-5"><div class="flex items-center justify-between mb-1"><label class="text-sm font-medium text-gray-700">SWOT from Research</label><div class="flex gap-1"><button @click="generateAIContent('swot_from_research')" class="text-xs bg-purple-100 text-purple-700 px-2 py-0.5 rounded hover:bg-purple-200 font-medium cursor-pointer" :disabled="!!generatingField || generatingAll">{{ (generatingField || generatingAll) ? '...' : 'AI' }}</button><button @click="clearField('swot_from_research')" class="text-xs text-gray-400 hover:text-red-500 px-1.5 py-0.5 rounded hover:bg-red-50 font-medium cursor-pointer" title="Clear">&times;</button></div></div>
                    <textarea v-model="form.swot_from_research" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="Summary SWOT based on your research findings..."></textarea></div>
            </div>

            <!-- Step 9: Competitor Analysis -->
            <div v-if="currentStep === 9" class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h2 class="text-lg font-bold text-gray-900 mb-1">Competitor Analysis</h2>
                <p class="text-sm text-gray-500 mb-6">Analyze your competitive landscape</p>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div><label class="block text-sm font-medium text-gray-700 mb-1">Competitors (JSON array)</label>
                        <textarea v-model="form.competitors" rows="4" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder='["Competitor A","Competitor B","Competitor C"]'></textarea></div>
                    <div><label class="block text-sm font-medium text-gray-700 mb-1">Structured Competitors (JSON)</label>
                        <textarea v-model="form.structured_competitors" rows="4" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm font-mono text-xs" placeholder='[{"name":"Comp A","strengths":["..."],"weaknesses":["..."],"market_share":"30%","pricing":"Premium"}]'></textarea></div>
                </div>
                <div class="mt-5"><div class="flex items-center justify-between mb-1"><label class="text-sm font-medium text-gray-700">Competitive Analysis Summary</label><div class="flex gap-1"><button @click="generateAIContent('competitive_analysis')" class="text-xs bg-purple-100 text-purple-700 px-2 py-0.5 rounded hover:bg-purple-200 font-medium cursor-pointer" :disabled="!!generatingField || generatingAll">{{ (generatingField || generatingAll) ? '...' : 'AI' }}</button><button @click="clearField('competitive_analysis')" class="text-xs text-gray-400 hover:text-red-500 px-1.5 py-0.5 rounded hover:bg-red-50 font-medium cursor-pointer" title="Clear">&times;</button></div></div>
                    <textarea v-model="form.competitive_analysis" rows="4" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="Detailed competitive analysis including market positioning, differentiation, and competitive advantage..."></textarea></div>
            </div>

            <!-- Step 10: SWOT Analysis -->
            <div v-if="currentStep === 10" class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h2 class="text-lg font-bold text-gray-900 mb-1">SWOT Analysis</h2>
                <p class="text-sm text-gray-500 mb-6">Strengths, Weaknesses, Opportunities &amp; Threats</p>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div class="bg-green-50 rounded-lg p-4 border border-green-200">
                        <div class="flex items-center justify-between mb-1"><label class="text-sm font-bold text-green-800">Strengths</label><div class="flex gap-1"><button @click="generateAIContent('swot_strengths')" class="text-xs bg-purple-100 text-purple-700 px-2 py-0.5 rounded hover:bg-purple-200 font-medium cursor-pointer" :disabled="!!generatingField || generatingAll">{{ (generatingField || generatingAll) ? '...' : 'AI' }}</button><button @click="clearField('swot_strengths')" class="text-xs text-gray-400 hover:text-red-500 px-1.5 py-0.5 rounded hover:bg-red-50 font-medium cursor-pointer" title="Clear">&times;</button></div></div>
                        <textarea v-model="form.swot_strengths" rows="4" class="w-full px-3 py-2 border border-green-300 rounded-lg text-sm bg-white" placeholder="Internal strengths that give you an advantage..."></textarea>
                    </div>
                    <div class="bg-red-50 rounded-lg p-4 border border-red-200">
                        <div class="flex items-center justify-between mb-1"><label class="text-sm font-bold text-red-800">Weaknesses</label><div class="flex gap-1"><button @click="generateAIContent('swot_weaknesses')" class="text-xs bg-purple-100 text-purple-700 px-2 py-0.5 rounded hover:bg-purple-200 font-medium cursor-pointer" :disabled="!!generatingField || generatingAll">{{ (generatingField || generatingAll) ? '...' : 'AI' }}</button><button @click="clearField('swot_weaknesses')" class="text-xs text-gray-400 hover:text-red-500 px-1.5 py-0.5 rounded hover:bg-red-50 font-medium cursor-pointer" title="Clear">&times;</button></div></div>
                        <textarea v-model="form.swot_weaknesses" rows="4" class="w-full px-3 py-2 border border-red-300 rounded-lg text-sm bg-white" placeholder="Internal areas that need improvement..."></textarea>
                    </div>
                    <div class="bg-blue-50 rounded-lg p-4 border border-blue-200">
                        <div class="flex items-center justify-between mb-1"><label class="text-sm font-bold text-blue-800">Opportunities</label><div class="flex gap-1"><button @click="generateAIContent('swot_opportunities')" class="text-xs bg-purple-100 text-purple-700 px-2 py-0.5 rounded hover:bg-purple-200 font-medium cursor-pointer" :disabled="!!generatingField || generatingAll">{{ (generatingField || generatingAll) ? '...' : 'AI' }}</button><button @click="clearField('swot_opportunities')" class="text-xs text-gray-400 hover:text-red-500 px-1.5 py-0.5 rounded hover:bg-red-50 font-medium cursor-pointer" title="Clear">&times;</button></div></div>
                        <textarea v-model="form.swot_opportunities" rows="4" class="w-full px-3 py-2 border border-blue-300 rounded-lg text-sm bg-white" placeholder="External opportunities to leverage..."></textarea>
                    </div>
                    <div class="bg-yellow-50 rounded-lg p-4 border border-yellow-200">
                        <div class="flex items-center justify-between mb-1"><label class="text-sm font-bold text-yellow-800">Threats</label><div class="flex gap-1"><button @click="generateAIContent('swot_threats')" class="text-xs bg-purple-100 text-purple-700 px-2 py-0.5 rounded hover:bg-purple-200 font-medium cursor-pointer" :disabled="!!generatingField || generatingAll">{{ (generatingField || generatingAll) ? '...' : 'AI' }}</button><button @click="clearField('swot_threats')" class="text-xs text-gray-400 hover:text-red-500 px-1.5 py-0.5 rounded hover:bg-red-50 font-medium cursor-pointer" title="Clear">&times;</button></div></div>
                        <textarea v-model="form.swot_threats" rows="4" class="w-full px-3 py-2 border border-yellow-300 rounded-lg text-sm bg-white" placeholder="External threats to watch for..."></textarea>
                    </div>
                </div>
            </div>

            <!-- Step 11: Unique Selling Points -->
            <div v-if="currentStep === 11" class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h2 class="text-lg font-bold text-gray-900 mb-1">Unique Selling Points</h2>
                <p class="text-sm text-gray-500 mb-6">What sets your business apart</p>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <div class="flex items-center justify-between mb-1"><label class="text-sm font-medium text-gray-700">Unique Selling Points</label><div class="flex gap-1"><button @click="generateAIContent('unique_selling_points')" class="text-xs bg-purple-100 text-purple-700 px-2 py-0.5 rounded hover:bg-purple-200 font-medium cursor-pointer" :disabled="!!generatingField || generatingAll">{{ (generatingField || generatingAll) ? '...' : 'AI' }}</button><button @click="clearField('unique_selling_points')" class="text-xs text-gray-400 hover:text-red-500 px-1.5 py-0.5 rounded hover:bg-red-50 font-medium cursor-pointer" title="Clear">&times;</button></div></div>
                        <textarea v-model="form.unique_selling_points" rows="4" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="What makes your offering truly unique?"></textarea>
                    </div>
                    <div>
                        <div class="flex items-center justify-between mb-1"><label class="text-sm font-medium text-gray-700">Competitive Advantage</label><div class="flex gap-1"><button @click="generateAIContent('competitive_advantage')" class="text-xs bg-purple-100 text-purple-700 px-2 py-0.5 rounded hover:bg-purple-200 font-medium cursor-pointer" :disabled="!!generatingField || generatingAll">{{ (generatingField || generatingAll) ? '...' : 'AI' }}</button><button @click="clearField('competitive_advantage')" class="text-xs text-gray-400 hover:text-red-500 px-1.5 py-0.5 rounded hover:bg-red-50 font-medium cursor-pointer" title="Clear">&times;</button></div></div>
                        <textarea v-model="form.competitive_advantage" rows="4" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="What is your moat - what makes you hard to copy?"></textarea>
                    </div>
                </div>
                <div class="mt-5"><label class="block text-sm font-medium text-gray-700 mb-1">Customer Pain Points (JSON)</label>
                    <textarea v-model="form.customer_pain_points" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder='["Pain point 1","Pain point 2","Pain point 3"]'></textarea></div>
            </div>

            <!-- Step 12: Marketing Strategy -->
            <div v-if="currentStep === 12" class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h2 class="text-lg font-bold text-gray-900 mb-1">Marketing Strategy</h2>
                <p class="text-sm text-gray-500 mb-6">How you'll reach and attract customers</p>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div><label class="block text-sm font-medium text-gray-700 mb-1">Marketing Channels (JSON)</label>
                        <textarea v-model="form.marketing_channels" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder='["Social Media","Email","Content Marketing","Paid Ads"]'></textarea></div>
                    <div><label class="block text-sm font-medium text-gray-700 mb-1">Promotion Channels (JSON)</label>
                        <textarea v-model="form.promotion_channels" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder='[{"channel":"Facebook","budget":"K500","frequency":"daily"}]'></textarea></div>
                    <div><div class="flex items-center justify-between mb-1"><label class="text-sm font-medium text-gray-700">Branding Approach</label><div class="flex gap-1"><button @click="generateAIContent('branding_approach')" class="text-xs bg-purple-100 text-purple-700 px-2 py-0.5 rounded hover:bg-purple-200 font-medium cursor-pointer" :disabled="!!generatingField || generatingAll">{{ (generatingField || generatingAll) ? '...' : 'AI' }}</button><button @click="clearField('branding_approach')" class="text-xs text-gray-400 hover:text-red-500 px-1.5 py-0.5 rounded hover:bg-red-50 font-medium cursor-pointer" title="Clear">&times;</button></div></div>
                        <textarea v-model="form.branding_approach" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="Your brand identity, personality, and messaging..."></textarea></div>
                    <div><label class="block text-sm font-medium text-gray-700 mb-1">Brand Voice</label>
                        <input v-model="form.brand_voice" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="e.g. Professional, friendly, innovative" /></div>
                </div>
                <div class="mt-5"><div class="flex items-center justify-between mb-1"><label class="text-sm font-medium text-gray-700">Sales Funnel</label><div class="flex gap-1"><button @click="generateAIContent('sales_funnel')" class="text-xs bg-purple-100 text-purple-700 px-2 py-0.5 rounded hover:bg-purple-200 font-medium cursor-pointer" :disabled="!!generatingField || generatingAll">{{ (generatingField || generatingAll) ? '...' : 'AI' }}</button><button @click="clearField('sales_funnel')" class="text-xs text-gray-400 hover:text-red-500 px-1.5 py-0.5 rounded hover:bg-red-50 font-medium cursor-pointer" title="Clear">&times;</button></div></div>
                    <textarea v-model="form.sales_funnel" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="Awareness -> Interest -> Decision -> Action..."></textarea></div>
            </div>

            <!-- Step 13: Sales Strategy -->
            <div v-if="currentStep === 13" class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h2 class="text-lg font-bold text-gray-900 mb-1">Sales Strategy</h2>
                <p class="text-sm text-gray-500 mb-6">How you convert leads and retain customers</p>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div><label class="block text-sm font-medium text-gray-700 mb-1">Sales Channels (JSON)</label>
                        <textarea v-model="form.sales_channels" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder='["Direct Sales","Online Store","Retail Partners"]'></textarea></div>
                    <div><div class="flex items-center justify-between mb-1"><label class="text-sm font-medium text-gray-700">Customer Retention Strategy</label><div class="flex gap-1"><button @click="generateAIContent('customer_retention')" class="text-xs bg-purple-100 text-purple-700 px-2 py-0.5 rounded hover:bg-purple-200 font-medium cursor-pointer" :disabled="!!generatingField || generatingAll">{{ (generatingField || generatingAll) ? '...' : 'AI' }}</button><button @click="clearField('customer_retention')" class="text-xs text-gray-400 hover:text-red-500 px-1.5 py-0.5 rounded hover:bg-red-50 font-medium cursor-pointer" title="Clear">&times;</button></div></div>
                        <textarea v-model="form.customer_retention" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="Loyalty programs, follow-ups, support..."></textarea></div>
                    <div><div class="flex items-center justify-between mb-1"><label class="text-sm font-medium text-gray-700">Sales Process</label><div class="flex gap-1"><button @click="generateAIContent('sales_process')" class="text-xs bg-purple-100 text-purple-700 px-2 py-0.5 rounded hover:bg-purple-200 font-medium cursor-pointer" :disabled="!!generatingField || generatingAll">{{ (generatingField || generatingAll) ? '...' : 'AI' }}</button><button @click="clearField('sales_process')" class="text-xs text-gray-400 hover:text-red-500 px-1.5 py-0.5 rounded hover:bg-red-50 font-medium cursor-pointer" title="Clear">&times;</button></div></div>
                        <textarea v-model="form.sales_process" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="Step-by-step sales process from lead to close..."></textarea></div>
                    <div><div class="flex items-center justify-between mb-1"><label class="text-sm font-medium text-gray-700">Sales Targets</label><div class="flex gap-1"><button @click="generateAIContent('sales_targets')" class="text-xs bg-purple-100 text-purple-700 px-2 py-0.5 rounded hover:bg-purple-200 font-medium cursor-pointer" :disabled="!!generatingField || generatingAll">{{ (generatingField || generatingAll) ? '...' : 'AI' }}</button><button @click="clearField('sales_targets')" class="text-xs text-gray-400 hover:text-red-500 px-1.5 py-0.5 rounded hover:bg-red-50 font-medium cursor-pointer" title="Clear">&times;</button></div></div>
                        <textarea v-model="form.sales_targets" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="Monthly/quarterly targets, growth goals..."></textarea></div>
                </div>
                <div class="mt-5"><div class="flex items-center justify-between mb-1"><label class="text-sm font-medium text-gray-700">CRM Process</label><div class="flex gap-1"><button @click="generateAIContent('crm_process')" class="text-xs bg-purple-100 text-purple-700 px-2 py-0.5 rounded hover:bg-purple-200 font-medium cursor-pointer" :disabled="!!generatingField || generatingAll">{{ (generatingField || generatingAll) ? '...' : 'AI' }}</button><button @click="clearField('crm_process')" class="text-xs text-gray-400 hover:text-red-500 px-1.5 py-0.5 rounded hover:bg-red-50 font-medium cursor-pointer" title="Clear">&times;</button></div></div>
                    <textarea v-model="form.crm_process" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="How you manage customer relationships and data..."></textarea></div>
            </div>

            <!-- Step 14: Operations Plan -->
            <div v-if="currentStep === 14" class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h2 class="text-lg font-bold text-gray-900 mb-1">Operations Plan</h2>
                <p class="text-sm text-gray-500 mb-6">How your business runs day-to-day</p>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div><div class="flex items-center justify-between mb-1"><label class="text-sm font-medium text-gray-700">Daily Operations</label><div class="flex gap-1"><button @click="generateAIContent('daily_operations')" class="text-xs bg-purple-100 text-purple-700 px-2 py-0.5 rounded hover:bg-purple-200 font-medium cursor-pointer" :disabled="!!generatingField || generatingAll">{{ (generatingField || generatingAll) ? '...' : 'AI' }}</button><button @click="clearField('daily_operations')" class="text-xs text-gray-400 hover:text-red-500 px-1.5 py-0.5 rounded hover:bg-red-50 font-medium cursor-pointer" title="Clear">&times;</button></div></div>
                        <textarea v-model="form.daily_operations" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="Describe a typical day of operations..."></textarea></div>
                    <div><div class="flex items-center justify-between mb-1"><label class="text-sm font-medium text-gray-700">Facilities</label><div class="flex gap-1"><button @click="generateAIContent('facilities')" class="text-xs bg-purple-100 text-purple-700 px-2 py-0.5 rounded hover:bg-purple-200 font-medium cursor-pointer" :disabled="!!generatingField || generatingAll">{{ (generatingField || generatingAll) ? '...' : 'AI' }}</button><button @click="clearField('facilities')" class="text-xs text-gray-400 hover:text-red-500 px-1.5 py-0.5 rounded hover:bg-red-50 font-medium cursor-pointer" title="Clear">&times;</button></div></div>
                        <textarea v-model="form.facilities" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="Office, warehouse, retail space requirements..."></textarea></div>
                    <div><div class="flex items-center justify-between mb-1"><label class="text-sm font-medium text-gray-700">Technology Stack</label><div class="flex gap-1"><button @click="generateAIContent('technology_stack')" class="text-xs bg-purple-100 text-purple-700 px-2 py-0.5 rounded hover:bg-purple-200 font-medium cursor-pointer" :disabled="!!generatingField || generatingAll">{{ (generatingField || generatingAll) ? '...' : 'AI' }}</button><button @click="clearField('technology_stack')" class="text-xs text-gray-400 hover:text-red-500 px-1.5 py-0.5 rounded hover:bg-red-50 font-medium cursor-pointer" title="Clear">&times;</button></div></div>
                        <textarea v-model="form.technology_stack" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="Software, hardware, platforms you use..."></textarea></div>
                    <div><div class="flex items-center justify-between mb-1"><label class="text-sm font-medium text-gray-700">Quality Control</label><div class="flex gap-1"><button @click="generateAIContent('quality_control')" class="text-xs bg-purple-100 text-purple-700 px-2 py-0.5 rounded hover:bg-purple-200 font-medium cursor-pointer" :disabled="!!generatingField || generatingAll">{{ (generatingField || generatingAll) ? '...' : 'AI' }}</button><button @click="clearField('quality_control')" class="text-xs text-gray-400 hover:text-red-500 px-1.5 py-0.5 rounded hover:bg-red-50 font-medium cursor-pointer" title="Clear">&times;</button></div></div>
                        <textarea v-model="form.quality_control" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="How do you ensure quality?"></textarea></div>
                    <div><label class="block text-sm font-medium text-gray-700 mb-1">Equipment &amp; Tools (JSON)</label>
                        <textarea v-model="form.equipment_tools" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder='["Computer","Printer","Vehicle"]'></textarea></div>
                    <div><label class="block text-sm font-medium text-gray-700 mb-1">Supplier List (JSON)</label>
                        <textarea v-model="form.supplier_list" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder='[{"name":"Supplier A","product":"Raw materials","cost":"K..."}]'></textarea></div>
                    <div><div class="flex items-center justify-between mb-1"><label class="text-sm font-medium text-gray-700">Operational Workflow</label><div class="flex gap-1"><button @click="generateAIContent('operational_workflow')" class="text-xs bg-purple-100 text-purple-700 px-2 py-0.5 rounded hover:bg-purple-200 font-medium cursor-pointer" :disabled="!!generatingField || generatingAll">{{ (generatingField || generatingAll) ? '...' : 'AI' }}</button><button @click="clearField('operational_workflow')" class="text-xs text-gray-400 hover:text-red-500 px-1.5 py-0.5 rounded hover:bg-red-50 font-medium cursor-pointer" title="Clear">&times;</button></div></div>
                        <textarea v-model="form.operational_workflow" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="Key processes from order to delivery..."></textarea></div>
                </div>
            </div>

            <!-- Step 15: Organization & HR -->
            <div v-if="currentStep === 15" class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h2 class="text-lg font-bold text-gray-900 mb-1">Organization &amp; Human Resources</h2>
                <p class="text-sm text-gray-500 mb-6">Your team structure and people strategy</p>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div><label class="block text-sm font-medium text-gray-700 mb-1">Staff Roles (JSON)</label>
                        <textarea v-model="form.staff_roles" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder='[{"role":"Manager","count":1,"salary":5000}]'></textarea></div>
                    <div><label class="block text-sm font-medium text-gray-700 mb-1">Key Staff (JSON)</label>
                        <textarea v-model="form.key_staff" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder='[{"name":"John","title":"CEO","background":"..."}]'></textarea></div>
                    <div><label class="block text-sm font-medium text-gray-700 mb-1">Departments (JSON)</label>
                        <textarea v-model="form.departments_data" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder='[{"name":"Sales","head_count":3}]'></textarea></div>
                    <div><label class="block text-sm font-medium text-gray-700 mb-1">Org Chart Data (JSON)</label>
                        <textarea v-model="form.organizational_chart_data" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder='{"CEO":{"direct_reports":["CTO","CFO","COO"]}}'></textarea></div>
                </div>
                <div class="mt-5 space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div><div class="flex items-center justify-between mb-1"><label class="text-sm font-medium text-gray-700">Hiring Plan</label><div class="flex gap-1"><button @click="generateAIContent('hiring_plan')" class="text-xs bg-purple-100 text-purple-700 px-2 py-0.5 rounded hover:bg-purple-200 font-medium cursor-pointer" :disabled="!!generatingField || generatingAll">{{ (generatingField || generatingAll) ? '...' : 'AI' }}</button><button @click="clearField('hiring_plan')" class="text-xs text-gray-400 hover:text-red-500 px-1.5 py-0.5 rounded hover:bg-red-50 font-medium cursor-pointer" title="Clear">&times;</button></div></div>
                            <textarea v-model="form.hiring_plan" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="Who do you need to hire and when?"></textarea></div>
                        <div><div class="flex items-center justify-between mb-1"><label class="text-sm font-medium text-gray-700">Recruitment Strategy</label><div class="flex gap-1"><button @click="generateAIContent('recruitment_strategy')" class="text-xs bg-purple-100 text-purple-700 px-2 py-0.5 rounded hover:bg-purple-200 font-medium cursor-pointer" :disabled="!!generatingField || generatingAll">{{ (generatingField || generatingAll) ? '...' : 'AI' }}</button><button @click="clearField('recruitment_strategy')" class="text-xs text-gray-400 hover:text-red-500 px-1.5 py-0.5 rounded hover:bg-red-50 font-medium cursor-pointer" title="Clear">&times;</button></div></div>
                            <textarea v-model="form.recruitment_strategy" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="How will you attract and recruit talent?"></textarea></div>
                        <div><div class="flex items-center justify-between mb-1"><label class="text-sm font-medium text-gray-700">Employee Benefits</label><div class="flex gap-1"><button @click="generateAIContent('employee_benefits')" class="text-xs bg-purple-100 text-purple-700 px-2 py-0.5 rounded hover:bg-purple-200 font-medium cursor-pointer" :disabled="!!generatingField || generatingAll">{{ (generatingField || generatingAll) ? '...' : 'AI' }}</button><button @click="clearField('employee_benefits')" class="text-xs text-gray-400 hover:text-red-500 px-1.5 py-0.5 rounded hover:bg-red-50 font-medium cursor-pointer" title="Clear">&times;</button></div></div>
                            <textarea v-model="form.employee_benefits" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="Salaries, bonuses, health, leave, etc."></textarea></div>
                        <div><div class="flex items-center justify-between mb-1"><label class="text-sm font-medium text-gray-700">Training Plan</label><div class="flex gap-1"><button @click="generateAIContent('training_plan')" class="text-xs bg-purple-100 text-purple-700 px-2 py-0.5 rounded hover:bg-purple-200 font-medium cursor-pointer" :disabled="!!generatingField || generatingAll">{{ (generatingField || generatingAll) ? '...' : 'AI' }}</button><button @click="clearField('training_plan')" class="text-xs text-gray-400 hover:text-red-500 px-1.5 py-0.5 rounded hover:bg-red-50 font-medium cursor-pointer" title="Clear">&times;</button></div></div>
                            <textarea v-model="form.training_plan" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="Onboarding and ongoing training programs"></textarea></div>
                    </div>
                    <div><div class="flex items-center justify-between mb-1"><label class="text-sm font-medium text-gray-700">Performance Management</label><div class="flex gap-1"><button @click="generateAIContent('performance_management')" class="text-xs bg-purple-100 text-purple-700 px-2 py-0.5 rounded hover:bg-purple-200 font-medium cursor-pointer" :disabled="!!generatingField || generatingAll">{{ (generatingField || generatingAll) ? '...' : 'AI' }}</button><button @click="clearField('performance_management')" class="text-xs text-gray-400 hover:text-red-500 px-1.5 py-0.5 rounded hover:bg-red-50 font-medium cursor-pointer" title="Clear">&times;</button></div></div>
                        <textarea v-model="form.performance_management" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="Reviews, KPIs, feedback processes..."></textarea></div>
                </div>
            </div>

            <!-- Step 16: Risk Analysis -->
            <div v-if="currentStep === 16" class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h2 class="text-lg font-bold text-gray-900 mb-1">Risk Analysis</h2>
                <p class="text-sm text-gray-500 mb-6">Identify and plan for potential risks</p>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div><div class="flex items-center justify-between mb-1"><label class="text-sm font-medium text-gray-700">Risks (JSON array)</label><div class="flex gap-1"><button @click="generateAIContent('risks')" class="text-xs bg-purple-100 text-purple-700 px-2 py-0.5 rounded hover:bg-purple-200 font-medium cursor-pointer" :disabled="!!generatingField || generatingAll">{{ (generatingField || generatingAll) ? '...' : 'AI' }}</button><button @click="clearField('risks')" class="text-xs text-gray-400 hover:text-red-500 px-1.5 py-0.5 rounded hover:bg-red-50 font-medium cursor-pointer" title="Clear">&times;</button></div></div>
                        <textarea v-model="form.risks" rows="4" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder='["Market risk","Financial risk","Operational risk"]'></textarea></div>
                    <div><div class="flex items-center justify-between mb-1"><label class="text-sm font-medium text-gray-700">Mitigation Strategies (JSON)</label><div class="flex gap-1"><button @click="generateAIContent('mitigation_strategies')" class="text-xs bg-purple-100 text-purple-700 px-2 py-0.5 rounded hover:bg-purple-200 font-medium cursor-pointer" :disabled="!!generatingField || generatingAll">{{ (generatingField || generatingAll) ? '...' : 'AI' }}</button><button @click="clearField('mitigation_strategies')" class="text-xs text-gray-400 hover:text-red-500 px-1.5 py-0.5 rounded hover:bg-red-50 font-medium cursor-pointer" title="Clear">&times;</button></div></div>
                        <textarea v-model="form.mitigation_strategies" rows="4" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder='[{"risk":"Market","mitigation":"Diversify customer segments"}]'></textarea></div>
                </div>
                <div class="mt-5"><label class="block text-sm font-medium text-gray-700 mb-1">Structured Risks (JSON with probability/impact)</label>
                    <textarea v-model="form.structured_risks" rows="4" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm font-mono text-xs" placeholder='[{"risk":"Competition","probability":"High","impact":"Medium","mitigation":"Focus on niche","category":"Market"}]'></textarea></div>
            </div>

            <!-- Step 17: Implementation Roadmap -->
            <div v-if="currentStep === 17" class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h2 class="text-lg font-bold text-gray-900 mb-1">Implementation Roadmap</h2>
                <p class="text-sm text-gray-500 mb-6">Map your path to launch and beyond</p>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                    <div><div class="flex items-center justify-between mb-1"><label class="text-sm font-medium text-gray-700">Timeline (JSON)</label><div class="flex gap-1"><button @click="generateAIContent('timeline')" class="text-xs bg-purple-100 text-purple-700 px-2 py-0.5 rounded hover:bg-purple-200 font-medium cursor-pointer" :disabled="!!generatingField || generatingAll">{{ (generatingField || generatingAll) ? '...' : 'AI' }}</button><button @click="clearField('timeline')" class="text-xs text-gray-400 hover:text-red-500 px-1.5 py-0.5 rounded hover:bg-red-50 font-medium cursor-pointer" title="Clear">&times;</button></div></div>
                        <textarea v-model="form.timeline" rows="4" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder='[{"phase":"Setup","duration":"Month 1-3","tasks":["Register business","Secure funding"]}]'></textarea></div>
                    <div><div class="flex items-center justify-between mb-1"><label class="text-sm font-medium text-gray-700">Milestones (JSON)</label><div class="flex gap-1"><button @click="generateAIContent('milestones')" class="text-xs bg-purple-100 text-purple-700 px-2 py-0.5 rounded hover:bg-purple-200 font-medium cursor-pointer" :disabled="!!generatingField || generatingAll">{{ (generatingField || generatingAll) ? '...' : 'AI' }}</button><button @click="clearField('milestones')" class="text-xs text-gray-400 hover:text-red-500 px-1.5 py-0.5 rounded hover:bg-red-50 font-medium cursor-pointer" title="Clear">&times;</button></div></div>
                        <textarea v-model="form.milestones" rows="4" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder='[{"milestone":"First Sale","target_date":"Month 3"},{"milestone":"Break-even","target_date":"Month 8"}]'></textarea></div>
                    <div><label class="block text-sm font-medium text-gray-700 mb-1">Resource Requirements (JSON)</label>
                        <textarea v-model="form.resource_requirements" rows="4" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder='{"capital":"K50,000","staff":3,"equipment":"..."}'></textarea></div>
                </div>
            </div>

            <!-- Step 18: Financial Plan -->
            <div v-if="currentStep === 18" class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h2 class="text-lg font-bold text-gray-900 mb-1">Financial Plan</h2>
                <p class="text-sm text-gray-500 mb-6">Your financial projections and assumptions</p>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
                    <div><label class="block text-sm font-medium text-gray-700 mb-1">Startup Costs (K)</label><input v-model.number="form.startup_costs" type="number" min="0" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" /></div>
                    <div><label class="block text-sm font-medium text-gray-700 mb-1">Monthly Operating Costs (K)</label><input v-model.number="form.monthly_operating_costs" type="number" min="0" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" /></div>
                    <div><label class="block text-sm font-medium text-gray-700 mb-1">Expected Monthly Revenue (K)</label><input v-model.number="form.expected_monthly_revenue" type="number" min="0" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" /></div>
                    <div><label class="block text-sm font-medium text-gray-700 mb-1">Price Per Unit (K)</label><input v-model.number="form.price_per_unit" type="number" min="0" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" /></div>
                    <div><label class="block text-sm font-medium text-gray-700 mb-1">Expected Sales Volume</label><input v-model.number="form.expected_sales_volume" type="number" min="0" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" /></div>
                    <div><label class="block text-sm font-medium text-gray-700 mb-1">Staff Salaries (K)</label><input v-model.number="form.staff_salaries" type="number" min="0" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" /></div>
                    <div><label class="block text-sm font-medium text-gray-700 mb-1">Inventory Costs (K)</label><input v-model.number="form.inventory_costs" type="number" min="0" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" /></div>
                    <div><label class="block text-sm font-medium text-gray-700 mb-1">Utilities (K)</label><input v-model.number="form.utilities_costs" type="number" min="0" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" /></div>
                </div>
                <div v-if="form.expected_monthly_revenue || form.monthly_operating_costs || form.staff_salaries" class="mt-6 p-4 bg-blue-50 rounded-lg">
                    <p class="text-sm font-medium text-blue-800">Estimated Monthly Profit: <span :class="monthlyProfit >= 0 ? 'text-green-700' : 'text-red-700'">{{ formatCurrency(monthlyProfit) }}</span></p>
                    <p class="text-xs text-blue-600 mt-1">Revenue ({{ formatCurrency(form.expected_monthly_revenue) }}) - Operating Costs ({{ formatCurrency(form.monthly_operating_costs) }}) - Salaries ({{ formatCurrency(form.staff_salaries) }})</p>
                </div>
                <div class="mt-5"><div class="flex items-center justify-between mb-1"><label class="text-sm font-medium text-gray-700">Financial Projections (JSON)</label><div class="flex gap-1"><button @click="generateAIContent('financial_projections')" class="text-xs bg-purple-100 text-purple-700 px-2 py-0.5 rounded hover:bg-purple-200 font-medium cursor-pointer" :disabled="!!generatingField || generatingAll">{{ (generatingField || generatingAll) ? '...' : 'AI' }}</button><button @click="clearField('financial_projections')" class="text-xs text-gray-400 hover:text-red-500 px-1.5 py-0.5 rounded hover:bg-red-50 font-medium cursor-pointer" title="Clear">&times;</button></div></div>
                    <textarea v-model="form.financial_projections" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm font-mono text-xs" placeholder='{"year_1":{"revenue":500000,"costs":350000},"year_2":{"revenue":800000,"costs":500000}}'></textarea></div>
            </div>

            <!-- Step 19: Advanced Financials & Funding -->
            <div v-if="currentStep === 19" class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h2 class="text-lg font-bold text-gray-900 mb-1">Advanced Financials &amp; Funding</h2>
                <p class="text-sm text-gray-500 mb-6">Break-even, funding needs, and detailed projections</p>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div><div class="flex items-center justify-between mb-1"><label class="text-sm font-medium text-gray-700">Break-Even Analysis</label><div class="flex gap-1"><button @click="generateAIContent('break_even_analysis')" class="text-xs bg-purple-100 text-purple-700 px-2 py-0.5 rounded hover:bg-purple-200 font-medium cursor-pointer" :disabled="!!generatingField || generatingAll">{{ (generatingField || generatingAll) ? '...' : 'AI' }}</button><button @click="clearField('break_even_analysis')" class="text-xs text-gray-400 hover:text-red-500 px-1.5 py-0.5 rounded hover:bg-red-50 font-medium cursor-pointer" title="Clear">&times;</button></div></div>
                        <textarea v-model="form.break_even_analysis" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="When will you break even and how?"></textarea></div>
                    <div><label class="block text-sm font-medium text-gray-700 mb-1">Funding Requirements (JSON)</label>
                        <textarea v-model="form.funding_requirements" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder='[{"source":"Bank Loan","amount":100000,"terms":"5 years","status":"Pending"}]'></textarea></div>
                    <div><label class="block text-sm font-medium text-gray-700 mb-1">P&amp;L Projection (JSON)</label>
                        <textarea v-model="form.profit_loss_projection" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm font-mono text-xs" placeholder='{"month_1":{"revenue":0,"costs":10000},"month_3":{"revenue":15000,"costs":12000}}'></textarea></div>
                    <div><label class="block text-sm font-medium text-gray-700 mb-1">Cash Flow Projection (JSON)</label>
                        <textarea v-model="form.cash_flow_projection" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm font-mono text-xs" placeholder='{"q1":{"inflow":50000,"outflow":40000},"q2":{"inflow":60000,"outflow":45000}}'></textarea></div>
                    <div><label class="block text-sm font-medium text-gray-700 mb-1">Balance Sheet (JSON)</label>
                        <textarea v-model="form.balance_sheet_projection" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm font-mono text-xs" placeholder='{"assets":{"cash":50000},"liabilities":{"loans":30000},"equity":{"capital":20000}}'></textarea></div>
                    <div><label class="block text-sm font-medium text-gray-700 mb-1">Financial Ratios (JSON)</label>
                        <textarea v-model="form.financial_ratios" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder='{"gross_margin":"60%","net_margin":"20%","roi":"25%"}'></textarea></div>
                </div>
                <div class="mt-5"><label class="block text-sm font-medium text-gray-700 mb-1">Scenario Planning (JSON)</label>
                    <textarea v-model="form.scenario_planning_data" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm font-mono text-xs" placeholder='{"best_case":{"revenue":100000,"profit":30000},"expected":{"revenue":75000,"profit":15000},"worst_case":{"revenue":50000,"profit":5000}}'></textarea></div>
            </div>

            <!-- Step 20: Exit Strategy & Review -->
            <div v-if="currentStep === 20" class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h2 class="text-lg font-bold text-gray-900 mb-1">Exit Strategy &amp; Review</h2>
                <p class="text-sm text-gray-500 mb-6">Plan your eventual exit and review your complete business plan</p>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-6">
                    <div><label class="block text-sm font-medium text-gray-700 mb-1">Exit Strategy Type</label>
                        <select v-model="form.exit_strategy_type" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                            <option value="">Select...</option><option value="acquisition">Acquisition</option><option value="merger">Merger</option><option value="ipo">IPO</option><option value="succession">Family Succession</option><option value="management_buyout">Management Buyout</option><option value="liquidation">Liquidation</option>
                        </select></div>
                    <div><div class="flex items-center justify-between mb-1"><label class="text-sm font-medium text-gray-700">Exit Strategy Details</label><div class="flex gap-1"><button @click="generateAIContent('exit_strategy_details')" class="text-xs bg-purple-100 text-purple-700 px-2 py-0.5 rounded hover:bg-purple-200 font-medium cursor-pointer" :disabled="!!generatingField || generatingAll">{{ (generatingField || generatingAll) ? '...' : 'AI' }}</button><button @click="clearField('exit_strategy_details')" class="text-xs text-gray-400 hover:text-red-500 px-1.5 py-0.5 rounded hover:bg-red-50 font-medium cursor-pointer" title="Clear">&times;</button></div></div>
                        <textarea v-model="form.exit_strategy_details" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="Describe your exit plan in detail..."></textarea></div>
                </div>
                <div class="mb-6"><label class="block text-sm font-medium text-gray-700 mb-1">Appendices (JSON - file refs or links)</label>
                    <textarea v-model="form.appendices" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder='[{"name":"Market Report","url":"..."},{"name":"Financial Model","url":"..."}]'></textarea></div>

                <!-- Review Summary -->
                <div class="p-4 bg-gray-50 rounded-lg mb-6">
                    <h3 class="font-semibold text-gray-800 mb-2">Business Plan Summary</h3>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                        <div><span class="text-gray-500">Business:</span> {{ form.business_name || '-' }}</div>
                        <div><span class="text-gray-500">Industry:</span> {{ form.industry || '-' }}</div>
                        <div><span class="text-gray-500">Structure:</span> {{ form.legal_structure || '-' }}</div>
                        <div><span class="text-gray-500">Stage:</span> {{ form.business_stage || '-' }}</div>
                    </div>
                    <div class="mt-2 text-sm text-gray-500">
                        <span>20 modules completed:</span>
                        <span class="text-green-600 font-medium ml-1">{{ progressPercent }}%</span>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <button @click="saveDraft" :disabled="saving" class="px-6 py-2.5 bg-gray-600 text-white font-medium rounded-lg hover:bg-gray-700 disabled:opacity-50">Save Draft</button>
                    <button v-if="form.id" @click="completePlan" class="px-6 py-2.5 bg-green-600 text-white font-medium rounded-lg hover:bg-green-700">Mark as Complete</button>
                </div>
            </div>

            <!-- Navigation Buttons -->
            <div v-if="!(currentStep === 1 && !form.wizard_completed && !props.existingPlan)" class="flex items-center justify-between mt-6">
                <button @click="prevStep" v-if="currentStep > 1" class="px-5 py-2.5 border border-gray-300 text-gray-700 text-sm rounded-lg hover:bg-gray-50">&larr; Previous</button>
                <div v-else></div>
                <button @click="nextStep" v-if="currentStep < totalSteps" class="px-5 py-2.5 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700">Next &rarr;</button>
            </div>
        </div>

        <!-- Chat toggle button -->
        <button @click="showChat = !showChat" class="fixed bottom-6 right-6 z-50 w-14 h-14 bg-purple-600 text-white rounded-full shadow-lg hover:bg-purple-700 flex items-center justify-center text-2xl" :title="showChat ? 'Close AI Chat' : 'Open AI Chat'">
            <span v-if="!showChat">&#x1F4AC;</span>
            <span v-else>&times;</span>
        </button>

        <!-- Chat panel -->
        <div v-if="showChat" class="fixed bottom-24 right-6 z-50 w-96 h-[500px] bg-white rounded-xl shadow-2xl border border-gray-200 flex flex-col overflow-hidden">
            <div class="bg-purple-600 text-white px-4 py-3 flex items-center justify-between">
                <span class="font-semibold text-sm">AI Business Plan Assistant</span>
                <button @click="showChat = false" class="text-white/80 hover:text-white">&times;</button>
            </div>
            <div ref="chatListRef" class="flex-1 overflow-y-auto p-3 space-y-3 text-sm">
                <div v-if="chatMessages.length === 0" class="text-gray-400 text-center mt-10">
                    <p class="text-lg mb-1">&#x1F4AC;</p>
                    <p>Ask me to write anything for your plan.</p>
                    <p class="text-xs mt-2 text-gray-300">e.g. "Write a mission statement about youth"</p>
                </div>
                <div v-for="(m, i) in chatMessages" :key="i" :class="m.role === 'user' ? 'text-right' : 'text-left'">
                    <div :class="m.role === 'user' ? 'bg-blue-100 text-blue-900 inline-block rounded-lg px-3 py-2 max-w-xs text-left' : 'bg-gray-100 text-gray-800 inline-block rounded-lg px-3 py-2 max-w-xs text-left'">
                        <div class="whitespace-pre-wrap">{{ m.content }}</div>
                        <div v-if="m.field" class="mt-2 pt-2 border-t border-gray-200 flex gap-2">
                            <button @click="applyChatField(m.field!, m.content)" class="text-xs bg-purple-600 text-white px-2 py-1 rounded hover:bg-purple-700">Apply to {{ m.field.replace(/_/g, ' ') }}</button>
                        </div>
                    </div>
                </div>
                <div v-if="chatLoading" class="text-left">
                    <div class="bg-gray-100 text-gray-500 inline-block rounded-lg px-3 py-2 text-xs italic">Thinking...</div>
                </div>
            </div>
            <div class="border-t border-gray-200 p-3 flex gap-2">
                <input v-model="chatInput" @keydown="handleChatKeydown" type="text" placeholder="Ask AI to write something..." class="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-sm focus:border-purple-400 focus:ring-1 focus:ring-purple-400" :disabled="chatLoading" />
                <button @click="sendChatMessage" :disabled="chatLoading || !chatInput.trim()" class="px-4 py-2 bg-purple-600 text-white text-sm rounded-lg hover:bg-purple-700 disabled:opacity-50">Send</button>
            </div>
        </div>

        <!-- Preview Modal -->
        <div v-if="showPreview" class="fixed inset-0 z-[100] bg-black/60 flex items-center justify-center p-4" @click.self="showPreview = false">
            <div class="bg-white rounded-xl shadow-2xl w-full max-w-4xl max-h-[90vh] flex flex-col overflow-hidden">
                <div class="bg-gradient-to-r from-indigo-600 to-purple-600 text-white px-6 py-4 flex items-center justify-between">
                    <div>
                        <h2 class="text-lg font-semibold">{{ form.business_name || 'Untitled Plan' }} &mdash; Preview</h2>
                        <p class="text-xs text-white/70 mt-0.5">{{ progressPercent }}% complete &middot; {{ form.business_name ? form.industry || 'Industry not set' : 'No business name yet' }}</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <button @click="showPreview = false" class="text-white/80 hover:text-white text-2xl leading-none">&times;</button>
                    </div>
                </div>
                <div class="flex-1 overflow-y-auto p-6 space-y-8">
                    <div v-for="step in steps" :key="step.num">
                        <h3 class="text-sm font-bold uppercase tracking-wider text-indigo-700 border-b border-indigo-200 pb-1 mb-3">
                            {{ step.num }}. {{ step.label }}
                        </h3>
                        <div class="space-y-3">
                            <div v-for="f in (stepFields[step.num] || [])" :key="f.key">
                                <div class="text-xs font-medium text-gray-500 mb-0.5">{{ f.label }}</div>
                                <div class="text-sm text-gray-800 whitespace-pre-wrap" v-html="previewAsHtml(f.key, (form as any)[f.key])"></div>
                            </div>
                            <div v-if="!(stepFields[step.num] || []).length" class="text-xs text-gray-400 italic">No fields for this module.</div>
                            <div v-if="(stepFields[step.num] || []).every(f => !formatPreviewValue(f.key, (form as any)[f.key]))" class="text-xs text-gray-400 italic">No content yet for this module.</div>
                        </div>
                    </div>
                </div>
                <div class="border-t border-gray-200 px-6 py-3 flex items-center justify-between bg-gray-50">
                    <span class="text-xs text-gray-500">{{ lastSavedAt ? 'Last saved ' + lastSavedAt : 'Not saved yet' }}</span>
                    <button @click="showPreview = false" class="px-4 py-2 bg-indigo-600 text-white text-sm rounded-lg hover:bg-indigo-700">Close</button>
                </div>
            </div>
        </div>
    </CMSLayout>
</template>
