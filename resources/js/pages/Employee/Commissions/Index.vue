<template>
    <AdminLayout title="Commission Management">
        <template #header>
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <h2 class="text-xl font-semibold text-gray-800">Commission Management</h2>
                <div class="flex gap-2">
                    <Link
                        v-if="$page.props.auth.user.permissions?.includes('create-commissions')"
                        :href="route('commissions.calculate')"
                        class="inline-flex items-center px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition-colors"
                    >
                        <CalendarIcon class="w-4 h-4 mr-2" />
                        Calculate Commissions
                    </Link>
                    <Link
                        :href="route('commissions.analytics')"
                        class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors"
                    >
                        <BarChart3Icon class="w-4 h-4 mr-2" />
                        Analytics
                    </Link>
                </div>
            </div>
        </template>

        <div class="py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Statistics Cards -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                    <div class="bg-white p-6 rounded-lg shadow-sm border">
                        <div class="flex items-center">
                            <div class="p-2 bg-blue-100 rounded-lg">
                                <DollarSignIcon class="w-6 h-6 text-blue-600" />
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-600">Total Commissions</p>
                                <p class="text-2xl font-semibold text-gray-900">{{ commissions.total || commissions.length }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white p-6 rounded-lg shadow-sm border">
                        <div class="flex items-center">
                            <div class="p-2 bg-green-100 rounded-lg">
                                <CheckCircleIcon class="w-6 h-6 text-green-600" />
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-600">Paid</p>
                                <p class="text-2xl font-semibold text-gray-900">{{ paidCommissionsCount }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white p-6 rounded-lg shadow-sm border">
                        <div class="flex items-center">
                            <div class="p-2 bg-yellow-100 rounded-lg">
                                <ClockIcon class="w-6 h-6 text-yellow-600" />
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-600">Pending</p>
                                <p class="text-2xl font-semibold text-gray-900">{{ pendingCommissionsCount }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white p-6 rounded-lg shadow-sm border">
                        <div class="flex items-center">
                            <div class="p-2 bg-purple-100 rounded-lg">
                                <TrendingUp class="w-6 h-6 text-purple-600" />
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-600">Total Value</p>
                                <p class="text-2xl font-semibold text-gray-900">{{ formatKwacha(totalCommissionValue) }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filters -->
                <div class="bg-white p-6 rounded-lg shadow-sm border mb-6">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Search</label>
                            <input
                                v-model="searchQuery"
                                type="text"
                                placeholder="Search employees..."
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                @input="handleSearch"
                            />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                            <select
                                v-model="selectedStatus"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                @change="handleFilter"
                            >
                                <option value="">All Statuses</option>
                                <option value="pending">Pending</option>
                                <option value="approved">Approved</option>
                                <option value="paid">Paid</option>
                                <option value="rejected">Rejected</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Period</label>
                            <select
                                v-model="selectedPeriod"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                @change="handleFilter"
                            >
                                <option value="">All Periods</option>
                                <option value="current_month">Current Month</option>
                                <option value="last_month">Last Month</option>
                                <option value="current_quarter">Current Quarter</option>
                                <option value="last_quarter">Last Quarter</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Type</label>
                            <select
                                v-model="selectedType"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                @change="handleFilter"
                            >
                                <option value="">All Types</option>
                                <option value="sales">Sales Commission</option>
                                <option value="referral">Referral Commission</option>
                                <option value="performance">Performance Bonus</option>
                                <option value="override">Override Commission</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Commissions List -->
                <div class="bg-white rounded-lg shadow-sm border">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h3 class="text-lg font-medium text-gray-900">Commissions</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Employee
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Type
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Amount
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Rate
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Period
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Status
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Date
                                    </th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="commission in commissions.data || commissions" :key="commission.id">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div>
                                                <div class="text-sm font-medium text-gray-900">
                                                    {{ commission.employee?.first_name }} {{ commission.employee?.last_name }}
                                                </div>
                                                <div class="text-sm text-gray-500">
                                                    {{ commission.employee?.position?.title }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span
                                            :class="[
                                                'inline-flex px-2 py-1 text-xs font-semibold rounded-full',
                                                getTypeColor(commission.commission_type)
                                            ]"
                                        >
                                            {{ formatCommissionType(commission.commission_type) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        {{ formatKwacha(commission.amount) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ commission.commission_rate }}%
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ commission.commission_period }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span
                                            :class="[
                                                'inline-flex px-2 py-1 text-xs font-semibold rounded-full',
                                                getStatusColor(commission.status)
                                            ]"
                                        >
                                            {{ commission.status }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ formatDate(commission.created_at) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <div class="flex justify-end gap-2">
                                            <button
                                                v-if="commission.status === 'pending' && $page.props.auth.user.permissions?.includes('approve-commissions')"
                                                @click="approveCommission(commission.id)"
                                                class="text-green-600 hover:text-green-900"
                                            >
                                                Approve
                                            </button>
                                            <button
                                                v-if="commission.status === 'approved' && $page.props.auth.user.permissions?.includes('process-commission-payments')"
                                                @click="markAsPaid(commission.id)"
                                                class="text-blue-600 hover:text-blue-900"
                                            >
                                                Mark Paid
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup lang="ts">
import { ref, watch, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { Link } from '@inertiajs/vue3';
import { DollarSign as DollarSign, TrendingUp as TrendingUp, Calendar as Calendar, Filter as Filter, BarChart3 as BarChart3, CheckCircle as CheckCircle, Clock as Clock} from 'lucide-vue-next';
import { formatKwacha, formatDate } from '@/utils/formatting.ts';

interface Commission {
    id: number;
    employee?: {
        first_name: string;
        last_name: string;
        position?: {
            title: string;
        };
    };
    commission_type: string;
    amount: number;
    commission_rate: number;
    commission_period: string;
    status: string;
    created_at: string;
}

interface Props {
    commissions: {
        data: Commission[];
        total?: number;
    } | Commission[];
    filters: {
        search?: string;
        status?: string;
        period?: string;
        type?: string;
    };
}

const props = defineProps<Props>();

const searchQuery = ref(props.filters.search || '');
const selectedStatus = ref(props.filters.status || '');
const selectedPeriod = ref(props.filters.period || '');
const selectedType = ref(props.filters.type || '');

const paidCommissionsCount = computed(() => {
    const commissionsList = Array.isArray(props.commissions) ? props.commissions : props.commissions.data;
    return commissionsList.filter(c => c.status === 'paid').length;
});

const pendingCommissionsCount = computed(() => {
    const commissionsList = Array.isArray(props.commissions) ? props.commissions : props.commissions.data;
    return commissionsList.filter(c => c.status === 'pending').length;
});

const totalCommissionValue = computed(() => {
    const commissionsList = Array.isArray(props.commissions) ? props.commissions : props.commissions.data;
    return commissionsList.reduce((sum, c) => sum + c.amount, 0);
});

// Removed - using formatKwacha from utils instead

const formatCommissionType = (type: string) => {
    return type.replace('_', ' ').replace(/\b\w/g, l => l.toUpperCase());
};

const getTypeColor = (type: string) => {
    switch (type.toLowerCase()) {
        case 'sales':
            return 'bg-blue-100 text-blue-800';
        case 'referral':
            return 'bg-green-100 text-green-800';
        case 'performance':
            return 'bg-purple-100 text-purple-800';
        case 'override':
            return 'bg-orange-100 text-orange-800';
        default:
            return 'bg-gray-100 text-gray-800';
    }
};

const getStatusColor = (status: string) => {
    switch (status.toLowerCase()) {
        case 'paid':
            return 'bg-green-100 text-green-800';
        case 'approved':
            return 'bg-blue-100 text-blue-800';
        case 'pending':
            return 'bg-yellow-100 text-yellow-800';
        case 'rejected':
            return 'bg-red-100 text-red-800';
        default:
            return 'bg-gray-100 text-gray-800';
    }
};

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString();
};

const handleSearch = () => {
    router.get(route('admin.commissions.index'), {
        search: searchQuery.value,
        status: selectedStatus.value,
        period: selectedPeriod.value,
        type: selectedType.value,
    }, {
        preserveState: true,
        replace: true,
    });
};

const handleFilter = () => {
    router.get(route('admin.commissions.index'), {
        search: searchQuery.value,
        status: selectedStatus.value,
        period: selectedPeriod.value,
        type: selectedType.value,
    }, {
        preserveState: true,
        replace: true,
    });
};

const approveCommission = (commissionId: number) => {
    router.patch(route('commissions.approve', commissionId), {}, {
        preserveScroll: true,
        onSuccess: () => {
            // Handle success
        }
    });
};

const markAsPaid = (commissionId: number) => {
    router.patch(route('commissions.mark-paid', commissionId), {}, {
        preserveScroll: true,
        onSuccess: () => {
            // Handle success
        }
    });
};
</script>
