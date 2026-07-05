<script setup lang="ts">
import { Head, router, usePage } from '@inertiajs/vue3';
import CMSLayout from '@/Layouts/CMSLayout.vue';
import { ref, computed, watch } from 'vue';

const props = defineProps<{ existingPlan: any | null; userTier: string }>();
const page = usePage();

const totalSteps = 10;
const currentStep = ref(props.existingPlan?.current_step || 1);
const saving = ref(false);
const generating = ref(false);
const generatedContent = ref<string | null>(null);

const industries = ['Agriculture', 'Construction', 'Education', 'Energy', 'Fashion', 'Finance', 'Food & Beverage', 'Healthcare', 'Hospitality', 'Information Technology', 'Manufacturing', 'Media & Entertainment', 'Mining', 'Real Estate', 'Retail', 'Telecommunications', 'Transportation & Logistics', 'Tourism', 'Other'];

const steps = [
    { num: 1, label: 'Business Information', short: 'Info', icon: 'Info' },
    { num: 2, label: 'Mission & Vision', short: 'Mission', icon: 'Target' },
    { num: 3, label: 'Problem & Solution', short: 'Problem', icon: 'Lightbulb' },
    { num: 4, label: 'Products & Services', short: 'Products', icon: 'Package' },
    { num: 5, label: 'Market Analysis', short: 'Market', icon: 'Chart' },
    { num: 6, label: 'Marketing & Sales', short: 'Sales', icon: 'Megaphone' },
    { num: 7, label: 'Operations', short: 'Ops', icon: 'Settings' },
    { num: 8, label: 'Financials', short: 'Financials', icon: 'Dollar' },
    { num: 9, label: 'Risks & Timeline', short: 'Risks', icon: 'Shield' },
    { num: 10, label: 'Review & Export', short: 'Review', icon: 'Check' },
];

const form = ref({
    id: props.existingPlan?.id || null,
    business_name: props.existingPlan?.business_name || '',
    industry: props.existingPlan?.industry || '',
    country: props.existingPlan?.country || '',
    province: props.existingPlan?.province || '',
    city: props.existingPlan?.city || '',
    legal_structure: props.existingPlan?.legal_structure || '',
    mission_statement: props.existingPlan?.mission_statement || '',
    vision_statement: props.existingPlan?.vision_statement || '',
    background: props.existingPlan?.background || '',
    problem_statement: props.existingPlan?.problem_statement || '',
    solution_description: props.existingPlan?.solution_description || '',
    competitive_advantage: props.existingPlan?.competitive_advantage || '',
    customer_pain_points: props.existingPlan?.customer_pain_points || '',
    product_description: props.existingPlan?.product_description || '',
    product_features: props.existingPlan?.product_features || '',
    pricing_strategy: props.existingPlan?.pricing_strategy || '',
    unique_selling_points: props.existingPlan?.unique_selling_points || '',
    production_process: props.existingPlan?.production_process || '',
    resource_requirements: props.existingPlan?.resource_requirements || '',
    target_market: props.existingPlan?.target_market || '',
    customer_demographics: props.existingPlan?.customer_demographics || '',
    market_size: props.existingPlan?.market_size || '',
    competitors: props.existingPlan?.competitors || '',
    competitive_analysis: props.existingPlan?.competitive_analysis || '',
    marketing_channels: props.existingPlan?.marketing_channels || '',
    branding_approach: props.existingPlan?.branding_approach || '',
    sales_channels: props.existingPlan?.sales_channels || '',
    customer_retention: props.existingPlan?.customer_retention || '',
    daily_operations: props.existingPlan?.daily_operations || '',
    staff_roles: props.existingPlan?.staff_roles || '',
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
    key_risks: props.existingPlan?.key_risks || '',
    mitigation_strategies: props.existingPlan?.mitigation_strategies || '',
    timeline: props.existingPlan?.timeline || '',
    milestones: props.existingPlan?.milestones || '',
    responsibilities: props.existingPlan?.responsibilities || '',
    current_step: props.existingPlan?.current_step || 1,
    status: props.existingPlan?.status || 'draft',
});

const progressPercent = computed(() => Math.round((currentStep.value / totalSteps) * 100));
const isPremium = computed(() => props.userTier === 'premium');
const monthlyProfit = computed(() => (form.value.expected_monthly_revenue || 0) - (form.value.monthly_operating_costs || 0) - (form.value.staff_salaries || 0));

watch(() => (page.props as any).flash?.generatedContent, (val: string | null) => {
    if (val) { generatedContent.value = val; }
});

const goToStep = (step: number) => {
    if (step >= 1 && step <= totalSteps) currentStep.value = step;
};

const saveDraft = async () => {
    saving.value = true;
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
    router.post(route('cms.business-plans.complete'), { id: form.value.id });
};

const generateAIContent = (field: string) => {
    if (!isPremium.value) { alert('AI generation is a premium feature.'); return; }
    generating.value = true;
    generatedContent.value = null;
    router.post(route('cms.business-plans.generate-ai'), {
        business_plan_id: form.value.id,
        field,
        context: { business_name: form.value.business_name, industry: form.value.industry, ...form.value },
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
    }
};

const formatCurrency = (v: number | null) => v != null ? `K${Number(v).toLocaleString()}` : '-';
</script>

<template>
    <Head title="Business Plan Generator" />
    <CMSLayout>
        <div class="p-6 max-w-5xl mx-auto">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Business Plan Generator</h1>
                    <p class="text-sm text-gray-500 mt-1">Create a professional business plan step by step</p>
                </div>
                <button v-if="form.id" @click="saveDraft" :disabled="saving" class="px-4 py-2 bg-gray-600 text-white text-sm rounded-lg hover:bg-gray-700 disabled:opacity-50">
                    {{ saving ? 'Saving...' : 'Save Draft' }}
                </button>
            </div>

            <!-- Progress Bar -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 mb-6">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-medium text-gray-600">Step {{ currentStep }} of {{ totalSteps }}</span>
                    <span class="text-xs text-gray-500">{{ progressPercent }}%</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-2"><div class="bg-blue-600 h-2 rounded-full transition-all duration-300" :style="{ width: progressPercent + '%' }"></div></div>
                <div class="mt-3 grid grid-cols-10 gap-1">
                    <div v-for="s in steps" :key="s.num" @click="goToStep(s.num)" class="h-1.5 rounded-full cursor-pointer transition-colors"
                        :class="currentStep > s.num ? 'bg-green-400' : currentStep === s.num ? 'bg-blue-600' : 'bg-gray-200'" :title="s.label"></div>
                </div>
            </div>

            <!-- Step 1: Business Information -->
            <div v-if="currentStep === 1" class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h2 class="text-lg font-bold text-gray-900 mb-1">Business Information</h2>
                <p class="text-sm text-gray-500 mb-6">Tell us about your business basics</p>
                <div class="space-y-5">
                    <div><label class="block text-sm font-medium text-gray-700 mb-1">Business Name <span class="text-red-500">*</span></label>
                        <input v-model="form.business_name" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="e.g. MyGrowNet Success Hub" /></div>
                    <div><label class="block text-sm font-medium text-gray-700 mb-1">Industry <span class="text-red-500">*</span></label>
                        <select v-model="form.industry" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                            <option value="">Select industry...</option><option v-for="ind in industries" :key="ind" :value="ind">{{ ind }}</option>
                        </select></div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div><label class="block text-sm font-medium text-gray-700 mb-1">Country</label><input v-model="form.country" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="Zambia" /></div>
                        <div><label class="block text-sm font-medium text-gray-700 mb-1">Province</label><input v-model="form.province" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="Lusaka" /></div>
                        <div><label class="block text-sm font-medium text-gray-700 mb-1">City</label><input v-model="form.city" type="text" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="Lusaka" /></div>
                    </div>
                    <div><label class="block text-sm font-medium text-gray-700 mb-1">Legal Structure <span class="text-red-500">*</span></label>
                        <select v-model="form.legal_structure" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                            <option value="">Select structure...</option><option value="sole_trader">Sole Trader</option><option value="partnership">Partnership</option><option value="company">Company</option><option value="cooperative">Cooperative</option>
                        </select></div>
                </div>
            </div>

            <!-- Step 2: Mission & Vision -->
            <div v-if="currentStep === 2" class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h2 class="text-lg font-bold text-gray-900 mb-1">Mission &amp; Vision</h2>
                <p class="text-sm text-gray-500 mb-6">Define your business purpose and direction</p>
                <div class="space-y-5">
                    <div>
                        <div class="flex items-center justify-between mb-1"><label class="text-sm font-medium text-gray-700">Mission Statement</label><button v-if="isPremium" @click="generateAIContent('mission_statement')" class="text-xs text-purple-600 hover:text-purple-800">Generate with AI</button></div>
                        <textarea v-model="form.mission_statement" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="Our mission is to..."></textarea>
                        <div v-if="generatedContent && generatedContent" class="mt-2 p-3 bg-purple-50 rounded-lg"><p class="text-sm text-purple-800">{{ generatedContent }}</p><button @click="applyGenerated('mission_statement')" class="mt-1 text-xs text-purple-600 hover:text-purple-800 font-medium">Apply</button></div>
                    </div>
                    <div>
                        <div class="flex items-center justify-between mb-1"><label class="text-sm font-medium text-gray-700">Vision Statement</label><button v-if="isPremium" @click="generateAIContent('vision_statement')" class="text-xs text-purple-600 hover:text-purple-800">Generate with AI</button></div>
                        <textarea v-model="form.vision_statement" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="To become the leading..."></textarea>
                    </div>
                    <div>
                        <div class="flex items-center justify-between mb-1"><label class="text-sm font-medium text-gray-700">Business Background</label><button v-if="isPremium" @click="generateAIContent('background')" class="text-xs text-purple-600 hover:text-purple-800">Generate with AI</button></div>
                        <textarea v-model="form.background" rows="4" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="Tell the story of your business..."></textarea>
                    </div>
                </div>
            </div>

            <!-- Step 3: Problem & Solution -->
            <div v-if="currentStep === 3" class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h2 class="text-lg font-bold text-gray-900 mb-1">Problem &amp; Solution</h2>
                <p class="text-sm text-gray-500 mb-6">Define the problem you're solving and how</p>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div><div class="flex items-center justify-between mb-1"><label class="text-sm font-medium text-gray-700">Problem Statement</label><button v-if="isPremium" @click="generateAIContent('problem_statement')" class="text-xs text-purple-600">AI</button></div>
                        <textarea v-model="form.problem_statement" rows="4" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="What problem are you solving?"></textarea></div>
                    <div><div class="flex items-center justify-between mb-1"><label class="text-sm font-medium text-gray-700">Solution Description</label><button v-if="isPremium" @click="generateAIContent('solution_description')" class="text-xs text-purple-600">AI</button></div>
                        <textarea v-model="form.solution_description" rows="4" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="How does your solution work?"></textarea></div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mt-5">
                    <div><label class="block text-sm font-medium text-gray-700 mb-1">Competitive Advantage</label>
                        <textarea v-model="form.competitive_advantage" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="What makes you different?"></textarea></div>
                    <div><label class="block text-sm font-medium text-gray-700 mb-1">Customer Pain Points</label>
                        <textarea v-model="form.customer_pain_points" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="What are your customers struggling with?"></textarea></div>
                </div>
            </div>

            <!-- Step 4: Products & Services -->
            <div v-if="currentStep === 4" class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h2 class="text-lg font-bold text-gray-900 mb-1">Products &amp; Services</h2>
                <p class="text-sm text-gray-500 mb-6">Describe what you're offering</p>
                <div class="space-y-5">
                    <div><label class="block text-sm font-medium text-gray-700 mb-1">Product Description</label>
                        <textarea v-model="form.product_description" rows="4" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="Describe your products or services in detail..."></textarea></div>
                    <div><label class="block text-sm font-medium text-gray-700 mb-1">Key Features</label>
                        <textarea v-model="form.product_features" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="List the key features and benefits..."></textarea></div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div><label class="block text-sm font-medium text-gray-700 mb-1">Pricing Strategy</label>
                            <textarea v-model="form.pricing_strategy" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="How will you price your products?"></textarea></div>
                        <div><label class="block text-sm font-medium text-gray-700 mb-1">Unique Selling Points</label>
                            <textarea v-model="form.unique_selling_points" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="What sets you apart from competitors?"></textarea></div>
                    </div>
                </div>
            </div>

            <!-- Step 5: Market Analysis -->
            <div v-if="currentStep === 5" class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h2 class="text-lg font-bold text-gray-900 mb-1">Market Analysis</h2>
                <p class="text-sm text-gray-500 mb-6">Understand your market and competition</p>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div><label class="block text-sm font-medium text-gray-700 mb-1">Target Market</label><textarea v-model="form.target_market" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="Who are your target customers?"></textarea></div>
                    <div><label class="block text-sm font-medium text-gray-700 mb-1">Customer Demographics</label><textarea v-model="form.customer_demographics" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="Age, income, location, behavior..."></textarea></div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-5 mt-5">
                    <div><label class="block text-sm font-medium text-gray-700 mb-1">Market Size</label><textarea v-model="form.market_size" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="How big is your market?"></textarea></div>
                    <div><label class="block text-sm font-medium text-gray-700 mb-1">Competitors</label><textarea v-model="form.competitors" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="Who are your main competitors?"></textarea></div>
                    <div><label class="block text-sm font-medium text-gray-700 mb-1">Competitive Analysis</label><textarea v-model="form.competitive_analysis" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="SWOT analysis..."></textarea></div>
                </div>
            </div>

            <!-- Step 6: Marketing & Sales -->
            <div v-if="currentStep === 6" class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h2 class="text-lg font-bold text-gray-900 mb-1">Marketing &amp; Sales</h2>
                <p class="text-sm text-gray-500 mb-6">How you'll reach and retain customers</p>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div><label class="block text-sm font-medium text-gray-700 mb-1">Marketing Channels</label><textarea v-model="form.marketing_channels" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="Social media, ads, referrals..."></textarea></div>
                    <div><label class="block text-sm font-medium text-gray-700 mb-1">Branding Approach</label><textarea v-model="form.branding_approach" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="Your brand identity and messaging..."></textarea></div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mt-5">
                    <div><label class="block text-sm font-medium text-gray-700 mb-1">Sales Channels</label><textarea v-model="form.sales_channels" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="Online, retail, direct sales..."></textarea></div>
                    <div><label class="block text-sm font-medium text-gray-700 mb-1">Customer Retention Strategy</label><textarea v-model="form.customer_retention" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="How will you keep customers coming back?"></textarea></div>
                </div>
            </div>

            <!-- Step 7: Operations -->
            <div v-if="currentStep === 7" class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h2 class="text-lg font-bold text-gray-900 mb-1">Operations</h2>
                <p class="text-sm text-gray-500 mb-6">How your business runs day-to-day</p>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div><label class="block text-sm font-medium text-gray-700 mb-1">Daily Operations</label><textarea v-model="form.daily_operations" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="Describe a typical day..."></textarea></div>
                    <div><label class="block text-sm font-medium text-gray-700 mb-1">Staff Roles</label><textarea v-model="form.staff_roles" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="Key roles and responsibilities..."></textarea></div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-5 mt-5">
                    <div><label class="block text-sm font-medium text-gray-700 mb-1">Equipment &amp; Tools</label><textarea v-model="form.equipment_tools" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="What equipment do you need?"></textarea></div>
                    <div><label class="block text-sm font-medium text-gray-700 mb-1">Suppliers</label><textarea v-model="form.supplier_list" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="Key suppliers..."></textarea></div>
                    <div><label class="block text-sm font-medium text-gray-700 mb-1">Workflow</label><textarea v-model="form.operational_workflow" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="Key processes..."></textarea></div>
                </div>
            </div>

            <!-- Step 8: Financials -->
            <div v-if="currentStep === 8" class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h2 class="text-lg font-bold text-gray-900 mb-1">Financials</h2>
                <p class="text-sm text-gray-500 mb-6">Your financial projections</p>
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
            </div>

            <!-- Step 9: Risks & Timeline -->
            <div v-if="currentStep === 9" class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h2 class="text-lg font-bold text-gray-900 mb-1">Risks &amp; Implementation Timeline</h2>
                <p class="text-sm text-gray-500 mb-6">Plan for challenges and map your path forward</p>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div><label class="block text-sm font-medium text-gray-700 mb-1">Key Risks</label><textarea v-model="form.key_risks" rows="4" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="What could go wrong?"></textarea></div>
                    <div><label class="block text-sm font-medium text-gray-700 mb-1">Mitigation Strategies</label><textarea v-model="form.mitigation_strategies" rows="4" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="How will you address these risks?"></textarea></div>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-5 mt-5">
                    <div><label class="block text-sm font-medium text-gray-700 mb-1">Implementation Timeline</label><textarea v-model="form.timeline" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="Month 1-3: Setup, Month 4-6: Launch..."></textarea></div>
                    <div><label class="block text-sm font-medium text-gray-700 mb-1">Key Milestones</label><textarea v-model="form.milestones" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="First sale, break-even, expansion..."></textarea></div>
                    <div><label class="block text-sm font-medium text-gray-700 mb-1">Responsibilities</label><textarea v-model="form.responsibilities" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="Who does what?"></textarea></div>
                </div>
            </div>

            <!-- Step 10: Review & Export -->
            <div v-if="currentStep === 10" class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h2 class="text-lg font-bold text-gray-900 mb-1">Review &amp; Complete</h2>
                <p class="text-sm text-gray-500 mb-6">Review your business plan and mark it complete</p>
                <div class="p-4 bg-gray-50 rounded-lg mb-6">
                    <h3 class="font-semibold text-gray-800">{{ form.business_name || 'Business Plan' }}</h3>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-3 text-sm">
                        <div><span class="text-gray-500">Industry:</span> {{ form.industry || '-' }}</div>
                        <div><span class="text-gray-500">Structure:</span> {{ form.legal_structure || '-' }}</div>
                        <div><span class="text-gray-500">Location:</span> {{ form.country || '-' }}{{ form.city ? ', ' + form.city : '' }}</div>
                        <div><span class="text-gray-500">Status:</span> {{ form.status || 'draft' }}</div>
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
    </CMSLayout>
</template>
