<template>
    <AdminLayout title="Profit Distribution Management">
        <template #header>
            <h2 class="text-lg sm:text-xl font-semibold text-gray-800 leading-tight">
                Profit Distribution Management
            </h2>
        </template>

        <div class="py-2 sm:py-4 lg:py-6">
            <div class="mx-auto max-w-7xl px-2 sm:px-4 lg:px-6">
                <div class="space-y-3 sm:space-y-4 lg:space-y-6">
                    <!-- Distribution Statistics -->
                    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
                        <div class="bg-white overflow-hidden shadow rounded-lg">
                            <div class="p-5">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0">
                                        <CurrencyDollarIcon class="h-6 w-6 text-green-400" />
                                    </div>
                                    <div class="ml-5 w-0 flex-1">
                                        <dl>
                                            <dt class="text-sm font-medium text-gray-500 truncate">Total Distributed</dt>
                                            <dd class="flex items-baseline">
                                                <div class="text-2xl font-semibold text-green-600">{{ formatCurrency(stats.total_distributed) }}</div>
                                            </dd>
                                        </dl>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white overflow-hidden shadow rounded-lg">
                            <div class="p-5">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0">
                                        <ClockIcon class="h-6 w-6 text-yellow-400" />
                                    </div>
                                    <div class="ml-5 w-0 flex-1">
                                        <dl>
                                            <dt class="text-sm font-medium text-gray-500 truncate">Pending Distributions</dt>
                                            <dd class="flex items-baseline">
                                                <div class="text-2xl font-semibold text-yellow-600">{{ stats.pending_distributions }}</div>
                                            </dd>
                                        </dl>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white overflow-hidden shadow rounded-lg">
                            <div class="p-5">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0">
                                        <BanknotesIcon class="h-6 w-6 text-blue-400" />
                                    </div>
                                    <div class="ml-5 w-0 flex-1">
                                        <dl>
                                            <dt class="text-sm font-medium text-gray-500 truncate">Pending Amount</dt>
                                            <dd class="flex items-baseline">
                                                <div class="text-2xl font-semibold text-blue-600">{{ formatCurrency(stats.pending_amount) }}</div>
                                            </dd>
                                        </dl>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white overflow-hidden shadow rounded-lg">
                            <div class="p-5">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0">
                                        <ChartBarIcon class="h-6 w-6 text-purple-400" />
                                    </div>
                                    <div class="ml-5 w-0 flex-1">
                                        <dl>
                                            <dt class="text-sm font-medium text-gray-500 truncate">This Year</dt>
                                            <dd class="flex items-baseline">
                                                <div class="text-2xl font-semibold text-purple-600">{{ formatCurrency(stats.this_year_distributed) }}</div>
                                            </dd>
                                        </dl>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Distribution Actions -->
                    <div class="bg-white shadow rounded-lg p-6">
                        <div class="flex justify-between items-center mb-6">
                            <h3 class="text-lg font-medium text-gray-900">Distribution Actions</h3>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Annual Distribution -->
                            <div class="border rounded-lg p-4">
                                <h4 class="font-medium text-gray-900 mb-4">Annual Profit Distribution</h4>
                                <form @submit.prevent="processAnnualDistribution">
                                    <div class="space-y-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700">Year</label>
                                            <select v-model="annualForm.year" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                                                <option v-for="year in availableYears" :key="year" :value="year">{{ year }}</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700">Fund Performance (%)</label>
                                            <input 
                                                v-model="annualForm.fund_performance" 
                                                type="number" 
                                                step="0.01" 
                                                min="0" 
                                                max="100"
                                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                                            >
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700">Total Fund Value (K)</label>
                                            <input 
                                                v-model="annualForm.total_fund_value" 
                                                type="number" 
                                                step="0.01" 
                                                min="0"
                                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                                            >
                                        </div>
                                        <button 
                                            type="submit"
                                            :disabled="processing"
                                            class="w-full inline-flex justify-center items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50"
                                        >
                                            {{ processing ? 'Processing...' : 'Process Annual Distribution' }}
                                        </button>
                                    </div>
                                </form>
                            </div>

                            <!-- Quarterly Bonus -->
                            <div class="border rounded-lg p-4">
                                <h4 class="font-medium text-gray-900 mb-4">Quarterly Bonus Distribution</h4>
                                <form @submit.prevent="processQuarterlyBonus">
                                    <div class="space-y-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700">Quarter</label>
                                            <select v-model="quarterlyForm.quarter" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                                                <option value="1">Q1</option>
                                                <option value="2">Q2</option>
                                                <option value="3">Q3</option>
                                                <option value="4">Q4</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700">Year</label>
                                            <select v-model="quarterlyForm.year" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                                                <option v-for="year in availableYears" :key="year" :value="year">{{ year }}</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700">Bonus Pool (K)</label>
                                            <input 
                                                v-model="quarterlyForm.bonus_pool" 
                                                type="number" 
                                                step="0.01" 
                                                min="0"
                                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                                            >
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700">Performance Threshold (%)</label>
                                            <input 
                                                v-model="quarterlyForm.performance_threshold" 
                                                type="number" 
                                                step="0.01" 
                                                min="0" 
                                                max="100"
                                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                                            >
                                        </div>
                                        <button 
                                            type="submit"
                                            :disabled="processing"
                                            class="w-full inline-flex justify-center items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 disabled:opacity-50"
                                        >
                                            {{ processing ? 'Processing...' : 'Process Quarterly Bonus' }}
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Upcoming Distributions -->
                    <div class="bg-white shadow rounded-lg" v-if="upcoming_distributions.length > 0">
                        <div class="px-4 py-5 sm:p-6">
                            <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Upcoming Distributions</h3>
                            <div class="space-y-4">
                                <div v-for="upcoming in upcoming_distributions" :key="upcoming.period"
                                     :class="upcoming.status === 'overdue' ? 'bg-red-50 border-red-200' : 'bg-yellow-50 border-yellow-200'"
                                     class="border rounded-lg p-4">
                                    <div class="flex justify-between items-center">
                                        <div>
                                            <h4 class="font-medium text-gray-900">
                                                {{ upcoming.type === 'annual' ? 'Annual Distribution' : 'Quarterly Bonus' }} - {{ upcoming.period }}
                                            </h4>
                                            <p class="text-sm text-gray-600">
                                                Due: {{ formatDate(upcoming.due_date) }} | 
                                                Estimated Recipients: {{ upcoming.estimated_recipients }}
                                            </p>
                                        </div>
                                        <span :class="upcoming.status === 'overdue' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800'"
                                              class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium">
                                            {{ upcoming.status }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Distribution History -->
                    <div class="bg-white shadow rounded-lg">
                        <div class="px-4 py-5 sm:p-6">
                            <div class="flex justify-between items-center mb-4">
                                <h3 class="text-lg leading-6 font-medium text-gray-900">Distribution History</h3>
                                <div class="flex space-x-2">
                                    <select v-model="filters.period_type" @change="applyFilters" class="text-sm border-gray-300 rounded-md">
                                        <option value="">All Types</option>
                                        <option value="annual">Annual</option>
                                        <option value="quarterly">Quarterly</option>
                                    </select>
                                    <select v-model="filters.status" @change="applyFilters" class="text-sm border-gray-300 rounded-md">
                                        <option value="">All Status</option>
                                        <option value="pending">Pending</option>
                                        <option value="approved">Approved</option>
                                        <option value="paid">Paid</option>
                                        <option value="cancelled">Cancelled</option>
                                    </select>
                                </div>
                            </div>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                <input
                                                    type="checkbox"
                                                    @change="toggleAllDistributions"
                                                    class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                                >
                                            </th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Period</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Amount</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        <tr v-for="distribution in distributions.data" :key="distribution.id">
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <input
                                                    type="checkbox"
                                                    :value="distribution.id"
                                                    v-model="selectedDistributions"
                                                    class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                                >
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm font-medium text-gray-900">{{ distribution.user.name }}</div>
                                                <div class="text-sm text-gray-500">{{ distribution.user.email }}</div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span :class="distribution.period_type === 'annual' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800'"
                                                      class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium">
                                                    {{ distribution.period_type }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ distribution.period }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ formatCurrency(distribution.amount) }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span :class="getStatusColor(distribution.status)"
                                                      class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium">
                                                    {{ distribution.status }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ formatDate(distribution.created_at) }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                <Link :href="route('admin.profit-distribution.show', distribution.id)" class="text-indigo-600 hover:text-indigo-900">
                                                    View
                                                </Link>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            
                            <!-- Bulk Actions -->
                            <div v-if="selectedDistributions.length > 0" class="mt-4 flex justify-between items-center">
                                <span class="text-sm text-gray-700">{{ selectedDistributions.length }} selected</span>
                                <button
                                    @click="bulkApprove"
                                    class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500"
                                >
                                    Bulk Approve
                                </button>
                            </div>

                            <!-- Pagination -->
                            <div class="mt-6">
                                <nav class="flex items-center justify-between">
                                    <div class="flex-1 flex justify-between sm:hidden">
                                        <Link v-if="distributions.prev_page_url" :href="distributions.prev_page_url" class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                                            Previous
                                        </Link>
                                        <Link v-if="distributions.next_page_url" :href="distributions.next_page_url" class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                                            Next
                                        </Link>
                                    </div>
                                    <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                                        <div>
                                            <p class="text-sm text-gray-700">
                                                Showing {{ distributions.from }} to {{ distributions.to }} of {{ distributions.total }} results
                                            </p>
                                        </div>
                                        <div>
                                            <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px">
                                                <Link v-if="distributions.prev_page_url" :href="distributions.prev_page_url" class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                                    Previous
                                                </Link>
                                                <Link v-if="distributions.next_page_url" :href="distributions.next_page_url" class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                                    Next
                                                </Link>
                                            </nav>
                                        </div>
                                    </div>
                                </nav>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { 
    CurrencyDollarIcon, 
    ClockIcon, 
    BanknotesIcon,
    ChartBarIcon 
} from '@heroicons/vue/24/outline'
import AdminLayout from '@/layouts/AdminLayout.vue'
import { router, Link } from '@inertiajs/vue3'
import { formatCurrency } from '@/utils/formatting'

const props = defineProps({
    distributions: Object,
    stats: Object,
    upcoming_distributions: Array,
    filters: Object
})

const processing = ref(false)
const selectedDistributions = ref([])

const annualForm = ref({
    year: new Date().getFullYear(),
    fund_performance: 0,
    total_fund_value: 0
})

const quarterlyForm = ref({
    quarter: Math.ceil(new Date().getMonth() / 3),
    year: new Date().getFullYear(),
    bonus_pool: 0,
    performance_threshold: 0
})

const filters = ref({
    period_type: props.filters.period_type || '',
    status: props.filters.status || '',
    search: props.filters.search || ''
})

const availableYears = computed(() => {
    const currentYear = new Date().getFullYear()
    const years = []
    for (let i = currentYear; i >= currentYear - 5; i--) {
        years.push(i)
    }
    return years
})

const processAnnualDistribution = () => {
    processing.value = true
    router.post(route('admin.profit-distribution.process-annual'), annualForm.value, {
        onFinish: () => processing.value = false
    })
}

const processQuarterlyBonus = () => {
    processing.value = true
    router.post(route('admin.profit-distribution.process-quarterly'), quarterlyForm.value, {
        onFinish: () => processing.value = false
    })
}

const toggleAllDistributions = (event) => {
    if (event.target.checked) {
        selectedDistributions.value = props.distributions.data.map(d => d.id)
    } else {
        selectedDistributions.value = []
    }
}

const bulkApprove = () => {
    router.post(route('admin.profit-distribution.bulk-approve'), {
        distribution_ids: selectedDistributions.value
    }, {
        onSuccess: () => {
            selectedDistributions.value = []
        }
    })
}

const applyFilters = () => {
    router.get(route('admin.profit-distribution.index'), filters.value, {
        preserveState: true,
        preserveScroll: true
    })
}

const getStatusColor = (status) => {
    const colors = {
        pending: 'bg-yellow-100 text-yellow-800',
        approved: 'bg-blue-100 text-blue-800',
        paid: 'bg-green-100 text-green-800',
        cancelled: 'bg-red-100 text-red-800'
    }
    return colors[status] || 'bg-gray-100 text-gray-800'
}



const formatDate = (date) => {
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    })
}
</script>