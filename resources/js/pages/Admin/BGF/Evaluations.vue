<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import CustomAppSidebarLayout from '@/layouts/admin/CustomAppSidebarLayout.vue';
import { Target, ThumbsUp, ThumbsDown, AlertTriangle } from 'lucide-vue-next';

interface Evaluation {
    id: number;
    total_score: number;
    recommendation: string;
    risk_level: string;
    created_at: string;
    membership_score: number;
    training_score: number;
    viability_score: number;
    credibility_score: number;
    contribution_score: number;
    risk_control_score: number;
    track_record_score: number;
    application: {
        id: number;
        reference_number: string;
        business_name: string;
        amount_requested: number;
        user: {
            name: string;
        };
    };
    evaluator: {
        name: string;
    };
}

defineProps<{
    evaluations: {
        data: Evaluation[];
    };
}>();

const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('en-ZM', {
        style: 'currency',
        currency: 'ZMW',
        minimumFractionDigits: 0,
    }).format(amount);
};

const getScoreColor = (score: number) => {
    if (score >= 80) return 'text-green-600';
    if (score >= 70) return 'text-blue-600';
    if (score >= 60) return 'text-yellow-600';
    return 'text-red-600';
};

const getRecommendationIcon = (recommendation: string) => {
    switch (recommendation) {
        case 'approve': return ThumbsUp;
        case 'reject': return ThumbsDown;
        default: return AlertTriangle;
    }
};

const getRecommendationColor = (recommendation: string) => {
    const colors: Record<string, string> = {
        approve: 'bg-green-100 text-green-800',
        reject: 'bg-red-100 text-red-800',
        request_more_info: 'bg-yellow-100 text-yellow-800',
    };
    return colors[recommendation] || 'bg-gray-100 text-gray-800';
};

const getRiskColor = (risk: string) => {
    const colors: Record<string, string> = {
        low: 'bg-green-100 text-green-800',
        medium: 'bg-yellow-100 text-yellow-800',
        high: 'bg-red-100 text-red-800',
    };
    return colors[risk] || 'bg-gray-100 text-gray-800';
};
</script>

<template>
    <Head title="BGF Evaluations" />

    <CustomAppSidebarLayout>
        <div class="space-y-6">
            <div>
                <h1 class="text-3xl font-bold text-gray-900">Evaluations</h1>
                <p class="mt-2 text-gray-600">Application evaluation history</p>
            </div>

            <!-- Stats -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="bg-white rounded-lg shadow p-4">
                    <div class="flex items-center gap-3">
                        <Target class="h-8 w-8 text-blue-600" />
                        <div>
                            <p class="text-sm text-gray-600">Total Evaluations</p>
                            <p class="text-2xl font-bold text-gray-900">{{ evaluations.data.length }}</p>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-lg shadow p-4">
                    <div class="flex items-center gap-3">
                        <ThumbsUp class="h-8 w-8 text-green-600" />
                        <div>
                            <p class="text-sm text-gray-600">Approved</p>
                            <p class="text-2xl font-bold text-green-600">
                                {{ evaluations.data.filter(e => e.recommendation === 'approve').length }}
                            </p>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-lg shadow p-4">
                    <div class="flex items-center gap-3">
                        <ThumbsDown class="h-8 w-8 text-red-600" />
                        <div>
                            <p class="text-sm text-gray-600">Rejected</p>
                            <p class="text-2xl font-bold text-red-600">
                                {{ evaluations.data.filter(e => e.recommendation === 'reject').length }}
                            </p>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-lg shadow p-4">
                    <div class="flex items-center gap-3">
                        <Target class="h-8 w-8 text-blue-600" />
                        <div>
                            <p class="text-sm text-gray-600">Avg Score</p>
                            <p class="text-2xl font-bold text-blue-600">
                                {{ evaluations.data.length > 0 ? (evaluations.data.reduce((sum, e) => sum + e.total_score, 0) / evaluations.data.length).toFixed(1) : 0 }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Evaluations Table -->
            <div class="bg-white rounded-lg shadow overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Application</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Business</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Evaluator</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Score</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Breakdown</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Risk</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Recommendation</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <tr v-if="evaluations.data.length === 0">
                            <td colspan="8" class="px-6 py-12 text-center text-gray-500">
                                No evaluations found
                            </td>
                        </tr>
                        <tr
                            v-for="evaluation in evaluations.data"
                            :key="evaluation.id"
                            class="hover:bg-gray-50 cursor-pointer"
                            @click="router.visit(route('admin.bgf.applications.show', evaluation.application.id))"
                        >
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900">{{ evaluation.application.reference_number }}</div>
                                <div class="text-sm text-gray-500">{{ evaluation.application.user.name }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm text-gray-900">{{ evaluation.application.business_name }}</div>
                                <div class="text-sm text-gray-500">{{ formatCurrency(evaluation.application.amount_requested) }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ evaluation.evaluator.name }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span :class="['text-2xl font-bold', getScoreColor(evaluation.total_score)]">
                                    {{ evaluation.total_score }}
                                </span>
                                <span class="text-sm text-gray-500">/100</span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-xs space-y-1">
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Membership:</span>
                                        <span class="font-medium">{{ evaluation.membership_score }}/15</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Viability:</span>
                                        <span class="font-medium">{{ evaluation.viability_score }}/25</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Credibility:</span>
                                        <span class="font-medium">{{ evaluation.credibility_score }}/15</span>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span :class="['px-2 py-1 text-xs font-medium rounded-full', getRiskColor(evaluation.risk_level)]">
                                    {{ evaluation.risk_level }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span :class="['inline-flex items-center gap-1 px-2 py-1 text-xs font-medium rounded-full', getRecommendationColor(evaluation.recommendation)]">
                                    <component :is="getRecommendationIcon(evaluation.recommendation)" class="h-3 w-3" />
                                    {{ evaluation.recommendation.replace('_', ' ') }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {{ new Date(evaluation.created_at).toLocaleDateString() }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </CustomAppSidebarLayout>
</template>
