<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { ref, computed } from 'vue';

interface KPI {
    total_members: number;
    active_members: number;
    members_with_kit: number;
    total_commissions_paid: number;
    pending_commissions: number;
    pending_commissions_count: number;
    total_profit_shares: number;
    pending_profit_shares: number;
    lgr_awarded_total: number;
    lgr_current_balance: number;
    lgr_withdrawn_total: number;
    total_team_volume: number;
}

interface PeriodMetrics {
    period: string;
    commissions: number;
    new_members: number;
    kit_purchases: number;
    team_volume: number;
}

interface MonthlyTrend {
    month: string;
    commissions: number;
    new_members: number;
    profit_shares: number;
}

interface TopEarner {
    id: number;
    name: string;
    email: string;
    total_earnings: number;
}

interface MlmOverview {
    total_commissions: any;
    pending_commissions: any;
    active_members: number;
    network_growth: number;
    total_volume: number;
    compliance_score: number;
}

interface Props {
    kpis: KPI;
    periodMetrics: PeriodMetrics;
    monthlyTrend: MonthlyTrend[];
    topEarners: TopEarner[];
    mlmOverview: MlmOverview;
}

const props = defineProps<Props>();

const selectedPeriod = ref(props.periodMetrics.period);

const changePeriod = (period: string) => {
    selectedPeriod.value = period;
    router.get(route('admin.grownet.dashboard'), { period }, { preserveState: true });
};

const formatCurrency = (value: number) => `K${value.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;

const maxCommission = computed(() => Math.max(...props.monthlyTrend.map(m => m.commissions), 1));
const maxMembers = computed(() => Math.max(...props.monthlyTrend.map(m => m.new_members), 1));
</script>

<template>
    <Head title="GrowNet Dashboard" />
    <AdminLayout>
        <div class="p-6">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">GrowNet Dashboard</h1>
                    <p class="text-sm text-gray-600 mt-1">Aggregated KPIs and metrics for the GrowNet platform</p>
                </div>
                <div class="flex gap-2">
                    <button v-for="p in ['week', 'month', 'quarter', 'year']" :key="p"
                        @click="changePeriod(p)"
                        :class="['px-3 py-1.5 text-sm rounded-lg font-medium transition-colors', selectedPeriod === p ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200']">
                        {{ p.charAt(0).toUpperCase() + p.slice(1) }}
                    </button>
                </div>
            </div>

            <!-- KPI Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                <div class="bg-white rounded-lg shadow p-5 border-l-4 border-blue-500">
                    <p class="text-sm text-gray-600">Total Members</p>
                    <p class="text-2xl font-bold text-gray-900 mt-1">{{ kpis.total_members.toLocaleString() }}</p>
                    <p class="text-xs text-green-600 mt-1">{{ kpis.active_members.toLocaleString() }} active</p>
                </div>
                <div class="bg-white rounded-lg shadow p-5 border-l-4 border-green-500">
                    <p class="text-sm text-gray-600">Commissions Paid</p>
                    <p class="text-2xl font-bold text-gray-900 mt-1">{{ formatCurrency(kpis.total_commissions_paid) }}</p>
                    <p v-if="kpis.pending_commissions_count > 0" class="text-xs text-amber-600 mt-1">{{ kpis.pending_commissions_count }} pending ({{ formatCurrency(kpis.pending_commissions) }})</p>
                </div>
                <div class="bg-white rounded-lg shadow p-5 border-l-4 border-purple-500">
                    <p class="text-sm text-gray-600">Profit Shares</p>
                    <p class="text-2xl font-bold text-gray-900 mt-1">{{ formatCurrency(kpis.total_profit_shares) }}</p>
                    <p v-if="kpis.pending_profit_shares > 0" class="text-xs text-amber-600 mt-1">{{ formatCurrency(kpis.pending_profit_shares) }} pending approval</p>
                </div>
                <div class="bg-white rounded-lg shadow p-5 border-l-4 border-amber-500">
                    <p class="text-sm text-gray-600">LGR Total Awarded</p>
                    <p class="text-2xl font-bold text-gray-900 mt-1">{{ formatCurrency(kpis.lgr_awarded_total) }}</p>
                    <p class="text-xs text-gray-500 mt-1">Current: {{ formatCurrency(kpis.lgr_current_balance) }}</p>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                <div class="bg-white rounded-lg shadow p-5">
                    <p class="text-sm text-gray-600">Members with Starter Kit</p>
                    <p class="text-2xl font-bold text-gray-900 mt-1">{{ kpis.members_with_kit.toLocaleString() }}</p>
                    <p class="text-xs text-gray-500 mt-1">{{ kpis.total_members > 0 ? ((kpis.members_with_kit / kpis.total_members) * 100).toFixed(1) : 0 }}% conversion</p>
                </div>
                <div class="bg-white rounded-lg shadow p-5">
                    <p class="text-sm text-gray-600">Team Volume</p>
                    <p class="text-2xl font-bold text-gray-900 mt-1">{{ formatCurrency(kpis.total_team_volume) }}</p>
                </div>
                <div class="bg-white rounded-lg shadow p-5">
                    <p class="text-sm text-gray-600">LGR Withdrawn</p>
                    <p class="text-2xl font-bold text-gray-900 mt-1">{{ formatCurrency(kpis.lgr_withdrawn_total) }}</p>
                </div>
                <div class="bg-white rounded-lg shadow p-5">
                    <p class="text-sm text-gray-600">MLM System Score</p>
                    <p class="text-2xl font-bold text-gray-900 mt-1">{{ mlmOverview.compliance_score ?? 'N/A' }}</p>
                </div>
            </div>

            <!-- Period Metrics -->
            <div class="bg-white rounded-lg shadow mb-6">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-900">This {{ selectedPeriod.charAt(0).toUpperCase() + selectedPeriod.slice(1) }}</h2>
                </div>
                <div class="p-6 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    <div>
                        <p class="text-sm text-gray-600">Commissions</p>
                        <p class="text-xl font-bold text-gray-900">{{ formatCurrency(periodMetrics.commissions) }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">New Members</p>
                        <p class="text-xl font-bold text-gray-900">{{ periodMetrics.new_members.toLocaleString() }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Kit Purchases</p>
                        <p class="text-xl font-bold text-gray-900">{{ periodMetrics.kit_purchases.toLocaleString() }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Team Volume</p>
                        <p class="text-xl font-bold text-gray-900">{{ formatCurrency(periodMetrics.team_volume) }}</p>
                    </div>
                </div>
            </div>

            <!-- Monthly Trend + Top Earners -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
                <!-- Monthly Trend Chart -->
                <div class="lg:col-span-2 bg-white rounded-lg shadow">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-gray-900">Monthly Trend (6 months)</h2>
                    </div>
                    <div class="p-6 space-y-4">
                        <div v-for="month in monthlyTrend" :key="month.month">
                            <div class="flex items-center justify-between text-sm mb-1">
                                <span class="font-medium text-gray-700">{{ month.month }}</span>
                                <span class="text-gray-900 font-semibold">{{ formatCurrency(month.commissions) }}</span>
                            </div>
                            <div class="w-full bg-gray-100 rounded-full h-2.5">
                                <div class="bg-blue-600 h-2.5 rounded-full transition-all" :style="{ width: `${(month.commissions / maxCommission) * 100}%` }"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Top Earners -->
                <div class="bg-white rounded-lg shadow">
                    <div class="px-6 py-4 border-b border-gray-200">
                        <h2 class="text-lg font-semibold text-gray-900">Top Earners</h2>
                    </div>
                    <div class="divide-y divide-gray-100">
                        <div v-for="(earner, index) in topEarners" :key="earner.id" class="px-6 py-3 flex items-center justify-between hover:bg-gray-50">
                            <div class="flex items-center gap-3">
                                <span class="text-sm font-bold text-gray-400 w-5">{{ index + 1 }}</span>
                                <div>
                                    <p class="text-sm font-medium text-gray-900">{{ earner.name }}</p>
                                    <p class="text-xs text-gray-500">{{ earner.email }}</p>
                                </div>
                            </div>
                            <p class="text-sm font-semibold text-green-600">{{ formatCurrency(earner.total_earnings) }}</p>
                        </div>
                        <div v-if="topEarners.length === 0" class="px-6 py-8 text-center text-gray-500 text-sm">No earners yet</div>
                    </div>
                </div>
            </div>

            <!-- Quick Links -->
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-900">Quick Links</h2>
                </div>
                <div class="p-6 grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3">
                    <Link :href="route('admin.grownet.earnings')" class="text-center p-4 bg-blue-50 rounded-lg hover:bg-blue-100 transition-colors">
                        <p class="text-sm font-medium text-blue-700">Earnings</p>
                    </Link>
                    <Link :href="route('admin.commission-settings.index')" class="text-center p-4 bg-green-50 rounded-lg hover:bg-green-100 transition-colors">
                        <p class="text-sm font-medium text-green-700">Commission Settings</p>
                    </Link>
                    <Link :href="route('admin.points.index')" class="text-center p-4 bg-purple-50 rounded-lg hover:bg-purple-100 transition-colors">
                        <p class="text-sm font-medium text-purple-700">Points</p>
                    </Link>
                    <Link :href="route('admin.referrals.index')" class="text-center p-4 bg-amber-50 rounded-lg hover:bg-amber-100 transition-colors">
                        <p class="text-sm font-medium text-amber-700">Referrals</p>
                    </Link>
                    <Link :href="route('admin.starter-kit.dashboard')" class="text-center p-4 bg-indigo-50 rounded-lg hover:bg-indigo-100 transition-colors">
                        <p class="text-sm font-medium text-indigo-700">Starter Kits</p>
                    </Link>
                    <Link :href="route('admin.lgr.index')" class="text-center p-4 bg-rose-50 rounded-lg hover:bg-rose-100 transition-colors">
                        <p class="text-sm font-medium text-rose-700">LGR</p>
                    </Link>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
