<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { ChartBarIcon, UsersIcon, CurrencyDollarIcon, CheckCircleIcon, Cog6ToothIcon } from '@heroicons/vue/24/outline';

interface Stats {
    total_purchases: number;
    total_revenue: number;
    pending_purchases: number;
    active_members: number;
    completion_rate: number;
    avg_progress: number;
}

interface Purchase {
    id: number;
    invoice_number: string;
    user_name: string;
    user_email: string;
    amount: number;
    status: string;
    payment_method: string;
    purchased_at: string;
}

interface MonthlyRevenue {
    month: string;
    revenue: number;
    count: number;
}

interface ContentAccess {
    type: string;
    users: number;
    accesses: number;
}

defineProps<{
    stats: Stats;
    recentPurchases: Purchase[];
    monthlyRevenue: MonthlyRevenue[];
    contentAccessStats: ContentAccess[];
}>();

const formatCurrency = (amount: number) => `K${amount.toLocaleString()}`;

const getStatusColor = (status: string) => {
    const colors = {
        completed: 'bg-green-100 text-green-800',
        pending: 'bg-yellow-100 text-yellow-800',
        failed: 'bg-red-100 text-red-800',
        refunded: 'bg-gray-100 text-gray-800',
    };
    return colors[status as keyof typeof colors] || 'bg-gray-100 text-gray-800';
};

</script>

<template>
    <Head title="Starter Kit Admin Dashboard" />

    <AdminLayout>
        <div class="py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="mb-8 flex justify-between items-start">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">Starter Kit Management</h1>
                        <p class="mt-2 text-gray-600">Monitor purchases, member progress, and content engagement</p>
                    </div>
                    <Link
                        :href="route('admin.starter-kit.content.index')"
                        class="flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
                    >
                        <Cog6ToothIcon class="w-5 h-5 mr-2" />
                        Manage Content
                    </Link>
                </div>

                <!-- Stats Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-blue-100 rounded-lg p-3">
                                <ChartBarIcon class="h-6 w-6 text-blue-600" />
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-600">Total Purchases</p>
                                <p class="text-2xl font-bold text-gray-900">{{ stats.total_purchases }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-green-100 rounded-lg p-3">
                                <CurrencyDollarIcon class="h-6 w-6 text-green-600" />
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-600">Total Revenue</p>
                                <p class="text-2xl font-bold text-gray-900">{{ formatCurrency(stats.total_revenue) }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-purple-100 rounded-lg p-3">
                                <UsersIcon class="h-6 w-6 text-purple-600" />
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-600">Active Members</p>
                                <p class="text-2xl font-bold text-gray-900">{{ stats.active_members }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex items-center">
                            <div class="flex-shrink-0 bg-indigo-100 rounded-lg p-3">
                                <CheckCircleIcon class="h-6 w-6 text-indigo-600" />
                            </div>
                            <div class="ml-4">
                                <p class="text-sm font-medium text-gray-600">Completion Rate</p>
                                <p class="text-2xl font-bold text-gray-900">{{ stats.completion_rate }}%</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                    <Link
                        :href="route('admin.starter-kit.purchases')"
                        class="bg-white rounded-lg shadow p-6 hover:shadow-lg transition-shadow"
                    >
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Manage Purchases</h3>
                        <p class="text-gray-600 mb-4">View and manage all starter kit purchases</p>
                        <span class="text-blue-600 font-medium">View Purchases →</span>
                    </Link>

                    <Link
                        :href="route('admin.starter-kit.members')"
                        class="bg-white rounded-lg shadow p-6 hover:shadow-lg transition-shadow"
                    >
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Member Progress</h3>
                        <p class="text-gray-600 mb-4">Track member engagement and completion</p>
                        <span class="text-blue-600 font-medium">View Members →</span>
                    </Link>

                    <Link
                        :href="route('admin.starter-kit.analytics')"
                        class="bg-white rounded-lg shadow p-6 hover:shadow-lg transition-shadow"
                    >
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Analytics</h3>
                        <p class="text-gray-600 mb-4">Detailed insights and trends</p>
                        <span class="text-blue-600 font-medium">View Analytics →</span>
                    </Link>
                </div>

                <!-- Recent Purchases -->
                <div class="bg-white rounded-lg shadow mb-8">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-xl font-semibold text-gray-900">Recent Purchases</h2>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Invoice</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Member</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Amount</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Payment</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <tr v-for="purchase in recentPurchases" :key="purchase.id" class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        {{ purchase.invoice_number }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">{{ purchase.user_name }}</div>
                                        <div class="text-sm text-gray-500">{{ purchase.user_email }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ formatCurrency(purchase.amount) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ purchase.payment_method }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span :class="getStatusColor(purchase.status)" class="px-2 py-1 text-xs font-medium rounded-full">
                                            {{ purchase.status }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ purchase.purchased_at }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Content Engagement -->
                <div class="bg-white rounded-lg shadow">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-xl font-semibold text-gray-900">Content Engagement</h2>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div v-for="stat in contentAccessStats" :key="stat.type" class="border border-gray-200 rounded-lg p-4">
                                <h3 class="text-lg font-semibold text-gray-900 capitalize mb-2">{{ stat.type }}</h3>
                                <div class="space-y-2">
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Unique Users:</span>
                                        <span class="font-semibold">{{ stat.users }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-600">Total Accesses:</span>
                                        <span class="font-semibold">{{ stat.accesses }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
