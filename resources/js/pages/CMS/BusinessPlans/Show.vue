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
const hasVal = (v: any) => v != null && v !== '';
</script>

<template>
    <Head :title="plan.business_name" />
    <CMSLayout>
        <div class="p-6 max-w-4xl mx-auto">
            <div class="flex items-center justify-between mb-6">
                <Link :href="route('cms.business-plans.index')" class="text-sm text-blue-600 hover:text-blue-800 font-medium">&larr; Back to Plans</Link>
                <div class="flex items-center gap-2">
                    <Link :href="route('cms.business-plans.edit', plan.id)" class="px-3 py-1.5 text-sm border border-gray-300 rounded-lg hover:bg-gray-50">Edit</Link>
                    <button @click="exportPlan('pdf')" :disabled="exporting" class="px-3 py-1.5 text-sm bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50">{{ exporting ? 'Exporting...' : 'Export PDF' }}</button>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-8">
                <!-- Header -->
                <div class="text-center mb-8 pb-8 border-b border-gray-200">
                    <h1 class="text-3xl font-bold text-gray-900">{{ plan.business_name }}</h1>
                    <p v-if="hasVal(plan.tagline)" class="text-gray-400 mt-1 italic">{{ plan.tagline }}</p>
                    <p v-if="hasVal(plan.industry)" class="text-gray-500 mt-1">{{ plan.industry }}</p>
                    <div class="flex items-center justify-center gap-4 mt-3 text-sm text-gray-500 flex-wrap">
                        <span v-if="hasVal(plan.country) || hasVal(plan.city)">{{ plan.country || '' }}{{ plan.city ? ', ' + plan.city : '' }}</span>
                        <span v-if="hasVal(plan.legal_structure)">&middot; {{ plan.legal_structure }}</span>
                        <span v-if="hasVal(plan.business_stage)">&middot; {{ plan.business_stage }}</span>
                        <span v-if="plan.completed_at">&middot; Completed {{ formatDate(plan.completed_at) }}</span>
                    </div>
                </div>

                <div class="space-y-10">
                    <!-- 1. Business Profile -->
                    <div v-if="hasVal(plan.business_name)||hasVal(plan.website)||hasVal(plan.registration_status)||hasVal(plan.date_established)" class="border-b border-gray-200 pb-6">
                        <h3 class="text-sm font-bold text-gray-700 uppercase tracking-wider mb-3 flex items-center gap-2"><span class="w-6 h-6 bg-blue-100 text-blue-700 rounded-full flex items-center justify-center text-xs font-bold">1</span> Business Profile</h3>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                            <div v-if="hasVal(plan.website)"><p class="text-xs text-gray-500">Website</p><p class="text-gray-700">{{ plan.website }}</p></div>
                            <div v-if="hasVal(plan.city)"><p class="text-xs text-gray-500">Location</p><p class="text-gray-700">{{ plan.city }}{{ plan.province ? ', ' + plan.province : '' }}</p></div>
                            <div v-if="hasVal(plan.registration_status)"><p class="text-xs text-gray-500">Registration</p><p class="text-gray-700">{{ plan.registration_status }}</p></div>
                            <div v-if="hasVal(plan.date_established)"><p class="text-xs text-gray-500">Established</p><p class="text-gray-700">{{ formatDate(plan.date_established) }}</p></div>
                        </div>
                    </div>

                    <!-- 2. Company Description -->
                    <div v-if="hasVal(plan.mission_statement)||hasVal(plan.vision_statement)||hasVal(plan.background)||hasVal(plan.core_values)||hasVal(plan.business_objectives)" class="border-b border-gray-200 pb-6">
                        <h3 class="text-sm font-bold text-gray-700 uppercase tracking-wider mb-3 flex items-center gap-2"><span class="w-6 h-6 bg-blue-100 text-blue-700 rounded-full flex items-center justify-center text-xs font-bold">2</span> Company Description</h3>
                        <div v-if="hasVal(plan.mission_statement)||hasVal(plan.vision_statement)" class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <div v-if="hasVal(plan.mission_statement)" class="bg-blue-50 rounded-lg p-4">
                                <p class="text-xs font-bold text-blue-800 uppercase tracking-wider mb-1">Mission</p>
                                <p class="text-sm text-blue-900">{{ plan.mission_statement }}</p>
                            </div>
                            <div v-if="hasVal(plan.vision_statement)" class="bg-purple-50 rounded-lg p-4">
                                <p class="text-xs font-bold text-purple-800 uppercase tracking-wider mb-1">Vision</p>
                                <p class="text-sm text-purple-900">{{ plan.vision_statement }}</p>
                            </div>
                        </div>
                        <div v-if="hasVal(plan.core_values)" class="mb-3"><p class="text-xs text-gray-500 mb-1">Core Values</p><p class="text-sm text-gray-700">{{ Array.isArray(plan.core_values) ? plan.core_values.join(', ') : plan.core_values }}</p></div>
                        <div v-if="hasVal(plan.business_objectives)" class="mb-3"><p class="text-xs text-gray-500 mb-1">Objectives</p><p class="text-sm text-gray-700 whitespace-pre-wrap">{{ plan.business_objectives }}</p></div>
                        <div v-if="hasVal(plan.company_history)"><p class="text-xs text-gray-500 mb-1">History</p><p class="text-sm text-gray-700 whitespace-pre-wrap">{{ plan.company_history }}</p></div>
                        <div v-if="hasVal(plan.long_term_goals)"><p class="text-xs text-gray-500 mb-1 mt-3">Long-Term Goals</p><p class="text-sm text-gray-700 whitespace-pre-wrap">{{ plan.long_term_goals }}</p></div>
                        <div v-if="hasVal(plan.success_factors)"><p class="text-xs text-gray-500 mb-1 mt-3">Key Success Factors</p><p class="text-sm text-gray-700 whitespace-pre-wrap">{{ plan.success_factors }}</p></div>
                        <div v-if="hasVal(plan.background)" class="mt-3"><p class="text-xs text-gray-500 mb-1">Background</p><p class="text-sm text-gray-700 whitespace-pre-wrap">{{ plan.background }}</p></div>
                    </div>

                    <!-- 3. Problem Statement -->
                    <div v-if="hasVal(plan.problem_statement)||hasVal(plan.existing_alternatives)||hasVal(plan.why_existing_fail)||hasVal(plan.solution_description)" class="border-b border-gray-200 pb-6">
                        <h3 class="text-sm font-bold text-gray-700 uppercase tracking-wider mb-3 flex items-center gap-2"><span class="w-6 h-6 bg-blue-100 text-blue-700 rounded-full flex items-center justify-center text-xs font-bold">3</span> Problem Statement</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div v-if="hasVal(plan.problem_statement)"><p class="text-xs text-gray-500 mb-1">Problem</p><p class="text-sm text-gray-700 whitespace-pre-wrap">{{ plan.problem_statement }}</p></div>
                            <div v-if="hasVal(plan.solution_description)"><p class="text-xs text-gray-500 mb-1">Solution</p><p class="text-sm text-gray-700 whitespace-pre-wrap">{{ plan.solution_description }}</p></div>
                        </div>
                        <div v-if="hasVal(plan.existing_alternatives)" class="mt-3"><p class="text-xs text-gray-500 mb-1">Existing Alternatives</p><p class="text-sm text-gray-700 whitespace-pre-wrap">{{ plan.existing_alternatives }}</p></div>
                        <div v-if="hasVal(plan.why_existing_fail)"><p class="text-xs text-gray-500 mb-1 mt-2">Why Alternatives Fail</p><p class="text-sm text-gray-700 whitespace-pre-wrap">{{ plan.why_existing_fail }}</p></div>
                    </div>

                    <!-- 4. Products & Services -->
                    <div v-if="hasVal(plan.product_description)||hasVal(plan.delivery_method)||hasVal(plan.product_lifecycle)||hasVal(plan.future_improvements)" class="border-b border-gray-200 pb-6">
                        <h3 class="text-sm font-bold text-gray-700 uppercase tracking-wider mb-3 flex items-center gap-2"><span class="w-6 h-6 bg-blue-100 text-blue-700 rounded-full flex items-center justify-center text-xs font-bold">4</span> Products &amp; Services</h3>
                        <p v-if="hasVal(plan.product_description)" class="text-sm text-gray-700 whitespace-pre-wrap mb-3">{{ plan.product_description }}</p>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                            <div v-if="hasVal(plan.delivery_method)"><p class="text-xs text-gray-500">Delivery Method</p><p class="text-gray-700">{{ plan.delivery_method }}</p></div>
                            <div v-if="hasVal(plan.product_lifecycle)"><p class="text-xs text-gray-500">Lifecycle</p><p class="text-gray-700">{{ plan.product_lifecycle }}</p></div>
                            <div v-if="hasVal(plan.future_improvements)"><p class="text-xs text-gray-500">Future Improvements</p><p class="text-gray-700">{{ plan.future_improvements }}</p></div>
                        </div>
                    </div>

                    <!-- 5. Product Features & Production -->
                    <div v-if="hasVal(plan.product_features)||hasVal(plan.production_process)||hasVal(plan.resource_requirements)||hasVal(plan.structured_products)" class="border-b border-gray-200 pb-6">
                        <h3 class="text-sm font-bold text-gray-700 uppercase tracking-wider mb-3 flex items-center gap-2"><span class="w-6 h-6 bg-blue-100 text-blue-700 rounded-full flex items-center justify-center text-xs font-bold">5</span> Features &amp; Production</h3>
                        <div v-if="hasVal(plan.product_features)"><p class="text-xs text-gray-500 mb-1">Key Features</p><p class="text-sm text-gray-700">{{ Array.isArray(plan.product_features) ? plan.product_features.join(', ') : plan.product_features }}</p></div>
                        <div v-if="hasVal(plan.production_process)" class="mt-3"><p class="text-xs text-gray-500 mb-1">Production Process</p><p class="text-sm text-gray-700 whitespace-pre-wrap">{{ plan.production_process }}</p></div>
                    </div>

                    <!-- 6. Pricing & Business Model -->
                    <div v-if="hasVal(plan.pricing_strategy)||hasVal(plan.revenue_streams)||hasVal(plan.cost_structure)||hasVal(plan.customer_relationships)||hasVal(plan.channels)||hasVal(plan.key_activities)||hasVal(plan.key_resources)||hasVal(plan.key_partners)" class="border-b border-gray-200 pb-6">
                        <h3 class="text-sm font-bold text-gray-700 uppercase tracking-wider mb-3 flex items-center gap-2"><span class="w-6 h-6 bg-blue-100 text-blue-700 rounded-full flex items-center justify-center text-xs font-bold">6</span> Pricing &amp; Business Model</h3>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                            <div v-if="hasVal(plan.pricing_strategy)"><p class="text-xs text-gray-500">Pricing</p><p class="text-gray-700">{{ plan.pricing_strategy }}</p></div>
                            <div v-if="hasVal(plan.revenue_streams)"><p class="text-xs text-gray-500">Revenue Streams</p><p class="text-gray-700">{{ plan.revenue_streams }}</p></div>
                            <div v-if="hasVal(plan.cost_structure)"><p class="text-xs text-gray-500">Cost Structure</p><p class="text-gray-700">{{ plan.cost_structure }}</p></div>
                            <div v-if="hasVal(plan.customer_relationships)"><p class="text-xs text-gray-500">Customer Relationships</p><p class="text-gray-700">{{ plan.customer_relationships }}</p></div>
                            <div v-if="hasVal(plan.channels)"><p class="text-xs text-gray-500">Channels</p><p class="text-gray-700">{{ plan.channels }}</p></div>
                            <div v-if="hasVal(plan.key_activities)"><p class="text-xs text-gray-500">Key Activities</p><p class="text-gray-700">{{ plan.key_activities }}</p></div>
                            <div v-if="hasVal(plan.key_resources)"><p class="text-xs text-gray-500">Key Resources</p><p class="text-gray-700">{{ plan.key_resources }}</p></div>
                            <div v-if="hasVal(plan.key_partners)"><p class="text-xs text-gray-500">Key Partners</p><p class="text-gray-700">{{ plan.key_partners }}</p></div>
                        </div>
                    </div>

                    <!-- 7. Market Analysis -->
                    <div v-if="hasVal(plan.industry)||hasVal(plan.target_market)||hasVal(plan.industry_size)||hasVal(plan.growth_rate)||hasVal(plan.industry_trends)||hasVal(plan.regulations)||hasVal(plan.technology_changes)||hasVal(plan.market_size)||hasVal(plan.customer_demographics)||hasVal(plan.customer_personas)" class="border-b border-gray-200 pb-6">
                        <h3 class="text-sm font-bold text-gray-700 uppercase tracking-wider mb-3 flex items-center gap-2"><span class="w-6 h-6 bg-blue-100 text-blue-700 rounded-full flex items-center justify-center text-xs font-bold">7</span> Market Analysis</h3>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                            <div v-if="hasVal(plan.industry_size)"><p class="text-xs text-gray-500">Industry Size</p><p class="text-gray-700">{{ plan.industry_size }}</p></div>
                            <div v-if="hasVal(plan.growth_rate)"><p class="text-xs text-gray-500">Growth Rate</p><p class="text-gray-700">{{ plan.growth_rate }}</p></div>
                            <div v-if="hasVal(plan.market_size)"><p class="text-xs text-gray-500">Market Size</p><p class="text-gray-700">{{ plan.market_size }}</p></div>
                            <div v-if="hasVal(plan.target_market)"><p class="text-xs text-gray-500">Target Market</p><p class="text-gray-700">{{ plan.target_market }}</p></div>
                        </div>
                        <div v-if="hasVal(plan.industry_trends)" class="mt-3"><p class="text-xs text-gray-500 mb-1">Industry Trends</p><p class="text-sm text-gray-700 whitespace-pre-wrap">{{ plan.industry_trends }}</p></div>
                        <div v-if="hasVal(plan.regulations)"><p class="text-xs text-gray-500 mb-1 mt-2">Regulations</p><p class="text-sm text-gray-700 whitespace-pre-wrap">{{ plan.regulations }}</p></div>
                        <div v-if="hasVal(plan.technology_changes)"><p class="text-xs text-gray-500 mb-1 mt-2">Technology Changes</p><p class="text-sm text-gray-700 whitespace-pre-wrap">{{ plan.technology_changes }}</p></div>
                        <div v-if="hasVal(plan.customer_demographics)" class="mt-3"><p class="text-xs text-gray-500 mb-1">Customer Demographics</p><p class="text-sm text-gray-700">{{ typeof plan.customer_demographics === 'object' ? JSON.stringify(plan.customer_demographics) : plan.customer_demographics }}</p></div>
                    </div>

                    <!-- 8. Market Research -->
                    <div v-if="hasVal(plan.surveys_data)||hasVal(plan.interviews_data)||hasVal(plan.competitor_pricing_data)||hasVal(plan.customer_feedback_information)||hasVal(plan.swot_from_research)" class="border-b border-gray-200 pb-6">
                        <h3 class="text-sm font-bold text-gray-700 uppercase tracking-wider mb-3 flex items-center gap-2"><span class="w-6 h-6 bg-blue-100 text-blue-700 rounded-full flex items-center justify-center text-xs font-bold">8</span> Market Research</h3>
                        <div v-if="hasVal(plan.surveys_data)"><p class="text-xs text-gray-500 mb-1">Survey Findings</p><p class="text-sm text-gray-700 whitespace-pre-wrap">{{ plan.surveys_data }}</p></div>
                        <div v-if="hasVal(plan.interviews_data)" class="mt-3"><p class="text-xs text-gray-500 mb-1">Interview Insights</p><p class="text-sm text-gray-700 whitespace-pre-wrap">{{ plan.interviews_data }}</p></div>
                        <div v-if="hasVal(plan.competitor_pricing_data)" class="mt-3"><p class="text-xs text-gray-500 mb-1">Competitor Pricing Data</p><p class="text-sm text-gray-700 whitespace-pre-wrap">{{ plan.competitor_pricing_data }}</p></div>
                        <div v-if="hasVal(plan.customer_feedback_information)" class="mt-3"><p class="text-xs text-gray-500 mb-1">Customer Feedback</p><p class="text-sm text-gray-700 whitespace-pre-wrap">{{ plan.customer_feedback_information }}</p></div>
                        <div v-if="hasVal(plan.swot_from_research)" class="mt-3"><p class="text-xs text-gray-500 mb-1">SWOT from Research</p><p class="text-sm text-gray-700 whitespace-pre-wrap">{{ plan.swot_from_research }}</p></div>
                    </div>

                    <!-- 9. Competitor Analysis -->
                    <div v-if="hasVal(plan.competitors)||hasVal(plan.structured_competitors)||hasVal(plan.competitive_analysis)" class="border-b border-gray-200 pb-6">
                        <h3 class="text-sm font-bold text-gray-700 uppercase tracking-wider mb-3 flex items-center gap-2"><span class="w-6 h-6 bg-blue-100 text-blue-700 rounded-full flex items-center justify-center text-xs font-bold">9</span> Competitor Analysis</h3>
                        <div v-if="hasVal(plan.competitors)"><p class="text-xs text-gray-500 mb-1">Competitors</p><p class="text-sm text-gray-700">{{ Array.isArray(plan.competitors) ? plan.competitors.join(', ') : plan.competitors }}</p></div>
                        <div v-if="hasVal(plan.structured_competitors)" class="mt-3"><p class="text-xs text-gray-500 mb-1">Detailed Competitor Data</p><p class="text-sm text-gray-700 whitespace-pre-wrap">{{ typeof plan.structured_competitors === 'object' ? JSON.stringify(plan.structured_competitors, null, 2) : plan.structured_competitors }}</p></div>
                        <div v-if="hasVal(plan.competitive_analysis)" class="mt-3"><p class="text-xs text-gray-500 mb-1">Competitive Analysis</p><p class="text-sm text-gray-700 whitespace-pre-wrap">{{ plan.competitive_analysis }}</p></div>
                    </div>

                    <!-- 10. SWOT Analysis -->
                    <div v-if="hasVal(plan.swot_strengths)||hasVal(plan.swot_weaknesses)||hasVal(plan.swot_opportunities)||hasVal(plan.swot_threats)" class="border-b border-gray-200 pb-6">
                        <h3 class="text-sm font-bold text-gray-700 uppercase tracking-wider mb-3 flex items-center gap-2"><span class="w-6 h-6 bg-blue-100 text-blue-700 rounded-full flex items-center justify-center text-xs font-bold">10</span> SWOT Analysis</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div v-if="hasVal(plan.swot_strengths)" class="bg-green-50 rounded-lg p-4 border border-green-200">
                                <p class="text-xs font-bold text-green-800 uppercase mb-1">Strengths</p>
                                <p class="text-sm text-green-900 whitespace-pre-wrap">{{ plan.swot_strengths }}</p>
                            </div>
                            <div v-if="hasVal(plan.swot_weaknesses)" class="bg-red-50 rounded-lg p-4 border border-red-200">
                                <p class="text-xs font-bold text-red-800 uppercase mb-1">Weaknesses</p>
                                <p class="text-sm text-red-900 whitespace-pre-wrap">{{ plan.swot_weaknesses }}</p>
                            </div>
                            <div v-if="hasVal(plan.swot_opportunities)" class="bg-blue-50 rounded-lg p-4 border border-blue-200">
                                <p class="text-xs font-bold text-blue-800 uppercase mb-1">Opportunities</p>
                                <p class="text-sm text-blue-900 whitespace-pre-wrap">{{ plan.swot_opportunities }}</p>
                            </div>
                            <div v-if="hasVal(plan.swot_threats)" class="bg-yellow-50 rounded-lg p-4 border border-yellow-200">
                                <p class="text-xs font-bold text-yellow-800 uppercase mb-1">Threats</p>
                                <p class="text-sm text-yellow-900 whitespace-pre-wrap">{{ plan.swot_threats }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- 11. Unique Selling Points -->
                    <div v-if="hasVal(plan.unique_selling_points)||hasVal(plan.competitive_advantage)||hasVal(plan.customer_pain_points)" class="border-b border-gray-200 pb-6">
                        <h3 class="text-sm font-bold text-gray-700 uppercase tracking-wider mb-3 flex items-center gap-2"><span class="w-6 h-6 bg-blue-100 text-blue-700 rounded-full flex items-center justify-center text-xs font-bold">11</span> Unique Selling Points</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div v-if="hasVal(plan.unique_selling_points)"><p class="text-xs text-gray-500 mb-1">Unique Selling Points</p><p class="text-sm text-gray-700 whitespace-pre-wrap">{{ plan.unique_selling_points }}</p></div>
                            <div v-if="hasVal(plan.competitive_advantage)"><p class="text-xs text-gray-500 mb-1">Competitive Advantage</p><p class="text-sm text-gray-700 whitespace-pre-wrap">{{ plan.competitive_advantage }}</p></div>
                        </div>
                        <div v-if="hasVal(plan.customer_pain_points)" class="mt-3"><p class="text-xs text-gray-500 mb-1">Customer Pain Points</p><p class="text-sm text-gray-700">{{ Array.isArray(plan.customer_pain_points) ? plan.customer_pain_points.join(', ') : plan.customer_pain_points }}</p></div>
                    </div>

                    <!-- 12. Marketing Strategy -->
                    <div v-if="hasVal(plan.marketing_channels)||hasVal(plan.branding_approach)||hasVal(plan.brand_voice)||hasVal(plan.sales_funnel)||hasVal(plan.promotion_channels)" class="border-b border-gray-200 pb-6">
                        <h3 class="text-sm font-bold text-gray-700 uppercase tracking-wider mb-3 flex items-center gap-2"><span class="w-6 h-6 bg-blue-100 text-blue-700 rounded-full flex items-center justify-center text-xs font-bold">12</span> Marketing Strategy</h3>
                        <div v-if="hasVal(plan.marketing_channels)"><p class="text-xs text-gray-500 mb-1">Marketing Channels</p><p class="text-sm text-gray-700">{{ Array.isArray(plan.marketing_channels) ? plan.marketing_channels.join(', ') : plan.marketing_channels }}</p></div>
                        <div v-if="hasVal(plan.branding_approach)" class="mt-3"><p class="text-xs text-gray-500 mb-1">Branding Approach</p><p class="text-sm text-gray-700 whitespace-pre-wrap">{{ plan.branding_approach }}</p></div>
                        <div v-if="hasVal(plan.brand_voice)" class="mt-2"><span class="text-xs text-gray-500">Brand Voice: </span><span class="text-sm text-gray-700">{{ plan.brand_voice }}</span></div>
                        <div v-if="hasVal(plan.sales_funnel)" class="mt-3"><p class="text-xs text-gray-500 mb-1">Sales Funnel</p><p class="text-sm text-gray-700 whitespace-pre-wrap">{{ plan.sales_funnel }}</p></div>
                    </div>

                    <!-- 13. Sales Strategy -->
                    <div v-if="hasVal(plan.sales_channels)||hasVal(plan.customer_retention)||hasVal(plan.sales_process)||hasVal(plan.sales_targets)||hasVal(plan.crm_process)" class="border-b border-gray-200 pb-6">
                        <h3 class="text-sm font-bold text-gray-700 uppercase tracking-wider mb-3 flex items-center gap-2"><span class="w-6 h-6 bg-blue-100 text-blue-700 rounded-full flex items-center justify-center text-xs font-bold">13</span> Sales Strategy</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div v-if="hasVal(plan.sales_channels)"><p class="text-xs text-gray-500 mb-1">Sales Channels</p><p class="text-sm text-gray-700">{{ Array.isArray(plan.sales_channels) ? plan.sales_channels.join(', ') : plan.sales_channels }}</p></div>
                            <div v-if="hasVal(plan.customer_retention)"><p class="text-xs text-gray-500 mb-1">Retention Strategy</p><p class="text-sm text-gray-700">{{ plan.customer_retention }}</p></div>
                        </div>
                        <div v-if="hasVal(plan.sales_process)" class="mt-3"><p class="text-xs text-gray-500 mb-1">Sales Process</p><p class="text-sm text-gray-700 whitespace-pre-wrap">{{ plan.sales_process }}</p></div>
                        <div v-if="hasVal(plan.sales_targets)" class="mt-2"><p class="text-xs text-gray-500 mb-1">Sales Targets</p><p class="text-sm text-gray-700 whitespace-pre-wrap">{{ plan.sales_targets }}</p></div>
                        <div v-if="hasVal(plan.crm_process)" class="mt-2"><p class="text-xs text-gray-500 mb-1">CRM Process</p><p class="text-sm text-gray-700 whitespace-pre-wrap">{{ plan.crm_process }}</p></div>
                    </div>

                    <!-- 14. Operations Plan -->
                    <div v-if="hasVal(plan.daily_operations)||hasVal(plan.facilities)||hasVal(plan.technology_stack)||hasVal(plan.quality_control)||hasVal(plan.equipment_tools)||hasVal(plan.supplier_list)||hasVal(plan.operational_workflow)" class="border-b border-gray-200 pb-6">
                        <h3 class="text-sm font-bold text-gray-700 uppercase tracking-wider mb-3 flex items-center gap-2"><span class="w-6 h-6 bg-blue-100 text-blue-700 rounded-full flex items-center justify-center text-xs font-bold">14</span> Operations Plan</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div v-if="hasVal(plan.daily_operations)"><p class="text-xs text-gray-500 mb-1">Daily Operations</p><p class="text-sm text-gray-700 whitespace-pre-wrap">{{ plan.daily_operations }}</p></div>
                            <div v-if="hasVal(plan.facilities)"><p class="text-xs text-gray-500 mb-1">Facilities</p><p class="text-sm text-gray-700 whitespace-pre-wrap">{{ plan.facilities }}</p></div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-3">
                            <div v-if="hasVal(plan.technology_stack)"><p class="text-xs text-gray-500">Tech Stack</p><p class="text-sm text-gray-700">{{ plan.technology_stack }}</p></div>
                            <div v-if="hasVal(plan.quality_control)"><p class="text-xs text-gray-500">Quality Control</p><p class="text-sm text-gray-700">{{ plan.quality_control }}</p></div>
                            <div v-if="hasVal(plan.operational_workflow)"><p class="text-xs text-gray-500">Workflow</p><p class="text-sm text-gray-700">{{ plan.operational_workflow }}</p></div>
                        </div>
                        <div v-if="hasVal(plan.equipment_tools)" class="mt-3"><p class="text-xs text-gray-500 mb-1">Equipment &amp; Tools</p><p class="text-sm text-gray-700">{{ Array.isArray(plan.equipment_tools) ? plan.equipment_tools.join(', ') : plan.equipment_tools }}</p></div>
                        <div v-if="hasVal(plan.supplier_list)"><p class="text-xs text-gray-500 mb-1 mt-2">Suppliers</p><p class="text-sm text-gray-700">{{ typeof plan.supplier_list === 'object' ? JSON.stringify(plan.supplier_list) : plan.supplier_list }}</p></div>
                    </div>

                    <!-- 15. Organization & HR -->
                    <div v-if="hasVal(plan.staff_roles)||hasVal(plan.hiring_plan)||hasVal(plan.recruitment_strategy)||hasVal(plan.employee_benefits)||hasVal(plan.training_plan)||hasVal(plan.performance_management)||hasVal(plan.key_staff)||hasVal(plan.organizational_chart_data)||hasVal(plan.departments_data)" class="border-b border-gray-200 pb-6">
                        <h3 class="text-sm font-bold text-gray-700 uppercase tracking-wider mb-3 flex items-center gap-2"><span class="w-6 h-6 bg-blue-100 text-blue-700 rounded-full flex items-center justify-center text-xs font-bold">15</span> Organization &amp; Human Resources</h3>
                        <div v-if="hasVal(plan.staff_roles)"><p class="text-xs text-gray-500 mb-1">Staff Roles</p><p class="text-sm text-gray-700">{{ typeof plan.staff_roles === 'object' ? JSON.stringify(plan.staff_roles) : plan.staff_roles }}</p></div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-3">
                            <div v-if="hasVal(plan.hiring_plan)"><p class="text-xs text-gray-500 mb-1">Hiring Plan</p><p class="text-sm text-gray-700 whitespace-pre-wrap">{{ plan.hiring_plan }}</p></div>
                            <div v-if="hasVal(plan.recruitment_strategy)"><p class="text-xs text-gray-500 mb-1">Recruitment Strategy</p><p class="text-sm text-gray-700 whitespace-pre-wrap">{{ plan.recruitment_strategy }}</p></div>
                            <div v-if="hasVal(plan.employee_benefits)"><p class="text-xs text-gray-500 mb-1">Employee Benefits</p><p class="text-sm text-gray-700 whitespace-pre-wrap">{{ plan.employee_benefits }}</p></div>
                            <div v-if="hasVal(plan.training_plan)"><p class="text-xs text-gray-500 mb-1">Training Plan</p><p class="text-sm text-gray-700 whitespace-pre-wrap">{{ plan.training_plan }}</p></div>
                        </div>
                        <div v-if="hasVal(plan.performance_management)" class="mt-3"><p class="text-xs text-gray-500 mb-1">Performance Management</p><p class="text-sm text-gray-700 whitespace-pre-wrap">{{ plan.performance_management }}</p></div>
                    </div>

                    <!-- 16. Risk Analysis -->
                    <div v-if="hasVal(plan.risks)||hasVal(plan.structured_risks)||hasVal(plan.mitigation_strategies)" class="border-b border-gray-200 pb-6">
                        <h3 class="text-sm font-bold text-gray-700 uppercase tracking-wider mb-3 flex items-center gap-2"><span class="w-6 h-6 bg-blue-100 text-blue-700 rounded-full flex items-center justify-center text-xs font-bold">16</span> Risk Analysis</h3>
                        <div v-if="hasVal(plan.risks)"><p class="text-xs text-gray-500 mb-1">Key Risks</p><p class="text-sm text-gray-700">{{ Array.isArray(plan.risks) ? plan.risks.join(', ') : plan.risks }}</p></div>
                        <div v-if="hasVal(plan.mitigation_strategies)" class="mt-3"><p class="text-xs text-gray-500 mb-1">Mitigation Strategies</p><p class="text-sm text-gray-700">{{ typeof plan.mitigation_strategies === 'object' ? JSON.stringify(plan.mitigation_strategies) : plan.mitigation_strategies }}</p></div>
                    </div>

                    <!-- 17. Implementation Roadmap -->
                    <div v-if="hasVal(plan.timeline)||hasVal(plan.milestones)" class="border-b border-gray-200 pb-6">
                        <h3 class="text-sm font-bold text-gray-700 uppercase tracking-wider mb-3 flex items-center gap-2"><span class="w-6 h-6 bg-blue-100 text-blue-700 rounded-full flex items-center justify-center text-xs font-bold">17</span> Implementation Roadmap</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div v-if="hasVal(plan.timeline)"><p class="text-xs text-gray-500 mb-1">Timeline</p><p class="text-sm text-gray-700 whitespace-pre-wrap">{{ typeof plan.timeline === 'object' ? JSON.stringify(plan.timeline, null, 2) : plan.timeline }}</p></div>
                            <div v-if="hasVal(plan.milestones)"><p class="text-xs text-gray-500 mb-1">Milestones</p><p class="text-sm text-gray-700 whitespace-pre-wrap">{{ typeof plan.milestones === 'object' ? JSON.stringify(plan.milestones, null, 2) : plan.milestones }}</p></div>
                        </div>
                    </div>

                    <!-- 18. Financial Plan -->
                    <div v-if="plan.startup_costs != null||plan.expected_monthly_revenue != null||plan.monthly_operating_costs != null||plan.price_per_unit != null||plan.expected_sales_volume != null||plan.staff_salaries != null||plan.inventory_costs != null||plan.utilities_costs != null||hasVal(plan.financial_projections)" class="border-b border-gray-200 pb-6">
                        <h3 class="text-sm font-bold text-gray-700 uppercase tracking-wider mb-3 flex items-center gap-2"><span class="w-6 h-6 bg-blue-100 text-blue-700 rounded-full flex items-center justify-center text-xs font-bold">18</span> Financial Plan</h3>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                            <div v-if="plan.startup_costs != null"><p class="text-xs text-gray-500">Startup Costs</p><p class="font-semibold text-gray-900">{{ formatCurrency(plan.startup_costs) }}</p></div>
                            <div v-if="plan.expected_monthly_revenue != null"><p class="text-xs text-gray-500">Monthly Revenue</p><p class="font-semibold text-gray-900">{{ formatCurrency(plan.expected_monthly_revenue) }}</p></div>
                            <div v-if="plan.monthly_operating_costs != null"><p class="text-xs text-gray-500">Operating Costs</p><p class="font-semibold text-gray-900">{{ formatCurrency(plan.monthly_operating_costs) }}</p></div>
                            <div v-if="plan.staff_salaries != null"><p class="text-xs text-gray-500">Staff Salaries</p><p class="font-semibold text-gray-900">{{ formatCurrency(plan.staff_salaries) }}</p></div>
                            <div v-if="plan.price_per_unit != null"><p class="text-xs text-gray-500">Price/Unit</p><p class="text-gray-700">{{ formatCurrency(plan.price_per_unit) }}</p></div>
                            <div v-if="plan.expected_sales_volume != null"><p class="text-xs text-gray-500">Sales Volume</p><p class="text-gray-700">{{ plan.expected_sales_volume.toLocaleString() }}</p></div>
                            <div v-if="plan.inventory_costs != null"><p class="text-xs text-gray-500">Inventory Costs</p><p class="text-gray-700">{{ formatCurrency(plan.inventory_costs) }}</p></div>
                            <div v-if="plan.utilities_costs != null"><p class="text-xs text-gray-500">Utilities</p><p class="text-gray-700">{{ formatCurrency(plan.utilities_costs) }}</p></div>
                        </div>
                        <div v-if="plan.expected_monthly_revenue != null && plan.monthly_operating_costs != null" class="mt-4 p-3 bg-blue-50 rounded-lg">
                            <p class="text-sm">Monthly Profit: <span :class="(plan.expected_monthly_revenue - plan.monthly_operating_costs - (plan.staff_salaries||0)) >= 0 ? 'text-green-700 font-semibold' : 'text-red-700 font-semibold'">{{ formatCurrency(plan.expected_monthly_revenue - plan.monthly_operating_costs - (plan.staff_salaries||0)) }}</span></p>
                        </div>
                    </div>

                    <!-- 19. Advanced Financials & Funding -->
                    <div v-if="hasVal(plan.break_even_analysis)||hasVal(plan.funding_requirements)||hasVal(plan.profit_loss_projection)||hasVal(plan.cash_flow_projection)||hasVal(plan.balance_sheet_projection)||hasVal(plan.financial_ratios)||hasVal(plan.scenario_planning_data)" class="border-b border-gray-200 pb-6">
                        <h3 class="text-sm font-bold text-gray-700 uppercase tracking-wider mb-3 flex items-center gap-2"><span class="w-6 h-6 bg-blue-100 text-blue-700 rounded-full flex items-center justify-center text-xs font-bold">19</span> Advanced Financials &amp; Funding</h3>
                        <div v-if="hasVal(plan.break_even_analysis)"><p class="text-xs text-gray-500 mb-1">Break-Even Analysis</p><p class="text-sm text-gray-700 whitespace-pre-wrap">{{ plan.break_even_analysis }}</p></div>
                        <div v-if="hasVal(plan.funding_requirements)" class="mt-3"><p class="text-xs text-gray-500 mb-1">Funding Requirements</p><p class="text-sm text-gray-700 whitespace-pre-wrap">{{ typeof plan.funding_requirements === 'object' ? JSON.stringify(plan.funding_requirements, null, 2) : plan.funding_requirements }}</p></div>
                        <div v-if="hasVal(plan.profit_loss_projection)" class="mt-3"><p class="text-xs text-gray-500 mb-1">P&amp;L Projection</p><p class="text-sm text-gray-700 whitespace-pre-wrap">{{ typeof plan.profit_loss_projection === 'object' ? JSON.stringify(plan.profit_loss_projection, null, 2) : plan.profit_loss_projection }}</p></div>
                    </div>

                    <!-- 20. Exit Strategy -->
                    <div v-if="hasVal(plan.exit_strategy_type)||hasVal(plan.exit_strategy_details)||hasVal(plan.appendices)" class="border-b border-gray-200 pb-6">
                        <h3 class="text-sm font-bold text-gray-700 uppercase tracking-wider mb-3 flex items-center gap-2"><span class="w-6 h-6 bg-blue-100 text-blue-700 rounded-full flex items-center justify-center text-xs font-bold">20</span> Exit Strategy &amp; Appendices</h3>
                        <div v-if="hasVal(plan.exit_strategy_type)"><p class="text-xs text-gray-500 mb-1">Exit Strategy</p><p class="text-sm font-medium text-gray-700">{{ plan.exit_strategy_type }}</p></div>
                        <div v-if="hasVal(plan.exit_strategy_details)" class="mt-2"><p class="text-xs text-gray-500 mb-1">Details</p><p class="text-sm text-gray-700 whitespace-pre-wrap">{{ plan.exit_strategy_details }}</p></div>
                        <div v-if="hasVal(plan.appendices)" class="mt-3"><p class="text-xs text-gray-500 mb-1">Appendices</p><p class="text-sm text-gray-700">{{ typeof plan.appendices === 'object' ? JSON.stringify(plan.appendices) : plan.appendices }}</p></div>
                    </div>
                </div>
            </div>
        </div>
    </CMSLayout>
</template>
