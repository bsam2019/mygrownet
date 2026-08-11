<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import SlideOverDrawer from '@/Components/SlideOverDrawer.vue';
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
    mlmOverview?: MlmOverview;
}

const props = defineProps<Props>();

const selectedPeriod = ref(props.periodMetrics?.period || 'month');

const changePeriod = (period: string) => {
    selectedPeriod.value = period;
    router.get(route('admin.grownet.dashboard'), { period }, { preserveState: true, preserveScroll: true });
};

const formatCurrency = (value?: number | null) => `K${(value ?? 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;

const maxCommission = computed(() => Math.max(...props.monthlyTrend.map(m => m.commissions), 1));

// Slide-Over Drawer State
const selectedEarner = ref<TopEarner | null>(null);
const showEarnerDrawer = ref(false);
const showQuickNavDrawer = ref(false);

const openEarnerDrawer = (earner: TopEarner) => {
    selectedEarner.value = earner;
    showEarnerDrawer.value = true;
};
</script>

<template>
    <Head title="GrowNet Administration Dashboard" />

    <AdminLayout>
        <div class="p-6 space-y-8">
            <!-- Header with Reactive Period Selector -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <div class="flex items-center gap-2">
                        <h1 class="text-2xl font-bold text-gray-900">GrowNet Administration Dashboard</h1>
                        <span class="px-2.5 py-0.5 rounded-full bg-blue-50 text-blue-700 text-xs font-extrabold uppercase border border-blue-200">
                            {{ selectedPeriod }} View
                        </span>
                    </div>
                    <p class="text-sm text-gray-600 mt-1">Central control center for education, network management, commissions, and member progression.</p>
                </div>

                <div class="flex items-center gap-3">
                    <!-- Slide-Over Drawer Quick Launcher Button -->
                    <button
                        @click="showQuickNavDrawer = true"
                        class="px-4 py-2 rounded-xl bg-slate-900 text-white font-bold text-xs hover:bg-slate-800 transition-all shadow flex items-center gap-1.5"
                    >
                        <span>⚡ Quick Admin Drawer</span>
                    </button>

                    <!-- Period Selector Buttons -->
                    <div class="flex bg-gray-100 p-1 rounded-xl border border-gray-200 text-xs font-bold">
                        <button
                            v-for="p in ['week', 'month', 'quarter', 'year']"
                            :key="p"
                            @click="changePeriod(p)"
                            :class="[
                                'px-3 py-1.5 rounded-lg transition-all',
                                selectedPeriod === p ? 'bg-white text-blue-700 shadow-xs' : 'text-gray-600 hover:text-gray-900'
                            ]"
                        >
                            {{ p.charAt(0).toUpperCase() + p.slice(1) }}
                        </button>
                    </div>
                </div>
            </div>

            <!-- Dynamic Period Telemetry Banner (Reflects Week / Month / Quarter / Year) -->
            <div class="bg-gradient-to-r from-blue-900 via-indigo-900 to-slate-900 text-white rounded-2xl p-6 shadow-md border border-white/10">
                <div class="flex items-center justify-between border-b border-white/10 pb-3 mb-4">
                    <div>
                        <span class="text-xs font-bold text-blue-300 uppercase tracking-wider">Filtered Telemetry</span>
                        <h3 class="text-lg font-black text-white">Performance Metrics for This {{ selectedPeriod.charAt(0).toUpperCase() + selectedPeriod.slice(1) }}</h3>
                    </div>
                    <span class="px-3 py-1 rounded-full bg-emerald-500/20 text-emerald-300 font-bold text-xs border border-emerald-500/30">
                        Live Filter
                    </span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    <div class="p-4 rounded-xl bg-white/5 border border-white/10">
                        <p class="text-xs text-blue-200 font-bold">Period Commissions</p>
                        <p class="text-2xl font-black text-white mt-1">{{ formatCurrency(periodMetrics.commissions) }}</p>
                        <p class="text-[11px] text-slate-300 mt-0.5">Paid during this {{ selectedPeriod }}</p>
                    </div>

                    <div class="p-4 rounded-xl bg-white/5 border border-white/10">
                        <p class="text-xs text-emerald-200 font-bold">New Member Registrations</p>
                        <p class="text-2xl font-black text-white mt-1">{{ periodMetrics.new_members.toLocaleString() }}</p>
                        <p class="text-[11px] text-slate-300 mt-0.5">Joined this {{ selectedPeriod }}</p>
                    </div>

                    <div class="p-4 rounded-xl bg-white/5 border border-white/10">
                        <p class="text-xs text-purple-200 font-bold">Starter Kit Conversions</p>
                        <p class="text-2xl font-black text-white mt-1">{{ periodMetrics.kit_purchases.toLocaleString() }}</p>
                        <p class="text-[11px] text-slate-300 mt-0.5">Activated this {{ selectedPeriod }}</p>
                    </div>

                    <div class="p-4 rounded-xl bg-white/5 border border-white/10">
                        <p class="text-xs text-amber-200 font-bold">Period Team Volume</p>
                        <p class="text-2xl font-black text-white mt-1">{{ formatCurrency(periodMetrics.team_volume) }}</p>
                        <p class="text-[11px] text-slate-300 mt-0.5">Accumulated this {{ selectedPeriod }}</p>
                    </div>
                </div>
            </div>

            <!-- KPI Cards Row -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-white rounded-xl shadow-xs p-5 border-l-4 border-blue-500 border border-gray-100">
                    <p class="text-xs font-bold text-gray-500 uppercase tracking-wider">Total Members</p>
                    <p class="text-2xl font-black text-gray-900 mt-1">{{ kpis.total_members.toLocaleString() }}</p>
                    <p class="text-xs text-emerald-600 font-bold mt-1">{{ kpis.active_members.toLocaleString() }} Active Members</p>
                </div>
                <div class="bg-white rounded-xl shadow-xs p-5 border-l-4 border-emerald-500 border border-gray-100">
                    <p class="text-xs font-bold text-gray-500 uppercase tracking-wider">Total Commissions Paid</p>
                    <p class="text-2xl font-black text-gray-900 mt-1">{{ formatCurrency(kpis.total_commissions_paid) }}</p>
                    <p v-if="kpis.pending_commissions_count > 0" class="text-xs text-amber-600 font-bold mt-1">{{ kpis.pending_commissions_count }} pending ({{ formatCurrency(kpis.pending_commissions) }})</p>
                </div>
                <div class="bg-white rounded-xl shadow-xs p-5 border-l-4 border-purple-500 border border-gray-100">
                    <p class="text-xs font-bold text-gray-500 uppercase tracking-wider">Profit Shares</p>
                    <p class="text-2xl font-black text-gray-900 mt-1">{{ formatCurrency(kpis.total_profit_shares) }}</p>
                    <p v-if="kpis.pending_profit_shares > 0" class="text-xs text-amber-600 font-bold mt-1">{{ formatCurrency(kpis.pending_profit_shares) }} pending approval</p>
                </div>
                <div class="bg-white rounded-xl shadow-xs p-5 border-l-4 border-amber-500 border border-gray-100">
                    <p class="text-xs font-bold text-gray-500 uppercase tracking-wider">LGR Total Awarded</p>
                    <p class="text-2xl font-black text-gray-900 mt-1">{{ formatCurrency(kpis.lgr_awarded_total) }}</p>
                    <p class="text-xs text-gray-500 font-medium mt-1">Current Balance: {{ formatCurrency(kpis.lgr_current_balance) }}</p>
                </div>
            </div>

            <!-- Education & Member Development Management Hero Section -->
            <section class="bg-gradient-to-r from-indigo-900 via-slate-900 to-purple-950 text-white rounded-3xl shadow-xl p-6 sm:p-8 space-y-6">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-white/10 pb-4">
                    <div>
                        <span class="px-3 py-1 rounded-full bg-indigo-500/20 text-indigo-300 text-[11px] font-extrabold border border-indigo-500/30 uppercase tracking-wider">
                            🎓 Education & Workshops Control Center
                        </span>
                        <h2 class="text-xl sm:text-2xl font-black text-white mt-2">Curricula, Video Streaming & Regional Workshops</h2>
                        <p class="text-xs sm:text-sm text-slate-300 mt-1">Manage 7-level education curricula, video streaming links, regional workshops, and demand-driven skills.</p>
                    </div>
                    <Link :href="route('admin.grownet.education.index')"
                        class="px-6 py-3 rounded-2xl bg-gradient-to-r from-indigo-500 to-purple-600 hover:from-indigo-400 hover:to-purple-500 text-white font-bold text-xs transition-all shadow-lg shadow-indigo-500/25 flex items-center justify-center gap-2 flex-shrink-0">
                        Launch Education Center →
                    </Link>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <Link :href="route('admin.grownet.education.index', { tab: 'curricula' })" class="p-5 rounded-2xl bg-white/5 hover:bg-white/10 border border-white/10 transition-all space-y-2 group">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-black text-indigo-300 uppercase tracking-wide">Level Curricula</span>
                            <span class="text-xs font-bold text-white bg-indigo-500/20 px-2 py-0.5 rounded border border-indigo-500/30">7 Levels</span>
                        </div>
                        <p class="text-xs text-slate-300 leading-relaxed">Setup 7-level curriculum lessons, video stream IDs, and practical exercise prompts.</p>
                    </Link>

                    <Link :href="route('admin.grownet.education.index', { tab: 'workshops' })" class="p-5 rounded-2xl bg-white/5 hover:bg-white/10 border border-white/10 transition-all space-y-2 group">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-black text-emerald-300 uppercase tracking-wide">Facilitated Workshops</span>
                            <span class="text-xs font-bold text-emerald-400 bg-emerald-500/20 px-2 py-0.5 rounded border border-emerald-500/30">Live & Physical</span>
                        </div>
                        <p class="text-xs text-slate-300 leading-relaxed">Publish live workshops, set instructors, dates, and partner institution accreditations.</p>
                    </Link>

                    <Link :href="route('admin.grownet.education.index', { tab: 'skills' })" class="p-5 rounded-2xl bg-white/5 hover:bg-white/10 border border-white/10 transition-all space-y-2 group">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-black text-purple-300 uppercase tracking-wide">Skills Training Demand</span>
                            <span class="text-xs font-bold text-purple-400 bg-purple-500/20 px-2 py-0.5 rounded border border-purple-500/30">Demand-Driven</span>
                        </div>
                        <p class="text-xs text-slate-300 leading-relaxed">Review real-time member skill requests, aggregate demand, and organize training cohorts.</p>
                    </Link>
                </div>
            </section>

            <!-- Monthly Trend & Top Earners (Interactive Slide-Over Drawer Integration) -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Monthly Trend Chart -->
                <div class="lg:col-span-2 bg-white rounded-2xl border border-gray-200 shadow-xs">
                    <div class="px-6 py-4 border-b border-gray-100">
                        <h3 class="text-sm font-black text-gray-900">Monthly Trend (6 Months)</h3>
                    </div>
                    <div class="p-6 space-y-4">
                        <div v-for="month in monthlyTrend" :key="month.month">
                            <div class="flex items-center justify-between text-xs mb-1">
                                <span class="font-bold text-gray-700">{{ month.month }}</span>
                                <span class="text-gray-900 font-black">{{ formatCurrency(month.commissions) }}</span>
                            </div>
                            <div class="w-full bg-gray-100 rounded-full h-2.5">
                                <div class="bg-indigo-600 h-2.5 rounded-full transition-all" :style="{ width: `${(month.commissions / maxCommission) * 100}%` }"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Top Earners List (Click to Open Slide-Over Drawer) -->
                <div class="bg-white rounded-2xl border border-gray-200 shadow-xs">
                    <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                        <h3 class="text-sm font-black text-gray-900">Top Member Earners</h3>
                        <span class="text-[11px] text-indigo-600 font-bold">Click row to preview &rarr;</span>
                    </div>
                    <div class="divide-y divide-gray-100">
                        <div
                            v-for="(earner, index) in topEarners"
                            :key="earner.id"
                            @click="openEarnerDrawer(earner)"
                            class="px-6 py-3 flex items-center justify-between hover:bg-indigo-50/50 cursor-pointer transition-colors"
                        >
                            <div class="flex items-center gap-3">
                                <span class="text-xs font-black text-gray-400 w-5">{{ index + 1 }}</span>
                                <div>
                                    <p class="text-xs font-bold text-gray-900 group-hover:text-indigo-600">{{ earner.name }}</p>
                                    <p class="text-[11px] text-gray-500">{{ earner.email }}</p>
                                </div>
                            </div>
                            <p class="text-xs font-black text-emerald-600">{{ formatCurrency(earner.total_earnings) }}</p>
                        </div>
                        <div v-if="topEarners.length === 0" class="px-6 py-8 text-center text-gray-500 text-xs">No earners yet</div>
                    </div>
                </div>
            </div>

            <!-- SLIDE-OVER DRAWER 1: Top Earner Quick Inspection Panel -->
            <SlideOverDrawer
                :show="showEarnerDrawer"
                title="Member Earnings Audit"
                :subtitle="selectedEarner?.name ? `Quick inspection for ${selectedEarner.name}` : ''"
                maxWidth="md"
                @close="showEarnerDrawer = false"
            >
                <div v-if="selectedEarner" class="space-y-6">
                    <div class="p-4 rounded-2xl bg-indigo-50 border border-indigo-100 space-y-2">
                        <p class="text-xs text-indigo-600 font-bold uppercase tracking-wider">Member Name</p>
                        <h4 class="text-lg font-black text-gray-900">{{ selectedEarner.name }}</h4>
                        <p class="text-xs text-gray-600">{{ selectedEarner.email }}</p>
                    </div>

                    <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-100 space-y-1">
                        <p class="text-xs text-emerald-700 font-bold uppercase tracking-wider">Total Earnings Paid</p>
                        <p class="text-2xl font-black text-emerald-800">{{ formatCurrency(selectedEarner.total_earnings) }}</p>
                    </div>

                    <div class="pt-4 border-t border-gray-100">
                        <Link
                            :href="route('admin.grownet.earnings.show', selectedEarner.id)"
                            class="w-full py-3 px-4 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-xs transition-colors flex items-center justify-center gap-2 shadow"
                        >
                            Open Full Earnings Ledger Page &rarr;
                        </Link>
                    </div>
                </div>
            </SlideOverDrawer>

            <!-- SLIDE-OVER DRAWER 2: Quick Management Flyout Drawer -->
            <SlideOverDrawer
                :show="showQuickNavDrawer"
                title="GrowNet Module Control Drawer"
                subtitle="Fast access to all GrowNet administration domains"
                maxWidth="lg"
                @close="showQuickNavDrawer = false"
            >
                <div class="space-y-4">
                    <Link :href="route('admin.grownet.education.index')" class="block p-4 rounded-2xl bg-indigo-50/50 hover:bg-indigo-50 border border-indigo-100 transition-colors space-y-1">
                        <p class="text-xs font-black text-indigo-900">🎓 Education & Workshops Control</p>
                        <p class="text-xs text-gray-600">7-level curriculum, video stream links, regional workshops & demand tracking.</p>
                    </Link>

                    <Link :href="route('admin.grownet.earnings')" class="block p-4 rounded-2xl bg-emerald-50/50 hover:bg-emerald-50 border border-emerald-100 transition-colors space-y-1">
                        <p class="text-xs font-black text-emerald-900">💰 Earnings & Payout Management</p>
                        <p class="text-xs text-gray-600">Audit commissions, adjust bonus balances & view payout records.</p>
                    </Link>

                    <Link :href="route('admin.mlm.dashboard')" class="block p-4 rounded-2xl bg-blue-50/50 hover:bg-blue-50 border border-blue-100 transition-colors space-y-1">
                        <p class="text-xs font-black text-blue-900">🌐 MLM Network & Matrix Administration</p>
                        <p class="text-xs text-gray-600">Matrix position management, network tree, and commission policies.</p>
                    </Link>

                    <Link href="/admin/points" class="block p-4 rounded-2xl bg-purple-50/50 hover:bg-purple-50 border border-purple-100 transition-colors space-y-1">
                        <p class="text-xs font-black text-purple-900">🎯 Points & Level Progression</p>
                        <p class="text-xs text-gray-600">Manage member points, level thresholds & awards.</p>
                    </Link>

                    <Link :href="route('admin.starter-kit.dashboard')" class="block p-4 rounded-2xl bg-amber-50/50 hover:bg-amber-50 border border-amber-100 transition-colors space-y-1">
                        <p class="text-xs font-black text-amber-900">📦 Starter Kit & Tiers</p>
                        <p class="text-xs text-gray-600">Product starter kits, starter kit tier levels & content packages.</p>
                    </Link>

                    <Link :href="route('admin.lgr.index')" class="block p-4 rounded-2xl bg-rose-50/50 hover:bg-rose-50 border border-rose-100 transition-colors space-y-1">
                        <p class="text-xs font-black text-rose-900">🌟 LGR Rewards & Profit Sharing</p>
                        <p class="text-xs text-gray-600">Loyalty rewards pool, profit sharing distributions & qualification rules.</p>
                    </Link>
                </div>
            </SlideOverDrawer>
        </div>
    </AdminLayout>
</template>
