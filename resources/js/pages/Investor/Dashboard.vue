<template>
  <div class="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50/30 to-indigo-50/50">
    <!-- Sidebar Navigation -->
    <aside class="fixed inset-y-0 left-0 w-72 bg-white/80 backdrop-blur-xl border-r border-gray-200/50 z-40 hidden lg:block">
      <!-- Logo & Brand -->
      <div class="h-20 flex items-center px-6 border-b border-gray-100">
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 bg-gradient-to-br from-blue-600 to-indigo-600 rounded-xl flex items-center justify-center shadow-lg shadow-blue-500/25">
            <BuildingOffice2Icon class="h-5 w-5 text-white" aria-hidden="true" />
          </div>
          <div>
            <h1 class="text-lg font-bold text-gray-900">Investor Portal</h1>
            <p class="text-xs text-gray-500">MyGrowNet</p>
          </div>
        </div>
      </div>

      <!-- User Profile Card -->
      <div class="p-4">
        <div class="bg-gradient-to-br from-blue-600 to-indigo-700 rounded-2xl p-4 text-white">
          <div class="flex items-center gap-3 mb-3">
            <div class="w-12 h-12 bg-white/20 backdrop-blur rounded-xl flex items-center justify-center ring-2 ring-white/30">
              <span class="text-lg font-bold">{{ getInitials(investor.name) }}</span>
            </div>
            <div class="flex-1 min-w-0">
              <p class="font-semibold truncate">{{ investor.name }}</p>
              <p class="text-xs text-blue-200 truncate">{{ investor.email }}</p>
            </div>
          </div>
          <div class="flex items-center justify-between text-xs">
            <span class="text-blue-200">Status</span>
            <span :class="statusBadgeClass" class="px-2 py-0.5 rounded-full font-medium">
              {{ investor.status_label }}
            </span>
          </div>
        </div>
      </div>

      <!-- Navigation Menu -->
      <nav class="px-3 py-2">
        <div class="space-y-1">
          <button
            class="w-full flex items-center gap-3 px-4 py-3 text-sm font-medium text-white bg-gradient-to-r from-blue-600 to-indigo-600 rounded-xl shadow-md shadow-blue-500/25"
          >
            <HomeIcon class="h-5 w-5" aria-hidden="true" />
            Dashboard
          </button>
          
          <Link
            :href="route('investor.documents')"
            class="w-full flex items-center gap-3 px-4 py-3 text-sm font-medium text-gray-700 hover:bg-gray-100 rounded-xl transition-all duration-200"
          >
            <DocumentTextIcon class="h-5 w-5 text-gray-400" aria-hidden="true" />
            Documents
          </Link>
          
          <Link
            :href="route('investor.reports')"
            class="w-full flex items-center gap-3 px-4 py-3 text-sm font-medium text-gray-700 hover:bg-gray-100 rounded-xl transition-all duration-200"
          >
            <ChartBarIcon class="h-5 w-5 text-gray-400" aria-hidden="true" />
            Reports
          </Link>
          
          <Link
            :href="route('investor.messages')"
            class="w-full flex items-center gap-3 px-4 py-3 text-sm font-medium text-gray-700 hover:bg-gray-100 rounded-xl transition-all duration-200 relative"
          >
            <ChatBubbleLeftRightIcon class="h-5 w-5 text-gray-400" aria-hidden="true" />
            Messages
            <span v-if="unreadMessagesCount > 0" class="ml-auto w-5 h-5 bg-red-500 text-white text-xs rounded-full flex items-center justify-center">
              {{ unreadMessagesCount > 9 ? '9+' : unreadMessagesCount }}
            </span>
          </Link>
          
          <Link
            :href="route('investor.announcements')"
            class="w-full flex items-center gap-3 px-4 py-3 text-sm font-medium text-gray-700 hover:bg-gray-100 rounded-xl transition-all duration-200"
          >
            <MegaphoneIcon class="h-5 w-5 text-gray-400" aria-hidden="true" />
            Announcements
          </Link>
          
          <Link
            :href="route('investor.settings')"
            class="w-full flex items-center gap-3 px-4 py-3 text-sm font-medium text-gray-700 hover:bg-gray-100 rounded-xl transition-all duration-200"
          >
            <Cog6ToothIcon class="h-5 w-5 text-gray-400" aria-hidden="true" />
            Settings
          </Link>
        </div>
      </nav>

      <!-- Logout Button -->
      <div class="absolute bottom-0 left-0 right-0 p-4 border-t border-gray-100 bg-white/50">
        <Link
          :href="route('investor.logout')"
          method="post"
          as="button"
          class="w-full flex items-center justify-center gap-2 px-4 py-3 text-sm font-medium text-gray-600 hover:text-red-600 hover:bg-red-50 rounded-xl transition-all duration-200"
        >
          <ArrowRightOnRectangleIcon class="h-5 w-5" aria-hidden="true" />
          Sign Out
        </Link>
      </div>
    </aside>

    <!-- Mobile Header -->
    <header class="lg:hidden sticky top-0 z-30 bg-white/80 backdrop-blur-xl border-b border-gray-200/50">
      <div class="flex items-center justify-between px-4 py-3">
        <div class="flex items-center gap-3">
          <div class="w-9 h-9 bg-gradient-to-br from-blue-600 to-indigo-600 rounded-lg flex items-center justify-center">
            <BuildingOffice2Icon class="h-5 w-5 text-white" aria-hidden="true" />
          </div>
          <span class="font-semibold text-gray-900">Investor Portal</span>
        </div>
        <button
          @click="mobileMenuOpen = !mobileMenuOpen"
          class="p-2 text-gray-600 hover:bg-gray-100 rounded-lg"
          :aria-label="mobileMenuOpen ? 'Close menu' : 'Open menu'"
        >
          <Bars3Icon v-if="!mobileMenuOpen" class="h-6 w-6" aria-hidden="true" />
          <XMarkIcon v-else class="h-6 w-6" aria-hidden="true" />
        </button>
      </div>
    </header>

    <!-- Mobile Menu Overlay -->
    <Transition
      enter-active-class="transition-opacity duration-200"
      enter-from-class="opacity-0"
      enter-to-class="opacity-100"
      leave-active-class="transition-opacity duration-200"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <div v-if="mobileMenuOpen" class="lg:hidden fixed inset-0 z-40 bg-black/50" @click="mobileMenuOpen = false"></div>
    </Transition>

    <!-- Mobile Menu Slide-in -->
    <Transition
      enter-active-class="transition-transform duration-300"
      enter-from-class="-translate-x-full"
      enter-to-class="translate-x-0"
      leave-active-class="transition-transform duration-300"
      leave-from-class="translate-x-0"
      leave-to-class="-translate-x-full"
    >
      <aside v-if="mobileMenuOpen" class="lg:hidden fixed inset-y-0 left-0 w-72 bg-white z-50 shadow-2xl">
        <!-- Same content as desktop sidebar -->
        <div class="h-16 flex items-center justify-between px-4 border-b border-gray-100">
          <span class="font-semibold text-gray-900">Menu</span>
          <button @click="mobileMenuOpen = false" class="p-2 hover:bg-gray-100 rounded-lg" aria-label="Close menu">
            <XMarkIcon class="h-5 w-5 text-gray-600" aria-hidden="true" />
          </button>
        </div>
        <nav class="p-3 space-y-1">
          <Link :href="route('investor.dashboard')" class="flex items-center gap-3 px-4 py-3 text-sm font-medium text-white bg-gradient-to-r from-blue-600 to-indigo-600 rounded-xl">
            <HomeIcon class="h-5 w-5" aria-hidden="true" />
            Dashboard
          </Link>
          <Link :href="route('investor.documents')" class="flex items-center gap-3 px-4 py-3 text-sm font-medium text-gray-700 hover:bg-gray-100 rounded-xl">
            <DocumentTextIcon class="h-5 w-5 text-gray-400" aria-hidden="true" />
            Documents
          </Link>
          <Link :href="route('investor.reports')" class="flex items-center gap-3 px-4 py-3 text-sm font-medium text-gray-700 hover:bg-gray-100 rounded-xl">
            <ChartBarIcon class="h-5 w-5 text-gray-400" aria-hidden="true" />
            Reports
          </Link>
          <Link :href="route('investor.messages')" class="flex items-center gap-3 px-4 py-3 text-sm font-medium text-gray-700 hover:bg-gray-100 rounded-xl relative">
            <ChatBubbleLeftRightIcon class="h-5 w-5 text-gray-400" aria-hidden="true" />
            Messages
            <span v-if="unreadMessagesCount > 0" class="ml-auto w-5 h-5 bg-red-500 text-white text-xs rounded-full flex items-center justify-center">
              {{ unreadMessagesCount > 9 ? '9+' : unreadMessagesCount }}
            </span>
          </Link>
          <Link :href="route('investor.settings')" class="flex items-center gap-3 px-4 py-3 text-sm font-medium text-gray-700 hover:bg-gray-100 rounded-xl">
            <Cog6ToothIcon class="h-5 w-5 text-gray-400" aria-hidden="true" />
            Settings
          </Link>
          <Link :href="route('investor.logout')" method="post" as="button" class="w-full flex items-center gap-3 px-4 py-3 text-sm font-medium text-red-600 hover:bg-red-50 rounded-xl">
            <ArrowRightOnRectangleIcon class="h-5 w-5" aria-hidden="true" />
            Sign Out
          </Link>
        </nav>
      </aside>
    </Transition>

    <!-- Main Content Area -->
    <main class="lg:pl-72">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 lg:py-8">
        <!-- Announcements -->
        <div v-if="announcements && announcements.length > 0" class="mb-6 space-y-3">
          <AnnouncementBanner
            v-for="announcement in visibleAnnouncements"
            :key="announcement.id"
            :announcement="announcement"
            @dismissed="dismissAnnouncement"
          />
        </div>

        <!-- Welcome Section -->
        <div class="mb-8">
          <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
              <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">
                Welcome back, {{ investor.name.split(' ')[0] }}! 👋
              </h1>
              <p class="mt-1 text-gray-600">
                Here's an overview of your investment portfolio
              </p>
            </div>
            <div class="flex items-center gap-2 text-sm text-gray-500">
              <CalendarIcon class="h-4 w-4" aria-hidden="true" />
              <span>Invested since {{ investor.investment_date_formatted }}</span>
            </div>
          </div>
        </div>

        <!-- Portfolio Value Hero Card -->
        <div class="relative overflow-hidden bg-gradient-to-br from-blue-600 via-blue-700 to-indigo-800 rounded-3xl shadow-2xl shadow-blue-500/25 p-6 sm:p-8 mb-8">
          <!-- Background Pattern -->
          <div class="absolute inset-0 opacity-10">
            <svg class="w-full h-full" viewBox="0 0 100 100" preserveAspectRatio="none">
              <defs>
                <pattern id="grid" width="10" height="10" patternUnits="userSpaceOnUse">
                  <path d="M 10 0 L 0 0 0 10" fill="none" stroke="white" stroke-width="0.5"/>
                </pattern>
              </defs>
              <rect width="100" height="100" fill="url(#grid)" />
            </svg>
          </div>
          
          <!-- Floating Orbs -->
          <div class="absolute -top-20 -right-20 w-64 h-64 bg-white/10 rounded-full blur-3xl"></div>
          <div class="absolute -bottom-20 -left-20 w-48 h-48 bg-indigo-400/20 rounded-full blur-3xl"></div>

          <div class="relative">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
              <!-- Left: Portfolio Value -->
              <div class="flex-1">
                <div class="flex items-center gap-2 mb-2">
                  <span class="text-blue-200 text-sm font-medium">Portfolio Value</span>
                  <span :class="statusPillClass" class="px-2.5 py-0.5 rounded-full text-xs font-semibold">
                    {{ investor.status_label }}
                  </span>
                </div>
                
                <div v-if="investor.status === 'ciu'" class="mb-4">
                  <p class="text-4xl sm:text-5xl font-bold text-white tracking-tight">
                    K{{ formatNumber(investmentMetrics?.initial_investment || 0) }}
                  </p>
                  <p class="text-blue-200 text-sm mt-2 flex items-center gap-2">
                    <ClockIcon class="h-4 w-4" aria-hidden="true" />
                    CIU - Awaiting Conversion to Equity
                  </p>
                </div>
                <div v-else class="mb-4">
                  <p class="text-4xl sm:text-5xl font-bold text-white tracking-tight">
                    K{{ formatNumber(investmentMetrics?.current_value || 0) }}
                  </p>
                  <div class="flex items-center gap-3 mt-2">
                    <span 
                      :class="[
                        'inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-sm font-semibold',
                        (investmentMetrics?.roi_percentage || 0) >= 0 
                          ? 'bg-emerald-400/20 text-emerald-300' 
                          : 'bg-red-400/20 text-red-300'
                      ]"
                    >
                      <ArrowTrendingUpIcon v-if="(investmentMetrics?.roi_percentage || 0) >= 0" class="h-4 w-4" aria-hidden="true" />
                      <ArrowTrendingDownIcon v-else class="h-4 w-4" aria-hidden="true" />
                      {{ (investmentMetrics?.roi_percentage || 0) >= 0 ? '+' : '' }}{{ investmentMetrics?.roi_percentage || 0 }}%
                    </span>
                    <span class="text-blue-200 text-sm">All-time return</span>
                  </div>
                </div>

                <!-- Quick Stats Row -->
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

              <!-- Right: Mini Chart Placeholder -->
              <div class="hidden xl:block w-64 h-32 bg-white/5 rounded-2xl backdrop-blur border border-white/10 p-4">
                <p class="text-blue-200 text-xs font-medium mb-2">Portfolio Growth</p>
                <div class="h-16 flex items-end gap-1">
                  <div v-for="(height, i) in miniChartData" :key="i" 
                    class="flex-1 bg-gradient-to-t from-blue-400 to-blue-300 rounded-t opacity-80 transition-all duration-300 hover:opacity-100"
                    :style="{ height: `${height}%` }"
                  ></div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Stats Grid -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
          <StatCard
            title="Investment Date"
            :value="formatDate(investor.investment_date)"
            icon="calendar"
            color="blue"
          />
          <StatCard
            title="Current Valuation"
            :value="`K${formatNumber(investmentMetrics?.current_valuation || 0)}`"
            icon="currency"
            color="emerald"
          />
          <StatCard
            title="Co-Investors"
            :value="String(round?.total_investors || 0)"
            icon="users"
            color="violet"
          />
          <StatCard
            title="Total Raised"
            :value="`K${formatNumber(round?.total_raised || 0)}`"
            icon="chart"
            color="amber"
          />
        </div>

        <!-- Two Column Layout -->
        <div class="grid grid-cols-1 xl:grid-cols-2 gap-6 mb-8">
          <!-- Investment Round Card -->
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
                <span :class="roundStatusBadgeClass" class="px-3 py-1 rounded-full text-xs font-semibold">
                  {{ round.status_label }}
                </span>
              </div>

              <!-- Progress Bar -->
              <div class="mb-6">
                <div class="flex justify-between text-sm mb-2">
                  <span class="text-gray-600">Fundraising Progress</span>
                  <span class="font-semibold text-gray-900">{{ round.progress_percentage }}%</span>
                </div>
                <div class="relative h-3 bg-gray-100 rounded-full overflow-hidden">
                  <div 
                    class="absolute inset-y-0 left-0 bg-gradient-to-r from-violet-500 to-purple-600 rounded-full transition-all duration-700 ease-out"
                    :style="{ width: `${Math.min(round.progress_percentage, 100)}%` }"
                  ></div>
                </div>
                <div class="flex justify-between text-xs text-gray-500 mt-2">
                  <span>K{{ formatNumber(round.raised_amount) }} raised</span>
                  <span>K{{ formatNumber(round.goal_amount) }} goal</span>
                </div>
              </div>

              <!-- Round Stats -->
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

              <!-- Your Position -->
              <div v-if="investmentMetrics" class="mt-4 bg-gradient-to-r from-violet-50 to-purple-50 rounded-xl p-4 border border-violet-100">
                <div class="flex items-center gap-2 mb-1">
                  <SparklesIcon class="h-4 w-4 text-violet-600" aria-hidden="true" />
                  <p class="text-sm font-semibold text-violet-900">Your Position</p>
                </div>
                <p class="text-sm text-violet-700">
                  You own <span class="font-semibold">{{ investmentMetrics.equity_percentage }}%</span> of the company, 
                  valued at <span class="font-semibold">K{{ formatNumber(investmentMetrics.current_value) }}</span>
                </p>
              </div>
            </div>
          </div>

          <!-- Platform Metrics Card -->
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
                <MetricCard
                  label="Total Members"
                  :value="formatNumber(platformMetrics.total_members)"
                  trend="+12%"
                  trendUp
                  icon="users"
                />
                <MetricCard
                  label="Monthly Revenue"
                  :value="`K${formatNumber(platformMetrics.monthly_revenue)}`"
                  trend="+8%"
                  trendUp
                  icon="currency"
                />
                <MetricCard
                  label="Active Rate"
                  :value="`${platformMetrics.active_rate}%`"
                  trend="+3%"
                  trendUp
                  icon="activity"
                />
                <MetricCard
                  label="Retention"
                  :value="`${platformMetrics.retention_rate}%`"
                  trend="+5%"
                  trendUp
                  icon="refresh"
                />
              </div>
            </div>
          </div>
        </div>

        <!-- Financial Performance Section -->
        <div class="grid grid-cols-1 xl:grid-cols-2 gap-6 mb-8">
          <FinancialSummaryCard :financial-summary="financialSummary" />
          <PerformanceChart :performance-metrics="performanceMetrics" />
        </div>

        <!-- Info Banner -->
        <div class="bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-100 rounded-2xl p-6 flex flex-col sm:flex-row items-start sm:items-center gap-4">
          <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center flex-shrink-0">
            <InformationCircleIcon class="h-6 w-6 text-blue-600" aria-hidden="true" />
          </div>
          <div class="flex-1">
            <h3 class="font-semibold text-blue-900 mb-1">Stay Informed</h3>
            <p class="text-sm text-blue-700">
              We'll keep you updated on company performance, financial reports, and important announcements. 
              Check the Documents section for quarterly reports and legal documents.
            </p>
          </div>
          <Link
            :href="route('investor.documents')"
            class="inline-flex items-center gap-2 px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-xl transition-colors shadow-sm"
          >
            View Documents
            <ArrowRightIcon class="h-4 w-4" aria-hidden="true" />
          </Link>
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
import StatCard from '@/components/Investor/StatCard.vue';
import MetricCard from '@/components/Investor/MetricCard.vue';
import {
  HomeIcon,
  DocumentTextIcon,
  ChartBarIcon,
  ChatBubbleLeftRightIcon,
  Cog6ToothIcon,
  BuildingOffice2Icon,
  ArrowRightOnRectangleIcon,
  Bars3Icon,
  XMarkIcon,
  CalendarIcon,
  ClockIcon,
  ArrowTrendingUpIcon,
  ArrowTrendingDownIcon,
  RocketLaunchIcon,
  SparklesIcon,
  PresentationChartLineIcon,
  InformationCircleIcon,
  ArrowRightIcon,
  MegaphoneIcon,
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

// Mobile menu state
const mobileMenuOpen = ref(false);

// Announcement state
const dismissedAnnouncements = ref<number[]>([]);

const visibleAnnouncements = computed(() => {
  if (!props.announcements) return [];
  return props.announcements.filter(a => !dismissedAnnouncements.value.includes(a.id));
});

const dismissAnnouncement = (id: number) => {
  dismissedAnnouncements.value.push(id);
};

// Mini chart data for the hero section
const miniChartData = computed(() => {
  // Generate sample growth data or use real data if available
  if (props.performanceMetrics?.revenue_trend?.data?.length) {
    const data = props.performanceMetrics.revenue_trend.data;
    const max = Math.max(...data);
    return data.slice(-8).map(v => (v / max) * 100);
  }
  return [40, 55, 45, 60, 75, 65, 80, 90];
});

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

const statusBadgeClass = computed(() => {
  const classes = {
    ciu: 'bg-blue-400/20 text-blue-100',
    shareholder: 'bg-emerald-400/20 text-emerald-100',
    exited: 'bg-gray-400/20 text-gray-100',
  };
  return classes[props.investor.status as keyof typeof classes] || classes.ciu;
});

const statusPillClass = computed(() => {
  const classes = {
    ciu: 'bg-blue-400/30 text-blue-100',
    shareholder: 'bg-emerald-400/30 text-emerald-100',
    exited: 'bg-gray-400/30 text-gray-100',
  };
  return classes[props.investor.status as keyof typeof classes] || classes.ciu;
});

const roundStatusBadgeClass = computed(() => {
  if (!props.round) return '';
  const classes = {
    active: 'bg-emerald-100 text-emerald-700',
    closed: 'bg-gray-100 text-gray-700',
    upcoming: 'bg-blue-100 text-blue-700',
  };
  return classes[props.round.status as keyof typeof classes] || classes.active;
});
</script>
