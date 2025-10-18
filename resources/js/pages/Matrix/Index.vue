<script setup lang="ts">
import { ref, computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import InvestorLayout from '@/layouts/InvestorLayout.vue';
import { 
    UserGroupIcon, 
    ShareIcon, 
    ClipboardDocumentIcon,
    ChartBarIcon,
    CurrencyDollarIcon,
    UsersIcon
} from '@heroicons/vue/24/outline';
import { formatCurrency } from '@/utils/formatting';

interface MatrixPosition {
    id: number;
    level: number;
    position: number;
    user: {
        id: number;
        name: string;
        email: string;
        total_investment: number;
        tier?: string;
    };
    children: MatrixPosition[];
}

interface ReferralStats {
    total_referrals: number;
    active_referrals: number;
    total_commission: number;
    pending_commission: number;
}

interface DownlineCounts {
    level_1: number;
    level_2: number;
    level_3: number;
    total: number;
}

interface PageProps {
    matrixStructure: MatrixPosition[];
    downlineCounts: DownlineCounts;
    referralStats: ReferralStats;
    matrixPosition: any;
    referralTree: any[];
    referralCode: string;
    referralLink: string;
}

const page = usePage<PageProps>();
const copied = ref(false);

// Provide default values for props
const matrixStructure = computed(() => page.props.matrixStructure || []);
const downlineCounts = computed(() => page.props.downlineCounts || {
    level_1: 0,
    level_2: 0,
    level_3: 0,
    total: 0
});
const referralStats = computed(() => page.props.referralStats || {
    total_referrals: 0,
    active_referrals: 0,
    total_commission: 0,
    pending_commission: 0
});
const referralCode = computed(() => page.props.referralCode || '');
const referralLink = computed(() => page.props.referralLink || '');

const copyReferralLink = async () => {
    try {
        await navigator.clipboard.writeText(referralLink.value);
        copied.value = true;
        setTimeout(() => {
            copied.value = false;
        }, 2000);
    } catch (err) {
        console.error('Failed to copy: ', err);
    }
};

const getTierColor = (tier?: string) => {
    const colors = {
        'Basic': 'bg-gray-100 text-gray-800',
        'Starter': 'bg-blue-100 text-blue-800',
        'Builder': 'bg-green-100 text-green-800',
        'Leader': 'bg-purple-100 text-purple-800',
        'Elite': 'bg-yellow-100 text-yellow-800'
    };
    return colors[tier as keyof typeof colors] || 'bg-gray-100 text-gray-800';
};

const renderMatrixLevel = (positions: MatrixPosition[], level: number) => {
    return positions.filter(p => p.level === level);
};
</script>

<template>
    <InvestorLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Matrix & Referrals
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="space-y-8">
                    <!-- Stats Overview -->
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="p-3 bg-blue-50 rounded-lg">
                                        <UserGroupIcon class="h-6 w-6 text-blue-600" />
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm font-medium text-gray-500">Total Referrals</p>
                                    <p class="text-2xl font-bold text-gray-900">
                                        {{ referralStats.total_referrals }}
                                    </p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="p-3 bg-green-50 rounded-lg">
                                        <UsersIcon class="h-6 w-6 text-green-600" />
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm font-medium text-gray-500">Active Referrals</p>
                                    <p class="text-2xl font-bold text-gray-900">
                                        {{ referralStats.active_referrals }}
                                    </p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="p-3 bg-purple-50 rounded-lg">
                                        <CurrencyDollarIcon class="h-6 w-6 text-purple-600" />
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm font-medium text-gray-500">Total Commission</p>
                                    <p class="text-2xl font-bold text-green-600">
                                        {{ formatCurrency(referralStats.total_commission) }}
                                    </p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="p-3 bg-yellow-50 rounded-lg">
                                        <ChartBarIcon class="h-6 w-6 text-yellow-600" />
                                    </div>
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm font-medium text-gray-500">Pending Commission</p>
                                    <p class="text-2xl font-bold text-yellow-600">
                                        {{ formatCurrency(referralStats.pending_commission) }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Referral Link Section -->
                    <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Your Referral Link</h3>
                        <div class="flex items-center space-x-4">
                            <div class="flex-1">
                                <div class="flex rounded-lg shadow-sm">
                                    <input
                                        type="text"
                                        :value="referralLink"
                                        readonly
                                        class="flex-1 min-w-0 block w-full px-3 py-2 rounded-l-lg border border-gray-300 bg-gray-50 text-gray-900 focus:ring-primary-500 focus:border-primary-500 sm:text-sm"
                                    />
                                    <button
                                        @click="copyReferralLink"
                                        class="inline-flex items-center px-4 py-2 border border-l-0 border-gray-300 rounded-r-lg bg-gray-50 text-gray-500 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                                    >
                                        <ClipboardDocumentIcon class="h-5 w-5" />
                                    </button>
                                </div>
                            </div>
                            <div class="text-sm">
                                <span v-if="copied" class="text-green-600 font-medium">Copied!</span>
                                <span v-else class="text-gray-500">Referral Code: {{ referralCode }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Matrix Structure -->
                    <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                        <h3 class="text-lg font-semibold text-gray-900 mb-6">3x3 Matrix Structure</h3>
                        
                        <!-- Level Statistics -->
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
                            <div class="text-center p-4 bg-gray-50 rounded-lg">
                                <div class="text-2xl font-bold text-gray-900">{{ downlineCounts.level_1 }}</div>
                                <div class="text-sm text-gray-500">Level 1</div>
                            </div>
                            <div class="text-center p-4 bg-gray-50 rounded-lg">
                                <div class="text-2xl font-bold text-gray-900">{{ downlineCounts.level_2 }}</div>
                                <div class="text-sm text-gray-500">Level 2</div>
                            </div>
                            <div class="text-center p-4 bg-gray-50 rounded-lg">
                                <div class="text-2xl font-bold text-gray-900">{{ downlineCounts.level_3 }}</div>
                                <div class="text-sm text-gray-500">Level 3</div>
                            </div>
                            <div class="text-center p-4 bg-primary-50 rounded-lg">
                                <div class="text-2xl font-bold text-primary-600">{{ downlineCounts.total }}</div>
                                <div class="text-sm text-primary-600">Total</div>
                            </div>
                        </div>

                        <!-- Matrix Visual -->
                        <div class="space-y-8">
                            <!-- Level 1 -->
                            <div v-if="matrixStructure && renderMatrixLevel(matrixStructure, 1).length > 0">
                                <h4 class="text-md font-medium text-gray-700 mb-4">Level 1 (Direct Referrals)</h4>
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <div 
                                        v-for="position in renderMatrixLevel(matrixStructure, 1)" 
                                        :key="position.id"
                                        class="bg-blue-50 border border-blue-200 rounded-lg p-4"
                                    >
                                        <div class="text-center">
                                            <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-2">
                                                <UserGroupIcon class="h-6 w-6 text-blue-600" />
                                            </div>
                                            <div class="text-sm font-medium text-gray-900">{{ position.user.name }}</div>
                                            <div class="text-xs text-gray-500">{{ position.user.email }}</div>
                                            <div class="text-xs text-gray-600 mt-1">
                                                {{ formatCurrency(position.user.total_investment) }}
                                            </div>
                                            <div v-if="position.user.tier" class="mt-2">
                                                <span :class="getTierColor(position.user.tier)" 
                                                      class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium">
                                                    {{ position.user.tier }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Level 2 -->
                            <div v-if="matrixStructure && renderMatrixLevel(matrixStructure, 2).length > 0">
                                <h4 class="text-md font-medium text-gray-700 mb-4">Level 2</h4>
                                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3">
                                    <div 
                                        v-for="position in renderMatrixLevel(matrixStructure, 2)" 
                                        :key="position.id"
                                        class="bg-green-50 border border-green-200 rounded-lg p-3"
                                    >
                                        <div class="text-center">
                                            <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-2">
                                                <UserGroupIcon class="h-5 w-5 text-green-600" />
                                            </div>
                                            <div class="text-xs font-medium text-gray-900 truncate">{{ position.user.name }}</div>
                                            <div class="text-xs text-gray-600">
                                                {{ formatCurrency(position.user.total_investment) }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Level 3 -->
                            <div v-if="matrixStructure && renderMatrixLevel(matrixStructure, 3).length > 0">
                                <h4 class="text-md font-medium text-gray-700 mb-4">Level 3</h4>
                                <div class="grid grid-cols-3 md:grid-cols-6 lg:grid-cols-9 gap-2">
                                    <div 
                                        v-for="position in renderMatrixLevel(matrixStructure, 3)" 
                                        :key="position.id"
                                        class="bg-purple-50 border border-purple-200 rounded-lg p-2"
                                    >
                                        <div class="text-center">
                                            <div class="w-8 h-8 bg-purple-100 rounded-full flex items-center justify-center mx-auto mb-1">
                                                <UserGroupIcon class="h-4 w-4 text-purple-600" />
                                            </div>
                                            <div class="text-xs font-medium text-gray-900 truncate">{{ position.user.name }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Empty State -->
                        <div v-if="!matrixStructure || matrixStructure.length === 0" class="text-center py-12">
                            <UserGroupIcon class="mx-auto h-12 w-12 text-gray-400" />
                            <h3 class="mt-2 text-sm font-medium text-gray-900">No referrals yet</h3>
                            <p class="mt-1 text-sm text-gray-500">Start sharing your referral link to build your matrix.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </InvestorLayout>
</template>