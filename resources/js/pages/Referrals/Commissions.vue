<script setup lang="ts">
import { ref, computed } from 'vue';
import { Link, usePage, router } from '@inertiajs/vue3';
import InvestorLayout from '@/layouts/InvestorLayout.vue';
import { 
    CurrencyDollarIcon, 
    ChartBarIcon, 
    UserGroupIcon,
    CalendarIcon,
    FunnelIcon,
    ArrowPathIcon
} from '@heroicons/vue/24/outline';
import { formatCurrency, formatDate } from '@/utils/formatting';

interface Commission {
    id: number;
    amount: number;
    level: number;
    status: string;
    created_at: string;
    referee?: {
        id: number;
        name: string;
        email: string;
    };
    investment?: {
        id: number;
        amount: number;
    };
}

interface Props {
    commissions: {
        data: Commission[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
        links: Array<{
            url: string | null;
            label: string;
            active: boolean;
        }>;
    };
    filters: {
        status?: string;
        level?: string;
        date_from?: string;
        date_to?: string;
    };
}

const props = defineProps<Props>();

const filters = ref({
    status: props.filters.status || '',
    level: props.filters.level || '',
    date_from: props.filters.date_from || '',
    date_to: props.filters.date_to || ''
});

const applyFilters = () => {
    router.get(route('referrals.commissions'), filters.value, {
        preserveState: true,
        preserveScroll: true
    });
};

const clearFilters = () => {
    filters.value = {
        status: '',
        level: '',
        date_from: '',
        date_to: ''
    };
    applyFilters();
};

const getStatusClass = (status: string): string => {
    const classes: Record<string, string> = {
        'paid': 'bg-green-100 text-green-800',
        'pending': 'bg-yellow-100 text-yellow-800',
        'cancelled': 'bg-red-100 text-red-800'
    };
    return classes[status.toLowerCase()] || 'bg-gray-100 text-gray-800';
};

const getLevelColor = (level: number): string => {
    const colors: Record<number, string> = {
        1: 'text-blue-600 bg-blue-50',
        2: 'text-green-600 bg-green-50',
        3: 'text-purple-600 bg-purple-50'
    };
    return colors[level] || 'text-gray-600 bg-gray-50';
};

const totalCommissions = computed(() => {
    return props.commissions.data.reduce((sum, commission) => sum + commission.amount, 0);
});
</script>

<template>
    <InvestorLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Commission History
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Summary Stats -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                    <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="p-3 bg-green-50 rounded-lg">
                                    <CurrencyDollarIcon class="h-6 w-6 text-green-600" />
                                </div>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500">Total Commissions</p>
                                <p class="text-2xl font-bold text-green-600">
                                    {{ formatCurrency(totalCommissions) }}
                                </p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="p-3 bg-blue-50 rounded-lg">
                                    <ChartBarIcon class="h-6 w-6 text-blue-600" />
                                </div>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500">Total Records</p>
                                <p class="text-2xl font-bold text-gray-900">
                                    {{ commissions.total }}
                                </p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="p-3 bg-purple-50 rounded-lg">
                                    <UserGroupIcon class="h-6 w-6 text-purple-600" />
                                </div>
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-500">Average Commission</p>
                                <p class="text-2xl font-bold text-purple-600">
                                    {{ commissions.total > 0 ? formatCurrency(totalCommissions / commissions.total) : formatCurrency(0) }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filters -->
                <div class="bg-white rounded-xl shadow-sm p-6 border border-gray-100 mb-8">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-900">Filters</h3>
                        <button 
                            @click="clearFilters"
                            class="text-sm text-gray-500 hover:text-gray-700 flex items-center"
                        >
                            <ArrowPathIcon class="h-4 w-4 mr-1" />
                            Clear Filters
                        </button>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                            <select 
                                v-model="filters.status"
                                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                            >
                                <option value="">All Statuses</option>
                                <option value="paid">Paid</option>
                                <option value="pending">Pending</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Level</label>
                            <select 
                                v-model="filters.level"
                                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                            >
                                <option value="">All Levels</option>
                                <option value="1">Level 1</option>
                                <option value="2">Level 2</option>
                                <option value="3">Level 3</option>
                            </select>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">From Date</label>
                            <input 
                                v-model="filters.date_from"
                                type="date"
                                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                            />
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">To Date</label>
                            <input 
                                v-model="filters.date_to"
                                type="date"
                                class="w-full rounded-lg border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500"
                            />
                        </div>
                    </div>
                    
                    <div class="mt-4">
                        <button 
                            @click="applyFilters"
                            class="inline-flex items-center px-4 py-2 bg-primary-600 border border-transparent rounded-lg font-medium text-sm text-white hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
                        >
                            <FunnelIcon class="h-4 w-4 mr-2" />
                            Apply Filters
                        </button>
                    </div>
                </div>

                <!-- Commissions Table -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-6">Commission History</h3>
                        
                        <div v-if="commissions.data.length > 0" class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Date
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Referee
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Level
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Investment
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Commission
                                        </th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Status
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="commission in commissions.data" :key="commission.id" class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            <div class="flex items-center">
                                                <CalendarIcon class="h-4 w-4 text-gray-400 mr-2" />
                                                {{ formatDate(commission.created_at) }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div v-if="commission.referee">
                                                <div class="text-sm font-medium text-gray-900">{{ commission.referee.name }}</div>
                                                <div class="text-sm text-gray-500">{{ commission.referee.email }}</div>
                                            </div>
                                            <div v-else class="text-sm text-gray-500">N/A</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span 
                                                :class="getLevelColor(commission.level)"
                                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                            >
                                                Level {{ commission.level }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            <div v-if="commission.investment">
                                                {{ formatCurrency(commission.investment.amount) }}
                                            </div>
                                            <div v-else class="text-gray-500">N/A</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-green-600">
                                            {{ formatCurrency(commission.amount) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span 
                                                :class="getStatusClass(commission.status)"
                                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium"
                                            >
                                                {{ commission.status.charAt(0).toUpperCase() + commission.status.slice(1) }}
                                            </span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Empty State -->
                        <div v-else class="text-center py-12">
                            <CurrencyDollarIcon class="h-12 w-12 text-gray-400 mx-auto mb-4" />
                            <h3 class="text-lg font-medium text-gray-900 mb-2">No commissions found</h3>
                            <p class="text-gray-500">No commission records match your current filters.</p>
                        </div>
                    </div>
                    
                    <!-- Pagination -->
                    <div v-if="commissions.data.length > 0" class="bg-gray-50 px-6 py-3 border-t border-gray-200">
                        <div class="flex items-center justify-between">
                            <div class="text-sm text-gray-700">
                                Showing {{ (commissions.current_page - 1) * commissions.per_page + 1 }} to 
                                {{ Math.min(commissions.current_page * commissions.per_page, commissions.total) }} of 
                                {{ commissions.total }} results
                            </div>
                            
                            <div class="flex space-x-2">
                                <Link 
                                    v-for="link in commissions.links" 
                                    :key="link.label"
                                    :href="link.url || '#'"
                                    :class="[
                                        'px-3 py-2 text-sm rounded-lg',
                                        link.active 
                                            ? 'bg-primary-600 text-white' 
                                            : link.url 
                                                ? 'bg-white text-gray-700 hover:bg-gray-50 border border-gray-300' 
                                                : 'bg-gray-100 text-gray-400 cursor-not-allowed'
                                    ]"
                                    v-html="link.label"
                                />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </InvestorLayout>
</template>