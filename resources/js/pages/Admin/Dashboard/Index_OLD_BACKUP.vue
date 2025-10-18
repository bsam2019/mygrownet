<template>
    <AdminLayout title="Dashboard">
        <template #header>
            <h2 class="text-lg sm:text-xl font-semibold text-gray-800 leading-tight">
                Dashboard
            </h2>
        </template>

        <!-- Loading State -->
        <div v-if="isLoading" class="flex items-center justify-center min-h-[400px]">
            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-500"></div>
        </div>

        <!-- Error State -->
        <div v-else-if="error" class="bg-red-50 p-4 rounded-lg">
            <div class="flex">
                <div class="flex-shrink-0">
                    <ExclamationCircleIcon class="h-5 w-5 text-red-400" />
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-red-800">Error loading dashboard</h3>
                    <div class="mt-2 text-sm text-red-700">
                        {{ error }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Dashboard Content -->
        <div v-else class="py-2 sm:py-4 lg:py-6">
            <div class="mx-auto max-w-7xl px-2 sm:px-4 lg:px-6">
                <div class="space-y-3 sm:space-y-4 lg:space-y-6">
                    <!-- Investment Metrics -->
                    <div class="bg-white p-2 sm:p-4 lg:p-6 rounded-lg shadow-sm">
                        <InvestmentMetrics :metrics="investmentMetrics" />
                    </div>

                    <!-- Investment Trends -->
                    <div class="bg-white p-2 sm:p-4 lg:p-6 rounded-lg shadow-sm overflow-x-auto">
                        <InvestmentTrends
                            :initial-data="props.trendData"
                            @update:period="handlePeriodChange"
                        />
                    </div>

                    <!-- Summary Cards -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-2 sm:gap-3 lg:gap-4">
                        <SummaryCard
                            title="Total Investments"
                            :value="formatCurrency(summary.total_value)"
                            icon="currency-dollar"
                            :trend="summary.revenue_growth >= 0 ? 'up' : 'down'"
                            :change="summary.revenue_growth"
                        />
                        <SummaryCard
                            title="Active Users"
                            :value="summary.total_count"
                            icon="users"
                            :trend="stats.user_growth >= 0 ? 'up' : 'down'"
                            :change="stats.user_growth"
                        />
                        <SummaryCard
                            title="Active Investments"
                            :value="summary.active_count"
                            icon="chart-bar"
                            :trend="stats.investment_growth >= 0 ? 'up' : 'down'"
                            :change="stats.investment_growth"
                        />
                        <SummaryCard
                            title="Platform Revenue"
                            :value="formatCurrency(summary.revenue)"
                            icon="banknotes"
                            :trend="summary.revenue_growth >= 0 ? 'up' : 'down'"
                            :change="summary.revenue_growth"
                        />
                    </div>

                    <!-- Stats Grid -->
                    <div class="grid gap-2 sm:gap-4 lg:gap-6">
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-2 sm:gap-4 lg:gap-6">
                            <!-- Category Distribution -->
                            <div class="bg-white p-2 sm:p-4 lg:p-6 rounded-lg shadow-sm">
                                <h3 class="text-sm sm:text-base lg:text-lg font-semibold mb-3 sm:mb-4">Investment Categories</h3>
                                <div class="hidden sm:block">
                                    <div class="space-y-2 sm:space-y-3">
                                        <div v-for="category in categoryDistribution" :key="category.id" class="flex items-center justify-between">
                                            <div class="flex items-center space-x-2">
                                                <div class="h-3 w-3 rounded-full" :style="{ backgroundColor: category.color }"></div>
                                                <span class="text-sm">{{ category.name }}</span>
                                            </div>
                                            <div class="text-right">
                                                <div class="text-sm text-gray-600">{{ formatCurrency(category.total_value) }}</div>
                                                <div class="text-xs text-gray-500">{{ category.percentage }}%</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <!-- Mobile view -->
                                <div class="sm:hidden">
                                    <div class="grid gap-3">
                                        <div v-for="category in categoryDistribution" :key="category.id"
                                            class="p-3 border rounded-lg bg-gray-50"
                                        >
                                            <div class="flex items-center space-x-2 mb-2">
                                                <div class="h-3 w-3 rounded-full" :style="{ backgroundColor: category.color }"></div>
                                                <span class="text-sm font-medium">{{ category.name }}</span>
                                            </div>
                                            <div class="flex justify-between items-center">
                                                <div class="text-sm text-gray-600">{{ formatCurrency(category.total_value) }}</div>
                                                <div class="text-xs font-medium bg-white px-2 py-1 rounded-full">{{ category.percentage }}%</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Performance Stats -->
                            <div class="bg-white p-2 sm:p-4 lg:p-6 rounded-lg shadow-sm">
                                <h3 class="text-sm sm:text-base lg:text-lg font-semibold mb-3 sm:mb-4">Performance Metrics</h3>
                                <!-- Desktop view -->
                                <div class="hidden sm:grid grid-rows-2 gap-4">
                                    <div class="grid grid-cols-2 gap-4">
                                        <StatCard
                                            title="Average ROI"
                                            :value="props.stats.average_roi"
                                            type="percentage"
                                            :change="props.stats.investment_growth"
                                            :show-progress="true"
                                            :progress="props.stats.roi_progress"
                                            description="Target: 15%"
                                        />
                                        <StatCard
                                            title="Success Rate"
                                            :value="props.stats.success_rate"
                                            type="percentage"
                                            :show-progress="true"
                                            :progress="props.stats.success_rate"
                                        />
                                    </div>
                                    <div class="grid grid-cols-2 gap-4">
                                        <StatCard
                                            title="Risk Score"
                                            :value="props.stats.risk_score"
                                            type="number"
                                            :show-progress="true"
                                            :progress="props.stats.risk_progress"
                                            description="Lower is better"
                                            :threshold="{ warning: 60, danger: 40 }"
                                        />
                                        <StatCard
                                            title="Active Investments"
                                            :value="props.stats.completed_investments"
                                            type="number"
                                            :change="props.stats.investment_growth"
                                        />
                                    </div>
                                </div>
                                <!-- Mobile view -->
                                <div class="sm:hidden grid grid-cols-1 gap-3">
                                    <StatCard
                                        title="Average ROI"
                                        :value="props.stats.average_roi"
                                        type="percentage"
                                        :change="props.stats.investment_growth"
                                        :show-progress="true"
                                        :progress="props.stats.roi_progress"
                                        description="Target: 15%"
                                    />
                                    <StatCard
                                        title="Success Rate"
                                        :value="props.stats.success_rate"
                                        type="percentage"
                                        :show-progress="true"
                                        :progress="props.stats.success_rate"
                                    />
                                    <StatCard
                                        title="Risk Score"
                                        :value="props.stats.risk_score"
                                        type="number"
                                        :show-progress="true"
                                        :progress="props.stats.risk_progress"
                                        description="Lower is better"
                                        :threshold="{ warning: 60, danger: 40 }"
                                    />
                                    <StatCard
                                        title="Active Investments"
                                        :value="props.stats.completed_investments"
                                        type="number"
                                        :change="props.stats.investment_growth"
                                    />
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Alerts Section -->
                    <div class="bg-white p-4 rounded-lg shadow-sm">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-base font-semibold text-gray-900">Alerts & Notifications</h3>
                            <Link v-if="hasUnreadAlerts" href="#" class="text-xs text-blue-600 hover:text-blue-800">
                                Mark all as read
                            </Link>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                            <AlertCard
                                title="Pending Approvals"
                                :value="props.alerts.pending_approvals"
                                type="warning"
                                :subtext="props.alerts.pending_approvals === 1 ? '1 investment needs review' : `${props.alerts.pending_approvals} investments need review`"
                                :link="route('admin.investments.index', { status: 'pending' })"
                            />
                            <AlertCard
                                title="Pending Withdrawals"
                                :value="props.alerts.pending_withdrawals"
                                :subtext="formatCurrency(props.alerts.withdrawal_amount)"
                                type="info"
                                :link="route('admin.withdrawals.pending')"
                            />
                            <template v-if="props.alerts.system_alerts.length">
                                <AlertCard
                                    v-for="(alert, index) in props.alerts.system_alerts"
                                    :key="index"
                                    :title="alert.title"
                                    :value="alert.severity"
                                    type="error"
                                    :subtext="alert.message"
                                />
                            </template>
                            <div v-else-if="props.alerts.pending_approvals === 0 && props.alerts.pending_withdrawals === 0"
                                class="col-span-full p-4 bg-green-50 rounded-lg">
                                <div class="flex items-center justify-center text-green-800">
                                    <CheckCircleIcon class="w-5 h-5 mr-2" />
                                    <span class="text-sm">All clear! No pending actions required.</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Employee Management Section -->
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 lg:gap-6">
                        <EmployeeManagementWidget
                            :stats="employeeManagement.stats"
                            :recent-activities="employeeManagement.recentActivities"
                            :department-overview="employeeManagement.departmentOverview"
                            @view-all-employees="handleViewAllEmployees"
                            @add-employee="handleAddEmployee"
                            @view-reports="handleViewReports"
                        />
                        
                        <!-- Performance Dashboard Widget -->
                        <PerformanceDashboard
                            :stats="employeeManagement.performanceStats"
                            @view-analytics="handleViewAnalytics"
                            @manage-goals="handleManageGoals"
                        />
                    </div>

                    <!-- Reports Section -->
                    <div class="bg-white p-4 rounded-lg shadow-sm">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
                            <h3 class="text-base font-semibold">Reports Overview</h3>
                            <div class="flex items-center space-x-2">
                                <label class="text-sm text-gray-600">Period:</label>
                                <select
                                    v-model="reportPeriod"
                                    @change="handleReportPeriodChange(reportPeriod)"
                                    class="text-sm border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
                                >
                                    <option value="week">Last Week</option>
                                    <option value="month">Last Month</option>
                                    <option value="quarter">Last Quarter</option>
                                    <option value="year">Last Year</option>
                                </select>
                            </div>
                        </div>

                        <!-- Period Summary -->
                        <TransitionGroup
                            tag="div"
                            class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6"
                            enter-active-class="transition duration-200 ease-out"
                            enter-from-class="opacity-0 translate-y-2"
                            enter-to-class="opacity-100 translate-y-0"
                            leave-active-class="transition duration-150 ease-in"
                        >
                            <div
                                v-for="(card, index) in summaryCards"
                                :key="card.key"
                                :class="[
                                    'p-4 rounded-lg transition-all duration-200 hover:shadow-md',
                                    card.bgClass,
                                    { 'animate-pulse': isLoading }
                                ]"
                            >
                                <div :class="['text-xs font-medium', card.textClass]">{{ card.title }}</div>
                                <div :class="['mt-1.5 text-lg font-semibold', card.valueClass]">
                                    {{ isLoading ? '-' : card.getValue() }}
                                </div>
                            </div>
                        </TransitionGroup>

                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                            <ReportsCard
                                title="Total Investments"
                                :value="summaryData.total_investments"
                                :trend="summaryData.investment_trend"
                                icon="chart-bar"
                                bgColor="bg-blue-600"
                                format="currency"
                            />
                            <ReportsCard
                                title="Active Investors"
                                :value="summaryData.active_investors"
                                :trend="summaryData.investor_trend"
                                icon="users"
                                bgColor="bg-green-600"
                                format="number"
                            />
                            <ReportsCard
                                title="Average ROI"
                                :value="summaryData.average_roi"
                                :trend="summaryData.roi_trend"
                                icon="chart-pie"
                                bgColor="bg-purple-600"
                                format="percentage"
                            />
                            <ReportsCard
                                title="Pending Withdrawals"
                                :value="summaryData.pending_withdrawals"
                                :trend="summaryData.withdrawal_trend"
                                icon="currency-dollar"
                                bgColor="bg-yellow-600"
                                format="currency"
                            />
                        </div>

                        <!-- Analytics Charts -->
                        <div class="mt-6">
                            <ReportsAnalytics :chartData="props.reportData" />
                        </div>
                    </div>

                    <!-- Employee Management Section -->
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 lg:gap-6">
                        <EmployeeManagementWidget
                            :stats="employeeManagement.stats"
                            :recent-activities="employeeManagement.recentActivities"
                            :department-overview="employeeManagement.departmentOverview"
                            @view-all-employees="handleViewAllEmployees"
                            @add-employee="handleAddEmployee"
                            @view-reports="handleViewReports"
                        />
                        
                        <!-- Performance Dashboard Widget -->
                        <PerformanceDashboard
                            :stats="employeeManagement.performanceStats"
                            @view-analytics="handleViewAnalytics"
                            @manage-goals="handleManageGoals"
                        />
                    </div>

                    <!-- Recent Activity -->
                    <div class="bg-white rounded-lg shadow-sm">
                        <div class="p-2 sm:p-4 lg:p-6">
                            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4">
                                <h3 class="text-sm sm:text-base lg:text-lg font-semibold">Recent Activity</h3>
                                <div class="flex items-center space-x-3">
                                    <select v-model="activityFilter" class="text-sm border-gray-300 rounded-md">
                                        <option value="all">All Activities</option>
                                        <option value="investments">Investments</option>
                                        <option value="withdrawals">Withdrawals</option>
                                        <option value="users">User Activity</option>
                                    </select>
                                    <button
                                        @click="refreshActivity"
                                        class="p-1.5 text-gray-500 hover:text-gray-700 rounded-full hover:bg-gray-100"
                                        :class="{ 'animate-spin': isRefreshing }"
                                    >
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                            <div class="relative overflow-hidden">
                                <div class="overflow-x-auto">
                                    <div class="inline-block min-w-full align-middle">
                                        <div class="overflow-hidden ring-1 ring-black ring-opacity-5 sm:rounded-lg">
                                            <table class="min-w-full divide-y divide-gray-200">
                                                <thead class="bg-gray-50">
                                                    <tr>
                                                        <th scope="col" class="py-2 px-2 sm:px-4 lg:px-6 sm:py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                                                        <th scope="col" class="py-2 px-2 sm:px-4 lg:px-6 sm:py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                                                        <th scope="col" class="py-2 px-2 sm:px-4 lg:px-6 sm:py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Details</th>
                                                        <th scope="col" class="py-2 px-2 sm:px-4 lg:px-6 sm:py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                                                        <th scope="col" class="py-2 px-2 sm:px-4 lg:px-6 sm:py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                                    </tr>
                                                </thead>
                                                <tbody class="bg-white divide-y divide-gray-200">
                                                    <tr v-for="activity in filteredActivities" :key="activity.id" class="hover:bg-gray-50">
                                                        <td class="py-2 px-2 sm:px-4 lg:px-6 sm:py-4 whitespace-nowrap">
                                                            <div class="text-xs sm:text-sm font-medium text-gray-900">
                                                                {{ activity.user }}
                                                            </div>
                                                        </td>
                                                        <td class="py-2 px-2 sm:px-4 lg:px-6 sm:py-4 whitespace-nowrap">
                                                            <div class="text-xs sm:text-sm text-gray-900">
                                                                {{ formatActivityAction(activity.action) }}
                                                            </div>
                                                        </td>
                                                        <td class="py-2 px-2 sm:px-4 lg:px-6 sm:py-4">
                                                            <div class="text-xs sm:text-sm text-gray-900">
                                                                {{ activity.description }}
                                                            </div>
                                                        </td>
                                                        <td class="py-2 px-2 sm:px-4 lg:px-6 sm:py-4 whitespace-nowrap">
                                                            <div class="text-xs sm:text-sm text-gray-500">
                                                                {{ formatDate(activity.created_at) }}
                                                            </div>
                                                            <div class="text-xs text-gray-400">
                                                                {{ formatTime(activity.created_at) }}
                                                            </div>
                                                        </td>
                                                        <td class="py-2 px-2 sm:px-4 lg:px-6 sm:py-4 whitespace-nowrap">
                                                            <StatusBadge
                                                                v-if="activity.loggable?.status"
                                                                :status="activity.loggable.status"
                                                            />
                                                            <span v-else class="text-xs text-gray-500">-</span>
                                                        </td>
                                                    </tr>
                                                    <tr v-if="!filteredActivities?.length">
                                                        <td colspan="5" class="py-4 text-center text-sm text-gray-500">
                                                            No activities found
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
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

<script setup>
import { ref, computed, onMounted } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import {
    ChartBarIcon,
    UsersIcon,
    CurrencyDollarIcon,
    ChartPieIcon,
    CheckCircleIcon,
    ExclamationCircleIcon
} from '@heroicons/vue/24/outline';
import { formatCurrency, formatDate, formatPercentage, formatAmount, formatTime } from '@/utils/formatting';
import { route } from 'ziggy-js';
import AdminLayout from '@/layouts/AdminLayout.vue';
import AlertCard from '@/components/Admin/Dashboard/AlertCard.vue';
import InvestmentMetrics from '@/components/Admin/Dashboard/InvestmentMetrics.vue';
import InvestmentTrends from '@/components/Admin/Dashboard/InvestmentTrends.vue';
import ReportsAnalytics from '@/components/Admin/Dashboard/ReportsAnalytics.vue';
import ReportsCard from '@/components/Admin/Dashboard/ReportsCard.vue';
import StatCard from '@/components/Admin/Dashboard/StatCard.vue';
import EmployeeManagementWidget from '@/components/Employee/EmployeeManagementWidget.vue';
import PerformanceDashboard from '@/components/Employee/PerformanceDashboard.vue';
import StatusBadge from '@/components/StatusBadge.vue';
import SummaryCard from '@/components/Admin/Dashboard/SummaryCard.vue';

const props = defineProps({
    investmentMetrics: {
        type: Object,
        default: () => ({
            totalValue: 0,
            valueChange: 0,
            activeInvestors: 0,
            investorChange: 0,
            averageRoi: 0,
            roiChange: 0
        })
    },
    trendData: {
        type: Object,
        default: () => ({
            period: 'month',
            labels: [],
            amounts: [],
            counts: [],
            totals: { amount: 0, count: 0 },
            averages: { amount: 0 },
            rates: { success: 0 },
            growth: { amount: 0, count: 0, average: 0 }
        })
    },
    summary: {
        type: Object,
        default: () => ({
            total_value: 0,
            total_count: 0,
            active_count: 0,
            revenue: 0,
            revenue_growth: 0
        })
    },
    stats: {
        type: Object,
        default: () => ({
            user_growth: 0,
            investment_growth: 0,
            average_roi: 0,
            success_rate: 0,
            risk_score: 0,
            roi_progress: 0,
            risk_progress: 0,
            completed_investments: 0,
            new_users: 0
        })
    },
    categoryDistribution: {
        type: Array,
        default: () => []
    },
    alerts: {
        type: Object,
        default: () => ({
            pending_approvals: 0,
            pending_withdrawals: 0,
            withdrawal_amount: 0,
            system_alerts: []
        })
    },
    recentInvestments: {
        type: Array,
        default: () => []
    },
    reportData: {
        type: Object,
        default: () => ({
            investments: [],
            users: [],
            returns: [],
            period_stats: {
                success_rate: 0
            }
        })
    },
    employeeManagement: {
        type: Object,
        default: () => ({
            stats: {
                totalEmployees: 0,
                activeEmployees: 0,
                newHires: 0,
                departments: 0
            },
            recentActivities: [],
            departmentOverview: [],
            performanceStats: {
                averageScore: 0,
                topPerformers: 0,
                goalAchievementRate: 0,
                totalCommissions: 0
            }
        })
    }
});

const reportPeriod = ref(new URL(window.location.href).searchParams.get('period') || 'month');
const isLoading = ref(true);
const error = ref(null);
const activityFilter = ref('all');
const isRefreshing = ref(false);

const handlePeriodChange = (period) => {
    router.get(
        route('admin.dashboard'),
        { period },
        {
            preserveState: true,
            preserveScroll: true,
            only: ['trendData'],
            onSuccess: () => {
                // The chart will update automatically via watchers
            }
        }
    );
};

const handleReportPeriodChange = (period) => {
    if (period === reportPeriod.value) return;
    isLoading.value = true;

    router.get(
        route('admin.dashboard'),
        { period },
        {
            preserveState: true,
            preserveScroll: true,
            only: ['reportData', 'stats'],
            onSuccess: () => {
                reportPeriod.value = period;
                isLoading.value = false;
            },
            onError: () => {
                isLoading.value = false;
            }
        }
    );
};

const fetchDashboardData = async () => {
    isLoading.value = true;
    error.value = null;
    
    try {
        await router.reload({
            preserveState: true,
            preserveScroll: true,
            only: ['investmentMetrics', 'trendData', 'summary', 'stats', 'categoryDistribution', 'alerts']
        });
    } catch (err) {
        error.value = 'Failed to load dashboard data. Please try again.';
        console.error('Dashboard data fetch error:', err);
    } finally {
        isLoading.value = false;
    }
};

onMounted(() => {
    fetchDashboardData();
});

const hasUnreadAlerts = computed(() => {
    return props.alerts.pending_approvals > 0
        || props.alerts.pending_withdrawals > 0
        || props.alerts.system_alerts.length > 0;
});

const getPeriodTotal = (dataKey) => {
    if (!props.reportData?.[dataKey]) return 0;
    return props.reportData[dataKey].reduce((sum, val) => sum + (Number(val) || 0), 0);
};

const getPeriodAverage = (dataKey) => {
    if (!props.reportData?.[dataKey]) return 0;
    const values = props.reportData[dataKey].filter(val => val !== null && val !== undefined);
    if (values.length === 0) return 0;
    return values.reduce((sum, val) => sum + Number(val), 0) / values.length;
};

const lastValues = ref({});

const formatCardValue = (currentValue, key) => {
    if (isLoading.value) return '-';

    const value = Number(currentValue);
    if (isNaN(value)) return '-';
    
    lastValues.value[key] = value;

    switch(key) {
        case 'investments':
            return formatCurrency(value);
        case 'users':
            return value.toLocaleString();
        case 'returns':
        case 'success':
            return formatPercentage(value);
        default:
            return value;
    }
};

const summaryCards = computed(() => [
    {
        key: 'investments',
        title: 'Total Investment',
        bgClass: 'bg-blue-50 hover:bg-blue-100',
        textClass: 'text-blue-600',
        valueClass: 'text-blue-900',
        getValue: () => formatCardValue(getPeriodTotal('investments'), 'investments')
    },
    {
        key: 'users',
        title: 'New Users',
        bgClass: 'bg-green-50 hover:bg-green-100',
        textClass: 'text-green-600',
        valueClass: 'text-green-900',
        getValue: () => formatCardValue(getPeriodTotal('users'), 'users')
    },
    {
        key: 'returns',
        title: 'Avg. ROI',
        bgClass: 'bg-yellow-50 hover:bg-yellow-100',
        textClass: 'text-yellow-600',
        valueClass: 'text-yellow-900',
        getValue: () => formatCardValue(getPeriodAverage('returns'), 'returns')
    },
    {
        key: 'success',
        title: 'Success Rate',
        bgClass: 'bg-purple-50 hover:bg-purple-100',
        textClass: 'text-purple-600',
        valueClass: 'text-purple-900',
        getValue: () => formatCardValue(props.reportData?.period_stats?.success_rate, 'success')
    }
]);

// Activity display helpers
const getActivityType = (activity) => {
    if (activity.loggable_type) {
        return activity.action.replace('_', ' ').charAt(0).toUpperCase() + activity.action.slice(1);
    }
    const types = {
        register: 'New User',
        login: 'User Login',
        profile_update: 'Profile Update'
    };
    return types[activity.action] || activity.action;
};

const getAmountColor = (activity) => {
    if (activity.loggable_type === 'App\\Models\\Withdrawal') return 'text-red-600';
    if (activity.loggable_type === 'App\\Models\\Investment') {
        return activity.loggable?.status === 'completed' ? 'text-green-600' : 'text-gray-900';
    }
    return 'text-gray-900';
};

const getActivityStatus = (activity) => {
    if (!activity.loggable) return 'completed';
    return activity.loggable.status || 'completed';
};

const filteredActivities = computed(() => {
    if (!props.recentInvestments) return [];
    if (activityFilter.value === 'all') return props.recentInvestments;

    return props.recentInvestments.filter(activity => {
        switch (activityFilter.value) {
            case 'investments':
                return activity.loggable_type === 'Investment';
            case 'withdrawals':
                return activity.loggable_type === 'Withdrawal';
            case 'users':
                return activity.action === 'register' ||
                       activity.action === 'login' ||
                       activity.action === 'profile_update';
            default:
                return true;
        }
    });
});

const refreshActivity = () => {
    if (isRefreshing.value) return;
    isRefreshing.value = true;

    router.reload({
        only: ['recentInvestments'],
        onSuccess: () => { isRefreshing.value = false },
        onError: () => { isRefreshing.value = false }
    });
};

const formatActivityAction = (action) => {
    return action?.replace(/_/g, ' ')
        .split(' ')
        .map(word => word.charAt(0).toUpperCase() + word.slice(1))
        .join(' ') || '-';
};

const summaryData = computed(() => ({
    total_investments: props.summary?.total_value || 0,
    active_investors: props.summary?.total_count || 0,
    average_roi: props.stats?.average_roi || 0,
    pending_withdrawals: props.alerts?.withdrawal_amount || 0,
    investment_trend: props.stats?.investment_growth || 0,
    investor_trend: props.stats?.user_growth || 0,
    roi_trend: props.stats?.roi_progress || 0,
    withdrawal_trend: 0 // Default to 0 if not available
}));

// Employee Management Handlers
const handleViewAllEmployees = () => {
    router.visit(route('admin.employees.index'));
};

const handleAddEmployee = () => {
    router.visit(route('admin.employees.create'));
};

const handleViewReports = () => {
    router.visit(route('admin.performance.index'));
};

const handleViewAnalytics = () => {
    router.visit(route('admin.performance.index'));
};

const handleManageGoals = () => {
    router.visit(route('admin.performance.index'));
};

onMounted(() => {
    isLoading.value = false;
});
</script>