<template>
    <AdminLayout title="Dashboard">
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold text-gray-800">MyGrowNet Admin Dashboard</h2>
                <div class="text-sm text-gray-500">{{ currentDate }}</div>
            </div>
        </template>

        <div class="py-6">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
                
                <!-- Top Stat Cards -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-4">
                    <!-- Total Members -->
                    <StatCard
                        title="Total Members"
                        :value="formatNumber(memberMetrics?.total || 0)"
                        :change="memberMetrics?.growth_rate || 0"
                        icon="users"
                        color="blue"
                    />
                    
                    <!-- Active Subscriptions -->
                    <StatCard
                        title="Active Subscriptions"
                        :value="formatNumber(subscriptionMetrics?.active || 0)"
                        :subtitle="`K${formatNumber(subscriptionMetrics?.monthly_revenue || 0)} revenue`"
                        icon="credit-card"
                        color="green"
                    />
                    
                    <!-- Monthly Revenue -->
                    <StatCard
                        title="Monthly Revenue"
                        :value="`K${formatNumber(subscriptionMetrics?.monthly_revenue || 0)}`"
                        :change="subscriptionMetrics?.growth_rate || 0"
                        icon="currency-dollar"
                        color="emerald"
                    />
                    
                    <!-- Points Awarded -->
                    <StatCard
                        title="Points This Month"
                        :value="`${formatNumber((pointsMetrics?.this_month_lp || 0) + (pointsMetrics?.this_month_map || 0))}`"
                        :subtitle="`${pointsMetrics?.qualification_rate || 0}% qualified`"
                        icon="star"
                        color="purple"
                    />
                    
                    <!-- Matrix Fill Rate -->
                    <StatCard
                        title="Matrix Fill Rate"
                        :value="`${matrixMetrics?.fill_rate || 0}%`"
                        :subtitle="`${formatNumber(matrixMetrics?.filled_positions || 0)}/${formatNumber(matrixMetrics?.total_positions || 0)}`"
                        icon="grid"
                        color="indigo"
                    />
                    
                    <!-- Profit Distributed -->
                    <StatCard
                        title="Profit Distributed"
                        :value="`K${formatNumber(financialMetrics?.profit_distributed || 0)}`"
                        :subtitle="'This month'"
                        icon="banknotes"
                        color="amber"
                    />
                </div>

                <!-- Support Tickets Stats Row -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <StatCard
                        title="Total Tickets"
                        :value="formatNumber(supportData?.total_tickets || 0)"
                        :subtitle="'All time'"
                        icon="ticket"
                        color="blue"
                    />
                    
                    <StatCard
                        title="Open Tickets"
                        :value="formatNumber(supportData?.open_tickets || 0)"
                        :subtitle="'Needs attention'"
                        icon="exclamation-circle"
                        color="amber"
                    />
                    
                    <StatCard
                        title="In Progress"
                        :value="formatNumber(supportData?.pending_tickets || 0)"
                        :subtitle="'Being handled'"
                        icon="clock"
                        color="blue"
                    />
                    
                    <StatCard
                        title="Resolved Tickets"
                        :value="formatNumber(supportData?.resolved_tickets || 0)"
                        :subtitle="'Completed'"
                        icon="check-circle"
                        color="green"
                    />
                </div>

                <!-- Workshop & Starter Kit Stats Row -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                    <StatCard
                        title="Total Workshops"
                        :value="formatNumber(workshopMetrics?.total_workshops || 0)"
                        :subtitle="`${workshopMetrics?.upcoming || 0} upcoming`"
                        icon="academic-cap"
                        color="blue"
                    />
                    
                    <StatCard
                        title="Total Registrations"
                        :value="formatNumber(workshopMetrics?.total_registrations || 0)"
                        :subtitle="`${workshopMetrics?.this_month_registrations || 0} this month`"
                        icon="users"
                        color="green"
                    />
                    
                    <StatCard
                        title="Workshop Revenue"
                        :value="`K${formatNumber(workshopMetrics?.total_revenue || 0)}`"
                        :subtitle="'All time'"
                        icon="currency-dollar"
                        color="emerald"
                    />
                    
                    <StatCard
                        title="Starter Kits"
                        :value="formatNumber(starterKitMetrics?.total_assigned || 0)"
                        :subtitle="`${starterKitMetrics?.assignment_rate || 0}% of members`"
                        icon="gift"
                        color="purple"
                    />
                    
                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-sm font-medium text-gray-600">Quick Actions</h3>
                        </div>
                        <div class="space-y-2">
                            <Link :href="route('admin.subscriptions.index')" class="block w-full px-4 py-2 text-sm text-center bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                                Subscriptions
                            </Link>
                            <Link :href="route('admin.email-campaigns.index')" class="block w-full px-4 py-2 text-sm text-center bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-colors">
                                Email Campaigns
                            </Link>
                            <a href="/admin/workshops" class="block w-full px-4 py-2 text-sm text-center bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                                Manage Workshops
                            </a>
                            <a href="/admin/starter-kits" class="block w-full px-4 py-2 text-sm text-center bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors">
                                Starter Kits
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Telegram Notifications -->
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-semibold text-gray-900">Telegram Notifications (FREE)</h3>
                        <span class="text-xs bg-green-100 text-green-800 px-2 py-1 rounded-full font-semibold">K0 Cost</span>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="bg-blue-50 rounded-lg p-4">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm text-gray-600">Linked Accounts</p>
                                    <p class="text-2xl font-bold text-blue-600">{{ telegramMetrics?.total_linked || 0 }}</p>
                                    <p class="text-xs text-gray-500 mt-1">{{ telegramMetrics?.linkage_rate || 0 }}% of members</p>
                                </div>
                                <svg class="w-10 h-10 text-blue-600" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M12 0C5.373 0 0 5.373 0 12s5.373 12 12 12 12-5.373 12-12S18.627 0 12 0zm5.562 8.161c-.18 1.897-.962 6.502-1.359 8.627-.168.9-.5 1.201-.82 1.23-.697.064-1.226-.461-1.901-.903-1.056-.692-1.653-1.123-2.678-1.799-1.185-.781-.417-1.21.258-1.911.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.139-5.062 3.345-.479.329-.913.489-1.302.481-.428-.009-1.252-.242-1.865-.442-.752-.244-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.831-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635.099-.002.321.023.465.141.121.099.155.232.171.326.016.094.036.308.02.475z"/>
                                </svg>
                            </div>
                        </div>
                        <div class="bg-green-50 rounded-lg p-4">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm text-gray-600">This Week</p>
                                    <p class="text-2xl font-bold text-green-600">{{ telegramMetrics?.recently_linked || 0 }}</p>
                                    <p class="text-xs text-gray-500 mt-1">New links</p>
                                </div>
                                <svg class="w-10 h-10 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                                </svg>
                            </div>
                        </div>
                        <div class="bg-purple-50 rounded-lg p-4">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-sm text-gray-600">Instant Delivery</p>
                                    <p class="text-2xl font-bold text-purple-600">FREE</p>
                                    <p class="text-xs text-gray-500 mt-1">Unlimited messages</p>
                                </div>
                                <svg class="w-10 h-10 text-purple-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Charts Row -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Member Growth Trend -->
                    <div class="bg-white rounded-lg shadow p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Member Growth (Last 12 Months)</h3>
                        <div class="h-64">
                            <LineChart :data="memberGrowthData" />
                        </div>
                    </div>

                    <!-- Revenue Growth Trend -->
                    <div class="bg-white rounded-lg shadow p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Revenue Trend (Last 12 Months)</h3>
                        <div class="h-64">
                            <LineChart :data="revenueGrowthData" color="green" />
                        </div>
                    </div>
                </div>

                <!-- Professional Level Distribution -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Professional Level Distribution</h3>
                    <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-7 gap-4">
                        <div v-for="level in professionalLevelDistribution" :key="level.level" 
                             class="text-center p-4 bg-gradient-to-br from-blue-50 to-blue-100 rounded-lg">
                            <div class="text-3xl font-bold text-blue-600">{{ level.count }}</div>
                            <div class="text-sm font-medium text-gray-900 mt-1">{{ level.name }}</div>
                            <div class="text-xs text-gray-500 mt-1">Level {{ getLevelNumber(level.level) }}</div>
                        </div>
                    </div>
                </div>

                <!-- Bottom Row: Activity & Alerts -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    <!-- Recent Activity -->
                    <div class="bg-white rounded-lg shadow p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Recent Activity</h3>
                        <div class="space-y-3">
                            <div v-for="(activity, index) in recentActivity" :key="index" 
                                 class="flex items-start space-x-3 p-3 hover:bg-gray-50 rounded-lg transition">
                                <div class="flex-shrink-0">
                                    <div :class="getActivityIconClass(activity.type)" class="h-8 w-8 rounded-full flex items-center justify-center">
                                        <component :is="getActivityIcon(activity.type)" class="h-4 w-4" />
                                    </div>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm text-gray-900">{{ activity.description }}</p>
                                    <p class="text-xs text-gray-500 mt-1">{{ activity.timestamp }}</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Alerts & Quick Actions -->
                    <div class="bg-white rounded-lg shadow p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Alerts & Notifications</h3>
                        <div class="space-y-3">
                            <div v-if="alerts.length === 0" class="text-center py-8 text-gray-500">
                                <CheckCircleIcon class="h-12 w-12 mx-auto text-green-500 mb-2" />
                                <p>All systems operational</p>
                            </div>
                            <div v-for="(alert, index) in alerts" :key="index" 
                                 :class="getAlertClass(alert.type)" 
                                 class="p-4 rounded-lg">
                                <div class="flex">
                                    <div class="flex-shrink-0">
                                        <component :is="getAlertIcon(alert.type)" class="h-5 w-5" />
                                    </div>
                                    <div class="ml-3">
                                        <h4 class="text-sm font-medium">{{ alert.title }}</h4>
                                        <p class="text-sm mt-1">{{ alert.message }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Key Metrics Summary -->
                <div class="bg-gradient-to-r from-blue-500 to-indigo-600 rounded-lg shadow-lg p-6 text-white">
                    <h3 class="text-lg font-semibold mb-4">Platform Overview</h3>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <div>
                            <div class="text-2xl font-bold">{{ memberMetrics?.active_percentage || 0 }}%</div>
                            <div class="text-sm opacity-90">Active Members</div>
                        </div>
                        <div>
                            <div class="text-2xl font-bold">{{ subscriptionMetrics?.conversion_rate || 0 }}%</div>
                            <div class="text-sm opacity-90">Subscription Rate</div>
                        </div>
                        <div>
                            <div class="text-2xl font-bold">{{ pointsMetrics?.qualification_rate || 0 }}%</div>
                            <div class="text-sm opacity-90">Qualification Rate</div>
                        </div>
                        <div>
                            <div class="text-2xl font-bold">{{ financialMetrics?.commission_ratio || 0 }}%</div>
                            <div class="text-sm opacity-90">Commission Ratio</div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </AdminLayout>
</template>

<script setup lang="ts">
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import StatCard from '@/components/StatCard.vue';
import LineChart from '@/components/LineChart.vue';
import { 
    CheckCircleIcon, 
    ExclamationTriangleIcon, 
    InformationCircleIcon,
    UserPlusIcon,
    CreditCardIcon,
} from '@heroicons/vue/24/outline';

const props = withDefaults(defineProps<{
    memberMetrics?: any;
    subscriptionMetrics?: any;
    starterKitMetrics?: any;
    pointsMetrics?: any;
    matrixMetrics?: any;
    financialMetrics?: any;
    workshopMetrics?: any;
    emailMarketingMetrics?: any;
    telegramMetrics?: any;
    supportData?: any;
    professionalLevelDistribution?: any[];
    memberGrowthTrend?: any[];
    revenueGrowthTrend?: any[];
    recentActivity?: any[];
    alerts?: any[];
}>(), {
    memberMetrics: () => ({ total: 0, active: 0, growth_rate: 0, active_percentage: 0 }),
    subscriptionMetrics: () => ({ active: 0, monthly_revenue: 0, growth_rate: 0, conversion_rate: 0 }),
    starterKitMetrics: () => ({ total_assigned: 0, assignment_rate: 0 }),
    pointsMetrics: () => ({ this_month_lp: 0, this_month_map: 0, qualification_rate: 0 }),
    matrixMetrics: () => ({ fill_rate: 0, filled_positions: 0, total_positions: 0 }),
    financialMetrics: () => ({ profit_distributed: 0, commission_ratio: 0 }),
    workshopMetrics: () => ({ total_workshops: 0, upcoming: 0, total_registrations: 0, this_month_registrations: 0, total_revenue: 0 }),
    emailMarketingMetrics: () => ({ total_campaigns: 0, active_campaigns: 0, total_subscribers: 0, emails_sent: 0, open_rate: 0, click_rate: 0 }),
    telegramMetrics: () => ({ total_linked: 0, linkage_rate: 0, recently_linked: 0, total_members: 0 }),
    supportData: () => ({ total_tickets: 0, open_tickets: 0, pending_tickets: 0, resolved_tickets: 0 }),
    professionalLevelDistribution: () => [],
    memberGrowthTrend: () => [],
    revenueGrowthTrend: () => [],
    recentActivity: () => [],
    alerts: () => [],
});

const currentDate = computed(() => {
    return new Date().toLocaleDateString('en-US', { 
        weekday: 'long', 
        year: 'numeric', 
        month: 'long', 
        day: 'numeric' 
    });
});

const memberGrowthData = computed(() => ({
    labels: (props.memberGrowthTrend || []).map(item => item.date),
    datasets: [{
        label: 'New Members',
        data: (props.memberGrowthTrend || []).map(item => item.count),
        borderColor: '#2563eb',
        backgroundColor: 'rgba(37, 99, 235, 0.1)',
    }]
}));

const revenueGrowthData = computed(() => ({
    labels: (props.revenueGrowthTrend || []).map(item => item.date),
    datasets: [{
        label: 'Revenue (K)',
        data: (props.revenueGrowthTrend || []).map(item => item.revenue),
        borderColor: '#059669',
        backgroundColor: 'rgba(5, 150, 105, 0.1)',
    }]
}));

const formatNumber = (num: number) => {
    return new Intl.NumberFormat().format(Math.round(num));
};

const getLevelNumber = (level: string) => {
    const levels: Record<string, number> = {
        'associate': 1,
        'professional': 2,
        'senior': 3,
        'manager': 4,
        'director': 5,
        'executive': 6,
        'ambassador': 7,
    };
    return levels[level] || 0;
};

const getActivityIconClass = (type: string) => {
    const classes: Record<string, string> = {
        'member_joined': 'bg-blue-100 text-blue-600',
        'subscription': 'bg-green-100 text-green-600',
        'level_up': 'bg-purple-100 text-purple-600',
    };
    return classes[type] || 'bg-gray-100 text-gray-600';
};

const getActivityIcon = (type: string) => {
    const icons: Record<string, any> = {
        'member_joined': UserPlusIcon,
        'subscription': CreditCardIcon,
    };
    return icons[type] || InformationCircleIcon;
};

const getAlertClass = (type: string) => {
    const classes: Record<string, string> = {
        'warning': 'bg-amber-50 border border-amber-200 text-amber-800',
        'info': 'bg-blue-50 border border-blue-200 text-blue-800',
        'error': 'bg-red-50 border border-red-200 text-red-800',
    };
    return classes[type] || 'bg-gray-50 border border-gray-200 text-gray-800';
};

const getAlertIcon = (type: string) => {
    const icons: Record<string, any> = {
        'warning': ExclamationTriangleIcon,
        'info': InformationCircleIcon,
        'error': ExclamationTriangleIcon,
    };
    return icons[type] || InformationCircleIcon;
};
</script>
