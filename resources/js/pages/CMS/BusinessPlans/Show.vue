<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import CMSLayout from '@/Layouts/CMSLayout.vue';
import { ref } from 'vue';

const props = defineProps<{ plan: any; userTier: string }>();

const exporting = ref(false);

const exportPlan = (type: string) => {
    exporting.value = true;
    router.post(route('cms.business-plans.export'), { business_plan_id: props.plan.id, export_type: type }, {
        preserveScroll: true, onFinish: () => { exporting.value = false; }
    });
};

const formatCurrency = (v: number | null) => v != null ? `K${Number(v).toLocaleString()}` : '-';
const formatDate = (d: string) => d ? new Date(d).toLocaleDateString() : '-';

const section = (label: string, content: string | null) => content ? `<div class="mb-6"><h3 class="text-sm font-bold text-gray-700 uppercase tracking-wider mb-2">${label}</h3><p class="text-sm text-gray-600 whitespace-pre-wrap">${content}</p></div>` : '';
</script>

<template>
    <Head :title="plan.business_name" />
    <CMSLayout>
        <div class="p-6 max-w-4xl mx-auto">
            <div class="flex items-center justify-between mb-6">
                <Link :href="route('cms.business-plans.index')" class="text-sm text-blue-600 hover:text-blue-800 font-medium">&larr; Back to Plans</Link>
                <div class="flex items-center gap-2">
                    <Link :href="route('cms.business-plans.create', { plan: plan.id })" class="px-3 py-1.5 text-sm border border-gray-300 rounded-lg hover:bg-gray-50">Edit</Link>
                    <button @click="exportPlan('pdf')" :disabled="exporting" class="px-3 py-1.5 text-sm bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50">{{ exporting ? 'Exporting...' : 'Export PDF' }}</button>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-8">
                <div class="text-center mb-8 pb-8 border-b border-gray-200">
                    <h1 class="text-3xl font-bold text-gray-900">{{ plan.business_name }}</h1>
                    <p v-if="plan.industry" class="text-gray-500 mt-1">{{ plan.industry }}</p>
                    <div class="flex items-center justify-center gap-4 mt-3 text-sm text-gray-500">
                        <span>{{ plan.country || '' }}{{ plan.city ? ', ' + plan.city : '' }}</span>
                        <span v-if="plan.legal_structure">&middot; {{ plan.legal_structure }}</span>
                        <span v-if="plan.completed_at">&middot; Completed {{ formatDate(plan.completed_at) }}</span>
                    </div>
                </div>

                <div class="space-y-10">
                    <!-- Mission & Vision -->
                    <div v-if="plan.mission_statement || plan.vision_statement" class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div v-if="plan.mission_statement" class="bg-blue-50 rounded-lg p-5">
                            <h3 class="text-sm font-bold text-blue-800 uppercase tracking-wider mb-2">Mission</h3>
                            <p class="text-sm text-blue-900">{{ plan.mission_statement }}</p>
                        </div>
                        <div v-if="plan.vision_statement" class="bg-purple-50 rounded-lg p-5">
                            <h3 class="text-sm font-bold text-purple-800 uppercase tracking-wider mb-2">Vision</h3>
                            <p class="text-sm text-purple-900">{{ plan.vision_statement }}</p>
                        </div>
                    </div>

                    <!-- Business Background / Problem & Solution -->
                    <div v-if="plan.background" class="bg-white rounded-lg border border-gray-200 p-5">
                        <h3 class="text-sm font-bold text-gray-700 uppercase tracking-wider mb-2">Business Background</h3>
                        <p class="text-sm text-gray-600 whitespace-pre-wrap">{{ plan.background }}</p>
                    </div>

                    <div v-if="plan.problem_statement || plan.solution_description" class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div v-if="plan.problem_statement" class="bg-white rounded-lg border border-gray-200 p-5">
                            <h3 class="text-sm font-bold text-gray-700 uppercase tracking-wider mb-2">Problem Statement</h3>
                            <p class="text-sm text-gray-600 whitespace-pre-wrap">{{ plan.problem_statement }}</p>
                        </div>
                        <div v-if="plan.solution_description" class="bg-white rounded-lg border border-gray-200 p-5">
                            <h3 class="text-sm font-bold text-gray-700 uppercase tracking-wider mb-2">Solution</h3>
                            <p class="text-sm text-gray-600 whitespace-pre-wrap">{{ plan.solution_description }}</p>
                        </div>
                    </div>

                    <!-- Products & Services -->
                    <div v-if="plan.product_description || plan.product_features" class="bg-white rounded-lg border border-gray-200 p-5">
                        <h3 class="text-sm font-bold text-gray-700 uppercase tracking-wider mb-2">Products &amp; Services</h3>
                        <p v-if="plan.product_description" class="text-sm text-gray-600 whitespace-pre-wrap mb-3">{{ plan.product_description }}</p>
                        <p v-if="plan.product_features" class="text-sm text-gray-600 whitespace-pre-wrap"><strong>Features:</strong> {{ plan.product_features }}</p>
                        <p v-if="plan.pricing_strategy" class="text-sm text-gray-600 mt-2"><strong>Pricing:</strong> {{ plan.pricing_strategy }}</p>
                    </div>

                    <!-- Market Analysis -->
                    <div v-if="plan.target_market || plan.market_size || plan.competitors" class="bg-white rounded-lg border border-gray-200 p-5">
                        <h3 class="text-sm font-bold text-gray-700 uppercase tracking-wider mb-2">Market Analysis</h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-3">
                            <div v-if="plan.target_market"><p class="text-xs text-gray-500">Target Market</p><p class="text-sm text-gray-700">{{ plan.target_market }}</p></div>
                            <div v-if="plan.market_size"><p class="text-xs text-gray-500">Market Size</p><p class="text-sm text-gray-700">{{ plan.market_size }}</p></div>
                            <div v-if="plan.competitors"><p class="text-xs text-gray-500">Competitors</p><p class="text-sm text-gray-700">{{ plan.competitors }}</p></div>
                        </div>
                        <p v-if="plan.competitive_analysis" class="text-sm text-gray-600 whitespace-pre-wrap"><strong>Competitive Analysis:</strong> {{ plan.competitive_analysis }}</p>
                    </div>

                    <!-- Financials -->
                    <div v-if="plan.startup_costs != null || plan.expected_monthly_revenue != null" class="bg-white rounded-lg border border-gray-200 p-5">
                        <h3 class="text-sm font-bold text-gray-700 uppercase tracking-wider mb-3">Financial Summary</h3>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                            <div><p class="text-xs text-gray-500">Startup Costs</p><p class="text-sm font-semibold text-gray-900">{{ formatCurrency(plan.startup_costs) }}</p></div>
                            <div><p class="text-xs text-gray-500">Monthly Revenue</p><p class="text-sm font-semibold text-gray-900">{{ formatCurrency(plan.expected_monthly_revenue) }}</p></div>
                            <div><p class="text-xs text-gray-500">Operating Costs</p><p class="text-sm font-semibold text-gray-900">{{ formatCurrency(plan.monthly_operating_costs) }}</p></div>
                            <div><p class="text-xs text-gray-500">Staff Salaries</p><p class="text-sm font-semibold text-gray-900">{{ formatCurrency(plan.staff_salaries) }}</p></div>
                        </div>
                    </div>

                    <!-- Risk & Implementation -->
                    <div v-if="plan.key_risks || plan.timeline" class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div v-if="plan.key_risks" class="bg-white rounded-lg border border-gray-200 p-5">
                            <h3 class="text-sm font-bold text-gray-700 uppercase tracking-wider mb-2">Key Risks</h3>
                            <p class="text-sm text-gray-600 whitespace-pre-wrap">{{ plan.key_risks }}</p>
                            <p v-if="plan.mitigation_strategies" class="text-sm text-gray-600 mt-2 whitespace-pre-wrap"><strong>Mitigation:</strong> {{ plan.mitigation_strategies }}</p>
                        </div>
                        <div v-if="plan.timeline" class="bg-white rounded-lg border border-gray-200 p-5">
                            <h3 class="text-sm font-bold text-gray-700 uppercase tracking-wider mb-2">Implementation Timeline</h3>
                            <p class="text-sm text-gray-600 whitespace-pre-wrap">{{ plan.timeline }}</p>
                            <p v-if="plan.milestones" class="text-sm text-gray-600 mt-2 whitespace-pre-wrap"><strong>Milestones:</strong> {{ plan.milestones }}</p>
                        </div>
                    </div>

                    <!-- Marketing & Operations -->
                    <div v-if="plan.marketing_channels || plan.daily_operations" class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div v-if="plan.marketing_channels" class="bg-white rounded-lg border border-gray-200 p-5">
                            <h3 class="text-sm font-bold text-gray-700 uppercase tracking-wider mb-2">Marketing &amp; Sales</h3>
                            <p class="text-sm text-gray-600 whitespace-pre-wrap">{{ plan.marketing_channels }}</p>
                            <p v-if="plan.sales_channels" class="text-sm text-gray-600 mt-2"><strong>Sales Channels:</strong> {{ plan.sales_channels }}</p>
                        </div>
                        <div v-if="plan.daily_operations" class="bg-white rounded-lg border border-gray-200 p-5">
                            <h3 class="text-sm font-bold text-gray-700 uppercase tracking-wider mb-2">Daily Operations</h3>
                            <p class="text-sm text-gray-600 whitespace-pre-wrap">{{ plan.daily_operations }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </CMSLayout>
</template>
