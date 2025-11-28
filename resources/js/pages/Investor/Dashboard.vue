<template>
  <InvestorLayout 
    :investor="investor" 
    page-title="Dashboard" 
    active-page="dashboard"
    :unread-messages="unreadMessages"
    :unread-announcements="unreadAnnouncements"
  >
    <!-- Announcements Section - Show max 2, prioritize urgent/unread -->
    <div v-if="displayedAnnouncements.length > 0" class="mb-6">
      <div class="space-y-3">
        <AnnouncementBanner
          v-for="announcement in displayedAnnouncements"
          :key="announcement.id"
          :announcement="announcement"
          @dismissed="dismissAnnouncement"
        />
      </div>
      
      <!-- View All Link -->
      <div v-if="hasMoreAnnouncements" class="mt-3 flex items-center justify-between">
        <span class="text-sm text-gray-500">
          {{ hiddenAnnouncementsCount }} more announcement{{ hiddenAnnouncementsCount > 1 ? 's' : '' }}
        </span>
        <Link 
          :href="route('investor.announcements')" 
          class="inline-flex items-center gap-1.5 text-sm font-medium text-blue-600 hover:text-blue-700 transition-colors"
        >
          View all announcements
          <ArrowRightIcon class="h-4 w-4" aria-hidden="true" />
        </Link>
      </div>
    </div>

    <div class="mb-8">
      <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
          <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Welcome back, {{ investor.name.split(' ')[0] }}! 👋</h1>
          <p class="mt-1 text-gray-600">Here's an overview of your investment portfolio</p>
        </div>
        <div class="flex items-center gap-2 text-sm text-gray-500">
          <CalendarIcon class="h-4 w-4" aria-hidden="true" />
          <span>Invested since {{ investor.investment_date_formatted }}</span>
        </div>
      </div>
    </div>

    <div class="relative overflow-hidden bg-gradient-to-br from-blue-600 via-blue-700 to-indigo-800 rounded-3xl shadow-2xl shadow-blue-500/25 p-6 sm:p-8 mb-8">
      <div class="absolute inset-0 opacity-10">
        <svg class="w-full h-full" viewBox="0 0 100 100" preserveAspectRatio="none">
          <defs>
            <pattern id="grid" width="10" height="10" patternUnits="userSpaceOnUse">
              <path d="M 10 0 L 0 0 0 10" fill="none" stroke="white" stroke-width="0.5"></path>
            </pattern>
          </defs>
          <rect width="100" height="100" fill="url(#grid)"></rect>
        </svg>
      </div>
      <div class="absolute -top-20 -right-20 w-64 h-64 bg-white/10 rounded-full blur-3xl"></div>
      <div class="absolute -bottom-20 -left-20 w-48 h-48 bg-indigo-400/20 rounded-full blur-3xl"></div>

      <div class="relative">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
          <div class="flex-1">
            <div class="flex items-center gap-2 mb-2">
              <span class="text-blue-200 text-sm font-medium">Portfolio Value</span>
              <span :class="statusPillClass" class="px-2.5 py-0.5 rounded-full text-xs font-semibold">{{ investor.status_label }}</span>
            </div>
            
            <div v-if="investor.status === 'ciu'" class="mb-4">
              <p class="text-4xl sm:text-5xl font-bold text-white tracking-tight">K{{ formatNumber(investmentMetrics?.initial_investment || 0) }}</p>
              <p class="text-blue-200 text-sm mt-2 flex items-center gap-2">
                <ClockIcon class="h-4 w-4" aria-hidden="true" />
                CIU - Awaiting Conversion to Equity
              </p>
            </div>
            <div v-else class="mb-4">
              <p class="text-4xl sm:text-5xl font-bold text-white tracking-tight">K{{ formatNumber(investmentMetrics?.current_value || 0) }}</p>
              <div class="flex items-center gap-3 mt-2">
                <span :class="['inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-sm font-semibold', (investmentMetrics?.roi_percentage || 0) >= 0 ? 'bg-emerald-400/20 text-emerald-300' : 'bg-red-400/20 text-red-300']">
                  <ArrowTrendingUpIcon v-if="(investmentMetrics?.roi_percentage || 0) >= 0" class="h-4 w-4" aria-hidden="true" />
                  <ArrowTrendingDownIcon v-else class="h-4 w-4" aria-hidden="true" />
                  {{ (investmentMetrics?.roi_percentage || 0) >= 0 ? '+' : '' }}{{ investmentMetrics?.roi_percentage || 0 }}%
                </span>
                <span class="text-blue-200 text-sm">All-time return</span>
              </div>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 pt-4 border-t border-white/10">
              <div>
                <p class="text-blue-300 text-xs font-medium mb-1">Initial Investment</p>
                <p class="text-white font-semibold">K{{ formatNumber(investmentMetrics?.initial_investment || 0) }}</p>
              </div>
              <div>
                <p class="text-blue-300 text-xs font-medium mb-1">Equity Stake</p>
                <p v-if="investor.status === 'ciu'" class="text-blue-200 text-sm">Pending</p>
                <p v-else class="text-white font-semibold">{{ investmentMetrics?.equity_percentage || 0 }}%</p>
              </div>
              <div>
                <p class="text-blue-300 text-xs font-medium mb-1">Holding Period</p>
                <p class="text-white font-semibold">{{ investor.holding_months }} {{ investor.holding_months === 1 ? 'month' : 'months' }}</p>
              </div>
              <div>
                <p class="text-blue-300 text-xs font-medium mb-1">Valuation</p>
                <p class="text-white font-semibold">K{{ formatNumber(investmentMetrics?.current_valuation || 0) }}</p>
              </div>
            </div>
          </div>

          <div class="hidden xl:block w-64 h-32 bg-white/5 rounded-2xl backdrop-blur border border-white/10 p-4">
            <p class="text-blue-200 text-xs font-medium mb-2">Portfolio Growth</p>
            <div class="h-16 flex items-end gap-1">
              <div v-for="(height, i) in miniChartData" :key="i" class="flex-1 bg-gradient-to-t from-blue-400 to-blue-300 rounded-t opacity-80 transition-all duration-300 hover:opacity-100" :style="{ height: `${height}%` }"></div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
      <StatCard title="Investment Date" :value="formatDate(investor.investment_date)" icon="calendar" color="blue" />
      <StatCard title="Current Valuation" :value="`K${formatNumber(investmentMetrics?.current_valuation || 0)}`" icon="currency" color="emerald" />
      <StatCard title="Co-Investors" :value="String(round?.total_investors || 0)" icon="users" color="violet" />
      <StatCard title="Total Raised" :value="`K${formatNumber(round?.total_raised || 0)}`" icon="chart" color="amber" />
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6 mb-8">
      <div v-if="round" class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-lg transition-shadow duration-300">
        <div class="p-6">
          <div class="flex items-center justify-between mb-6">
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 bg-gradient-to-br from-violet-500 to-purple-600 rounded-xl flex items-center justify-center">
                <RocketLaunchIcon class="h-5 w-5 text-white" aria-hidden="true" />
              </div>
              <div>
                <h3 class="font-semibold text-gray-900">{{ round.name }}</h3>
                <p class="text-xs text-gray-500">Investment Round</p>
              </div>
            </div>
            <span :class="roundStatusBadgeClass" class="px-3 py-1 rounded-full text-xs font-semibold">{{ round.status_label }}</span>
          </div>

          <div class="mb-6">
            <div class="flex justify-between text-sm mb-2">
              <span class="text-gray-600">Fundraising Progress</span>
              <span class="font-semibold text-gray-900">{{ round.progress_percentage }}%</span>
            </div>
            <div class="relative h-3 bg-gray-100 rounded-full overflow-hidden">
              <div class="absolute inset-y-0 left-0 bg-gradient-to-r from-violet-500 to-purple-600 rounded-full transition-all duration-700 ease-out" :style="{ width: `${Math.min(round.progress_percentage, 100)}%` }"></div>
            </div>
            <div class="flex justify-between text-xs text-gray-500 mt-2">
              <span>K{{ formatNumber(round.raised_amount) }} raised</span>
              <span>K{{ formatNumber(round.goal_amount) }} goal</span>
            </div>
          </div>

          <div class="grid grid-cols-2 gap-4 pt-4 border-t border-gray-100">
            <div class="bg-gray-50 rounded-xl p-4">
              <p class="text-xs text-gray-500 font-medium mb-1">Company Valuation</p>
              <p class="text-xl font-bold text-gray-900">K{{ formatNumber(round.valuation) }}</p>
            </div>
            <div class="bg-gray-50 rounded-xl p-4">
              <p class="text-xs text-gray-500 font-medium mb-1">Total Investors</p>
              <p class="text-xl font-bold text-gray-900">{{ round.total_investors }}</p>
            </div>
          </div>

          <div v-if="investmentMetrics" class="mt-4 bg-gradient-to-r from-violet-50 to-purple-50 rounded-xl p-4 border border-violet-100">
            <div class="flex items-center gap-2 mb-1">
              <SparklesIcon class="h-4 w-4 text-violet-600" aria-hidden="true" />
              <p class="text-sm font-semibold text-violet-900">Your Position</p>
            </div>
            <p class="text-sm text-violet-700">You own <span class="font-semibold">{{ investmentMetrics.equity_percentage }}%</span> of the company, valued at <span class="font-semibold">K{{ formatNumber(investmentMetrics.current_value) }}</span></p>
          </div>
        </div>
      </div>

      <div v-if="platformMetrics" class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-lg transition-shadow duration-300">
        <div class="p-6">
          <div class="flex items-center gap-3 mb-6">
            <div class="w-10 h-10 bg-gradient-to-br from-emerald-500 to-teal-600 rounded-xl flex items-center justify-center">
              <PresentationChartLineIcon class="h-5 w-5 text-white" aria-hidden="true" />
            </div>
            <div>
              <h3 class="font-semibold text-gray-900">Platform Performance</h3>
              <p class="text-xs text-gray-500">Key business metrics</p>
            </div>
          </div>
          <div class="grid grid-cols-2 gap-4">
            <MetricCard label="Total Members" :value="formatNumber(platformMetrics.total_members)" trend="+12%" trend-up icon="users" />
            <MetricCard label="Monthly Revenue" :value="`K${formatNumber(platformMetrics.monthly_revenue)}`" trend="+8%" trend-up icon="currency" />
            <MetricCard label="Active Rate" :value="`${platformMetrics.active_rate}%`" trend="+3%" trend-up icon="activity" />
            <MetricCard label="Retention" :value="`${platformMetrics.retention_rate}%`" trend="+5%" trend-up icon="refresh" />
          </div>
        </div>
      </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6 mb-8">
      <FinancialSummaryCard :financial-summary="financialSummary" />
      <PerformanceChart :performance-metrics="performanceMetrics" />
    </div>

    <!-- Quick Access Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
      <!-- Legal Documents -->
      <Link :href="route('investor.legal-documents')" class="group bg-gradient-to-br from-blue-50 to-indigo-50 border border-blue-100 rounded-2xl p-6 hover:shadow-lg transition-all duration-300">
        <div class="flex items-start gap-4">
          <div class="w-12 h-12 bg-blue-600 rounded-xl flex items-center justify-center flex-shrink-0 group-hover:scale-110 transition-transform">
            <DocumentTextIcon class="h-6 w-6 text-white" aria-hidden="true" />
          </div>
          <div class="flex-1">
            <h3 class="font-semibold text-blue-900 mb-1">Legal Documents</h3>
            <p class="text-sm text-blue-700 mb-3">Share certificates, agreements, and compliance documents</p>
            <span class="inline-flex items-center gap-1 text-sm font-medium text-blue-600 group-hover:gap-2 transition-all">
              View Documents
              <ArrowRightIcon class="h-4 w-4" aria-hidden="true" />
            </span>
          </div>
        </div>
      </Link>

      <!-- Dividends -->
      <Link :href="route('investor.dividends')" class="group bg-gradient-to-br from-emerald-50 to-teal-50 border border-emerald-100 rounded-2xl p-6 hover:shadow-lg transition-all duration-300">
        <div class="flex items-start gap-4">
          <div class="w-12 h-12 bg-emerald-600 rounded-xl flex items-center justify-center flex-shrink-0 group-hover:scale-110 transition-transform">
            <BanknotesIcon class="h-6 w-6 text-white" aria-hidden="true" />
          </div>
          <div class="flex-1">
            <h3 class="font-semibold text-emerald-900 mb-1">Dividends</h3>
            <p class="text-sm text-emerald-700 mb-3">Payment history, upcoming distributions, and tax information</p>
            <span class="inline-flex items-center gap-1 text-sm font-medium text-emerald-600 group-hover:gap-2 transition-all">
              View Dividends
              <ArrowRightIcon class="h-4 w-4" aria-hidden="true" />
            </span>
          </div>
        </div>
      </Link>

      <!-- Investor Relations -->
      <Link :href="route('investor.investor-relations')" class="group bg-gradient-to-br from-violet-50 to-purple-50 border border-violet-100 rounded-2xl p-6 hover:shadow-lg transition-all duration-300">
        <div class="flex items-start gap-4">
          <div class="w-12 h-12 bg-violet-600 rounded-xl flex items-center justify-center flex-shrink-0 group-hover:scale-110 transition-transform">
            <NewspaperIcon class="h-6 w-6 text-white" aria-hidden="true" />
          </div>
          <div class="flex-1">
            <h3 class="font-semibold text-violet-900 mb-1">Investor Relations</h3>
            <p class="text-sm text-violet-700 mb-3">Quarterly reports, board updates, and meeting notices</p>
            <span class="inline-flex items-center gap-1 text-sm font-medium text-violet-600 group-hover:gap-2 transition-all">
              View Updates
              <ArrowRightIcon class="h-4 w-4" aria-hidden="true" />
            </span>
          </div>
        </div>
      </Link>
    </div>
  </InvestorLayout>
</template>

<script setup lang="ts">
import { computed, ref } from 'vue';
import { Link } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import InvestorLayout from '@/layouts/InvestorLayout.vue';
import AnnouncementBanner from '@/components/Investor/AnnouncementBanner.vue';
import FinancialSummaryCard from '@/components/Investor/FinancialSummaryCard.vue';
import PerformanceChart from '@/components/Investor/PerformanceChart.vue';
import StatCard from '@/components/Investor/StatCard.vue';
import MetricCard from '@/components/Investor/MetricCard.vue';
import {
  CalendarIcon,
  ClockIcon,
  ArrowTrendingUpIcon,
  ArrowTrendingDownIcon,
  RocketLaunchIcon,
  SparklesIcon,
  PresentationChartLineIcon,
  InformationCircleIcon,
  ArrowRightIcon,
  DocumentTextIcon,
  BanknotesIcon,
  NewspaperIcon,
} from '@heroicons/vue/24/outline';


interface Investor {
  id: number;
  name: string;
  email: string;
  investment_amount: number;
  investment_date: string;
  investment_date_formatted: string;
  status: string;
  status_label: string;
  equity_percentage: number;
  holding_days: number;
  holding_months: number;
}

interface InvestmentMetrics {
  initial_investment: number;
  current_value: number;
  roi_percentage: number;
  equity_percentage: number;
  valuation_at_investment: number;
  current_valuation: number;
}

interface InvestmentRound {
  id: number;
  name: string;
  valuation: number;
  goal_amount: number;
  raised_amount: number;
  progress_percentage: number;
  total_investors: number;
  total_raised: number;
  status: string;
  status_label: string;
}

interface PlatformMetrics {
  total_members: number;
  monthly_revenue: number;
  active_rate: number;
  retention_rate: number;
}

interface Announcement {
  id: number;
  title: string;
  message: string;
  type: 'info' | 'warning' | 'success' | 'urgent';
  is_urgent: boolean;
  created_at: string;
  created_at_human: string;
}

interface FinancialSummary {
  latest_period: string;
  latest_period_display: string;
  total_revenue: number;
  total_expenses: number;
  net_profit: number;
  profit_margin: number;
  growth_rate: number | null;
  health_score: number;
  health_score_label: string;
  health_score_color: string;
  report_date_formatted: string;
  revenue_breakdown: Array<{ source: string; amount: number; percentage: number }>;
}

interface PerformanceMetrics {
  revenue_trend: { labels: string[]; data: number[] };
  profit_trend: { labels: string[]; data: number[] };
  member_growth: { labels: string[]; data: number[] };
  health_score_trend: { labels: string[]; data: number[] };
}

const props = defineProps<{
  investor: Investor;
  investmentMetrics?: InvestmentMetrics;
  round?: InvestmentRound | null;
  platformMetrics?: PlatformMetrics;
  announcements?: Announcement[];
  financialSummary?: FinancialSummary | null;
  performanceMetrics?: PerformanceMetrics | null;
  unreadMessagesCount?: number;
}>();

const dismissedAnnouncements = ref<number[]>([]);
const MAX_DASHBOARD_ANNOUNCEMENTS = 2;

// Filter out dismissed announcements
const visibleAnnouncements = computed(() => {
  if (!props.announcements) return [];
  return props.announcements.filter(a => !dismissedAnnouncements.value.includes(a.id));
});

// Smart announcement display: prioritize urgent, then by recency, limit to 2
const displayedAnnouncements = computed(() => {
  const visible = visibleAnnouncements.value;
  if (visible.length === 0) return [];
  
  // Sort: urgent first, then by date (newest first)
  const sorted = [...visible].sort((a, b) => {
    // Urgent announcements first
    if (a.is_urgent && !b.is_urgent) return -1;
    if (!a.is_urgent && b.is_urgent) return 1;
    // Then by type priority (urgent > warning > info > success)
    const typePriority: Record<string, number> = { urgent: 0, warning: 1, info: 2, success: 3 };
    const aPriority = typePriority[a.type] ?? 2;
    const bPriority = typePriority[b.type] ?? 2;
    if (aPriority !== bPriority) return aPriority - bPriority;
    // Then by date (newest first)
    return new Date(b.created_at).getTime() - new Date(a.created_at).getTime();
  });
  
  return sorted.slice(0, MAX_DASHBOARD_ANNOUNCEMENTS);
});

// Check if there are more announcements than displayed
const hasMoreAnnouncements = computed(() => {
  return visibleAnnouncements.value.length > MAX_DASHBOARD_ANNOUNCEMENTS;
});

// Count of hidden announcements
const hiddenAnnouncementsCount = computed(() => {
  return Math.max(0, visibleAnnouncements.value.length - MAX_DASHBOARD_ANNOUNCEMENTS);
});

const dismissAnnouncement = (id: number) => {
  dismissedAnnouncements.value.push(id);
};

const miniChartData = computed(() => {
  if (props.performanceMetrics?.revenue_trend?.data?.length) {
    const data = props.performanceMetrics.revenue_trend.data;
    const max = Math.max(...data);
    return data.slice(-8).map(v => max > 0 ? (v / max) * 100 : 50);
  }
  return [40, 55, 45, 60, 75, 65, 80, 90];
});

const formatNumber = (value: number): string => {
  return new Intl.NumberFormat('en-US').format(value);
};

const formatDate = (date: string): string => {
  return new Date(date).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' });
};

const statusPillClass = computed(() => {
  const classes: Record<string, string> = {
    ciu: 'bg-blue-400/30 text-blue-100',
    shareholder: 'bg-emerald-400/30 text-emerald-100',
    exited: 'bg-gray-400/30 text-gray-100',
  };
  return classes[props.investor.status] || classes.ciu;
});

const roundStatusBadgeClass = computed(() => {
  if (!props.round) return '';
  const classes: Record<string, string> = {
    active: 'bg-emerald-100 text-emerald-700',
    closed: 'bg-gray-100 text-gray-700',
    upcoming: 'bg-blue-100 text-blue-700',
  };
  return classes[props.round.status] || classes.active;
});
</script>
