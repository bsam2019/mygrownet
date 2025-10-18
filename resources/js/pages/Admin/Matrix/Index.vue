<template>
    <AdminLayout title="Matrix Management">
        <template #header>
            <h2 class="text-lg sm:text-xl font-semibold text-gray-800 leading-tight">
                Matrix Management
            </h2>
        </template>

        <div class="py-2 sm:py-4 lg:py-6">
            <div class="mx-auto max-w-7xl px-2 sm:px-4 lg:px-6">
                <div class="space-y-3 sm:space-y-4 lg:space-y-6">
                    <!-- Matrix Statistics -->
                    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
                        <div class="bg-white overflow-hidden shadow rounded-lg">
                            <div class="p-5">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0">
                                        <UsersIcon class="h-6 w-6 text-gray-400" />
                                    </div>
                                    <div class="ml-5 w-0 flex-1">
                                        <dl>
                                            <dt class="text-sm font-medium text-gray-500 truncate">Total Positions</dt>
                                            <dd class="flex items-baseline">
                                                <div class="text-2xl font-semibold text-gray-900">{{ stats.total_positions }}</div>
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
                                        <CheckCircleIcon class="h-6 w-6 text-green-400" />
                                    </div>
                                    <div class="ml-5 w-0 flex-1">
                                        <dl>
                                            <dt class="text-sm font-medium text-gray-500 truncate">Filled Positions</dt>
                                            <dd class="flex items-baseline">
                                                <div class="text-2xl font-semibold text-green-600">{{ stats.filled_positions }}</div>
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
                                            <dt class="text-sm font-medium text-gray-500 truncate">Pending Spillovers</dt>
                                            <dd class="flex items-baseline">
                                                <div class="text-2xl font-semibold text-yellow-600">{{ stats.pending_spillovers }}</div>
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
                                        <CurrencyDollarIcon class="h-6 w-6 text-blue-400" />
                                    </div>
                                    <div class="ml-5 w-0 flex-1">
                                        <dl>
                                            <dt class="text-sm font-medium text-gray-500 truncate">Matrix Commissions</dt>
                                            <dd class="flex items-baseline">
                                                <div class="text-2xl font-semibold text-blue-600">{{ formatCurrency(stats.commission_stats.total_paid) }}</div>
                                            </dd>
                                        </dl>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Matrix Level Distribution - 7 Levels -->
                    <div class="bg-white shadow rounded-lg p-6">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-medium text-gray-900">7-Level Professional Progression</h3>
                            <div class="text-sm text-gray-500">
                                Total Capacity: {{ formatNumber(stats.max_network_capacity) }} members
                            </div>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Level</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Professional Name</th>
                                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Filled</th>
                                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Capacity</th>
                                        <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase">Fill %</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase">Progress</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="level in stats.matrix_levels" :key="level.level" class="hover:bg-gray-50">
                                        <td class="px-4 py-3 whitespace-nowrap">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium" :class="getLevelBadgeClass(level.level)">
                                                Level {{ level.level }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap">
                                            <span class="text-sm font-medium text-gray-900">{{ level.name }}</span>
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-right text-sm font-semibold text-gray-900">
                                            {{ formatNumber(level.count) }}
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-right text-sm text-gray-500">
                                            {{ formatNumber(level.capacity) }}
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap text-right text-sm font-medium" :class="getPercentageClass(level.fill_percentage)">
                                            {{ level.fill_percentage.toFixed(1) }}%
                                        </td>
                                        <td class="px-4 py-3 whitespace-nowrap">
                                            <div class="w-full bg-gray-200 rounded-full h-2">
                                                <div 
                                                    class="h-2 rounded-full transition-all" 
                                                    :class="getProgressBarClass(level.fill_percentage)"
                                                    :style="{ width: Math.min(level.fill_percentage, 100) + '%' }"
                                                ></div>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Spillover Queue -->
                    <div class="bg-white shadow rounded-lg" v-if="spillover_queue.length > 0">
                        <div class="px-4 py-5 sm:p-6">
                            <div class="flex justify-between items-center mb-4">
                                <h3 class="text-lg leading-6 font-medium text-gray-900">Spillover Queue</h3>
                                <button
                                    @click="processSelectedSpillovers"
                                    :disabled="selectedSpillovers.length === 0"
                                    class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50"
                                >
                                    Process Selected ({{ selectedSpillovers.length }})
                                </button>
                            </div>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                <input
                                                    type="checkbox"
                                                    @change="toggleAllSpillovers"
                                                    class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                                >
                                            </th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sponsor</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Investment</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Waiting Since</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        <tr v-for="user in spillover_queue" :key="user.id">
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <input
                                                    type="checkbox"
                                                    :value="user.id"
                                                    v-model="selectedSpillovers"
                                                    class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                                >
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm font-medium text-gray-900">{{ user.name }}</div>
                                                <div class="text-sm text-gray-500">{{ user.email }}</div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ user.sponsor }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ formatCurrency(user.investment_amount) }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ formatDate(user.waiting_since) }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                <button
                                                    @click="processIndividualSpillover(user.id)"
                                                    class="text-indigo-600 hover:text-indigo-900"
                                                >
                                                    Process Now
                                                </button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Matrix Placements -->
                    <div class="bg-white shadow rounded-lg">
                        <div class="px-4 py-5 sm:p-6">
                            <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Recent Matrix Placements</h3>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sponsor</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Position</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Level</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Placed At</th>
                                            <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        <tr v-for="placement in recent_placements" :key="placement.id">
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm font-medium text-gray-900">{{ placement.user?.name || placement.user }}</div>
                                                <div class="text-sm text-gray-500">{{ placement.user?.email }}</div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ placement.sponsor?.name || placement.sponsor }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                    Position {{ placement.position }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex flex-col">
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium" :class="getLevelBadgeClass(placement.level)">
                                                        Level {{ placement.level }}
                                                    </span>
                                                    <span class="text-xs text-gray-500 mt-1">{{ placement.level_name }}</span>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium" :class="placement.is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'">
                                                    {{ placement.is_active ? 'Active' : 'Inactive' }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ formatDate(placement.placed_at) }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                                <a 
                                                    v-if="placement.user?.id"
                                                    :href="route('admin.matrix.show', placement.user.id)" 
                                                    class="text-indigo-600 hover:text-indigo-900"
                                                >
                                                    View Matrix
                                                </a>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Matrix Issues -->
                    <div class="bg-white shadow rounded-lg" v-if="matrix_issues.length > 0">
                        <div class="px-4 py-5 sm:p-6">
                            <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">Matrix Issues</h3>
                            <div class="space-y-4">
                                <div v-for="issue in matrix_issues" :key="issue.type"
                                     :class="issue.severity === 'high' ? 'bg-red-50 border-red-200' : 'bg-yellow-50 border-yellow-200'"
                                     class="border rounded-lg p-4">
                                    <div class="flex items-center">
                                        <ExclamationTriangleIcon 
                                            :class="issue.severity === 'high' ? 'text-red-400' : 'text-yellow-400'"
                                            class="h-5 w-5 mr-2" 
                                        />
                                        <div>
                                            <h4 :class="issue.severity === 'high' ? 'text-red-800' : 'text-yellow-800'"
                                                class="font-medium">
                                                {{ issue.count }} {{ issue.description }}
                                            </h4>
                                            <p :class="issue.severity === 'high' ? 'text-red-600' : 'text-yellow-600'"
                                               class="text-sm mt-1">
                                                Severity: {{ issue.severity }}
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Toast Notification -->
        <div v-if="showToast" 
             :class="toastType === 'success' ? 'bg-emerald-500' : 'bg-red-500'"
             class="fixed bottom-4 right-4 text-white px-6 py-3 rounded-lg shadow-lg z-50 flex items-center space-x-2 animate-fade-in">
            <CheckCircleIcon v-if="toastType === 'success'" class="h-5 w-5" />
            <ExclamationTriangleIcon v-else class="h-5 w-5" />
            <span>{{ toastMessage }}</span>
        </div>
    </AdminLayout>
</template>

<script setup>
import { ref, computed } from 'vue'
import { 
    UsersIcon, 
    CheckCircleIcon, 
    ClockIcon, 
    CurrencyDollarIcon,
    ExclamationTriangleIcon 
} from '@heroicons/vue/24/outline'
import AdminLayout from '@/layouts/AdminLayout.vue'
import { router } from '@inertiajs/vue3'
import { formatCurrency } from '@/utils/formatting'

// Toast notification
const showToast = ref(false)
const toastMessage = ref('')
const toastType = ref('success') // success, error

const showToastMessage = (message, type = 'success') => {
    toastMessage.value = message
    toastType.value = type
    showToast.value = true
    setTimeout(() => {
        showToast.value = false
    }, 3000)
}

const props = defineProps({
    stats: Object,
    recent_placements: Array,
    spillover_queue: Array,
    matrix_issues: Array,
    filters: Object
})

const selectedSpillovers = ref([])

const toggleAllSpillovers = (event) => {
    if (event.target.checked) {
        selectedSpillovers.value = props.spillover_queue.map(user => user.id)
    } else {
        selectedSpillovers.value = []
    }
}

const processSelectedSpillovers = () => {
    if (selectedSpillovers.value.length === 0) {
        showToastMessage('Please select at least one user to process', 'error')
        return
    }

    const count = selectedSpillovers.value.length
    const userIds = [...selectedSpillovers.value]

    router.post('/admin/matrix/process-spillover', {
        user_ids: userIds
    }, {
        preserveState: true,
        preserveScroll: true,
        onSuccess: (page) => {
            // Remove processed users from the spillover queue
            props.spillover_queue = props.spillover_queue.filter(user => !userIds.includes(user.id))
            selectedSpillovers.value = []
            showToastMessage(`Successfully processed ${count} user(s)!`, 'success')
        },
        onError: (errors) => {
            console.error('Error processing spillovers:', errors)
            showToastMessage('Error processing spillovers. Please try again.', 'error')
        }
    })
}

const processIndividualSpillover = (userId) => {
    router.post('/admin/matrix/process-spillover', {
        user_ids: [userId]
    }, {
        preserveState: true,
        preserveScroll: true,
        onSuccess: (page) => {
            // Remove processed user from the spillover queue
            props.spillover_queue = props.spillover_queue.filter(user => user.id !== userId)
            showToastMessage('User processed successfully!', 'success')
        },
        onError: (errors) => {
            console.error('Error processing spillover:', errors)
            showToastMessage('Error processing spillover. Please try again.', 'error')
        }
    })
}



const formatDate = (date) => {
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    })
}

const formatNumber = (num) => {
    return new Intl.NumberFormat().format(num || 0)
}

const getLevelBadgeClass = (level) => {
    const classes = {
        1: 'bg-gray-100 text-gray-800',
        2: 'bg-blue-100 text-blue-800',
        3: 'bg-emerald-100 text-emerald-800',
        4: 'bg-purple-100 text-purple-800',
        5: 'bg-indigo-100 text-indigo-800',
        6: 'bg-pink-100 text-pink-800',
        7: 'bg-amber-100 text-amber-800',
    }
    return classes[level] || 'bg-gray-100 text-gray-800'
}

const getPercentageClass = (percentage) => {
    if (percentage >= 80) return 'text-green-600'
    if (percentage >= 50) return 'text-yellow-600'
    return 'text-gray-600'
}

const getProgressBarClass = (percentage) => {
    if (percentage >= 80) return 'bg-green-500'
    if (percentage >= 50) return 'bg-yellow-500'
    return 'bg-blue-500'
}
</script>