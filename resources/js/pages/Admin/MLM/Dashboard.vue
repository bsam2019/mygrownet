<template>
  <AdminLayout>
    <Head title="MLM Administration Dashboard" />
    
    <div class="space-y-6">
      <!-- Header -->
      <div class="flex justify-between items-center">
        <div>
          <h1 class="text-2xl font-bold text-gray-900">MLM Administration Dashboard</h1>
          <p class="text-gray-600">Monitor and manage the MyGrowNet MLM system</p>
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

      <!-- System Alerts -->
      <div v-if="alerts.length > 0" class="space-y-3">
        <div 
          v-for="alert in alerts" 
          :key="alert.title"
          :class="[
            'rounded-md p-4',
            alert.type === 'error' ? 'bg-red-50 border border-red-200' : '',
            alert.type === 'warning' ? 'bg-yellow-50 border border-yellow-200' : '',
            alert.type === 'info' ? 'bg-blue-50 border border-blue-200' : ''
          ]"
        >
          <div class="flex">
            <div class="flex-shrink-0">
              <ExclamationTriangleIcon 
                v-if="alert.type === 'warning'" 
                class="h-5 w-5 text-yellow-400" 
              />
              <XCircleIcon 
                v-else-if="alert.type === 'error'" 
                class="h-5 w-5 text-red-400" 
              />
              <InformationCircleIcon 
                v-else 
                class="h-5 w-5 text-blue-400" 
              />
            </div>
            <div class="ml-3 flex-1">
              <h3 :class="[
                'text-sm font-medium',
                alert.type === 'error' ? 'text-red-800' : '',
                alert.type === 'warning' ? 'text-yellow-800' : '',
                alert.type === 'info' ? 'text-blue-800' : ''
              ]">
                {{ alert.title }}
              </h3>
              <div :class="[
                'mt-1 text-sm',
                alert.type === 'error' ? 'text-red-700' : '',
                alert.type === 'warning' ? 'text-yellow-700' : '',
                alert.type === 'info' ? 'text-blue-700' : ''
              ]">
                <p>{{ alert.message }}</p>
              </div>
              <div class="mt-2">
                <Link 
                  :href="alert.url"
                  :class="[
                    'text-sm font-medium underline',
                    alert.type === 'error' ? 'text-red-800 hover:text-red-900' : '',
                    alert.type === 'warning' ? 'text-yellow-800 hover:text-yellow-900' : '',
                    alert.type === 'info' ? 'text-blue-800 hover:text-blue-900' : ''
                  ]"
                >
                  {{ alert.action }}
                </Link>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Overview Metrics -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <!-- Total Commissions -->
        <div class="bg-white overflow-hidden shadow rounded-lg">
          <div class="p-5">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <CurrencyDollarIcon class="h-6 w-6 text-gray-400" />
              </div>
              <div class="ml-5 w-0 flex-1">
                <dl>
                  <dt class="text-sm font-medium text-gray-500 truncate">
                    Total Commissions
                  </dt>
                  <dd class="flex items-baseline">
                    <div class="text-2xl font-semibold text-gray-900">
                      K{{ formatNumber(overview.total_commissions.current) }}
                    </div>
                    <div :class="[
                      'ml-2 flex items-baseline text-sm font-semibold',
                      overview.total_commissions.change_percent >= 0 ? 'text-green-600' : 'text-red-600'
                    ]">
                      <ArrowUpIcon v-if="overview.total_commissions.change_percent >= 0" class="h-4 w-4 flex-shrink-0 self-center" />
                      <ArrowDownIcon v-else class="h-4 w-4 flex-shrink-0 self-center" />
                      {{ Math.abs(overview.total_commissions.change_percent).toFixed(1) }}%
                    </div>
                  </dd>
                </dl>
              </div>
            </div>
          </div>
        </div>

        <!-- Pending Commissions -->
        <div class="bg-white overflow-hidden shadow rounded-lg">
          <div class="p-5">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <ClockIcon class="h-6 w-6 text-gray-400" />
              </div>
              <div class="ml-5 w-0 flex-1">
                <dl>
                  <dt class="text-sm font-medium text-gray-500 truncate">
                    Pending Commissions
                  </dt>
                  <dd class="flex items-baseline">
                    <div class="text-2xl font-semibold text-gray-900">
                      {{ overview.pending_commissions.count }}
                    </div>
                    <div class="ml-2 text-sm text-gray-500">
                      K{{ formatNumber(overview.pending_commissions.amount) }}
                    </div>
                  </dd>
                </dl>
              </div>
            </div>
          </div>
        </div>

        <!-- Active Members -->
        <div class="bg-white overflow-hidden shadow rounded-lg">
          <div class="p-5">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <UsersIcon class="h-6 w-6 text-gray-400" />
              </div>
              <div class="ml-5 w-0 flex-1">
                <dl>
                  <dt class="text-sm font-medium text-gray-500 truncate">
                    Active Members
                  </dt>
                  <dd class="flex items-baseline">
                    <div class="text-2xl font-semibold text-gray-900">
                      {{ overview.active_members.current }}
                    </div>
                    <div class="ml-2 text-sm text-gray-500">
                      of {{ overview.active_members.total }}
                    </div>
                  </dd>
                </dl>
              </div>
            </div>
          </div>
        </div>

        <!-- Network Growth -->
        <div class="bg-white overflow-hidden shadow rounded-lg">
          <div class="p-5">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <ChartBarIcon class="h-6 w-6 text-gray-400" />
              </div>
              <div class="ml-5 w-0 flex-1">
                <dl>
                  <dt class="text-sm font-medium text-gray-500 truncate">
                    Network Growth
                  </dt>
                  <dd class="flex items-baseline">
                    <div class="text-2xl font-semibold text-gray-900">
                      {{ overview.network_growth.new_members }}
                    </div>
                    <div class="ml-2 text-sm text-gray-500">
                      new members
                    </div>
                  </dd>
                </dl>
              </div>
            </div>
          </div>
        </div>

        <!-- Total Volume -->
        <div class="bg-white overflow-hidden shadow rounded-lg">
          <div class="p-5">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <ArrowTrendingUpIcon class="h-6 w-6 text-gray-400" />
              </div>
              <div class="ml-5 w-0 flex-1">
                <dl>
                  <dt class="text-sm font-medium text-gray-500 truncate">
                    Total Volume
                  </dt>
                  <dd class="flex items-baseline">
                    <div class="text-2xl font-semibold text-gray-900">
                      K{{ formatNumber(overview.total_volume.current) }}
                    </div>
                    <div class="ml-2 text-sm text-gray-500">
                      avg K{{ formatNumber(overview.total_volume.average_per_member) }}
                    </div>
                  </dd>
                </dl>
              </div>
            </div>
          </div>
        </div>

        <!-- Compliance Score -->
        <div class="bg-white overflow-hidden shadow rounded-lg">
          <div class="p-5">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <ShieldCheckIcon class="h-6 w-6 text-gray-400" />
              </div>
              <div class="ml-5 w-0 flex-1">
                <dl>
                  <dt class="text-sm font-medium text-gray-500 truncate">
                    Compliance Score
                  </dt>
                  <dd class="flex items-baseline">
                    <div :class="[
                      'text-2xl font-semibold',
                      overview.compliance_score >= 90 ? 'text-green-600' : 
                      overview.compliance_score >= 75 ? 'text-yellow-600' : 'text-red-600'
                    ]">
                      {{ overview.compliance_score.toFixed(1) }}%
                    </div>
                  </dd>
                </dl>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Commission Statistics -->
      <div class="bg-white shadow rounded-lg">
        <div class="px-4 py-5 sm:p-6">
          <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">
            Commission Statistics
          </h3>
          
          <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- By Type -->
            <div>
              <h4 class="text-sm font-medium text-gray-500 mb-3">By Type</h4>
              <div class="space-y-2">
                <div 
                  v-for="(stats, type) in commissionStats.by_type" 
                  :key="type"
                  class="flex justify-between items-center"
                >
                  <span class="text-sm text-gray-600">{{ type }}</span>
                  <div class="text-right">
                    <div class="text-sm font-medium text-gray-900">
                      K{{ formatNumber(stats.total_amount) }}
                    </div>
                    <div class="text-xs text-gray-500">
                      {{ stats.count }} commissions
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- By Level -->
            <div>
              <h4 class="text-sm font-medium text-gray-500 mb-3">By Level</h4>
              <div class="space-y-2">
                <div 
                  v-for="(stats, level) in commissionStats.by_level" 
                  :key="level"
                  class="flex justify-between items-center"
                >
                  <span class="text-sm text-gray-600">Level {{ level }}</span>
                  <div class="text-right">
                    <div class="text-sm font-medium text-gray-900">
                      K{{ formatNumber(stats.total_amount) }}
                    </div>
                    <div class="text-xs text-gray-500">
                      {{ stats.count }} commissions
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- By Status -->
            <div>
              <h4 class="text-sm font-medium text-gray-500 mb-3">By Status</h4>
              <div class="space-y-2">
                <div 
                  v-for="(stats, status) in commissionStats.by_status" 
                  :key="status"
                  class="flex justify-between items-center"
                >
                  <span class="text-sm text-gray-600 capitalize">{{ status }}</span>
                  <div class="text-right">
                    <div class="text-sm font-medium text-gray-900">
                      K{{ formatNumber(stats.total_amount) }}
                    </div>
                    <div class="text-xs text-gray-500">
                      {{ stats.count }} commissions
                    </div>
                  </div>
                </div>
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
              :href="route('admin.mlm.commissions')"
              class="inline-flex items-center justify-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
            >
              <CurrencyDollarIcon class="-ml-1 mr-2 h-4 w-4" />
              Manage Commissions
            </Link>
            
            <Link 
              :href="route('admin.mlm.network-analysis')"
              class="inline-flex items-center justify-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
            >
              <ChartBarIcon class="-ml-1 mr-2 h-4 w-4" />
              Network Analysis
            </Link>
            
            <Link 
              :href="route('admin.mlm.performance-monitoring')"
              class="inline-flex items-center justify-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
            >
              <ArrowTrendingUpIcon class="-ml-1 mr-2 h-4 w-4" />
              Performance Monitor
            </Link>
            
            <button 
              @click="recalculateNetwork"
              :disabled="recalculating"
              class="inline-flex items-center justify-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50"
            >
              <ArrowPathIcon v-if="recalculating" class="animate-spin -ml-1 mr-2 h-4 w-4" />
              <Cog6ToothIcon v-else class="-ml-1 mr-2 h-4 w-4" />
              Recalculate Network
            </button>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import AdminLayout from '@/layouts/AdminLayout.vue'
import {
  ArrowPathIcon,
  ArrowUpIcon,
  ArrowDownIcon,
  ChartBarIcon,
  ClockIcon,
  Cog6ToothIcon,
  CurrencyDollarIcon,
  ExclamationTriangleIcon,
  InformationCircleIcon,
  ShieldCheckIcon,
  ArrowTrendingUpIcon,
  UsersIcon,
  XCircleIcon,
} from '@heroicons/vue/24/outline'

interface Props {
  overview: any
  commissionStats: any
  networkAnalytics: any
  performanceMetrics: any
  alerts: any[]
  period: string
}

const props = defineProps<Props>()

const selectedPeriod = ref(props.period)
const loading = ref(false)
const recalculating = ref(false)

const formatNumber = (value: number): string => {
  return new Intl.NumberFormat('en-US', {
    minimumFractionDigits: 0,
    maximumFractionDigits: 2,
  }).format(value)
}

const updatePeriod = () => {
  loading.value = true
  router.get(route('admin.mlm.dashboard'), 
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

const recalculateNetwork = async () => {
  if (recalculating.value) return
  
  recalculating.value = true
  
  try {
    const response = await fetch(route('admin.mlm.recalculate-network'), {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
      },
    })
    
    const data = await response.json()
    
    if (data.success) {
      // Show success message
      alert('Network recalculation initiated successfully')
    } else {
      alert('Failed to initiate network recalculation: ' + data.message)
    }
  } catch (error) {
    console.error('Error recalculating network:', error)
    alert('An error occurred while recalculating the network')
  } finally {
    recalculating.value = false
  }
}
</script>