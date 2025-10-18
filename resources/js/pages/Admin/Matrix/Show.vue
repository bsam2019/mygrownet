<template>
    <AdminLayout :title="`Matrix - ${user.name}`">
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-lg sm:text-xl font-semibold text-gray-800 leading-tight">
                    Matrix Details - {{ user.name }}
                </h2>
                <a :href="route('admin.matrix.index')" class="text-sm text-blue-600 hover:text-blue-800">
                    ← Back to Matrix Overview
                </a>
            </div>
        </template>

        <div class="py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="space-y-6">
                    <!-- User Info Card -->
                    <div class="bg-white shadow rounded-lg p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">User Information</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <p class="text-sm text-gray-500">Name</p>
                                <p class="text-base font-medium text-gray-900">{{ user.name }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Email</p>
                                <p class="text-base font-medium text-gray-900">{{ user.email }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Referrer/Sponsor</p>
                                <p class="text-base font-medium text-gray-900">{{ user.referrer?.name || 'None' }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Professional Level</p>
                                <p class="text-base font-medium text-gray-900">{{ user.current_professional_level || 'Associate' }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Matrix Position Details -->
                    <div class="bg-white shadow rounded-lg p-6" v-if="position_details">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Matrix Position</h3>
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                            <div>
                                <p class="text-sm text-gray-500">Level</p>
                                <p class="text-2xl font-bold text-blue-600">{{ position_details.level }}</p>
                                <p class="text-xs text-gray-500">{{ position_details.professional_level_name || getLevelName(position_details.level) }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Position</p>
                                <p class="text-2xl font-bold text-green-600">{{ position_details.position }}</p>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Status</p>
                                <span :class="position_details.is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'"
                                      class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium">
                                    {{ position_details.is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500">Placed At</p>
                                <p class="text-base font-medium text-gray-900">{{ formatDate(position_details.placed_at) }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- No Matrix Position -->
                    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6" v-else>
                        <div class="flex items-center">
                            <svg class="h-5 w-5 text-yellow-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                            <p class="text-yellow-800 font-medium">This user does not have a matrix position yet.</p>
                        </div>
                    </div>

                    <!-- Downline Statistics - 7 Levels -->
                    <div class="bg-white shadow rounded-lg p-6" v-if="downline_counts">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Downline Statistics (7 Levels)</h3>
                        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-7 gap-4">
                            <div v-for="level in 7" :key="level" class="text-center p-4 rounded-lg" :class="getLevelBgClass(level)">
                                <p class="text-2xl font-bold text-gray-900">{{ downline_counts[`level_${level}`] || 0 }}</p>
                                <p class="text-xs font-medium text-gray-700">Level {{ level }}</p>
                                <p class="text-xs text-gray-500">{{ getLevelName(level) }}</p>
                            </div>
                        </div>
                        <div class="mt-4 pt-4 border-t border-gray-200">
                            <div class="flex justify-between items-center">
                                <span class="text-sm font-medium text-gray-700">Total Network Size:</span>
                                <span class="text-2xl font-bold text-blue-600">{{ downline_counts.total || 0 }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Matrix Structure Tree -->
                    <div class="bg-white shadow rounded-lg p-6" v-if="structure">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Network Tree</h3>
                        <div class="overflow-x-auto">
                            <div v-if="structure.user" class="space-y-4">
                                <!-- Root User -->
                                <div class="border-l-4 border-blue-500 pl-4 py-2 bg-blue-50">
                                    <p class="font-medium text-gray-900">{{ structure.user.name }}</p>
                                    <p class="text-sm text-gray-500">Level {{ structure.level }} - Position {{ structure.position }}</p>
                                    <p class="text-xs text-gray-400">{{ structure.user.email }}</p>
                                </div>
                                
                                <!-- Children -->
                                <div v-if="structure.children && structure.children.length > 0" class="ml-8 space-y-2">
                                    <p class="text-sm font-medium text-gray-700 mb-2">Direct Downline ({{ structure.children.length }})</p>
                                    <div v-for="(child, index) in structure.children" :key="index" class="border-l-2 border-gray-300 pl-4 py-2">
                                        <p class="font-medium text-gray-800">{{ child.user?.name || 'Unknown' }}</p>
                                        <p class="text-sm text-gray-500">Level {{ child.level }} - Position {{ child.position }}</p>
                                    </div>
                                </div>
                                
                                <div v-else class="ml-8 text-sm text-gray-500">
                                    No direct downline members yet
                                </div>
                            </div>
                            <p v-else class="text-sm text-gray-500">Network tree data not available</p>
                        </div>
                    </div>

                    <!-- Commission History -->
                    <div class="bg-white shadow rounded-lg p-6" v-if="commission_history && commission_history.data.length > 0">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Commission History</h3>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">From</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Level</th>
                                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Amount</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="commission in commission_history.data" :key="commission.id">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ formatDate(commission.created_at) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ commission.referee?.name || 'N/A' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            Level {{ commission.level }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 text-right">
                                            K{{ commission.amount }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span :class="getStatusClass(commission.status)"
                                                  class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium">
                                                {{ commission.status }}
                                            </span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import AdminLayout from '@/layouts/AdminLayout.vue'
import { router } from '@inertiajs/vue3'

const props = defineProps({
    user: Object,
    structure: [Object, Array],  // Can be either object or array
    downline_counts: Object,
    position_details: Object,
    commission_history: Object,
    performance_metrics: Object,
})

const formatDate = (date) => {
    return new Date(date).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    })
}

const getLevelName = (level) => {
    const names = {
        1: 'Direct Referrals',
        2: '2nd Generation',
        3: '3rd Generation',
        4: '4th Generation',
        5: '5th Generation',
        6: '6th Generation',
        7: '7th Generation',
    }
    return names[level] || 'Unknown'
}

const getLevelBgClass = (level) => {
    const classes = {
        1: 'bg-gray-100',
        2: 'bg-blue-100',
        3: 'bg-emerald-100',
        4: 'bg-purple-100',
        5: 'bg-indigo-100',
        6: 'bg-pink-100',
        7: 'bg-amber-100',
    }
    return classes[level] || 'bg-gray-100'
}

const getStatusClass = (status) => {
    const classes = {
        paid: 'bg-green-100 text-green-800',
        pending: 'bg-yellow-100 text-yellow-800',
        cancelled: 'bg-red-100 text-red-800',
    }
    return classes[status] || 'bg-gray-100 text-gray-800'
}
</script>
