<script setup lang="ts">
import { ref, computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import MemberLayout from '@/layouts/MemberLayout.vue';
import { 
    UserGroupIcon, 
    ChartBarIcon, 
    CurrencyDollarIcon,
    ArrowRightIcon,
    UsersIcon
} from '@heroicons/vue/24/outline';
import { formatCurrency } from '@/utils/formatting';

interface GenealogyData {
    user_id: number;
    has_matrix: boolean;
    genealogy: Array<{
        level: number;
        users: Array<{
            id: number;
            name: string;
            email: string;
            investment_amount?: number;
            tier?: string;
            position?: number;
        }>;
        total_users: number;
        total_investment: number;
    }>;
}

interface Props {
    genealogy: GenealogyData;
    maxLevel: number;
    user: {
        id: number;
        name: string;
        email: string;
    };
}

const props = defineProps<Props>();

const totalUsers = computed(() => {
    return props.genealogy.genealogy.reduce((sum, level) => sum + level.total_users, 0);
});

const totalInvestment = computed(() => {
    return props.genealogy.genealogy.reduce((sum, level) => sum + level.total_investment, 0);
});

const getLevelColor = (level: number): string => {
    const colors: Record<number, string> = {
        1: 'bg-blue-50 text-blue-600 border-blue-200',
        2: 'bg-green-50 text-green-600 border-green-200',
        3: 'bg-purple-50 text-purple-600 border-purple-200'
    };
    return colors[level] || 'bg-gray-50 text-gray-600 border-gray-200';
};

const getTierColor = (tier: string | undefined): string => {
    if (!tier) return 'bg-gray-100 text-gray-600';
    
    const colors: Record<string, string> = {
        'Basic': 'bg-gray-100 text-gray-600',
        'Starter': 'bg-blue-100 text-blue-600',
        'Builder': 'bg-green-100 text-green-600',
        'Leader': 'bg-yellow-100 text-yellow-600',
        'Elite': 'bg-purple-100 text-purple-600'
    };
    return colors[tier] || 'bg-gray-100 text-gray-600';
};
</script>

<template>
    <MemberLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Matrix Genealogy
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Summary Stats -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                    <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="p-3 bg-blue-50 rounded-lg">
                                    <UsersIcon class="h-6 w-6 text-blue-600" />
                                </div>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500">Total Matrix Members</p>
                                <p class="text-2xl font-bold text-blue-600">
                                    {{ totalUsers }}
                                </p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="p-3 bg-green-50 rounded-lg">
                                    <CurrencyDollarIcon class="h-6 w-6 text-green-600" />
                                </div>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500">Total Investment</p>
                                <p class="text-2xl font-bold text-green-600">
                                    {{ formatCurrency(totalInvestment) }}
                                </p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="p-3 bg-purple-50 rounded-lg">
                                    <ChartBarIcon class="h-6 w-6 text-purple-600" />
                                </div>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500">Matrix Levels</p>
                                <p class="text-2xl font-bold text-purple-600">
                                    {{ genealogy.genealogy.length }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Matrix Status -->
                <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100 mb-8">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">Matrix Status</h3>
                            <p class="text-sm text-gray-500 mt-1">Your current matrix position and genealogy</p>
                        </div>
                        <div class="flex items-center">
                            <span 
                                :class="genealogy.has_matrix ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'"
                                class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium"
                            >
                                {{ genealogy.has_matrix ? 'Active Matrix' : 'No Matrix Position' }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Matrix Genealogy -->
                <div v-if="genealogy.has_matrix && genealogy.genealogy.length > 0" class="space-y-8">
                    <div v-for="level in genealogy.genealogy" :key="level.level" class="bg-white rounded-xl shadow-sm border border-gray-100">
                        <div class="p-6">
                            <div class="flex items-center justify-between mb-6">
                                <div class="flex items-center">
                                    <span 
                                        :class="getLevelColor(level.level)"
                                        class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium border"
                                    >
                                        Level {{ level.level }}
                                    </span>
                                    <div class="ml-4">
                                        <p class="text-sm text-gray-500">{{ level.total_users }} members</p>
                                        <p class="text-sm font-medium text-gray-900">{{ formatCurrency(level.total_investment) }} invested</p>
                                    </div>
                                </div>
                                <Link 
                                    :href="route('referrals.level', { level: level.level })"
                                    class="text-sm text-primary-600 hover:text-primary-700 font-medium flex items-center"
                                >
                                    View Details
                                    <ArrowRightIcon class="h-4 w-4 ml-1" />
                                </Link>
                            </div>
                            
                            <div v-if="level.users.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                                <div 
                                    v-for="member in level.users" 
                                    :key="member.id"
                                    class="bg-gray-50 rounded-lg p-4 hover:bg-gray-100 transition-colors duration-200"
                                >
                                    <div class="flex items-start justify-between mb-3">
                                        <div class="flex-1">
                                            <h4 class="text-sm font-semibold text-gray-900">{{ member.name }}</h4>
                                            <p class="text-xs text-gray-500 mt-1">{{ member.email }}</p>
                                        </div>
                                        <span 
                                            v-if="member.tier"
                                            :class="getTierColor(member.tier)"
                                            class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium"
                                        >
                                            {{ member.tier }}
                                        </span>
                                    </div>
                                    
                                    <div class="space-y-2">
                                        <div v-if="member.investment_amount" class="flex justify-between text-xs">
                                            <span class="text-gray-500">Investment:</span>
                                            <span class="font-medium text-green-600">{{ formatCurrency(member.investment_amount) }}</span>
                                        </div>
                                        <div v-if="member.position" class="flex justify-between text-xs">
                                            <span class="text-gray-500">Position:</span>
                                            <span class="font-medium text-gray-900">#{{ member.position }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div v-else class="text-center py-8">
                                <UserGroupIcon class="h-12 w-12 text-gray-400 mx-auto mb-4" />
                                <p class="text-gray-500">No members at this level yet</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-else class="bg-white rounded-xl shadow-sm border border-gray-100">
                    <div class="p-12 text-center">
                        <UserGroupIcon class="h-16 w-16 text-gray-400 mx-auto mb-6" />
                        <h3 class="text-lg font-medium text-gray-900 mb-2">No Matrix Position</h3>
                        <p class="text-gray-500 max-w-md mx-auto mb-6">
                            You don't have an active matrix position yet. Start building your network by referring new members to establish your matrix genealogy.
                        </p>
                        <Link 
                            :href="route('referrals.index')"
                            class="inline-flex items-center px-4 py-2 bg-primary-600 border border-transparent rounded-lg font-medium text-sm text-white hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
                        >
                            Start Building Network
                            <ArrowRightIcon class="h-4 w-4 ml-2" />
                        </Link>
                    </div>
                </div>

                <!-- Navigation -->
                <div class="mt-8 flex justify-center">
                    <Link 
                        :href="route('referrals.index')"
                        class="inline-flex items-center px-4 py-2 bg-gray-100 border border-transparent rounded-lg font-medium text-sm text-gray-700 hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500"
                    >
                        Back to Referrals
                    </Link>
                </div>
            </div>
        </div>
    </MemberLayout>
</template>