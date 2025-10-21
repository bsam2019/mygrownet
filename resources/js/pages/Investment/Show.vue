<template>
  <div class="min-h-screen bg-gray-100">
    <!-- Main Content -->
    <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Left Column -->
        <div class="lg:col-span-2 space-y-6">
          <!-- Investment Summary Card -->
          <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-6">
              <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-medium text-gray-900">Investment Summary</h3>
                <Badge :variant="getStatusBadge(investment.status)">
                  {{ investment.status }}
                </Badge>
              </div>
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                  <dl class="mt-4 space-y-4">
                    <div>
                      <dt class="text-sm font-medium text-gray-500">Principal Amount</dt>
                      <dd class="mt-1 text-2xl font-semibold text-gray-900">
                        {{ investment.amount ? formatCurrency(investment.amount) : 'N/A' }}
                      </dd>
                    </div>
                    <div>
                      <dt class="text-sm font-medium text-gray-500">Expected Return</dt>
                      <dd class="mt-1 text-2xl font-semibold text-gray-900">
                        {{ investment.expected_return ? formatCurrency(investment.expected_return) : 'N/A' }}
                      </dd>
                    </div>
                  </dl>
                </div>
                <div>
                  <h3 class="text-lg font-medium text-gray-900">Investment Details</h3>
                  <dl class="mt-4 space-y-4">
                    <div>
                      <dt class="text-sm font-medium text-gray-500">Category</dt>
                      <dd class="mt-1 text-lg text-gray-900">
                        {{ investment.category?.name || 'N/A' }}
                      </dd>
                    </div>
                    <div>
                      <dt class="text-sm font-medium text-gray-500">Risk Level</dt>
                      <dd class="mt-1 text-lg text-gray-900">
                        {{ investment.category?.risk_level || 'N/A' }}
                      </dd>
                    </div>
                    <div>
                      <dt class="text-sm font-medium text-gray-500">Return Rate</dt>
                      <dd class="mt-1 text-lg text-gray-900">
                        {{ investment.category?.return_rate ? `${investment.category.return_rate}%` : 'N/A' }}
                      </dd>
                    </div>
                  </dl>
                </div>
              </div>
            </div>
          </div>

          <!-- Performance Chart -->
          <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-6">
              <h3 class="text-lg font-medium text-gray-900">Performance History</h3>
              <div class="mt-4 h-64">
                <LineChart :data="performanceHistory" />
              </div>
            </div>
          </div>

          <!-- Investment Timeline -->
          <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-6">
              <h3 class="text-lg font-medium text-gray-900">Investment Timeline</h3>
              <div class="mt-4">
                <InvestmentTimeline :events="timelineEvents" />
              </div>
            </div>
          </div>
        </div>

        <!-- Right Column -->
        <div class="space-y-6">
          <!-- Performance Metrics -->
          <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-6">
              <h3 class="text-lg font-medium text-gray-900">Performance Metrics</h3>
              <div class="mt-4">
                <InvestmentMetrics :metrics="metrics" />
              </div>
            </div>
          </div>

          <!-- Quick Actions -->
          <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-6">
              <h3 class="text-lg font-medium text-gray-900">Quick Actions</h3>
              <div class="mt-4 space-y-4">
                <button
                  v-if="canWithdraw"
                  @click="requestWithdrawal"
                  class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                >
                  Request Withdrawal
                </button>
                <button
                  v-if="canReinvest"
                  @click="reinvestReturns"
                  class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500"
                >
                  Reinvest Returns
                </button>
              </div>
            </div>
          </div>

          <!-- Investment Details -->
          <div class="bg-white overflow-hidden shadow rounded-lg">
            <div class="p-6">
              <h3 class="text-lg font-medium text-gray-900">Investment Details</h3>
              <dl class="mt-4 space-y-4">
                <div>
                  <dt class="text-sm font-medium text-gray-500">Investor</dt>
                  <dd class="mt-1 text-sm text-gray-900">{{ investment.user?.name || 'N/A' }}</dd>
                </div>
                <div>
                  <dt class="text-sm font-medium text-gray-500">Next Payout</dt>
                  <dd class="mt-1 text-sm text-gray-900">{{ formatCurrency(metrics.next_payout) }}</dd>
                </div>
                <div>
                  <dt class="text-sm font-medium text-gray-500">Days Until Lock-in End</dt>
                  <dd class="mt-1 text-sm text-gray-900">{{ metrics.days_remaining }} days</dd>
                </div>
              </dl>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { computed } from 'vue';
import Badge from '@/components/ui/badge/Badge.vue';
import InvestmentMetrics from '@/components/Investment/InvestmentMetrics.vue';
import InvestmentTimeline from '@/components/Investment/InvestmentTimeline.vue';
import LineChart from '@/components/Charts/LineChart.vue';
import MemberLayout from '@/layouts/MemberLayout.vue';
import { formatCurrency } from '@/utils/formatting';

export default {
  name: 'InvestmentShow',
  layout: MemberLayout,
  components: {
    Badge,
    InvestmentMetrics,
    InvestmentTimeline,
    LineChart
  },
  props: {
    investment: {
      type: Object,
      required: true
    },
    metrics: {
      type: Object,
      required: true
    },
    performanceHistory: {
      type: Array,
      required: true
    },
    canWithdraw: {
      type: Boolean,
      required: true
    },
    canReinvest: {
      type: Boolean,
      required: true
    }
  },
  setup(props) {
    const getStatusBadge = (status) => {
      const variants = {
        pending: 'warning',
        active: 'success',
        rejected: 'destructive',
        completed: 'default'
      };
      return variants[status?.toLowerCase()] || 'default';
    };

    const timelineEvents = computed(() => [
      {
        date: props.investment.created_at,
        title: 'Investment Created',
        description: `Initial investment of ${formatCurrency(props.investment.amount)}`,
        type: 'investment'
      },
      {
        date: props.investment.lock_in_period_end,
        title: 'Lock-in Period Ends',
        description: 'You can withdraw your investment after this date',
        type: 'lockin'
      },
      ...props.performanceHistory.map(record => ({
        date: record.date,
        title: 'Profit Share',
        description: `Earned ${formatCurrency(record.amount)}`,
        type: 'payout'
      }))
    ]);

    const requestWithdrawal = () => {
      // Implement withdrawal request logic
    };

    const reinvestReturns = () => {
      // Implement reinvestment logic
    };

    return {
      getStatusBadge,
      timelineEvents,
      formatCurrency,
      requestWithdrawal,
      reinvestReturns
    };
  }
};
</script>
