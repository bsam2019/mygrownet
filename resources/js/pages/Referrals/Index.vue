<template>
    <InvestorLayout>
        <div class="space-y-6">
            <!-- Header -->
            <div class="bg-white rounded-lg shadow-sm p-4 md:p-6">
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <div>
                        <h1 class="text-xl md:text-2xl font-bold text-gray-900">My Team</h1>
                        <p class="text-sm md:text-base text-gray-600 mt-1">Manage your business network and track team performance</p>
                    </div>
                    <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4">
                        <div class="text-left sm:text-right">
                            <div class="text-sm text-gray-500">Your Business ID</div>
                            <div class="text-base md:text-lg font-mono font-bold text-blue-600">{{ referralCode }}</div>
                        </div>
                        <div class="flex flex-wrap gap-2">
                            <button 
                                @click="activeTab = 'overview'"
                                :class="tabButtonClasses('overview')"
                                class="text-sm md:text-base"
                            >
                                Team Overview
                            </button>
                            <button 
                                @click="activeTab = 'matrix'"
                                :class="tabButtonClasses('matrix')"
                                class="text-sm md:text-base"
                            >
                                Network Structure
                            </button>
                            <button 
                                @click="activeTab = 'spillover'"
                                :class="tabButtonClasses('spillover')"
                                class="text-sm md:text-base"
                            >
                                Growth Opportunities
                            </button>
                            <button 
                                @click="activeTab = 'sharing'"
                                :class="tabButtonClasses('sharing')"
                                class="text-sm md:text-base"
                            >
                                Invite Partners
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tab Content -->
            <div v-show="activeTab === 'overview'">
                <!-- Referral Statistics Dashboard -->
                <ReferralStatsDashboard 
                    :stats="referralStats"
                    :earnings-breakdown="earningsBreakdown"
                    :performance="performance"
                    :recent-activity="recentActivity"
                    :tier-distribution="tierDistribution"
                />
            </div>

            <div v-show="activeTab === 'matrix'">
                <!-- Matrix Visualization -->
                <MatrixVisualization 
                    :matrix-data="matrixData"
                    :spillover-info="spilloverInfo"
                    :matrix-stats="matrixStats"
                    :current-user-tier="currentUserTier"
                />
            </div>

            <div v-show="activeTab === 'spillover'">
                <!-- Spillover Visualization -->
                <SpilloverVisualization 
                    :spillover-data="spilloverData"
                    :level1-referrals="level1Referrals"
                    :spillover-placements="spilloverPlacements"
                    :spillover-history="spilloverHistory"
                    :spillover-opportunities="spilloverOpportunities"
                    :spillover-stats="spilloverStats"
                />
            </div>

            <div v-show="activeTab === 'sharing'">
                <!-- Referral Link Sharing -->
                <ReferralLinkSharing 
                    :referral-code="referralCode"
                    :referral-link="referralLink"
                    :short-link="shortLink"
                    :code-stats="codeStats"
                    :link-stats="linkStats"
                    :message-templates="messageTemplates"
                />
            </div>
        </div>
    </InvestorLayout>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import InvestorLayout from '@/layouts/InvestorLayout.vue';
import { Link } from '@inertiajs/vue3';
import MatrixVisualization from '@/components/Referral/MatrixVisualization.vue';
import ReferralStatsDashboard from '@/components/Referral/ReferralStatsDashboard.vue';
import ReferralLinkSharing from '@/components/Referral/ReferralLinkSharing.vue';
import SpilloverVisualization from '@/components/Referral/SpilloverVisualization.vue';

interface ReferralStats {
    total_referrals_count: number;
    active_referrals_count: number;
    total_commission_earned: number;
    monthly_commission: number;
    pending_commission: number;
    pending_transactions_count: number;
    matrix_earnings: number;
    matrix_positions_filled: number;
}

interface EarningsBreakdown {
    by_level: Array<{
        level: number;
        amount: number;
        count: number;
    }>;
    direct_referrals: number;
    spillover: number;
    matrix_bonuses: number;
    reinvestment_bonuses: number;
    total: number;
}

interface Performance {
    conversion_rate: number;
    average_investment: number;
    retention_rate: number;
    growth_rate: number;
}

interface Activity {
    id: number;
    type: 'referral' | 'commission' | 'spillover' | 'bonus';
    description: string;
    amount?: number;
    created_at: string;
}

interface TierDistribution {
    name: string;
    count: number;
    total_investment: number;
}

interface MatrixData {
    root: any;
    levels: {
        level_1: any[];
        level_2: any[];
        level_3: any[];
    };
}

interface SpilloverInfo {
    has_opportunities: boolean;
    next_position?: {
        level: number;
        position: number;
    };
    placement_type?: string;
    available_slots?: number;
}

interface MatrixStats {
    level_1_count: number;
    level_2_count: number;
    level_3_count: number;
    total_earnings: number;
    filled_positions: number;
    total_positions: number;
}

interface CodeStats {
    uses_count: number;
    successful_registrations: number;
    active_investors: number;
    total_earnings: number;
}

interface LinkStats {
    clicks: number;
    conversion_rate: number;
}

interface MessageTemplate {
    id: number;
    title: string;
    description: string;
    message: string;
}

interface Props {
    referralStats: ReferralStats;
    earningsBreakdown: EarningsBreakdown;
    performance: Performance;
    recentActivity: Activity[];
    tierDistribution: TierDistribution[];
    matrixData: MatrixData;
    spilloverInfo: SpilloverInfo;
    matrixStats: MatrixStats;
    spilloverData: any;
    level1Referrals: any[];
    spilloverPlacements: any[];
    spilloverHistory: any[];
    spilloverOpportunities: any[];
    spilloverStats: any;
    referralCode: string;
    referralLink: string;
    shortLink?: string;
    codeStats: CodeStats;
    linkStats: LinkStats;
    messageTemplates: MessageTemplate[];
    currentUserTier?: string;
}

const props = defineProps<Props>();

const activeTab = ref<'overview' | 'matrix' | 'spillover' | 'sharing'>('overview');

// Computed classes for tab buttons
const tabButtonClasses = (tab: string) => [
    'px-4 py-2 text-sm font-medium rounded-md transition-colors',
    activeTab.value === tab
        ? 'bg-blue-100 text-blue-700'
        : 'text-gray-500 hover:text-gray-700 hover:bg-gray-100'
];


</script>