<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import InvestorLayout from '@/layouts/InvestorLayout.vue';
import {
    ChartBarIcon,
    ArrowTrendingUpIcon,
    ShieldExclamationIcon,
    CurrencyDollarIcon,
    CalendarIcon,
    ArrowUpIcon,
    ArrowDownIcon,
} from '@heroicons/vue/24/outline';

interface ValuationChart {
    labels: string[];
    values: number[];
    methods: string[];
}

interface InvestorValue {
    current_value: number;
    gain_loss: number;
    gain_loss_percentage: number;
    valuation_date: string | null;
}

interface RiskAssessment {
    id: number;
    assessment_date: string;
    overall_risk_score: number;
    risk_level: string;
    summary: string;
    risk_factors: Array<{ name: string; score: number; description: string }>;
}

interface Scenario {
    name: string;
    type: string;
    projections: {
        '1_year': { value: number; roi: number };
        '3_year': { value: number; roi: number };
        '5_year': { value: number; roi: number };
    };
    assumptions: Record<string, any>;
}

interface ExitProjection {
    exit_type: string;
    title: string;
    projected_date: string | null;
    investor_value: number;
    multiple: number;
    probability: number;
}

interface Investor {
    id: number;
    name: string;
    email: string;
}

const props = defineProps<{
    investor: Investor;
    valuationChart: ValuationChart;
    investorValue: InvestorValue;
    riskAssessment: RiskAssessment | null;
    scenarios: Scenario[];
    exitProjections: ExitProjection[];
    investmentAmount: number;
    equityPercentage: number;
    activePage?: string;
    unreadMessages?: number;
    unreadAnnouncements?: number;
}>();

const activeTab = ref<'overview' | 'scenarios' | 'risk' | 'exit'>('overview');

const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('en-ZM', {
        style: 'currency',
        currency: 'ZMW',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(amount);
};

const formatPercentage = (value: number) => {
    return `${value >= 0 ? '+' : ''}${value.toFixed(2)}%`;
};

const getRiskColor = (level: string) => {
    const colors: Record<string, string> = {
        low: 'text-green-600 bg-green-100',
        moderate: 'text-blue-600 bg-blue-100',
        elevated: 'text-yellow-600 bg-yellow-100',
        high: 'text-orange-600 bg-orange-100',
        critical: 'text-red-600 bg-red-100',
    };
    return colors[level] || 'text-gray-600 bg-gray-100';
};

const getScenarioColor = (type: string) => {
    const colors: Record<string, string> = {
        best_case: 'border-green-500 bg-green-50',
        expected: 'border-blue-500 bg-blue-50',
        worst_case: 'border-red-500 bg-red-50',
        custom: 'border-purple-500 bg-purple-50',
    };
    return colors[type] || 'border-gray-500 bg-gray-50';
};
</script>

<template>
    <InvestorLayout 
        :investor="investor" 
        page-title="Analytics" 
        :active-page="activePage || 'analytics'"
        :unread-messages="unreadMessages"
        :unread-announcements="unreadAnnouncements"
    >
        <Head title="Advanced Analytics" />

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Header -->
            <div class="mb-8">
                <h1 class="text-2xl font-bold text-gray-900">Advanced Analytics</h1>
                <p class="mt-1 text-sm text-gray-500">
                    Deep insights into your investment performance and projections
                </p>
            </div>

            <!-- Tab Navigation -->
            <div class="border-b border-gray-200 mb-6">
                <nav class="-mb-px flex space-x-8">
                    <button
                        v-for="tab in [
                            { id: 'overview', label: 'Overview', icon: ChartBarIcon },
                            { id: 'scenarios', label: 'Scenarios', icon: ArrowTrendingUpIcon },
                            { id: 'risk', label: 'Risk Assessment', icon: ShieldExclamationIcon },
                            { id: 'exit', label: 'Exit Projections', icon: CurrencyDollarIcon },
                        ]"
                        :key="tab.id"
                        @click="activeTab = tab.id as any"
                        :class="[
                            activeTab === tab.id
                                ? 'border-blue-500 text-blue-600'
                                : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                            'group inline-flex items-center py-4 px-1 border-b-2 font-medium text-sm',
                        ]"
                    >
                        <component
                            :is="tab.icon"
                            :class="[
                                activeTab === tab.id ? 'text-blue-500' : 'text-gray-400 group-hover:text-gray-500',
                                '-ml-0.5 mr-2 h-5 w-5',
                            ]"
                            aria-hidden="true"
                        />
                        {{ tab.label }}
                    </button>
                </nav>
            </div>

            <!-- Overview Tab -->
            <div v-if="activeTab === 'overview'" class="space-y-6">
                <!-- Value Summary Cards -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-500">Current Value</p>
                                <p class="mt-1 text-2xl font-semibold text-gray-900">
                                    {{ formatCurrency(investorValue.current_value) }}
                                </p>
                            </div>
                            <div class="p-3 bg-blue-100 rounded-full">
                                <CurrencyDollarIcon class="h-6 w-6 text-blue-600" aria-hidden="true" />
                            </div>
                        </div>
                        <p class="mt-2 text-xs text-gray-500">
                            Based on {{ equityPercentage }}% equity
                        </p>
                    </div>

                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-500">Gain/Loss</p>
                                <p
                                    :class="[
                                        'mt-1 text-2xl font-semibold',
                                        investorValue.gain_loss >= 0 ? 'text-green-600' : 'text-red-600',
                                    ]"
                                >
                                    {{ formatCurrency(investorValue.gain_loss) }}
                                </p>
                            </div>
                            <div
                                :class="[
                                    'p-3 rounded-full',
                                    investorValue.gain_loss >= 0 ? 'bg-green-100' : 'bg-red-100',
                                ]"
                            >
                                <component
                                    :is="investorValue.gain_loss >= 0 ? ArrowUpIcon : ArrowDownIcon"
                                    :class="[
                                        'h-6 w-6',
                                        investorValue.gain_loss >= 0 ? 'text-green-600' : 'text-red-600',
                                    ]"
                                    aria-hidden="true"
                                />
                            </div>
                        </div>
                        <p
                            :class="[
                                'mt-2 text-sm font-medium',
                                investorValue.gain_loss_percentage >= 0 ? 'text-green-600' : 'text-red-600',
                            ]"
                        >
                            {{ formatPercentage(investorValue.gain_loss_percentage) }}
                        </p>
                    </div>

                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-500">Initial Investment</p>
                                <p class="mt-1 text-2xl font-semibold text-gray-900">
                                    {{ formatCurrency(investmentAmount) }}
                                </p>
                            </div>
                            <div class="p-3 bg-gray-100 rounded-full">
                                <CalendarIcon class="h-6 w-6 text-gray-600" aria-hidden="true" />
                            </div>
                        </div>
                        <p class="mt-2 text-xs text-gray-500">
                            {{ equityPercentage }}% equity stake
                        </p>
                    </div>
                </div>

                <!-- Valuation Chart -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Valuation History</h3>
                    <div class="h-64 flex items-center justify-center bg-gray-50 rounded-lg">
                        <div v-if="valuationChart.labels.length > 0" class="w-full px-4">
                            <div class="flex items-end justify-between h-48 gap-2">
                                <div
                                    v-for="(value, index) in valuationChart.values"
                                    :key="index"
                                    class="flex-1 flex flex-col items-center"
                                >
                                    <div
                                        class="w-full bg-blue-500 rounded-t transition-all duration-300"
                                        :style="{
                                            height: `${(value / Math.max(...valuationChart.values)) * 100}%`,
                                            minHeight: '4px',
                                        }"
                                    ></div>
                                    <span class="text-xs text-gray-500 mt-2 truncate w-full text-center">
                                        {{ valuationChart.labels[index] }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        <p v-else class="text-gray-500">No valuation history available</p>
                    </div>
                </div>
            </div>

            <!-- Scenarios Tab -->
            <div v-if="activeTab === 'scenarios'" class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div
                        v-for="scenario in scenarios"
                        :key="scenario.name"
                        :class="['bg-white rounded-lg shadow border-l-4 p-6', getScenarioColor(scenario.type)]"
                    >
                        <h3 class="text-lg font-medium text-gray-900 capitalize">
                            {{ scenario.name }}
                        </h3>
                        <p class="text-sm text-gray-500 mb-4">{{ scenario.type.replace('_', ' ') }} scenario</p>

                        <div class="space-y-4">
                            <div>
                                <p class="text-xs text-gray-500">1 Year Projection</p>
                                <p class="text-lg font-semibold text-gray-900">
                                    {{ formatCurrency(scenario.projections['1_year'].value) }}
                                </p>
                                <p class="text-sm text-green-600">
                                    {{ formatPercentage(scenario.projections['1_year'].roi) }} ROI
                                </p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">3 Year Projection</p>
                                <p class="text-lg font-semibold text-gray-900">
                                    {{ formatCurrency(scenario.projections['3_year'].value) }}
                                </p>
                                <p class="text-sm text-green-600">
                                    {{ formatPercentage(scenario.projections['3_year'].roi) }} ROI
                                </p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500">5 Year Projection</p>
                                <p class="text-lg font-semibold text-gray-900">
                                    {{ formatCurrency(scenario.projections['5_year'].value) }}
                                </p>
                                <p class="text-sm text-green-600">
                                    {{ formatPercentage(scenario.projections['5_year'].roi) }} ROI
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-if="scenarios.length === 0" class="bg-white rounded-lg shadow p-8 text-center">
                    <ArrowTrendingUpIcon class="mx-auto h-12 w-12 text-gray-400" aria-hidden="true" />
                    <h3 class="mt-2 text-sm font-medium text-gray-900">No scenarios available</h3>
                    <p class="mt-1 text-sm text-gray-500">Scenario projections will be available soon.</p>
                </div>
            </div>

            <!-- Risk Assessment Tab -->
            <div v-if="activeTab === 'risk'" class="space-y-6">
                <div v-if="riskAssessment" class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center justify-between mb-6">
                        <div>
                            <h3 class="text-lg font-medium text-gray-900">Current Risk Assessment</h3>
                            <p class="text-sm text-gray-500">
                                Last assessed: {{ new Date(riskAssessment.assessment_date).toLocaleDateString() }}
                            </p>
                        </div>
                        <span
                            :class="[
                                'px-3 py-1 rounded-full text-sm font-medium capitalize',
                                getRiskColor(riskAssessment.risk_level),
                            ]"
                        >
                            {{ riskAssessment.risk_level }} Risk
                        </span>
                    </div>

                    <!-- Risk Score -->
                    <div class="mb-6">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-sm font-medium text-gray-700">Overall Risk Score</span>
                            <span class="text-sm font-medium text-gray-900">{{ riskAssessment.overall_risk_score }}/100</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-3">
                            <div
                                class="h-3 rounded-full transition-all duration-500"
                                :class="{
                                    'bg-green-500': riskAssessment.overall_risk_score <= 30,
                                    'bg-yellow-500': riskAssessment.overall_risk_score > 30 && riskAssessment.overall_risk_score <= 60,
                                    'bg-orange-500': riskAssessment.overall_risk_score > 60 && riskAssessment.overall_risk_score <= 80,
                                    'bg-red-500': riskAssessment.overall_risk_score > 80,
                                }"
                                :style="{ width: `${riskAssessment.overall_risk_score}%` }"
                            ></div>
                        </div>
                    </div>

                    <!-- Summary -->
                    <div class="mb-6">
                        <h4 class="text-sm font-medium text-gray-700 mb-2">Summary</h4>
                        <p class="text-sm text-gray-600">{{ riskAssessment.summary }}</p>
                    </div>

                    <!-- Risk Factors -->
                    <div v-if="riskAssessment.risk_factors?.length">
                        <h4 class="text-sm font-medium text-gray-700 mb-3">Risk Factors</h4>
                        <div class="space-y-3">
                            <div
                                v-for="factor in riskAssessment.risk_factors"
                                :key="factor.name"
                                class="bg-gray-50 rounded-lg p-4"
                            >
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-sm font-medium text-gray-900">{{ factor.name }}</span>
                                    <span class="text-sm text-gray-500">{{ factor.score }}/100</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2 mb-2">
                                    <div
                                        class="h-2 rounded-full"
                                        :class="{
                                            'bg-green-500': factor.score <= 30,
                                            'bg-yellow-500': factor.score > 30 && factor.score <= 60,
                                            'bg-red-500': factor.score > 60,
                                        }"
                                        :style="{ width: `${factor.score}%` }"
                                    ></div>
                                </div>
                                <p class="text-xs text-gray-500">{{ factor.description }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-else class="bg-white rounded-lg shadow p-8 text-center">
                    <ShieldExclamationIcon class="mx-auto h-12 w-12 text-gray-400" aria-hidden="true" />
                    <h3 class="mt-2 text-sm font-medium text-gray-900">No risk assessment available</h3>
                    <p class="mt-1 text-sm text-gray-500">Risk assessments will be published periodically.</p>
                </div>
            </div>

            <!-- Exit Projections Tab -->
            <div v-if="activeTab === 'exit'" class="space-y-6">
                <div v-if="exitProjections.length > 0" class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div
                        v-for="projection in exitProjections"
                        :key="projection.exit_type"
                        class="bg-white rounded-lg shadow p-6"
                    >
                        <div class="flex items-start justify-between mb-4">
                            <div>
                                <h3 class="text-lg font-medium text-gray-900">{{ projection.title }}</h3>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 capitalize">
                                    {{ projection.exit_type.replace('_', ' ') }}
                                </span>
                            </div>
                            <div v-if="projection.probability" class="text-right">
                                <p class="text-sm text-gray-500">Probability</p>
                                <p class="text-lg font-semibold text-gray-900">{{ projection.probability }}%</p>
                            </div>
                        </div>

                        <div class="space-y-3">
                            <div>
                                <p class="text-sm text-gray-500">Your Projected Value</p>
                                <p class="text-2xl font-bold text-green-600">
                                    {{ formatCurrency(projection.investor_value) }}
                                </p>
                            </div>
                            <div v-if="projection.multiple" class="flex items-center gap-2">
                                <span class="text-sm text-gray-500">Multiple:</span>
                                <span class="text-sm font-medium text-gray-900">{{ projection.multiple }}x</span>
                            </div>
                            <div v-if="projection.projected_date">
                                <p class="text-sm text-gray-500">Projected Timeline</p>
                                <p class="text-sm font-medium text-gray-900">
                                    {{ new Date(projection.projected_date).toLocaleDateString('en-US', { year: 'numeric', month: 'long' }) }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <div v-else class="bg-white rounded-lg shadow p-8 text-center">
                    <CurrencyDollarIcon class="mx-auto h-12 w-12 text-gray-400" aria-hidden="true" />
                    <h3 class="mt-2 text-sm font-medium text-gray-900">No exit projections available</h3>
                    <p class="mt-1 text-sm text-gray-500">Exit strategy projections will be shared when available.</p>
                </div>
            </div>
        </div>
    </InvestorLayout>
</template>
