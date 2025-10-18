<template>
    <AdminLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Investment Reports & Analytics</h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Report Filters -->
                <div class="bg-white rounded-lg shadow p-6 mb-6">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Date Range</label>
                            <select v-model="filters.dateRange" class="mt-1 block w-full rounded-md border-gray-300">
                                <option value="today">Today</option>
                                <option value="week">This Week</option>
                                <option value="month">This Month</option>
                                <option value="quarter">This Quarter</option>
                                <option value="year">This Year</option>
                                <option value="custom">Custom Range</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Category</label>
                            <select v-model="filters.category" class="mt-1 block w-full rounded-md border-gray-300">
                                <option value="">All Categories</option>
                                <option v-for="category in categories" :key="category.id" :value="category.id">
                                    {{ category.name }}
                                </option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Status</label>
                            <select v-model="filters.status" class="mt-1 block w-full rounded-md border-gray-300">
                                <option value="">All Status</option>
                                <option value="active">Active</option>
                                <option value="completed">Completed</option>
                                <option value="pending">Pending</option>
                            </select>
                        </div>
                        <div class="flex items-end">
                            <button @click="generateReport" class="bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600">
                                Generate Report
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Performance Overview -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                    <div class="bg-white rounded-lg shadow p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Total Investments</h3>
                        <div class="text-3xl font-bold text-gray-900">{{ formatCurrency(statistics.total_invested) }}</div>
                        <div class="mt-2 flex items-center text-sm">
                            <span :class="statistics.growth >= 0 ? 'text-green-600' : 'text-red-600'">
                                {{ statistics.growth >= 0 ? '+' : '' }}{{ statistics.growth }}%
                            </span>
                            <span class="text-gray-500 ml-2">vs previous period</span>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg shadow p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Average Investment</h3>
                        <div class="text-3xl font-bold text-gray-900">{{ formatCurrency(statistics.average_investment) }}</div>
                        <div class="mt-2 text-sm text-gray-500">
                            Based on {{ statistics.total_count }} investments
                        </div>
                    </div>

                    <div class="bg-white rounded-lg shadow p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Success Rate</h3>
                        <div class="text-3xl font-bold text-gray-900">{{ statistics.success_rate }}%</div>
                        <div class="mt-2 text-sm text-gray-500">
                            Completed investments
                        </div>
                    </div>
                </div>

                <!-- Charts -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div class="bg-white rounded-lg shadow p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Investment Trends</h3>
                        <canvas ref="trendChart"></canvas>
                    </div>
                    <div class="bg-white rounded-lg shadow p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Category Distribution</h3>
                        <canvas ref="categoryChart"></canvas>
                    </div>
                </div>

                <!-- Detailed Statistics -->
                <div class="bg-white rounded-lg shadow overflow-hidden">
                    <div class="p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Detailed Statistics</h3>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Category</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total Investments</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Average Amount</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Success Rate</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Growth</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="stat in categoryStats" :key="stat.category">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            {{ stat.category }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ stat.total_investments }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ formatCurrency(stat.average_amount) }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ stat.success_rate }}%
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <span :class="stat.growth >= 0 ? 'text-green-600' : 'text-red-600'">
                                                {{ stat.growth >= 0 ? '+' : '' }}{{ stat.growth }}%
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
import { ref, onMounted, watch } from 'vue';
import AdminLayout from '@/layouts/AdminLayout.vue';
import Chart from 'chart.js/auto';

const props = defineProps({
    categories: Array,
    statistics: Object,
    categoryStats: Array,
    chartData: Object
});

const filters = ref({
    dateRange: 'month',
    category: '',
    status: ''
});

const trendChart = ref(null);
const categoryChart = ref(null);

const formatCurrency = (value) => {
    return new Intl.NumberFormat('en-ZM', {
        style: 'currency',
        currency: 'ZMW'
    }).format(value);
};

const initCharts = () => {
    if (trendChart.value && props.chartData.trends) {
        new Chart(trendChart.value, {
            type: 'line',
            data: props.chartData.trends,
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    }

    if (categoryChart.value && props.chartData.categories) {
        new Chart(categoryChart.value, {
            type: 'doughnut',
            data: props.chartData.categories,
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    }
};

const generateReport = () => {
    router.get(route('admin.investments.reports'), filters.value, {
        preserveState: true,
        preserveScroll: true
    });
};

watch(filters, () => {
    generateReport();
}, { deep: true });

onMounted(() => {
    initCharts();
});
</script>
