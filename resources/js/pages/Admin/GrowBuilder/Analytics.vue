<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';
import { BarChart3, Globe, FileText, Users, ThumbsUp, ThumbsDown, TrendingUp } from 'lucide-vue-next';
import { Line } from 'vue-chartjs';
import { Chart as ChartJS, CategoryScale, LinearScale, PointElement, LineElement, Title, Tooltip, Legend, Filler } from 'chart.js';

ChartJS.register(CategoryScale, LinearScale, PointElement, LineElement, Title, Tooltip, Legend, Filler);

interface Props {
    sitesOverTime: { date: string; count: number }[];
    topSites: { id: number; name: string; subdomain: string; status: string; pages_count: number }[];
    aiStats: { total_requests: number; positive_feedback: number; negative_feedback: number };
    stats: { total_sites: number; active_sites: number; total_pages: number; total_users: number };
}

const props = defineProps<Props>();

const chartData = {
    labels: props.sitesOverTime.map(d => new Date(d.date).toLocaleDateString('en-US', { month: 'short', day: 'numeric' })),
    datasets: [{
        label: 'Sites Created',
        data: props.sitesOverTime.map(d => d.count),
        borderColor: '#3b82f6',
        backgroundColor: 'rgba(59, 130, 246, 0.1)',
        fill: true,
        tension: 0.4,
    }]
};

const chartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: { legend: { display: false } },
    scales: {
        y: { beginAtZero: true, ticks: { stepSize: 1 } },
        x: { grid: { display: false } }
    }
};

const aiSatisfactionRate = props.aiStats.total_requests > 0
    ? Math.round((props.aiStats.positive_feedback / (props.aiStats.positive_feedback + props.aiStats.negative_feedback || 1)) * 100)
    : 0;
</script>

<template>
    <AdminLayout>
        <Head title="GrowBuilder - Analytics" />

        <div class="p-6">
            <div class="mb-6">
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
                    <BarChart3 class="h-7 w-7 text-blue-600" aria-hidden="true" />
                    GrowBuilder Analytics
                </h1>
                <p class="text-gray-600 dark:text-gray-400 mt-1">Platform-wide GrowBuilder statistics and insights</p>
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-white dark:bg-gray-800 rounded-lg p-4 border border-gray-200 dark:border-gray-700">
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-blue-100 dark:bg-blue-900/30 rounded-lg">
                            <Globe class="h-5 w-5 text-blue-600" aria-hidden="true" />
                        </div>
                        <div>
                            <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ stats.total_sites }}</div>
                            <div class="text-sm text-gray-500">Total Sites</div>
                        </div>
                    </div>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-lg p-4 border border-gray-200 dark:border-gray-700">
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-green-100 dark:bg-green-900/30 rounded-lg">
                            <TrendingUp class="h-5 w-5 text-green-600" aria-hidden="true" />
                        </div>
                        <div>
                            <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ stats.active_sites }}</div>
                            <div class="text-sm text-gray-500">Published</div>
                        </div>
                    </div>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-lg p-4 border border-gray-200 dark:border-gray-700">
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-purple-100 dark:bg-purple-900/30 rounded-lg">
                            <FileText class="h-5 w-5 text-purple-600" aria-hidden="true" />
                        </div>
                        <div>
                            <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ stats.total_pages }}</div>
                            <div class="text-sm text-gray-500">Total Pages</div>
                        </div>
                    </div>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-lg p-4 border border-gray-200 dark:border-gray-700">
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-amber-100 dark:bg-amber-900/30 rounded-lg">
                            <Users class="h-5 w-5 text-amber-600" aria-hidden="true" />
                        </div>
                        <div>
                            <div class="text-2xl font-bold text-gray-900 dark:text-white">{{ stats.total_users }}</div>
                            <div class="text-sm text-gray-500">Site Users</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid md:grid-cols-2 gap-6">
                <!-- Sites Over Time Chart -->
                <div class="bg-white dark:bg-gray-800 rounded-lg p-4 border border-gray-200 dark:border-gray-700">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Sites Created (Last 30 Days)</h2>
                    <div class="h-64">
                        <Line :data="chartData" :options="chartOptions" />
                    </div>
                </div>

                <!-- AI Usage Stats -->
                <div class="bg-white dark:bg-gray-800 rounded-lg p-4 border border-gray-200 dark:border-gray-700">
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">AI Assistant Usage</h2>
                    <div class="space-y-4">
                        <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                            <span class="text-gray-600 dark:text-gray-400">Total AI Requests</span>
                            <span class="text-xl font-bold text-gray-900 dark:text-white">{{ aiStats.total_requests }}</span>
                        </div>
                        <div class="flex items-center justify-between p-3 bg-green-50 dark:bg-green-900/20 rounded-lg">
                            <div class="flex items-center gap-2">
                                <ThumbsUp class="h-4 w-4 text-green-600" aria-hidden="true" />
                                <span class="text-gray-600 dark:text-gray-400">Positive Feedback</span>
                            </div>
                            <span class="text-xl font-bold text-green-600">{{ aiStats.positive_feedback }}</span>
                        </div>
                        <div class="flex items-center justify-between p-3 bg-red-50 dark:bg-red-900/20 rounded-lg">
                            <div class="flex items-center gap-2">
                                <ThumbsDown class="h-4 w-4 text-red-600" aria-hidden="true" />
                                <span class="text-gray-600 dark:text-gray-400">Negative Feedback</span>
                            </div>
                            <span class="text-xl font-bold text-red-600">{{ aiStats.negative_feedback }}</span>
                        </div>
                        <div class="flex items-center justify-between p-3 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                            <span class="text-gray-600 dark:text-gray-400">Satisfaction Rate</span>
                            <span class="text-xl font-bold text-blue-600">{{ aiSatisfactionRate }}%</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Top Sites -->
            <div class="mt-6 bg-white dark:bg-gray-800 rounded-lg p-4 border border-gray-200 dark:border-gray-700">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Top Sites by Pages</h2>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="text-left text-xs font-medium text-gray-500 uppercase">
                                <th class="pb-3">Site</th>
                                <th class="pb-3">Status</th>
                                <th class="pb-3 text-right">Pages</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                            <tr v-for="site in topSites" :key="site.id">
                                <td class="py-3">
                                    <div class="font-medium text-gray-900 dark:text-white">{{ site.name }}</div>
                                    <div class="text-sm text-gray-500">{{ site.subdomain }}.mygrownet.com</div>
                                </td>
                                <td class="py-3">
                                    <span :class="[
                                        'px-2 py-1 text-xs font-medium rounded-full',
                                        site.status === 'published' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'
                                    ]">{{ site.status }}</span>
                                </td>
                                <td class="py-3 text-right font-medium text-gray-900 dark:text-white">{{ site.pages_count }}</td>
                            </tr>
                            <tr v-if="topSites.length === 0">
                                <td colspan="3" class="py-4 text-center text-gray-500">No sites yet</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
