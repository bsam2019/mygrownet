<template>
    <AdminLayout title="Referral Management">
        <template #header>
            <h2 class="text-lg sm:text-xl font-semibold text-gray-800 leading-tight">
                Referral Management
            </h2>
        </template>

        <div class="py-2 sm:py-4 lg:py-6">
            <div class="mx-auto max-w-7xl px-2 sm:px-4 lg:px-6">
                <div class="space-y-3 sm:space-y-4 lg:space-y-6">
                    <!-- Overview Stats -->
                    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
                        <div class="bg-white overflow-hidden shadow rounded-lg">
                            <div class="p-5">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0">
                                        <UserGroupIcon class="h-6 w-6 text-gray-400" />
                                    </div>
                                    <div class="ml-5 w-0 flex-1">
                                        <dl>
                                            <dt class="text-sm font-medium text-gray-500 truncate">Total Referrals</dt>
                                            <dd class="flex items-baseline">
                                                <div class="text-2xl font-semibold text-gray-900">{{ stats.total_referrals }}</div>
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
                                        <BanknotesIcon class="h-6 w-6 text-gray-400" />
                                    </div>
                                    <div class="ml-5 w-0 flex-1">
                                        <dl>
                                            <dt class="text-sm font-medium text-gray-500 truncate">Total Commission Paid</dt>
                                            <dd class="flex items-baseline">
                                                <div class="text-2xl font-semibold text-gray-900">{{ formatCurrency(stats.total_commission_paid) }}</div>
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
                                        <ClockIcon class="h-6 w-6 text-gray-400" />
                                    </div>
                                    <div class="ml-5 w-0 flex-1">
                                        <dl>
                                            <dt class="text-sm font-medium text-gray-500 truncate">Pending Commissions</dt>
                                            <dd class="flex items-baseline">
                                                <div class="text-2xl font-semibold text-gray-900">{{ formatCurrency(stats.pending_commission) }}</div>
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
                                        <UserIcon class="h-6 w-6 text-gray-400" />
                                    </div>
                                    <div class="ml-5 w-0 flex-1">
                                        <dl>
                                            <dt class="text-sm font-medium text-gray-500 truncate">Active Referrers</dt>
                                            <dd class="flex items-baseline">
                                                <div class="text-2xl font-semibold text-gray-900">{{ stats.active_referrers }}</div>
                                            </dd>
                                        </dl>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Commission Structure -->
                    <div class="bg-white shadow rounded-lg p-6">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-medium text-gray-900">Commission Structure</h3>
                            <button
                                @click="processPendingCommissions"
                                class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                            >
                                Process Pending Commissions
                            </button>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="border rounded-lg p-4">
                                <h4 class="font-medium text-gray-900 mb-2">Direct Referrals (Level 1)</h4>
                                <p class="text-3xl font-bold text-indigo-600">5%</p>
                                <p class="text-sm text-gray-500 mt-1">Commission on direct referral investments</p>
                            </div>
                            <div class="border rounded-lg p-4">
                                <h4 class="font-medium text-gray-900 mb-2">Indirect Referrals (Level 2)</h4>
                                <p class="text-3xl font-bold text-indigo-600">2%</p>
                                <p class="text-sm text-gray-500 mt-1">Commission on indirect referral investments</p>
                            </div>
                        </div>
                    </div>

                    <!-- Top Referrers -->
                    <div class="bg-white shadow rounded-lg">
                        <div class="px-4 py-5 sm:p-6">
                            <h3 class="text-lg leading-6 font-medium text-gray-900">Top Referrers</h3>
                            <div class="mt-4">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Referrals</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Active Referrals</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Commission</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        <tr v-for="referrer in topReferrers" :key="referrer.id">
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex items-center">
                                                    <div class="ml-4">
                                                        <div class="text-sm font-medium text-gray-900">{{ referrer.name }}</div>
                                                        <div class="text-sm text-gray-500">{{ referrer.email }}</div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ referrer.referrals_count }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ referrer.active_referrals }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ formatCurrency(referrer.total_commission) }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Monthly Stats -->
                    <div class="bg-white shadow rounded-lg">
                        <div class="px-4 py-5 sm:p-6">
                            <h3 class="text-lg leading-6 font-medium text-gray-900">Monthly Referral Statistics</h3>
                            <div class="mt-4">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Month</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">New Referrals</th>
                                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Commission</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        <tr v-for="stat in monthlyStats" :key="stat.month">
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ formatMonth(stat.month) }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ stat.referral_count }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ formatCurrency(stat.total_earnings) }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { UserGroupIcon, UserIcon, BanknotesIcon, ClockIcon } from '@heroicons/vue/24/outline'
import AdminLayout from '@/layouts/AdminLayout.vue'
import { formatCurrency } from '@/utils/formatting'
import axios from 'axios'

const stats = ref({
    total_referrals: 0,
    total_commission_paid: 0,
    pending_commission: 0,
    active_referrers: 0
})

const topReferrers = ref([])
const monthlyStats = ref([])

const fetchData = async () => {
    try {
        const [statsResponse, referrersResponse, monthlyResponse] = await Promise.all([
            axios.get('/api/admin/referral/stats'),
            axios.get('/api/admin/referral/top-referrers'),
            axios.get('/api/admin/referral/monthly-stats')
        ])

        stats.value = statsResponse.data.data
        topReferrers.value = referrersResponse.data.data
        monthlyStats.value = monthlyResponse.data.data
    } catch (error) {
        console.error('Error fetching referral data:', error)
    }
}

const processPendingCommissions = async () => {
    try {
        await axios.post('/api/admin/referral/process-pending')
        await fetchData()
        // Show success notification
    } catch (error) {
        console.error('Error processing pending commissions:', error)
        // Show error notification
    }
}


const formatMonth = (month) => {
    const [year, monthNum] = month.split('-')
    return new Date(year, monthNum - 1).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long'
    })
}

onMounted(() => {
    fetchData()
})
</script> 