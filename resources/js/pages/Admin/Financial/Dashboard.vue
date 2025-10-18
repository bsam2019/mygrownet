<template>
  <AdminLayout>
    <Head title="Financial Reporting Dashboard" />
    
    <div class="space-y-6">
      <!-- Header -->
      <div class="flex justify-between items-center">
        <div>
          <h1 class="text-2xl font-bold text-gray-900">Financial Reporting Dashboard</h1>
          <p class="text-gray-600">Monitor financial health, compliance, and sustainability metrics</p>
        </div>
        
        <div class="flex items-center space-x-4">
          <!-- Period Selector -->
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
          
          <!-- Refresh Button -->
          <button 
            @click="refreshData"
            :disabled="loading"
            class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50"
          >
            <ArrowPathIcon v-if="loading" class="animate-spin -ml-1 mr-2 h-4 w-4" />
            <ArrowPathIcon v-else class="-ml-1 mr-2 h-4 w-4" />
            Refresh
          </button>
        </div>
      </div>

      <!-- Financial Health Score -->
      <div class="bg-white shadow rounded-lg">
        <div class="px-4 py-5 sm:p-6">
          <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg leading-6 font-medium text-gray-900">
              Financial Health Score
            </h3>
            <div :class="[
              'inline-flex items-center px-3 py-1 rounded-full text-sm font-medium',
              healthScore.score >= 90 ? 'bg-green-100 text-green-800' :
              healthScore.score >= 75 ? 'bg-yellow-100 text-yellow-800' :
              healthScore.score >= 60 ? 'bg-orange-100 text-orange-800' : 'bg-red-100 text-red-800'
            ]">
              {{ healthScore.score.toFixed(1) }}% {{ healthScore.status }}
            </div>
          </div>
          
          <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div 
              v-for="metric in healthScore.metrics" 
              :key="metric.name"
              class="text-center p-4 bg-gray-50 rounded-lg"
            >
              <div :class="[
                'text-2xl font-bold',
                metric.score >= 80 ? 'text-green-600' :
                metric.score >= 60 ? 'text-yellow-600' : 'text-red-600'
              ]">
                {{ metric.score.toFixed(1) }}%
              </div>
              <div class="text-sm text-gray-500">{{ metric.name }}</div>
            </div>
          </div>
        </div>
      </div>

      <!-- Key Financial Metrics -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Total Revenue -->
        <div class="bg-white overflow-hidden shadow rounded-lg">
          <div class="p-5">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <CurrencyDollarIcon class="h-6 w-6 text-gray-400" />
              </div>
              <div class="ml-5 w-0 flex-1">
                <dl>
                  <dt class="text-sm font-medium text-gray-500 truncate">
                    Total Revenue
                  </dt>
                  <dd class="flex items-baseline">
                    <div class="text-2xl font-semibold text-gray-900">
                      K{{ formatNumber(overview.total_revenue.current) }}
                    </div>
                    <div :class="[
                      'ml-2 flex items-baseline text-sm font-semibold',
                      overview.total_revenue.change_percent >= 0 ? 'text-green-600' : 'text-red-600'
                    ]">
                      <ArrowUpIcon v-if="overview.total_revenue.change_percent >= 0" class="h-4 w-4 flex-shrink-0 self-center" />
                      <ArrowDownIcon v-else class="h-4 w-4 flex-shrink-0 self-center" />
                      {{ Math.abs(overview.total_revenue.change_percent).toFixed(1) }}%
                    </div>
                  </dd>
                </dl>
              </div>
            </div>
          </div>
        </div>

        <!-- Commission Payouts -->
        <div class="bg-white overflow-hidden shadow rounded-lg">
          <div class="p-5">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <BanknotesIcon class="h-6 w-6 text-gray-400" />
              </div>
              <div class="ml-5 w-0 flex-1">
                <dl>
                  <dt class="text-sm font-medium text-gray-500 truncate">
                    Commission Payouts
                  </dt>
                  <dd class="flex items-baseline">
                    <div class="text-2xl font-semibold text-gray-900">
                      K{{ formatNumber(overview.commission_payouts.current) }}
                    </div>
                    <div class="ml-2 text-sm text-gray-500">
                      {{ overview.commission_payouts.percentage_of_revenue.toFixed(1) }}% of revenue
                    </div>
                  </dd>
                </dl>
              </div>
            </div>
          </div>
        </div>

        <!-- Net Profit -->
        <div class="bg-white overflow-hidden shadow rounded-lg">
          <div class="p-5">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <ArrowTrendingUpIcon class="h-6 w-6 text-gray-400" />
              </div>
              <div class="ml-5 w-0 flex-1">
                <dl>
                  <dt class="text-sm font-medium text-gray-500 truncate">
                    Net Profit
                  </dt>
                  <dd class="flex items-baseline">
                    <div :class="[
                      'text-2xl font-semibold',
                      overview.net_profit.current >= 0 ? 'text-green-600' : 'text-red-600'
                    ]">
                      K{{ formatNumber(overview.net_profit.current) }}
                    </div>
                    <div class="ml-2 text-sm text-gray-500">
                      {{ overview.net_profit.margin.toFixed(1) }}% margin
                    </div>
                  </dd>
                </dl>
              </div>
            </div>
          </div>
        </div>

        <!-- Commission Cap Utilization -->
        <div class="bg-white overflow-hidden shadow rounded-lg">
          <div class="p-5">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <ShieldCheckIcon class="h-6 w-6 text-gray-400" />
              </div>
              <div class="ml-5 w-0 flex-1">
                <dl>
                  <dt class="text-sm font-medium text-gray-500 truncate">
                    Commission Cap
                  </dt>
                  <dd class="flex items-baseline">
                    <div :class="[
                      'text-2xl font-semibold',
                      overview.commission_cap.utilization >= 90 ? 'text-red-600' :
                      overview.commission_cap.utilization >= 75 ? 'text-yellow-600' : 'text-green-600'
                    ]">
                      {{ overview.commission_cap.utilization.toFixed(1) }}%
                    </div>
                    <div class="ml-2 text-sm text-gray-500">
                      utilized
                    </div>
                  </dd>
                </dl>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Revenue Breakdown -->
      <div class="bg-white shadow rounded-lg">
        <div class="px-4 py-5 sm:p-6">
          <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">
            Revenue Breakdown
          </h3>
          
          <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Revenue Sources -->
            <div>
              <h4 class="text-sm font-medium text-gray-500 mb-3">Revenue Sources</h4>
              <div class="space-y-3">
                <div 
                  v-for="source in revenueBreakdown.sources" 
                  :key="source.name"
                  class="flex items-center justify-between"
                >
                  <div class="flex items-center">
                    <div 
                      class="w-3 h-3 rounded-full mr-3"
                      :style="{ backgroundColor: source.color }"
                    ></div>
                    <span class="text-sm text-gray-600">{{ source.name }}</span>
                  </div>
                  <div class="text-right">
                    <div class="text-sm font-medium text-gray-900">
                      K{{ formatNumber(source.amount) }}
                    </div>
                    <div class="text-xs text-gray-500">
                      {{ source.percentage.toFixed(1) }}%
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Cost Breakdown -->
            <div>
              <h4 class="text-sm font-medium text-gray-500 mb-3">Cost Breakdown</h4>
              <div class="space-y-3">
                <div 
                  v-for="cost in costAnalysis.categories" 
                  :key="cost.name"
                  class="flex items-center justify-between"
                >
                  <div class="flex items-center">
                    <div 
                      class="w-3 h-3 rounded-full mr-3"
                      :style="{ backgroundColor: cost.color }"
                    ></div>
                    <span class="text-sm text-gray-600">{{ cost.name }}</span>
                  </div>
                  <div class="text-right">
                    <div class="text-sm font-medium text-gray-900">
                      K{{ formatNumber(cost.amount) }}
                    </div>
                    <div class="text-xs text-gray-500">
                      {{ cost.percentage.toFixed(1) }}%
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Sustainability Metrics -->
      <div class="bg-white shadow rounded-lg">
        <div class="px-4 py-5 sm:p-6">
          <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">
            Sustainability Metrics
          </h3>
          
          <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div 
              v-for="metric in sustainabilityMetrics" 
              :key="metric.name"
              class="text-center p-4 border rounded-lg"
            >
              <div :class="[
                'text-3xl font-bold mb-2',
                metric.status === 'healthy' ? 'text-green-600' :
                metric.status === 'warning' ? 'text-yellow-600' : 'text-red-600'
              ]">
                {{ metric.value }}{{ metric.unit }}
              </div>
              <div class="text-sm font-medium text-gray-900 mb-1">{{ metric.name }}</div>
              <div class="text-xs text-gray-500">{{ metric.description }}</div>
              <div :class="[
                'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium mt-2',
                metric.status === 'healthy' ? 'bg-green-100 text-green-800' :
                metric.status === 'warning' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800'
              ]">
                {{ metric.status }}
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Quick Actions -->
      <div class="bg-white shadow rounded-lg">
        <div class="px-4 py-5 sm:p-6">
          <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">
            Quick Actions
          </h3>
          
          <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <Link 
              :href="route('admin.financial.reports')"
              class="inline-flex items-center justify-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
            >
              <DocumentChartBarIcon class="-ml-1 mr-2 h-4 w-4" />
              Generate Reports
            </Link>
            
            <Link 
              :href="route('admin.financial.sustainability')"
              class="inline-flex items-center justify-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
            >
              <ChartBarIcon class="-ml-1 mr-2 h-4 w-4" />
              Sustainability
            </Link>
            
            <Link 
              :href="route('admin.financial.compliance')"
              class="inline-flex items-center justify-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
            >
              <ShieldCheckIcon class="-ml-1 mr-2 h-4 w-4" />
              Compliance
            </Link>
            
            <button 
              @click="updateCommissionCap"
              :disabled="updatingCap"
              class="inline-flex items-center justify-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50"
            >
              <Cog6ToothIcon v-if="updatingCap" class="animate-spin -ml-1 mr-2 h-4 w-4" />
              <Cog6ToothIcon v-else class="-ml-1 mr-2 h-4 w-4" />
              Update Cap
            </button>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import AdminLayout from '@/layouts/AdminLayout.vue'
import {
  ArrowPathIcon,
  ArrowUpIcon,
  ArrowDownIcon,
  BanknotesIcon,
  ChartBarIcon,
  Cog6ToothIcon,
  CurrencyDollarIcon,
  DocumentChartBarIcon,
  ShieldCheckIcon,
  ArrowTrendingUpIcon,
} from '@heroicons/vue/24/outline'

interface Props {
  overview: any
  healthScore: any
  revenueBreakdown: any
  costAnalysis: any
  sustainabilityMetrics: any[]
  period: string
}

const props = defineProps<Props>()

const selectedPeriod = ref(props.period)
const loading = ref(false)
const updatingCap = ref(false)

const formatNumber = (value: number): string => {
  return new Intl.NumberFormat('en-US', {
    minimumFractionDigits: 0,
    maximumFractionDigits: 2,
  }).format(value)
}

const updatePeriod = () => {
  loading.value = true
  router.get(route('admin.financial.dashboard'), 
    { period: selectedPeriod.value },
    { 
      preserveState: true,
      onFinish: () => loading.value = false
    }
  )
}

const refreshData = () => {
  loading.value = true
  router.reload({ 
    onFinish: () => loading.value = false 
  })
}

const updateCommissionCap = async () => {
  if (updatingCap.value) return
  
  updatingCap.value = true
  
  try {
    const response = await fetch(route('admin.financial.update-commission-cap'), {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
      },
    })
    
    const data = await response.json()
    
    if (data.success) {
      alert('Commission cap updated successfully')
      refreshData()
    } else {
      alert('Failed to update commission cap: ' + data.message)
    }
  } catch (error) {
    console.error('Error updating commission cap:', error)
    alert('An error occurred while updating the commission cap')
  } finally {
    updatingCap.value = false
  }
}
</script>