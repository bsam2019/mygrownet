<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Manager Dashboard Header -->
    <div class="bg-white shadow-sm border-b border-gray-200">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center py-6">
          <div>
            <h1 class="text-2xl font-bold text-gray-900">Manager Dashboard</h1>
            <p class="text-sm text-gray-600">Team oversight and performance management</p>
          </div>
          <div class="flex items-center space-x-4">
            <select 
              v-model="selectedPeriod" 
              @change="updatePeriod"
              class="rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
            >
              <option value="week">This Week</option>
              <option value="month">This Month</option>
              <option value="quarter">This Quarter</option>
              <option value="year">This Year</option>
            </select>
          </div>
        </div>
      </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Team Metrics Overview -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <MetricCard
          title="Team Size"
          :value="teamMetrics.team_size"
          icon="users"
          color="blue"
        />
        <MetricCard
          title="Active Investors"
          :value="teamMetrics.active_investors"
          :subtitle="`${teamMetrics.team_size > 0 ? Math.round((teamMetrics.active_investors / teamMetrics.team_size) * 100) : 0}% of team`"
          icon="trending-up"
          color="green"
        />
        <MetricCard
          title="Total Investments"
          :value="formatCurrency(teamMetrics.total_investments)"
          :subtitle="`Avg: ${formatCurrency(teamMetrics.average_investment)}`"
          icon="dollar-sign"
          color="indigo"
        />
        <MetricCard
          title="Team Performance"
          :value="`${teamMetrics.team_performance_score}%`"
          :subtitle="getPerformanceLabel(teamMetrics.team_performance_score)"
          icon="award"
          color="purple"
        />
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Performance Chart -->
        <div class="lg:col-span-2">
          <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between mb-6">
              <h3 class="text-lg font-semibold text-gray-900">Team Performance Trends</h3>
              <div class="flex space-x-2">
                <button
                  v-for="metric in ['investments', 'counts']"
                  :key="metric"
                  @click="selectedMetric = metric"
                  :class="[
                    'px-3 py-1 text-sm rounded-md transition-colors',
                    selectedMetric === metric
                      ? 'bg-blue-100 text-blue-700'
                      : 'text-gray-500 hover:text-gray-700'
                  ]"
                >
                  {{ metric === 'investments' ? 'Investment Value' : 'Investment Count' }}
                </button>
              </div>
            </div>
            <PerformanceChart 
              :data="performanceData" 
              :metric="selectedMetric"
            />
          </div>
        </div>

        <!-- Pending Approvals -->
        <div class="space-y-6">
          <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Pending Approvals</h3>
            <div class="space-y-4">
              <div class="flex items-center justify-between p-3 bg-amber-50 rounded-lg">
                <div>
                  <p class="font-medium text-amber-900">Withdrawals</p>
                  <p class="text-sm text-amber-700">{{ pendingApprovals.withdrawals.length }} pending</p>
                </div>
                <span class="bg-amber-100 text-amber-800 px-2 py-1 rounded-full text-xs font-medium">
                  {{ pendingApprovals.withdrawals.length }}
                </span>
              </div>
              
              <div class="flex items-center justify-between p-3 bg-blue-50 rounded-lg">
                <div>
                  <p class="font-medium text-blue-900">Tier Upgrades</p>
                  <p class="text-sm text-blue-700">{{ pendingApprovals.tier_upgrades }} pending</p>
                </div>
                <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded-full text-xs font-medium">
                  {{ pendingApprovals.tier_upgrades }}
                </span>
              </div>
              
              <div class="flex items-center justify-between p-3 bg-red-50 rounded-lg">
                <div>
                  <p class="font-medium text-red-900">Commission Disputes</p>
                  <p class="text-sm text-red-700">{{ pendingApprovals.commission_disputes }} pending</p>
                </div>
                <span class="bg-red-100 text-red-800 px-2 py-1 rounded-full text-xs font-medium">
                  {{ pendingApprovals.commission_disputes }}
                </span>
              </div>
            </div>
          </div>

          <!-- Commission Overview -->
          <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Commission Overview</h3>
            <div class="space-y-3">
              <div class="flex justify-between">
                <span class="text-gray-600">Total Paid</span>
                <span class="font-medium text-green-600">{{ formatCurrency(commissionOverview.total_paid) }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-600">Pending</span>
                <span class="font-medium text-amber-600">{{ formatCurrency(commissionOverview.total_pending) }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-gray-600">This Month</span>
                <span class="font-medium text-blue-600">{{ formatCurrency(commissionOverview.monthly_commissions) }}</span>
              </div>
              <div class="flex justify-between pt-2 border-t">
                <span class="text-gray-600">Commission Rate</span>
                <span class="font-medium">{{ commissionOverview.commission_rate }}%</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Team Members and Recent Activities -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mt-8">
        <!-- Managed Users -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
          <div class="flex items-center justify-between mb-6">
            <h3 class="text-lg font-semibold text-gray-900">Team Members</h3>
            <Link 
              :href="route('manager.team.overview')"
              class="text-blue-600 hover:text-blue-700 text-sm font-medium"
            >
              View All
            </Link>
          </div>
          <div class="space-y-4">
            <div 
              v-for="user in managedUsers.slice(0, 5)" 
              :key="user.id"
              class="flex items-center justify-between p-3 hover:bg-gray-50 rounded-lg transition-colors"
            >
              <div>
                <p class="font-medium text-gray-900">{{ user.name }}</p>
                <p class="text-sm text-gray-500">{{ user.email }}</p>
              </div>
              <div class="text-right">
                <p class="font-medium text-gray-900">{{ formatCurrency(user.total_investments) }}</p>
                <p class="text-sm text-gray-500">{{ user.investment_count }} investments</p>
              </div>
            </div>
          </div>
        </div>

        <!-- Recent Team Investments -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
          <div class="flex items-center justify-between mb-6">
            <h3 class="text-lg font-semibold text-gray-900">Recent Investments</h3>
            <Link 
              :href="route('manager.reports.team-performance')"
              class="text-blue-600 hover:text-blue-700 text-sm font-medium"
            >
              View Report
            </Link>
          </div>
          <div class="space-y-4">
            <div 
              v-for="investment in recentInvestments.slice(0, 5)" 
              :key="investment.id"
              class="flex items-center justify-between p-3 hover:bg-gray-50 rounded-lg transition-colors"
            >
              <div>
                <p class="font-medium text-gray-900">{{ investment.user_name }}</p>
                <p class="text-sm text-gray-500">{{ investment.tier }} • {{ formatDate(investment.created_at) }}</p>
              </div>
              <div class="text-right">
                <p class="font-medium text-gray-900">{{ formatCurrency(investment.amount) }}</p>
                <span 
                  :class="[
                    'inline-flex px-2 py-1 text-xs font-medium rounded-full',
                    investment.status === 'active' 
                      ? 'bg-green-100 text-green-800'
                      : 'bg-yellow-100 text-yellow-800'
                  ]"
                >
                  {{ investment.status }}
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import MetricCard from '@/components/MetricCard.vue'
import PerformanceChart from '@/components/PerformanceChart.vue'

interface Props {
  teamMetrics: {
    team_size: number
    active_investors: number
    total_investments: number
    monthly_growth: number
    total_commissions: number
    average_investment: number
    team_performance_score: number
  }
  performanceData: {
    labels: string[]
    investments: number[]
    counts: number[]
    growth_rate: number
  }
  pendingApprovals: {
    withdrawals: any[]
    tier_upgrades: number
    commission_disputes: number
  }
  teamActivities: any[]
  managedUsers: any[]
  recentInvestments: any[]
  commissionOverview: {
    total_paid: number
    total_pending: number
    monthly_commissions: number
    commission_rate: number
  }
}

const props = defineProps<Props>()

const selectedPeriod = ref('month')
const selectedMetric = ref('investments')

const updatePeriod = () => {
  router.get(route('manager.dashboard'), { period: selectedPeriod.value }, {
    preserveState: true,
    preserveScroll: true
  })
}

const formatCurrency = (amount: number): string => {
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'ZMW',
    minimumFractionDigits: 0,
    maximumFractionDigits: 0,
  }).format(amount).replace('ZMW', 'K')
}

const formatDate = (date: string): string => {
  return new Date(date).toLocaleDateString('en-US', {
    month: 'short',
    day: 'numeric',
    year: 'numeric'
  })
}

const getPerformanceLabel = (score: number): string => {
  if (score >= 80) return 'Excellent'
  if (score >= 60) return 'Good'
  if (score >= 40) return 'Average'
  return 'Needs Improvement'
}
</script>