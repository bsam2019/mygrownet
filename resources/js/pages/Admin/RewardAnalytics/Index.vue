<template>
    <AdminLayout title="Reward Analytics">
        <template #header>
            <h2 class="text-lg sm:text-xl font-semibold text-gray-800 leading-tight">
                Reward Analytics
            </h2>
        </template>

        <div class="py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="bg-white shadow rounded-lg p-6">
                    <h1 class="text-2xl font-bold text-gray-900 mb-4">Reward Analytics</h1>
                    <p class="text-gray-600 mb-6">Comprehensive analytics for the VBIF reward system</p>
                    
                    <!-- Debug Info (remove in production) -->
                    <div class="bg-gray-100 p-4 rounded mb-4 text-xs">
                        <strong>Debug:</strong> 
                        Analytics: {{ Object.keys(analytics).length }} keys, 
                        Summary: {{ Object.keys(summary).length }} keys,
                        Stats: {{ stats ? Object.keys(stats).length : 'null' }} keys
                    </div>
                    
                    <!-- Summary Cards -->
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
                        <div class="bg-blue-50 p-4 rounded-lg">
                            <h3 class="text-sm font-medium text-blue-900">Total Users</h3>
                            <p class="text-2xl font-bold text-blue-600">{{ summary?.total_users || 0 }}</p>
                        </div>
                        <div class="bg-green-50 p-4 rounded-lg">
                            <h3 class="text-sm font-medium text-green-900">Active Referrers</h3>
                            <p class="text-2xl font-bold text-green-600">{{ summary?.active_referrers || 0 }}</p>
                        </div>
                        <div class="bg-purple-50 p-4 rounded-lg">
                            <h3 class="text-sm font-medium text-purple-900">Commissions Paid</h3>
                            <p class="text-2xl font-bold text-purple-600">{{ formatCurrency(summary?.total_commissions_paid || 0) }}</p>
                        </div>
                        <div class="bg-yellow-50 p-4 rounded-lg">
                            <h3 class="text-sm font-medium text-yellow-900">System Health</h3>
                            <p class="text-2xl font-bold text-yellow-600">{{ summary?.system_health?.health_score || 0 }}%</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>

<script setup>
import { computed } from 'vue'
import { Head, Link } from '@inertiajs/vue3'
import AdminLayout from '@/layouts/AdminLayout.vue'
import { formatCurrency } from '@/utils/formatting'

const props = defineProps({
    analytics: {
        type: Object,
        default: () => ({})
    },
    summary: {
        type: Object,
        default: () => ({})
    },
    period: {
        type: String,
        default: 'month'
    },
    stats: {
        type: Object,
        default: () => ({
            user_growth: 0,
            investment_growth: 0,
            average_roi: 0
        })
    }
})


</script>