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
                        :value="formatNumber(memberMetrics.total)"
                        :change="memberMetrics.growth_rate"
                        icon="users"
                        color="blue"
                    />
                    
                    <!-- Active Subscriptions -->
                    <StatCard
                        title="Active Subscriptions"
                        :value="formatNumber(subscriptionMetrics.active)"
                        :subtitle="`K${formatNumber(subscriptionMetrics.monthly_revenue)} revenue`"
                        icon="credit-card"
                        color="green"
                    />
                    
                    <!-- Monthly Revenue -->
                    <StatCard
                        title="Monthly Revenue"
                        :value="`K${formatNumber(subscriptionMetrics.monthly_revenue)}`"
                        :change="subscriptionMetrics.growth_rate"
                        icon="currency-dollar"
                        color="emerald"
                    />
                    
                    <!-- Points Awarded -->
                    <StatCard
                        title="Points This Month"
                        :value="`${formatNumber(pointsMetrics.this_month_lp + pointsMetrics.this_month_map)}`"
                        :subtitle="`${pointsMetrics.qualification_rate}% qualified`"
                        icon="star"
                        color="purple"
                    />
                    
                    <!-- Matrix Fill Rate -->
                    <StatCard
                        title="Matrix Fill Rate"
                        :value="`${matrixMetrics.fill_rate}%`"
                        :subtitle="`${formatNumber(matrixMetrics.filled_positions)}/${formatNumber(matrixMetrics.total_positions)}`"
                        icon="grid"
                        color="indigo"
                    />
                    
                    <!-- Profit Distributed -->
                    <StatCard
                        title="Profit Distributed"
                        :value="`K${formatNumber(financialMetrics.profit_distributed)}`"
                        :subtitle="'This month'"
                        icon="banknotes"
                        color="amber"
                    />
                </div>

                <!-- Support Tickets Stats Row -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <StatCard
                        title="Total Tickets"
                        :value="formatNumber(supportData.total_tickets)"
                        :subtitle="'All time'"
                        icon="ticket"
                        color="blue"
                    />
                    
                    <StatCard
                        title="Open Tickets"
                        :value="formatNumber(supportData.open_tickets)"
                        :subtitle="'Needs attention'"
                        icon="exclamation-circle"
                        color="amber"
                    />
                    
                    <StatCard
                        title="In Progress"
                        :value="formatNumber(supportData.in_progress_tickets)"
                        :subtitle="'Being handled'"
                        icon="clock"
                        color="blue"
                    />
                    
                    <StatCard
                        title="Urgent Tickets"
                        :value="formatNumber(supportData.urgent_tickets)"
                        :subtitle="'Overdue'"
                        icon="fire"
                        color="red"
                    />
                </div>

                <!-- Workshop & Starter Kit Stats Row -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                    <StatCard
                        title="Total Workshops"
                        :value="formatNumber(workshopMetrics.total_workshops)"
                        :subtitle="`${workshopMetrics.upcoming} upcoming`"
                        icon="academic-cap"
                        color="blue"
                    />
                    
                    <StatCard
                        title="Total Registrations"
                        :value="formatNumber(workshopMetrics.total_registrations)"
                        :subtitle="`${workshopMetrics.this_month_registrations} this month`"
                        icon="users"
                        color="green"
                    />
                    
                    <StatCard
                        title="Workshop Revenue"
                        :value="`K${formatNumber(workshopMetrics.total_revenue)}`"
                        :subtitle="'All time'"
                        icon="currency-dollar"
                        color="emerald"
                    />
                    
                    <StatCard
                        title="Starter Kits"
                        :value="formatNumber(starterKitMetrics.total_assigned)"
                        :subtitle="`${starterKitMetrics.assignment_rate}% of members`"
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
                            <a href="/admin/workshops" class="block w-full px-4 py-2 text-sm text-center bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                                Manage Workshops
                            </a>
                            <a href="/admin/starter-kits" class="block w-full px-4 py-2 text-sm text-center bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors">
                                Starter Kits
                            </a>
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
                            <div class="text-2xl font-bold">{{ memberMetrics.active_percentage }}%</div>
                            <div class="text-sm opacity-90">Active Members</div>
                        </div>
                        <div>
                            <div class="text-2xl font-bold">{{ subscriptionMetrics.conversion_rate }}%</div>
                            <div class="text-sm opacity-90">Subscription Rate</div>
                        </div>
                        <div>
                            <div class="text-2xl font-bold">{{ pointsMetrics.qualification_rate }}%</div>
                            <div class="text-sm opacity-90">Qualification Rate</div>
                        </div>
                        <div>
                            <div class="text-2xl font-bold">{{ financialMetrics.commission_ratio }}%</div>
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

const props = defineProps<{
    memberMetrics: any;
    subscriptionMetrics: any;
    starterKitMetrics: any;
    pointsMetrics: any;
    matrixMetrics: any;
    financialMetrics: any;
    workshopMetrics: any;
    supportData: any;
    professionalLevelDistribution: any[];
    memberGrowthTrend: any[];
    revenueGrowthTrend: any[];
    recentActivity: any[];
    alerts: any[];
}>();

const currentDate = computed(() => {
    return new Date().toLocaleDateString('en-US', { 
        weekday: 'long', 
        year: 'numeric', 
        month: 'long', 
        day: 'numeric' 
    });
});

const memberGrowthData = computed(() => ({
    labels: props.memberGrowthTrend.map(item => item.date),
    datasets: [{
        label: 'New Members',
        data: props.memberGrowthTrend.map(item => item.count),
        borderColor: '#2563eb',
        backgroundColor: 'rgba(37, 99, 235, 0.1)',
    }]
}));

const revenueGrowthData = computed(() => ({
    labels: props.revenueGrowthTrend.map(item => item.date),
    datasets: [{
        label: 'Revenue (K)',
        data: props.revenueGrowthTrend.map(item => item.revenue),
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
