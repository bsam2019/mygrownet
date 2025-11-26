<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <header class="bg-white shadow-sm border-b border-gray-200">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
        <div class="flex justify-between items-center">
          <div>
            <div class="flex items-center gap-3">
              <div class="w-10 h-10 bg-gradient-to-br from-blue-600 to-blue-700 rounded-lg flex items-center justify-center">
                <span class="text-white font-bold text-lg">{{ getInitials(investor.name) }}</span>
              </div>
              <div>
                <h1 class="text-xl font-bold text-gray-900">{{ investor.name }}</h1>
                <p class="text-sm text-gray-600">Investor Portal</p>
              </div>
            </div>
          </div>
          <div class="flex items-center gap-3">
            <Link
              :href="route('investor.documents')"
              class="inline-flex items-center px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-lg transition-colors"
            >
              <DocumentTextIcon class="h-5 w-5 mr-2" aria-hidden="true" />
              Documents
            </Link>
            <Link
              :href="route('investor.reports')"
              class="inline-flex items-center px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-lg transition-colors"
            >
              <ChartBarIcon class="h-5 w-5 mr-2" aria-hidden="true" />
              Reports
            </Link>
            <Link
              :href="route('investor.messages')"
              class="inline-flex items-center px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-lg transition-colors relative"
            >
              <ChatBubbleLeftRightIcon class="h-5 w-5 mr-2" aria-hidden="true" />
              Messages
              <span v-if="unreadMessagesCount > 0" class="absolute -top-1 -right-1 w-5 h-5 bg-red-500 text-white text-xs rounded-full flex items-center justify-center">
                {{ unreadMessagesCount > 9 ? '9+' : unreadMessagesCount }}
              </span>
            </Link>
            <Link
              :href="route('investor.settings')"
              class="inline-flex items-center px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-lg transition-colors"
            >
              <Cog6ToothIcon class="h-5 w-5 mr-2" aria-hidden="true" />
              Settings
            </Link>
            <Link
              :href="route('investor.logout')"
              method="post"
              as="button"
              class="inline-flex items-center px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 transition-colors"
            >
              Logout
            </Link>
          </div>
        </div>
      </div>
    </header>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Announcements -->
      <div v-if="announcements && announcements.length > 0" class="mb-8 space-y-4">
        <AnnouncementBanner
          v-for="announcement in visibleAnnouncements"
          :key="announcement.id"
          :announcement="announcement"
          @dismissed="dismissAnnouncement"
        />
      </div>

      <!-- Welcome Banner -->
      <div class="bg-gradient-to-r from-blue-600 to-purple-600 rounded-2xl shadow-xl p-6 mb-8 text-white">
        <div class="flex items-center justify-between">
          <div>
            <h2 class="text-2xl font-bold mb-2">Welcome back, {{ investor.name }}!</h2>
            <p class="text-blue-100">
              You've been invested for {{ investor.holding_months }} months since {{ investor.investment_date_formatted }}
            </p>
          </div>
        </div>
      </div>

      <!-- Investment Summary Card -->
      <div v-if="investmentMetrics" class="bg-gradient-to-br from-blue-600 to-blue-700 rounded-2xl shadow-xl p-6 text-white mb-8">
        <div class="flex items-center justify-between mb-4">
          <h3 class="text-lg font-semibold">Your Investment</h3>
          <span :class="statusClass" class="px-3 py-1 rounded-full text-xs font-medium">
            {{ investor.status_label }}
          </span>
        </div>

        <div class="space-y-4">
          <div>
            <p class="text-blue-100 text-sm mb-1">Current Value</p>
            <p v-if="investor.status === 'ciu'" class="text-3xl font-bold">
              K{{ formatNumber(investmentMetrics.initial_investment) }}
              <span class="text-sm text-blue-200 block mt-1">CIU - Awaiting Conversion</span>
            </p>
            <p v-else class="text-3xl font-bold">K{{ formatNumber(investmentMetrics.current_value) }}</p>
          </div>

          <div class="grid grid-cols-2 gap-4 pt-4 border-t border-blue-500">
            <div>
              <p class="text-blue-100 text-xs mb-1">Initial Investment</p>
              <p class="text-lg font-semibold">K{{ formatNumber(investmentMetrics.initial_investment) }}</p>
            </div>
            <div>
              <p class="text-blue-100 text-xs mb-1">Return (ROI)</p>
              <p v-if="investor.status === 'ciu'" class="text-sm font-semibold text-blue-200">
                Pending Valuation
              </p>
              <p v-else class="text-lg font-semibold" :class="roiClass">
                {{ investmentMetrics.roi_percentage >= 0 ? '+' : '' }}{{ investmentMetrics.roi_percentage }}%
              </p>
            </div>
          </div>

          <div class="grid grid-cols-2 gap-4">
            <div>
              <p class="text-blue-100 text-xs mb-1">Equity Ownership</p>
              <p v-if="investor.status === 'ciu'" class="text-lg font-semibold">
                <span class="text-sm">Pending Conversion</span>
              </p>
              <p v-else class="text-lg font-semibold">{{ investmentMetrics.equity_percentage }}%</p>
            </div>
            <div>
              <p class="text-blue-100 text-xs mb-1">Holding Period</p>
              <p class="text-lg font-semibold">{{ investor.holding_months }} {{ investor.holding_months === 1 ? 'month' : 'months' }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Two Column Layout -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
        <!-- Investment Round Card -->
        <div v-if="round" class="bg-white rounded-xl shadow-lg p-6">
          <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-900">{{ round.name }}</h3>
            <span :class="roundStatusClass" class="px-3 py-1 rounded-full text-xs font-medium">
              {{ round.status_label }}
            </span>
          </div>

          <div class="space-y-4">
            <div>
              <div class="flex justify-between text-sm mb-2">
                <span class="text-gray-600">Fundraising Progress</span>
                <span class="font-semibold text-gray-900">{{ round.progress_percentage }}%</span>
              </div>
              <div class="w-full bg-gray-200 rounded-full h-3">
                <div 
                  class="bg-gradient-to-r from-blue-500 to-blue-600 h-3 rounded-full transition-all duration-500"
                  :style="{ width: `${Math.min(round.progress_percentage, 100)}%` }"
                ></div>
              </div>
              <div class="flex justify-between text-xs text-gray-500 mt-1">
                <span>K{{ formatNumber(round.raised_amount) }} raised</span>
                <span>K{{ formatNumber(round.goal_amount) }} goal</span>
              </div>
            </div>

            <div class="grid grid-cols-2 gap-4 pt-4 border-t border-gray-200">
              <div>
                <p class="text-xs text-gray-500 mb-1">Company Valuation</p>
                <p class="text-lg font-bold text-gray-900">K{{ formatNumber(round.valuation) }}</p>
              </div>
              <div>
                <p class="text-xs text-gray-500 mb-1">Total Investors</p>
                <p class="text-lg font-bold text-gray-900">{{ round.total_investors }}</p>
              </div>
            </div>

            <div v-if="investmentMetrics" class="bg-blue-50 rounded-lg p-4">
              <p class="text-sm font-medium text-blue-900">Your Position</p>
              <p class="text-xs text-blue-700 mt-1">
                You own {{ investmentMetrics.equity_percentage }}% of the company, valued at K{{ formatNumber(investmentMetrics.current_value) }}
              </p>
            </div>
          </div>
        </div>

        <!-- Platform Metrics Card -->
        <div v-if="platformMetrics" class="bg-white rounded-xl shadow-lg p-6">
          <h3 class="text-lg font-semibold text-gray-900 mb-6">Platform Performance</h3>

          <div class="grid grid-cols-2 gap-4 mb-6">
            <div class="bg-gray-50 rounded-lg p-4">
              <p class="text-xs text-gray-500 font-medium mb-2">Total Members</p>
              <p class="text-2xl font-bold text-gray-900">{{ formatNumber(platformMetrics.total_members) }}</p>
            </div>

            <div class="bg-gray-50 rounded-lg p-4">
              <p class="text-xs text-gray-500 font-medium mb-2">Monthly Revenue</p>
              <p class="text-2xl font-bold text-gray-900">K{{ formatNumber(platformMetrics.monthly_revenue) }}</p>
            </div>

            <div class="bg-gray-50 rounded-lg p-4">
              <p class="text-xs text-gray-500 font-medium mb-2">Active Rate</p>
              <p class="text-2xl font-bold text-gray-900">{{ platformMetrics.active_rate }}%</p>
            </div>

            <div class="bg-gray-50 rounded-lg p-4">
              <p class="text-xs text-gray-500 font-medium mb-2">Retention</p>
              <p class="text-2xl font-bold text-gray-900">{{ platformMetrics.retention_rate }}%</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Financial Performance Section -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
        <!-- Financial Summary Card -->
        <FinancialSummaryCard :financial-summary="financialSummary" />
        
        <!-- Performance Chart -->
        <PerformanceChart :performance-metrics="performanceMetrics" />
      </div>

      <!-- Quick Stats Grid -->
      <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-xl shadow-lg p-6">
          <p class="text-sm text-gray-600 font-medium mb-2">Investment Date</p>
          <p class="text-lg font-bold text-gray-900">{{ formatDate(investor.investment_date) }}</p>
        </div>

        <div class="bg-white rounded-xl shadow-lg p-6">
          <p class="text-sm text-gray-600 font-medium mb-2">Valuation</p>
          <p class="text-lg font-bold text-gray-900">K{{ formatNumber(investmentMetrics?.current_valuation || 0) }}</p>
        </div>

        <div class="bg-white rounded-xl shadow-lg p-6">
          <p class="text-sm text-gray-600 font-medium mb-2">Co-Investors</p>
          <p class="text-lg font-bold text-gray-900">{{ round?.total_investors || 0 }}</p>
        </div>

        <div class="bg-white rounded-xl shadow-lg p-6">
          <p class="text-sm text-gray-600 font-medium mb-2">Total Raised</p>
          <p class="text-lg font-bold text-gray-900">K{{ formatNumber(round?.total_raised || 0) }}</p>
        </div>
      </div>

      <!-- Info Banner -->
      <div class="bg-blue-50 border border-blue-200 rounded-xl p-6">
        <div class="flex items-start gap-4">
          <div>
            <h3 class="text-lg font-semibold text-blue-900 mb-2">Stay Informed</h3>
            <p class="text-sm text-blue-800 mb-3">
              We'll keep you updated on company performance, financial reports, and important announcements. 
              Check the Documents section for quarterly reports and legal documents.
            </p>
            <Link
              :href="route('investor.documents')"
              class="inline-flex items-center text-sm font-medium text-blue-600 hover:text-blue-700"
            >
              View Documents →
            </Link>
          </div>
        </div>
      </div>
    </main>
  </div>
</template>

<script setup lang="ts">
import { computed, ref } from 'vue';
import { Link } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import AnnouncementBanner from '@/components/Investor/AnnouncementBanner.vue';
import FinancialSummaryCard from '@/components/Investor/FinancialSummaryCard.vue';
import PerformanceChart from '@/components/Investor/PerformanceChart.vue';
import { DocumentTextIcon, ChartBarIcon, ChatBubbleLeftRightIcon, Cog6ToothIcon } from '@heroicons/vue/24/outline';

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
  revenue_growth: {
    labels: string[];
    data: number[];
  };
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
  growth_status: string;
  health_score: number;
  health_score_label: string;
  health_score_color: string;
  report_date_formatted: string;
  revenue_breakdown: Array<{
    source: string;
    amount: number;
    percentage: number;
  }>;
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

// Announcement state
const dismissedAnnouncements = ref<number[]>([]);

const visibleAnnouncements = computed(() => {
  if (!props.announcements) return [];
  return props.announcements.filter(a => !dismissedAnnouncements.value.includes(a.id));
});

const dismissAnnouncement = (id: number) => {
  dismissedAnnouncements.value.push(id);
};

const formatNumber = (value: number): string => {
  return new Intl.NumberFormat('en-US', {
    minimumFractionDigits: 0,
    maximumFractionDigits: 0,
  }).format(value);
};

const formatDate = (date: string): string => {
  return new Date(date).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
  });
};

const getInitials = (name: string): string => {
  return name
    .split(' ')
    .map(n => n[0])
    .join('')
    .toUpperCase()
    .substring(0, 2);
};

const statusClass = computed(() => {
  const classes = {
    ciu: 'bg-blue-400 text-blue-900',
    shareholder: 'bg-green-400 text-green-900',
    exited: 'bg-gray-400 text-gray-900',
  };
  return classes[props.investor.status as keyof typeof classes] || classes.ciu;
});

const roundStatusClass = computed(() => {
  if (!props.round) return '';
  const classes = {
    active: 'bg-green-100 text-green-800',
    closed: 'bg-gray-100 text-gray-800',
    upcoming: 'bg-blue-100 text-blue-800',
  };
  return classes[props.round.status as keyof typeof classes] || classes.active;
});

const roiClass = computed(() => {
  return props.investmentMetrics.roi_percentage >= 0 ? 'text-green-300' : 'text-red-300';
});
</script>
