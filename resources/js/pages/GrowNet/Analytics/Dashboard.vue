<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import MemberLayout from '@/layouts/MemberLayout.vue';
import { TrendingUpIcon, UsersIcon, DollarSignIcon, AwardIcon, XIcon, ArrowRightIcon } from 'lucide-vue-next';

interface Props {
    performance: {
        earnings: any;
        network: any;
        growth: any;
        engagement: any;
        health_score: number;
        vs_peers: any;
    };
    recommendations: any[];
    predictions: any;
    growthPotential: any;
    nextMilestone: any;
}

const props = defineProps<Props>();

const formatCurrency = (amount: number) => {
    return new Intl.NumberFormat('en-ZM', {
        style: 'currency',
        currency: 'ZMW',
        minimumFractionDigits: 0,
    }).format(amount);
};

const dismissRecommendation = async (id: number) => {
    try {
        await fetch(route('mygrownet.analytics.recommendations.dismiss', id), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
            },
        });
        // Remove from local array
        const index = props.recommendations.findIndex(r => r.id === id);
        if (index > -1) {
            props.recommendations.splice(index, 1);
        }
    } catch (error) {
        console.error('Failed to dismiss recommendation:', error);
    }
};

const getPriorityColor = (priority: string) => {
    switch (priority) {
        case 'high': return 'border-red-500 bg-red-50';
        case 'medium': return 'border-yellow-500 bg-yellow-50';
        case 'low': return 'border-blue-500 bg-blue-50';
        default: return 'border-gray-500 bg-gray-50';
    }
};

const getHealthScoreColor = (score: number) => {
    if (score >= 80) return 'text-green-600';
    if (score >= 60) return 'text-yellow-600';
    return 'text-red-600';
};
</script>

<template>
    <Head title="Analytics Dashboard" />

    <MemberLayout>
        <div class="py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="mb-6">
                    <h1 class="text-2xl font-bold text-gray-900">Performance Analytics</h1>
                    <p class="mt-1 text-sm text-gray-600">
                        Insights into your performance and growth
                    </p>
                </div>

                <!-- Key Metrics -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-gray-600">Total Earnings</p>
                                <p class="text-2xl font-bold text-gray-900">
                                    {{ formatCurrency(performance.earnings.total) }}
                                </p>
                                <p class="text-xs text-gray-500 mt-1">All time</p>
                            </div>
                            <DollarSignIcon class="h-10 w-10 text-green-600" />
                        </div>
                    </div>

                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-gray-600">Network Size</p>
                                <p class="text-2xl font-bold text-gray-900">
                                    {{ performance.network.total_size }}
                                </p>
                                <p class="text-xs text-gray-500 mt-1">
                                    {{ performance.network.active_percentage }}% active
                                </p>
                            </div>
                            <UsersIcon class="h-10 w-10 text-blue-600" />
                        </div>
                    </div>

                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-gray-600">Health Score</p>
                                <p class="text-2xl font-bold" :class="getHealthScoreColor(performance.health_score)">
                                    {{ performance.health_score }}/100
                                </p>
                                <p class="text-xs text-gray-500 mt-1">Network health</p>
                            </div>
                            <AwardIcon class="h-10 w-10 text-purple-600" />
                        </div>
                    </div>

                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm text-gray-600">Growth Rate</p>
                                <p class="text-2xl font-bold text-gray-900">
                                    {{ performance.growth.growth_rate }}%
                                </p>
                                <p class="text-xs text-gray-500 mt-1">Last 30 days</p>
                            </div>
                            <TrendingUpIcon class="h-10 w-10 text-orange-600" />
                        </div>
                    </div>
                </div>

                <!-- Next Milestone -->
                <div v-if="nextMilestone" class="bg-gradient-to-r from-blue-500 to-blue-600 rounded-lg shadow p-6 mb-6 text-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-semibold">Next Milestone: {{ nextMilestone.milestone.level }}</h3>
                            <p class="text-sm opacity-90 mt-1">
                                {{ nextMilestone.remaining }} more referrals to unlock {{ nextMilestone.milestone.reward }}
                            </p>
                            <div class="mt-3 bg-white bg-opacity-20 rounded-full h-2 overflow-hidden">
                                <div 
                                    class="bg-white h-full transition-all duration-500"
                                    :style="{ width: nextMilestone.current_progress + '%' }"
                                ></div>
                            </div>
                            <p class="text-xs mt-2 opacity-75">
                                {{ nextMilestone.current_progress }}% complete
                                <span v-if="nextMilestone.estimated_days">
                                    • Est. {{ nextMilestone.estimated_days }} days
                                </span>
                            </p>
                        </div>
                        <AwardIcon class="h-16 w-16 opacity-50" />
                    </div>
                </div>

                <!-- Recommendations -->
                <div v-if="recommendations.length > 0" class="mb-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Personalized Recommendations</h2>
                    <div class="space-y-3">
                        <div
                            v-for="rec in recommendations"
                            :key="rec.id"
                            class="bg-white rounded-lg shadow p-4 border-l-4 relative"
                            :class="getPriorityColor(rec.priority)"
                        >
                            <button
                                @click="dismissRecommendation(rec.id)"
                                class="absolute top-2 right-2 text-gray-400 hover:text-gray-600"
                            >
                                <XIcon class="h-4 w-4" />
                            </button>
                            <h3 class="font-semibold text-gray-900 pr-8">{{ rec.title }}</h3>
                            <p class="text-sm text-gray-600 mt-1">{{ rec.description }}</p>
                            <div class="mt-3 flex items-center justify-between">
                                <a
                                    :href="rec.action_url"
                                    class="inline-flex items-center text-sm text-blue-600 hover:text-blue-700 font-medium"
                                >
                                    {{ rec.action_text }}
                                    <ArrowRightIcon class="h-4 w-4 ml-1" />
                                </a>
                                <span class="text-xs text-gray-500">
                                    Impact: {{ rec.impact_score }}/100
                                </span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Earnings Breakdown -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                    <div class="bg-white rounded-lg shadow p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Earnings Breakdown</h3>
                        <div class="space-y-3">
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-600">Referral Commissions</span>
                                <span class="font-semibold text-gray-900">
                                    {{ formatCurrency(performance.earnings.by_source.referral_commissions || 0) }}
                                </span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-600">LGR Profit Sharing</span>
                                <span class="font-semibold text-gray-900">
                                    {{ formatCurrency(performance.earnings.by_source.lgr_profit_sharing || 0) }}
                                </span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-600">Level Bonuses</span>
                                <span class="font-semibold text-gray-900">
                                    {{ formatCurrency(performance.earnings.by_source.level_bonuses || 0) }}
                                </span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-600">Other</span>
                                <span class="font-semibold text-gray-900">
                                    {{ formatCurrency(performance.earnings.by_source.other || 0) }}
                                </span>
                            </div>
                            <div class="pt-3 border-t border-gray-200 flex items-center justify-between">
                                <span class="text-sm font-semibold text-gray-900">Total</span>
                                <span class="text-lg font-bold text-gray-900">
                                    {{ formatCurrency(performance.earnings.total) }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Growth Potential -->
                    <div class="bg-white rounded-lg shadow p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Growth Potential</h3>
                        <div class="space-y-4">
                            <div>
                                <div class="flex items-center justify-between mb-1">
                                    <span class="text-sm text-gray-600">Current Monthly Potential</span>
                                    <span class="font-semibold text-gray-900">
                                        {{ formatCurrency(growthPotential.current_monthly_potential) }}
                                    </span>
                                </div>
                                <div class="flex items-center justify-between mb-1">
                                    <span class="text-sm text-gray-600">Full Activation Potential</span>
                                    <span class="font-semibold text-green-600">
                                        {{ formatCurrency(growthPotential.full_activation_potential) }}
                                    </span>
                                </div>
                                <div class="flex items-center justify-between">
                                    <span class="text-sm font-medium text-gray-900">Untapped Potential</span>
                                    <span class="text-lg font-bold text-orange-600">
                                        {{ formatCurrency(growthPotential.untapped_potential) }}
                                    </span>
                                </div>
                            </div>
                            
                            <div v-if="growthPotential.growth_opportunities.length > 0" class="pt-4 border-t border-gray-200">
                                <p class="text-sm font-medium text-gray-900 mb-2">Opportunities:</p>
                                <div class="space-y-2">
                                    <div
                                        v-for="(opp, index) in growthPotential.growth_opportunities"
                                        :key="index"
                                        class="flex items-center justify-between text-sm"
                                    >
                                        <span class="text-gray-600">{{ opp.title }}</span>
                                        <span class="font-semibold text-blue-600">+{{ opp.potential_increase }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Network Metrics -->
                <div class="bg-white rounded-lg shadow p-6 mb-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Network Overview</h3>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <div>
                            <p class="text-sm text-gray-600">Total Network</p>
                            <p class="text-2xl font-bold text-gray-900">{{ performance.network.total_size }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Active Members</p>
                            <p class="text-2xl font-bold text-green-600">{{ performance.network.active_count }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Direct Referrals</p>
                            <p class="text-2xl font-bold text-blue-600">{{ performance.network.direct_referrals }}</p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Active Rate</p>
                            <p class="text-2xl font-bold text-purple-600">{{ performance.network.active_percentage }}%</p>
                        </div>
                    </div>
                </div>

                <!-- Peer Comparison -->
                <div v-if="performance.vs_peers" class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Peer Comparison</h3>
                    <p class="text-sm text-gray-600 mb-4">
                        How you compare to other {{ performance.vs_peers.tier }} members
                    </p>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="text-center p-4 bg-gray-50 rounded-lg">
                            <p class="text-sm text-gray-600 mb-1">Earnings Rank</p>
                            <p class="text-2xl font-bold text-gray-900">
                                {{ performance.vs_peers.earnings_percentile }}%
                            </p>
                            <p class="text-xs text-gray-500 mt-1">percentile</p>
                        </div>
                        <div class="text-center p-4 bg-gray-50 rounded-lg">
                            <p class="text-sm text-gray-600 mb-1">Network Rank</p>
                            <p class="text-2xl font-bold text-gray-900">
                                {{ performance.vs_peers.network_percentile }}%
                            </p>
                            <p class="text-xs text-gray-500 mt-1">percentile</p>
                        </div>
                        <div class="text-center p-4 bg-gray-50 rounded-lg">
                            <p class="text-sm text-gray-600 mb-1">Growth Rank</p>
                            <p class="text-2xl font-bold text-gray-900">
                                {{ performance.vs_peers.growth_percentile }}%
                            </p>
                            <p class="text-xs text-gray-500 mt-1">percentile</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </MemberLayout>
</template>
