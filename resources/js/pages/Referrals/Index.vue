<template>
    <MemberLayout>
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
                                @click="changeTab('members')"
                                :class="tabButtonClasses('members')"
                                class="text-sm md:text-base"
                                type="button"
                            >
                                My Team List
                            </button>
                            <button 
                                @click="changeTab('overview')"
                                :class="tabButtonClasses('overview')"
                                class="text-sm md:text-base"
                                type="button"
                            >
                                Team Overview
                            </button>
                            <button 
                                @click="changeTab('matrix')"
                                :class="tabButtonClasses('matrix')"
                                class="text-sm md:text-base"
                                type="button"
                            >
                                Network Structure
                            </button>
                            <button 
                                @click="changeTab('spillover')"
                                :class="tabButtonClasses('spillover')"
                                class="text-sm md:text-base"
                                type="button"
                            >
                                Growth Opportunities
                            </button>
                            <button 
                                @click="changeTab('sharing')"
                                :class="tabButtonClasses('sharing')"
                                class="text-sm md:text-base"
                                type="button"
                            >
                                Invite Partners
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tab Content -->
            <div v-show="activeTab === 'members'">
                <!-- Team Members List -->
                <div class="bg-white rounded-lg shadow-sm p-6">
                    <div class="mb-6">
                        <h2 class="text-2xl font-bold text-gray-900">My Team Members</h2>
                        <p class="text-lg text-gray-600 mt-2">
                            Total Team Members: <span class="font-bold text-blue-600">{{ totalTeamMembers }}</span>
                        </p>
                    </div>

                    <div v-if="teamMembers && teamMembers.length > 0" class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">#</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Name</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Phone Number</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Joined Date</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="(member, index) in teamMembers" :key="member.id" class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ index + 1 }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">{{ member.name }}</div>
                                        <div class="text-sm text-gray-500">{{ member.email }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ member.phone }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span 
                                            :class="member.is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'"
                                            class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full"
                                        >
                                            {{ member.is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ member.joined_at }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div v-else class="text-center py-12">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">No team members yet</h3>
                        <p class="mt-1 text-sm text-gray-500">Start inviting people to build your team!</p>
                    </div>
                </div>
            </div>

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
    </MemberLayout>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import MemberLayout from '@/layouts/MemberLayout.vue';
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

interface TeamMember {
    id: number;
    name: string;
    phone: string;
    email: string;
    status: string;
    joined_at: string;
    is_active: boolean;
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
    teamMembers: TeamMember[];
    totalTeamMembers: number;
}

const props = defineProps<Props>();

const activeTab = ref<'members' | 'overview' | 'matrix' | 'spillover' | 'sharing'>('members');

// Function to change tab with logging
const changeTab = (tab: 'members' | 'overview' | 'matrix' | 'spillover' | 'sharing') => {
    console.log('Changing tab to:', tab);
    activeTab.value = tab;
};

// Computed classes for tab buttons
const tabButtonClasses = (tab: string) => [
    'px-4 py-2 text-sm font-medium rounded-md transition-colors',
    activeTab.value === tab
        ? 'bg-blue-600 text-white'
        : 'text-gray-700 hover:text-gray-900 hover:bg-gray-100 bg-white border border-gray-300'
];


</script>