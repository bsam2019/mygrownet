<script setup lang="ts">
import { Head, router, usePage } from '@inertiajs/vue3';
import CMSLayout from '@/Layouts/CMSLayout.vue';
import { ref, computed, watch, nextTick } from 'vue';

const props = defineProps<{ existingPlan: any | null; userTier: string }>();
const page = usePage();

const totalSteps = 20;
const currentStep = ref(props.existingPlan?.current_step || 1);
const saving = ref(false);
const autoSaving = ref(false);
const lastSavedAt = ref<string | null>(null);
const generating = ref(false);
const generatedContent = ref<string | null>(null);
const currentAIField = ref<string | null>(null);
let autoSaveTimer: ReturnType<typeof setTimeout> | null = null;

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
    autoSaveTimer = setTimeout(() => {
        if (!form.value.business_name) return;
        autoSaving.value = true;
        form.value.current_step = currentStep.value;
        router.post(route('cms.business-plans.save'), form.value, {
            preserveScroll: true,
            preserveState: true,
            onSuccess: (res: any) => {
                if (res.props?.flash?.businessPlan?.id) form.value.id = res.props.flash.businessPlan.id;
                lastSavedAt.value = new Date().toLocaleTimeString();
            },
            onFinish: () => { autoSaving.value = false; },
        });
    }, 2000);
};

watch(form, triggerAutoSave, { deep: true });

const goToStep = (step: number) => {
    if (step >= 1 && step <= totalSteps) currentStep.value = step;
};

const saveDraft = async () => {
    saving.value = true;
    saveToLocalStorage();
    form.value.current_step = currentStep.value;
    router.post(route('cms.business-plans.save'), form.value, {
        preserveScroll: true,
        preserveState: true,
        onSuccess: (res: any) => {
            if (res.props?.flash?.businessPlan?.id) form.value.id = res.props.flash.businessPlan.id;
        },
        onFinish: () => { saving.value = false; },
    });
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

const generateAIContent = (field: string) => {
    generating.value = true;
    generatedContent.value = null;
    currentAIField.value = field;
    const ctx = { business_name: form.value.business_name, industry: form.value.industry, ...form.value };
    router.post(route('cms.business-plans.generate-ai'), {
        business_plan_id: form.value.id,
        field,
        context: ctx,
    }, {
        preserveScroll: true,
        preserveState: true,
        onFinish: () => { generating.value = false; },
    });
};

const applyGenerated = (field: string) => {
    if (generatedContent.value) {
        (form.value as any)[field] = generatedContent.value;
        generatedContent.value = null;
        currentAIField.value = null;
    }
};

const formatCurrency = (v: number | null) => v != null ? `K${Number(v).toLocaleString()}` : '-';

const csrfToken = () => document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

const chatMessages = ref<{role: string, content: string, field?: string}[]>([]);
const chatInput = ref('');
const chatLoading = ref(false);
const showChat = ref(false);

const sendChatMessage = async () => {
    if (!chatInput.value.trim() || chatLoading.value) return;
    const msg = chatInput.value.trim();
    chatInput.value = '';
    chatMessages.value.push({ role: 'user', content: msg });
    chatLoading.value = true;
    try {
        const resp = await fetch(route('cms.business-plans.chat').url(), {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken() },
            body: JSON.stringify({ message: msg, context: { business_name: form.value.business_name, industry: form.value.industry, ...form.value } }),
        });
        const data = await resp.json();
        if (data.type === 'field' && data.field && data.content) {
            chatMessages.value.push({ role: 'assistant', content: data.content, field: data.field });
        } else {
            chatMessages.value.push({ role: 'assistant', content: data.content || 'Could you be more specific?' });
        }
    } catch {
        chatMessages.value.push({ role: 'assistant', content: 'Connection error. Please try again.' });
    }
    chatLoading.value = false;
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

            <!-- AI Customization Prompt -->
            <div class="mb-4 p-4 bg-purple-50 rounded-lg border border-purple-200">
                <div class="flex items-center justify-between mb-1">
                    <label class="text-xs font-semibold text-purple-700 uppercase tracking-wider">AI Instructions <span class="text-purple-400 font-normal normal-case">(optional)</span></label>
                    <span class="text-[10px] text-purple-400">Tell the AI what to focus on</span>
                </div>
                <div class="flex gap-2">
                    <input v-model="form.user_ai_instructions" type="text" class="flex-1 px-3 py-2 text-sm border border-purple-200 rounded-lg focus:border-purple-400 focus:ring-1 focus:ring-purple-400" placeholder="e.g. Focus on youth market, include sustainability, emphasize local impact..." />
                    <button v-if="form.user_ai_instructions" @click="form.user_ai_instructions = ''" class="px-2 text-xs text-purple-500 hover:text-purple-700">Clear</button>
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

            <!-- Step 1: Business Profile -->
            <div v-if="currentStep === 1" class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h2 class="text-lg font-bold text-gray-900 mb-1">Business Profile</h2>
                <p class="text-sm text-gray-500 mb-6">Basic information about your business</p>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div><label class="block text-sm font-medium text-gray-700 mb-1">Business Name <span class="text-red-500">*</span></label>
                        <input v-model="form.business_name" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="e.g. MyGrowNet Success Hub" /></div>
                    <div><div class="flex items-center justify-between mb-1"><label class="text-sm font-medium text-gray-700">Tagline</label><button @click="generateAIContent('tagline')" class="text-xs bg-purple-100 text-purple-700 px-2 py-0.5 rounded hover:bg-purple-200 font-medium cursor-pointer">AI</button></div>
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
            </div>

            <!-- Step 2: Company Description -->
            <div v-if="currentStep === 2" class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h2 class="text-lg font-bold text-gray-900 mb-1">Company Description</h2>
                <p class="text-sm text-gray-500 mb-6">Define your business purpose, values, and direction</p>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <div class="flex items-center justify-between mb-1"><label class="text-sm font-medium text-gray-700">Mission Statement</label><button @click="generateAIContent('mission_statement')" class="text-xs bg-purple-100 text-purple-700 px-2 py-0.5 rounded hover:bg-purple-200 font-medium cursor-pointer">AI</button></div>
                        <textarea v-model="form.mission_statement" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="Our mission is to..."></textarea>
                    </div>
                    <div>
                        <div class="flex items-center justify-between mb-1"><label class="text-sm font-medium text-gray-700">Vision Statement</label><button @click="generateAIContent('vision_statement')" class="text-xs bg-purple-100 text-purple-700 px-2 py-0.5 rounded hover:bg-purple-200 font-medium cursor-pointer">AI</button></div>
                        <textarea v-model="form.vision_statement" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="To become the leading..."></textarea>
                    </div>
                    <div><div class="flex items-center justify-between mb-1"><label class="text-sm font-medium text-gray-700">Core Values (JSON array)</label><button @click="generateAIContent('core_values')" class="text-xs bg-purple-100 text-purple-700 px-2 py-0.5 rounded hover:bg-purple-200 font-medium cursor-pointer">AI</button></div>
                        <textarea v-model="form.core_values" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder='["Integrity","Innovation","Customer Focus"]'></textarea></div>
                    <div><div class="flex items-center justify-between mb-1"><label class="text-sm font-medium text-gray-700">Business Objectives</label><button @click="generateAIContent('business_objectives')" class="text-xs bg-purple-100 text-purple-700 px-2 py-0.5 rounded hover:bg-purple-200 font-medium cursor-pointer">AI</button></div>
                        <textarea v-model="form.business_objectives" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="What you aim to achieve..."></textarea></div>
                    <div><div class="flex items-center justify-between mb-1"><label class="text-sm font-medium text-gray-700">Company History</label><button @click="generateAIContent('company_history')" class="text-xs bg-purple-100 text-purple-700 px-2 py-0.5 rounded hover:bg-purple-200 font-medium cursor-pointer">AI</button></div>
                        <textarea v-model="form.company_history" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="How and when the business started..."></textarea></div>
                    <div><div class="flex items-center justify-between mb-1"><label class="text-sm font-medium text-gray-700">Long-Term Goals</label><button @click="generateAIContent('long_term_goals')" class="text-xs bg-purple-100 text-purple-700 px-2 py-0.5 rounded hover:bg-purple-200 font-medium cursor-pointer">AI</button></div>
                        <textarea v-model="form.long_term_goals" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="Where do you see the business in 5-10 years?"></textarea></div>
                </div>
                <div class="mt-5">
                    <div class="flex items-center justify-between mb-1"><label class="text-sm font-medium text-gray-700">Business Background</label><button @click="generateAIContent('background')" class="text-xs bg-purple-100 text-purple-700 px-2 py-0.5 rounded hover:bg-purple-200 font-medium cursor-pointer">AI</button></div>
                    <textarea v-model="form.background" rows="4" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="Tell the full story of your business..."></textarea>
                </div>
                <div class="mt-5"><div class="flex items-center justify-between mb-1"><label class="text-sm font-medium text-gray-700">Key Success Factors</label><button @click="generateAIContent('success_factors')" class="text-xs bg-purple-100 text-purple-700 px-2 py-0.5 rounded hover:bg-purple-200 font-medium cursor-pointer">AI</button></div>
                    <textarea v-model="form.success_factors" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="What will make your business successful?"></textarea></div>
            </div>

            <!-- Step 3: Problem Statement -->
            <div v-if="currentStep === 3" class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h2 class="text-lg font-bold text-gray-900 mb-1">Problem Statement</h2>
                <p class="text-sm text-gray-500 mb-6">Define the problem your business solves</p>
                <div class="space-y-5">
                    <div>
                        <div class="flex items-center justify-between mb-1"><label class="text-sm font-medium text-gray-700">Problem Statement</label><button @click="generateAIContent('problem_statement')" class="text-xs bg-purple-100 text-purple-700 px-2 py-0.5 rounded hover:bg-purple-200 font-medium cursor-pointer">AI</button></div>
                        <textarea v-model="form.problem_statement" rows="4" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="What specific problem are you solving for your customers?"></textarea>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div><div class="flex items-center justify-between mb-1"><label class="text-sm font-medium text-gray-700">Existing Alternatives</label><button @click="generateAIContent('existing_alternatives')" class="text-xs bg-purple-100 text-purple-700 px-2 py-0.5 rounded hover:bg-purple-200 font-medium cursor-pointer">AI</button></div>
                            <textarea v-model="form.existing_alternatives" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="How are customers currently solving this problem?"></textarea></div>
                        <div><div class="flex items-center justify-between mb-1"><label class="text-sm font-medium text-gray-700">Why Existing Alternatives Fail</label><button @click="generateAIContent('why_existing_fail')" class="text-xs bg-purple-100 text-purple-700 px-2 py-0.5 rounded hover:bg-purple-200 font-medium cursor-pointer">AI</button></div>
                            <textarea v-model="form.why_existing_fail" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="Why aren't current solutions good enough?"></textarea></div>
                    </div>
                    <div>
                        <div class="flex items-center justify-between mb-1"><label class="text-sm font-medium text-gray-700">Solution Description</label><button @click="generateAIContent('solution_description')" class="text-xs bg-purple-100 text-purple-700 px-2 py-0.5 rounded hover:bg-purple-200 font-medium cursor-pointer">AI</button></div>
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
                        <div class="flex items-center justify-between mb-1"><label class="text-sm font-medium text-gray-700">Product/Service Description</label><button @click="generateAIContent('product_description')" class="text-xs bg-purple-100 text-purple-700 px-2 py-0.5 rounded hover:bg-purple-200 font-medium cursor-pointer">AI</button></div>
                        <textarea v-model="form.product_description" rows="4" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="Describe your products or services in detail..."></textarea>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div><div class="flex items-center justify-between mb-1"><label class="text-sm font-medium text-gray-700">Delivery Method</label><button @click="generateAIContent('delivery_method')" class="text-xs bg-purple-100 text-purple-700 px-2 py-0.5 rounded hover:bg-purple-200 font-medium cursor-pointer">AI</button></div>
                            <textarea v-model="form.delivery_method" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="How do you deliver? (online, physical, hybrid...)"></textarea></div>
                        <div><div class="flex items-center justify-between mb-1"><label class="text-sm font-medium text-gray-700">Product Lifecycle</label><button @click="generateAIContent('product_lifecycle')" class="text-xs bg-purple-100 text-purple-700 px-2 py-0.5 rounded hover:bg-purple-200 font-medium cursor-pointer">AI</button></div>
                            <textarea v-model="form.product_lifecycle" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="Development, growth, maturity, decline stages..."></textarea></div>
                    </div>
                    <div><div class="flex items-center justify-between mb-1"><label class="text-sm font-medium text-gray-700">Future Improvements &amp; Roadmap</label><button @click="generateAIContent('future_improvements')" class="text-xs bg-purple-100 text-purple-700 px-2 py-0.5 rounded hover:bg-purple-200 font-medium cursor-pointer">AI</button></div>
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
                    <div><div class="flex items-center justify-between mb-1"><label class="text-sm font-medium text-gray-700">Key Features (JSON array)</label><button @click="generateAIContent('product_features')" class="text-xs bg-purple-100 text-purple-700 px-2 py-0.5 rounded hover:bg-purple-200 font-medium cursor-pointer">AI</button></div>
                        <textarea v-model="form.product_features" rows="4" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder='["Feature 1","Feature 2"]'></textarea></div>
                    <div><div class="flex items-center justify-between mb-1"><label class="text-sm font-medium text-gray-700">Production Process</label><button @click="generateAIContent('production_process')" class="text-xs bg-purple-100 text-purple-700 px-2 py-0.5 rounded hover:bg-purple-200 font-medium cursor-pointer">AI</button></div>
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
                    <div><div class="flex items-center justify-between mb-1"><label class="text-sm font-medium text-gray-700">Pricing Strategy</label><button @click="generateAIContent('pricing_strategy')" class="text-xs bg-purple-100 text-purple-700 px-2 py-0.5 rounded hover:bg-purple-200 font-medium cursor-pointer">AI</button></div>
                        <textarea v-model="form.pricing_strategy" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="Competitive, value-based, penetration, premium?"></textarea></div>
                    <div><div class="flex items-center justify-between mb-1"><label class="text-sm font-medium text-gray-700">Revenue Streams</label><button @click="generateAIContent('revenue_streams')" class="text-xs bg-purple-100 text-purple-700 px-2 py-0.5 rounded hover:bg-purple-200 font-medium cursor-pointer">AI</button></div>
                        <textarea v-model="form.revenue_streams" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="Direct sales, subscriptions, advertising, commissions..."></textarea></div>
                    <div><div class="flex items-center justify-between mb-1"><label class="text-sm font-medium text-gray-700">Cost Structure</label><button @click="generateAIContent('cost_structure')" class="text-xs bg-purple-100 text-purple-700 px-2 py-0.5 rounded hover:bg-purple-200 font-medium cursor-pointer">AI</button></div>
                        <textarea v-model="form.cost_structure" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="Fixed costs, variable costs, economies of scale..."></textarea></div>
                    <div><div class="flex items-center justify-between mb-1"><label class="text-sm font-medium text-gray-700">Customer Relationships</label><button @click="generateAIContent('customer_relationships')" class="text-xs bg-purple-100 text-purple-700 px-2 py-0.5 rounded hover:bg-purple-200 font-medium cursor-pointer">AI</button></div>
                        <textarea v-model="form.customer_relationships" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="Personal assistance, self-service, communities..."></textarea></div>
                    <div><div class="flex items-center justify-between mb-1"><label class="text-sm font-medium text-gray-700">Channels</label><button @click="generateAIContent('channels')" class="text-xs bg-purple-100 text-purple-700 px-2 py-0.5 rounded hover:bg-purple-200 font-medium cursor-pointer">AI</button></div>
                        <textarea v-model="form.channels" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="How do you reach customers?"></textarea></div>
                    <div><div class="flex items-center justify-between mb-1"><label class="text-sm font-medium text-gray-700">Key Activities</label><button @click="generateAIContent('key_activities')" class="text-xs bg-purple-100 text-purple-700 px-2 py-0.5 rounded hover:bg-purple-200 font-medium cursor-pointer">AI</button></div>
                        <textarea v-model="form.key_activities" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="Most important actions to make business model work"></textarea></div>
                    <div><div class="flex items-center justify-between mb-1"><label class="text-sm font-medium text-gray-700">Key Resources</label><button @click="generateAIContent('key_resources')" class="text-xs bg-purple-100 text-purple-700 px-2 py-0.5 rounded hover:bg-purple-200 font-medium cursor-pointer">AI</button></div>
                        <textarea v-model="form.key_resources" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="Physical, intellectual, human, financial assets"></textarea></div>
                    <div><div class="flex items-center justify-between mb-1"><label class="text-sm font-medium text-gray-700">Key Partners</label><button @click="generateAIContent('key_partners')" class="text-xs bg-purple-100 text-purple-700 px-2 py-0.5 rounded hover:bg-purple-200 font-medium cursor-pointer">AI</button></div>
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
                    <div><div class="flex items-center justify-between mb-1"><label class="text-sm font-medium text-gray-700">Target Market</label><button @click="generateAIContent('target_market')" class="text-xs bg-purple-100 text-purple-700 px-2 py-0.5 rounded hover:bg-purple-200 font-medium cursor-pointer">AI</button></div>
                        <textarea v-model="form.target_market" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="Who are your target customers?"></textarea></div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-5 mt-5">
                    <div><div class="flex items-center justify-between mb-1"><label class="text-sm font-medium text-gray-700">Industry Trends</label><button @click="generateAIContent('industry_trends')" class="text-xs bg-purple-100 text-purple-700 px-2 py-0.5 rounded hover:bg-purple-200 font-medium cursor-pointer">AI</button></div>
                        <textarea v-model="form.industry_trends" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="Current trends shaping the industry"></textarea></div>
                    <div><div class="flex items-center justify-between mb-1"><label class="text-sm font-medium text-gray-700">Regulations</label><button @click="generateAIContent('regulations')" class="text-xs bg-purple-100 text-purple-700 px-2 py-0.5 rounded hover:bg-purple-200 font-medium cursor-pointer">AI</button></div>
                        <textarea v-model="form.regulations" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="Laws, licenses, compliance requirements"></textarea></div>
                    <div><div class="flex items-center justify-between mb-1"><label class="text-sm font-medium text-gray-700">Technology Changes</label><button @click="generateAIContent('technology_changes')" class="text-xs bg-purple-100 text-purple-700 px-2 py-0.5 rounded hover:bg-purple-200 font-medium cursor-pointer">AI</button></div>
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
                    <div><div class="flex items-center justify-between mb-1"><label class="text-sm font-medium text-gray-700">Survey Data &amp; Findings</label><button @click="generateAIContent('surveys_data')" class="text-xs bg-purple-100 text-purple-700 px-2 py-0.5 rounded hover:bg-purple-200 font-medium cursor-pointer">AI</button></div>
                        <textarea v-model="form.surveys_data" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="Key findings from customer surveys..."></textarea></div>
                    <div><div class="flex items-center justify-between mb-1"><label class="text-sm font-medium text-gray-700">Interview Insights</label><button @click="generateAIContent('interviews_data')" class="text-xs bg-purple-100 text-purple-700 px-2 py-0.5 rounded hover:bg-purple-200 font-medium cursor-pointer">AI</button></div>
                        <textarea v-model="form.interviews_data" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="Insights from customer interviews..."></textarea></div>
                    <div><div class="flex items-center justify-between mb-1"><label class="text-sm font-medium text-gray-700">Competitor Pricing Data</label><button @click="generateAIContent('competitor_pricing_data')" class="text-xs bg-purple-100 text-purple-700 px-2 py-0.5 rounded hover:bg-purple-200 font-medium cursor-pointer">AI</button></div>
                        <textarea v-model="form.competitor_pricing_data" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="Pricing analysis of competitors..."></textarea></div>
                    <div><div class="flex items-center justify-between mb-1"><label class="text-sm font-medium text-gray-700">Customer Feedback</label><button @click="generateAIContent('customer_feedback_information')" class="text-xs bg-purple-100 text-purple-700 px-2 py-0.5 rounded hover:bg-purple-200 font-medium cursor-pointer">AI</button></div>
                        <textarea v-model="form.customer_feedback_information" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="What customers are saying about existing solutions..."></textarea></div>
                </div>
                <div class="mt-5"><div class="flex items-center justify-between mb-1"><label class="text-sm font-medium text-gray-700">SWOT from Research</label><button @click="generateAIContent('swot_from_research')" class="text-xs bg-purple-100 text-purple-700 px-2 py-0.5 rounded hover:bg-purple-200 font-medium cursor-pointer">AI</button></div>
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
                <div class="mt-5"><div class="flex items-center justify-between mb-1"><label class="text-sm font-medium text-gray-700">Competitive Analysis Summary</label><button @click="generateAIContent('competitive_analysis')" class="text-xs bg-purple-100 text-purple-700 px-2 py-0.5 rounded hover:bg-purple-200 font-medium cursor-pointer">AI</button></div>
                    <textarea v-model="form.competitive_analysis" rows="4" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="Detailed competitive analysis including market positioning, differentiation, and competitive advantage..."></textarea></div>
            </div>

            <!-- Step 10: SWOT Analysis -->
            <div v-if="currentStep === 10" class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h2 class="text-lg font-bold text-gray-900 mb-1">SWOT Analysis</h2>
                <p class="text-sm text-gray-500 mb-6">Strengths, Weaknesses, Opportunities &amp; Threats</p>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div class="bg-green-50 rounded-lg p-4 border border-green-200">
                        <div class="flex items-center justify-between mb-1"><label class="text-sm font-bold text-green-800">Strengths</label><button @click="generateAIContent('swot_strengths')" class="text-xs bg-purple-100 text-purple-700 px-2 py-0.5 rounded hover:bg-purple-200 font-medium cursor-pointer">AI</button></div>
                        <textarea v-model="form.swot_strengths" rows="4" class="w-full px-3 py-2 border border-green-300 rounded-lg text-sm bg-white" placeholder="Internal strengths that give you an advantage..."></textarea>
                    </div>
                    <div class="bg-red-50 rounded-lg p-4 border border-red-200">
                        <div class="flex items-center justify-between mb-1"><label class="text-sm font-bold text-red-800">Weaknesses</label><button @click="generateAIContent('swot_weaknesses')" class="text-xs bg-purple-100 text-purple-700 px-2 py-0.5 rounded hover:bg-purple-200 font-medium cursor-pointer">AI</button></div>
                        <textarea v-model="form.swot_weaknesses" rows="4" class="w-full px-3 py-2 border border-red-300 rounded-lg text-sm bg-white" placeholder="Internal areas that need improvement..."></textarea>
                    </div>
                    <div class="bg-blue-50 rounded-lg p-4 border border-blue-200">
                        <div class="flex items-center justify-between mb-1"><label class="text-sm font-bold text-blue-800">Opportunities</label><button @click="generateAIContent('swot_opportunities')" class="text-xs bg-purple-100 text-purple-700 px-2 py-0.5 rounded hover:bg-purple-200 font-medium cursor-pointer">AI</button></div>
                        <textarea v-model="form.swot_opportunities" rows="4" class="w-full px-3 py-2 border border-blue-300 rounded-lg text-sm bg-white" placeholder="External opportunities to leverage..."></textarea>
                    </div>
                    <div class="bg-yellow-50 rounded-lg p-4 border border-yellow-200">
                        <div class="flex items-center justify-between mb-1"><label class="text-sm font-bold text-yellow-800">Threats</label><button @click="generateAIContent('swot_threats')" class="text-xs bg-purple-100 text-purple-700 px-2 py-0.5 rounded hover:bg-purple-200 font-medium cursor-pointer">AI</button></div>
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
                        <div class="flex items-center justify-between mb-1"><label class="text-sm font-medium text-gray-700">Unique Selling Points</label><button @click="generateAIContent('unique_selling_points')" class="text-xs bg-purple-100 text-purple-700 px-2 py-0.5 rounded hover:bg-purple-200 font-medium cursor-pointer">AI</button></div>
                        <textarea v-model="form.unique_selling_points" rows="4" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="What makes your offering truly unique?"></textarea>
                    </div>
                    <div>
                        <div class="flex items-center justify-between mb-1"><label class="text-sm font-medium text-gray-700">Competitive Advantage</label><button @click="generateAIContent('competitive_advantage')" class="text-xs bg-purple-100 text-purple-700 px-2 py-0.5 rounded hover:bg-purple-200 font-medium cursor-pointer">AI</button></div>
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
                    <div><div class="flex items-center justify-between mb-1"><label class="text-sm font-medium text-gray-700">Branding Approach</label><button @click="generateAIContent('branding_approach')" class="text-xs bg-purple-100 text-purple-700 px-2 py-0.5 rounded hover:bg-purple-200 font-medium cursor-pointer">AI</button></div>
                        <textarea v-model="form.branding_approach" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="Your brand identity, personality, and messaging..."></textarea></div>
                    <div><label class="block text-sm font-medium text-gray-700 mb-1">Brand Voice</label>
                        <input v-model="form.brand_voice" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="e.g. Professional, friendly, innovative" /></div>
                </div>
                <div class="mt-5"><div class="flex items-center justify-between mb-1"><label class="text-sm font-medium text-gray-700">Sales Funnel</label><button @click="generateAIContent('sales_funnel')" class="text-xs bg-purple-100 text-purple-700 px-2 py-0.5 rounded hover:bg-purple-200 font-medium cursor-pointer">AI</button></div>
                    <textarea v-model="form.sales_funnel" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="Awareness -> Interest -> Decision -> Action..."></textarea></div>
            </div>

            <!-- Step 13: Sales Strategy -->
            <div v-if="currentStep === 13" class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h2 class="text-lg font-bold text-gray-900 mb-1">Sales Strategy</h2>
                <p class="text-sm text-gray-500 mb-6">How you convert leads and retain customers</p>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div><label class="block text-sm font-medium text-gray-700 mb-1">Sales Channels (JSON)</label>
                        <textarea v-model="form.sales_channels" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder='["Direct Sales","Online Store","Retail Partners"]'></textarea></div>
                    <div><div class="flex items-center justify-between mb-1"><label class="text-sm font-medium text-gray-700">Customer Retention Strategy</label><button @click="generateAIContent('customer_retention')" class="text-xs bg-purple-100 text-purple-700 px-2 py-0.5 rounded hover:bg-purple-200 font-medium cursor-pointer">AI</button></div>
                        <textarea v-model="form.customer_retention" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="Loyalty programs, follow-ups, support..."></textarea></div>
                    <div><div class="flex items-center justify-between mb-1"><label class="text-sm font-medium text-gray-700">Sales Process</label><button @click="generateAIContent('sales_process')" class="text-xs bg-purple-100 text-purple-700 px-2 py-0.5 rounded hover:bg-purple-200 font-medium cursor-pointer">AI</button></div>
                        <textarea v-model="form.sales_process" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="Step-by-step sales process from lead to close..."></textarea></div>
                    <div><div class="flex items-center justify-between mb-1"><label class="text-sm font-medium text-gray-700">Sales Targets</label><button @click="generateAIContent('sales_targets')" class="text-xs bg-purple-100 text-purple-700 px-2 py-0.5 rounded hover:bg-purple-200 font-medium cursor-pointer">AI</button></div>
                        <textarea v-model="form.sales_targets" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="Monthly/quarterly targets, growth goals..."></textarea></div>
                </div>
                <div class="mt-5"><div class="flex items-center justify-between mb-1"><label class="text-sm font-medium text-gray-700">CRM Process</label><button @click="generateAIContent('crm_process')" class="text-xs bg-purple-100 text-purple-700 px-2 py-0.5 rounded hover:bg-purple-200 font-medium cursor-pointer">AI</button></div>
                    <textarea v-model="form.crm_process" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="How you manage customer relationships and data..."></textarea></div>
            </div>

            <!-- Step 14: Operations Plan -->
            <div v-if="currentStep === 14" class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h2 class="text-lg font-bold text-gray-900 mb-1">Operations Plan</h2>
                <p class="text-sm text-gray-500 mb-6">How your business runs day-to-day</p>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div><div class="flex items-center justify-between mb-1"><label class="text-sm font-medium text-gray-700">Daily Operations</label><button @click="generateAIContent('daily_operations')" class="text-xs bg-purple-100 text-purple-700 px-2 py-0.5 rounded hover:bg-purple-200 font-medium cursor-pointer">AI</button></div>
                        <textarea v-model="form.daily_operations" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="Describe a typical day of operations..."></textarea></div>
                    <div><div class="flex items-center justify-between mb-1"><label class="text-sm font-medium text-gray-700">Facilities</label><button @click="generateAIContent('facilities')" class="text-xs bg-purple-100 text-purple-700 px-2 py-0.5 rounded hover:bg-purple-200 font-medium cursor-pointer">AI</button></div>
                        <textarea v-model="form.facilities" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="Office, warehouse, retail space requirements..."></textarea></div>
                    <div><div class="flex items-center justify-between mb-1"><label class="text-sm font-medium text-gray-700">Technology Stack</label><button @click="generateAIContent('technology_stack')" class="text-xs bg-purple-100 text-purple-700 px-2 py-0.5 rounded hover:bg-purple-200 font-medium cursor-pointer">AI</button></div>
                        <textarea v-model="form.technology_stack" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="Software, hardware, platforms you use..."></textarea></div>
                    <div><div class="flex items-center justify-between mb-1"><label class="text-sm font-medium text-gray-700">Quality Control</label><button @click="generateAIContent('quality_control')" class="text-xs bg-purple-100 text-purple-700 px-2 py-0.5 rounded hover:bg-purple-200 font-medium cursor-pointer">AI</button></div>
                        <textarea v-model="form.quality_control" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="How do you ensure quality?"></textarea></div>
                    <div><label class="block text-sm font-medium text-gray-700 mb-1">Equipment &amp; Tools (JSON)</label>
                        <textarea v-model="form.equipment_tools" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder='["Computer","Printer","Vehicle"]'></textarea></div>
                    <div><label class="block text-sm font-medium text-gray-700 mb-1">Supplier List (JSON)</label>
                        <textarea v-model="form.supplier_list" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder='[{"name":"Supplier A","product":"Raw materials","cost":"K..."}]'></textarea></div>
                    <div><div class="flex items-center justify-between mb-1"><label class="text-sm font-medium text-gray-700">Operational Workflow</label><button @click="generateAIContent('operational_workflow')" class="text-xs bg-purple-100 text-purple-700 px-2 py-0.5 rounded hover:bg-purple-200 font-medium cursor-pointer">AI</button></div>
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
                        <div><div class="flex items-center justify-between mb-1"><label class="text-sm font-medium text-gray-700">Hiring Plan</label><button @click="generateAIContent('hiring_plan')" class="text-xs bg-purple-100 text-purple-700 px-2 py-0.5 rounded hover:bg-purple-200 font-medium cursor-pointer">AI</button></div>
                            <textarea v-model="form.hiring_plan" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="Who do you need to hire and when?"></textarea></div>
                        <div><div class="flex items-center justify-between mb-1"><label class="text-sm font-medium text-gray-700">Recruitment Strategy</label><button @click="generateAIContent('recruitment_strategy')" class="text-xs bg-purple-100 text-purple-700 px-2 py-0.5 rounded hover:bg-purple-200 font-medium cursor-pointer">AI</button></div>
                            <textarea v-model="form.recruitment_strategy" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="How will you attract and recruit talent?"></textarea></div>
                        <div><div class="flex items-center justify-between mb-1"><label class="text-sm font-medium text-gray-700">Employee Benefits</label><button @click="generateAIContent('employee_benefits')" class="text-xs bg-purple-100 text-purple-700 px-2 py-0.5 rounded hover:bg-purple-200 font-medium cursor-pointer">AI</button></div>
                            <textarea v-model="form.employee_benefits" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="Salaries, bonuses, health, leave, etc."></textarea></div>
                        <div><div class="flex items-center justify-between mb-1"><label class="text-sm font-medium text-gray-700">Training Plan</label><button @click="generateAIContent('training_plan')" class="text-xs bg-purple-100 text-purple-700 px-2 py-0.5 rounded hover:bg-purple-200 font-medium cursor-pointer">AI</button></div>
                            <textarea v-model="form.training_plan" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="Onboarding and ongoing training programs"></textarea></div>
                    </div>
                    <div><div class="flex items-center justify-between mb-1"><label class="text-sm font-medium text-gray-700">Performance Management</label><button @click="generateAIContent('performance_management')" class="text-xs bg-purple-100 text-purple-700 px-2 py-0.5 rounded hover:bg-purple-200 font-medium cursor-pointer">AI</button></div>
                        <textarea v-model="form.performance_management" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="Reviews, KPIs, feedback processes..."></textarea></div>
                </div>
            </div>

            <!-- Step 16: Risk Analysis -->
            <div v-if="currentStep === 16" class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h2 class="text-lg font-bold text-gray-900 mb-1">Risk Analysis</h2>
                <p class="text-sm text-gray-500 mb-6">Identify and plan for potential risks</p>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div><div class="flex items-center justify-between mb-1"><label class="text-sm font-medium text-gray-700">Risks (JSON array)</label><button @click="generateAIContent('risks')" class="text-xs bg-purple-100 text-purple-700 px-2 py-0.5 rounded hover:bg-purple-200 font-medium cursor-pointer">AI</button></div>
                        <textarea v-model="form.risks" rows="4" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder='["Market risk","Financial risk","Operational risk"]'></textarea></div>
                    <div><div class="flex items-center justify-between mb-1"><label class="text-sm font-medium text-gray-700">Mitigation Strategies (JSON)</label><button @click="generateAIContent('mitigation_strategies')" class="text-xs bg-purple-100 text-purple-700 px-2 py-0.5 rounded hover:bg-purple-200 font-medium cursor-pointer">AI</button></div>
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
                    <div><div class="flex items-center justify-between mb-1"><label class="text-sm font-medium text-gray-700">Timeline (JSON)</label><button @click="generateAIContent('timeline')" class="text-xs bg-purple-100 text-purple-700 px-2 py-0.5 rounded hover:bg-purple-200 font-medium cursor-pointer">AI</button></div>
                        <textarea v-model="form.timeline" rows="4" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder='[{"phase":"Setup","duration":"Month 1-3","tasks":["Register business","Secure funding"]}]'></textarea></div>
                    <div><div class="flex items-center justify-between mb-1"><label class="text-sm font-medium text-gray-700">Milestones (JSON)</label><button @click="generateAIContent('milestones')" class="text-xs bg-purple-100 text-purple-700 px-2 py-0.5 rounded hover:bg-purple-200 font-medium cursor-pointer">AI</button></div>
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
                <div class="mt-5"><div class="flex items-center justify-between mb-1"><label class="text-sm font-medium text-gray-700">Financial Projections (JSON)</label><button @click="generateAIContent('financial_projections')" class="text-xs bg-purple-100 text-purple-700 px-2 py-0.5 rounded hover:bg-purple-200 font-medium cursor-pointer">AI</button></div>
                    <textarea v-model="form.financial_projections" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm font-mono text-xs" placeholder='{"year_1":{"revenue":500000,"costs":350000},"year_2":{"revenue":800000,"costs":500000}}'></textarea></div>
            </div>

            <!-- Step 19: Advanced Financials & Funding -->
            <div v-if="currentStep === 19" class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h2 class="text-lg font-bold text-gray-900 mb-1">Advanced Financials &amp; Funding</h2>
                <p class="text-sm text-gray-500 mb-6">Break-even, funding needs, and detailed projections</p>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div><div class="flex items-center justify-between mb-1"><label class="text-sm font-medium text-gray-700">Break-Even Analysis</label><button @click="generateAIContent('break_even_analysis')" class="text-xs bg-purple-100 text-purple-700 px-2 py-0.5 rounded hover:bg-purple-200 font-medium cursor-pointer">AI</button></div>
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
                    <div><div class="flex items-center justify-between mb-1"><label class="text-sm font-medium text-gray-700">Exit Strategy Details</label><button @click="generateAIContent('exit_strategy_details')" class="text-xs bg-purple-100 text-purple-700 px-2 py-0.5 rounded hover:bg-purple-200 font-medium cursor-pointer">AI</button></div>
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
            <div class="flex items-center justify-between mt-6">
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
    </CMSLayout>
</template>
