<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';

defineProps<{
    purchaseTrends: Array<{ date: string; count: number }>;
    revenueTrends: Array<{ date: string; revenue: number }>;
    contentEngagement: Array<{ content_type: string; content_id: string; views: number; avg_time: number }>;
    achievementStats: Array<{ achievement_type: string; count: number }>;
    paymentMethods: Array<{ payment_method: string; count: number; revenue: number }>;
}>();

const formatCurrency = (amount: number) => `K${amount.toLocaleString()}`;
</script>

<template>
    <Head title="Starter Kit Analytics" />

    <AdminLayout>
        <div class="py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <!-- Header -->
                <div class="mb-8">
                    <Link :href="route('admin.starter-kit.dashboard')" class="text-blue-600 hover:text-blue-800 mb-2 inline-block">
                        ← Back to Dashboard
                    </Link>
                    <h1 class="text-3xl font-bold text-gray-900">Starter Kit Analytics</h1>
                </div>

                <!-- Payment Methods -->
                <div class="bg-white rounded-lg shadow p-6 mb-6">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">Payment Methods</h2>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div v-for="method in paymentMethods" :key="method.payment_method" class="border border-gray-200 rounded-lg p-4">
                            <h3 class="text-lg font-semibold text-gray-900 capitalize mb-2">{{ method.payment_method }}</h3>
                            <div class="space-y-2">
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Transactions:</span>
                                    <span class="font-semibold">{{ method.count }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-600">Revenue:</span>
                                    <span class="font-semibold">{{ formatCurrency(method.revenue) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Purchase Trends -->
                <div class="bg-white rounded-lg shadow p-6 mb-6">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">Purchase Trends (Last 30 Days)</h2>
                    <div class="overflow-x-auto">
                        <table class="min-w-full">
                            <thead>
                                <tr class="border-b">
                                    <th class="text-left py-2 px-4">Date</th>
                                    <th class="text-right py-2 px-4">Purchases</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="trend in purchaseTrends" :key="trend.date" class="border-b hover:bg-gray-50">
                                    <td class="py-2 px-4">{{ trend.date }}</td>
                                    <td class="text-right py-2 px-4 font-semibold">{{ trend.count }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Revenue Trends -->
                <div class="bg-white rounded-lg shadow p-6 mb-6">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">Revenue Trends (Last 30 Days)</h2>
                    <div class="overflow-x-auto">
                        <table class="min-w-full">
                            <thead>
                                <tr class="border-b">
                                    <th class="text-left py-2 px-4">Date</th>
                                    <th class="text-right py-2 px-4">Revenue</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="trend in revenueTrends" :key="trend.date" class="border-b hover:bg-gray-50">
                                    <td class="py-2 px-4">{{ trend.date }}</td>
                                    <td class="text-right py-2 px-4 font-semibold">{{ formatCurrency(trend.revenue) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Content Engagement -->
                <div class="bg-white rounded-lg shadow p-6 mb-6">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">Top Content (By Views)</h2>
                    <div class="overflow-x-auto">
                        <table class="min-w-full">
                            <thead>
                                <tr class="border-b">
                                    <th class="text-left py-2 px-4">Type</th>
                                    <th class="text-left py-2 px-4">Content ID</th>
                                    <th class="text-right py-2 px-4">Views</th>
                                    <th class="text-right py-2 px-4">Avg Time (min)</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="content in contentEngagement" :key="`${content.content_type}-${content.content_id}`" class="border-b hover:bg-gray-50">
                                    <td class="py-2 px-4 capitalize">{{ content.content_type }}</td>
                                    <td class="py-2 px-4">{{ content.content_id }}</td>
                                    <td class="text-right py-2 px-4 font-semibold">{{ content.views }}</td>
                                    <td class="text-right py-2 px-4">{{ Math.round(content.avg_time) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Achievement Stats -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">Achievement Distribution</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <div v-for="achievement in achievementStats" :key="achievement.achievement_type" class="border border-gray-200 rounded-lg p-4">
                            <h3 class="text-sm font-medium text-gray-600 mb-1">{{ achievement.achievement_type }}</h3>
                            <p class="text-2xl font-bold text-gray-900">{{ achievement.count }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
