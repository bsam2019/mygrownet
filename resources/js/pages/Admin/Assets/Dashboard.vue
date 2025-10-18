<template>
  <AdminLayout>
    <Head title="Asset Management Dashboard" />
    
    <div class="space-y-6">
      <!-- Header -->
      <div class="flex justify-between items-center">
        <div>
          <h1 class="text-2xl font-bold text-gray-900">Asset Management Dashboard</h1>
          <p class="text-gray-600">Monitor and manage physical rewards and asset allocations</p>
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
            </div>
          </div>
        </div>
      </div>

      <!-- Overview Metrics -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Total Assets -->
        <div class="bg-white overflow-hidden shadow rounded-lg">
          <div class="p-5">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <BuildingOffice2Icon class="h-6 w-6 text-gray-400" />
              </div>
              <div class="ml-5 w-0 flex-1">
                <dl>
                  <dt class="text-sm font-medium text-gray-500 truncate">
                    Total Assets
                  </dt>
                  <dd class="flex items-baseline">
                    <div class="text-2xl font-semibold text-gray-900">
                      {{ overview.total_assets }}
                    </div>
                    <div class="ml-2 text-sm text-gray-500">
                      K{{ formatNumber(overview.total_asset_value) }}
                    </div>
                  </dd>
                </dl>
              </div>
            </div>
          </div>
        </div>

        <!-- Active Allocations -->
        <div class="bg-white overflow-hidden shadow rounded-lg">
          <div class="p-5">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <UserGroupIcon class="h-6 w-6 text-gray-400" />
              </div>
              <div class="ml-5 w-0 flex-1">
                <dl>
                  <dt class="text-sm font-medium text-gray-500 truncate">
                    Active Allocations
                  </dt>
                  <dd class="flex items-baseline">
                    <div class="text-2xl font-semibold text-gray-900">
                      {{ overview.active_allocations }}
                    </div>
                    <div class="ml-2 text-sm text-gray-500">
                      {{ overview.allocation_rate.toFixed(1) }}% rate
                    </div>
                  </dd>
                </dl>
              </div>
            </div>
          </div>
        </div>

        <!-- Income Generated -->
        <div class="bg-white overflow-hidden shadow rounded-lg">
          <div class="p-5">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <CurrencyDollarIcon class="h-6 w-6 text-gray-400" />
              </div>
              <div class="ml-5 w-0 flex-1">
                <dl>
                  <dt class="text-sm font-medium text-gray-500 truncate">
                    Income Generated
                  </dt>
                  <dd class="flex items-baseline">
                    <div class="text-2xl font-semibold text-gray-900">
                      K{{ formatNumber(overview.total_income_generated) }}
                    </div>
                    <div :class="[
                      'ml-2 flex items-baseline text-sm font-semibold',
                      overview.income_growth >= 0 ? 'text-green-600' : 'text-red-600'
                    ]">
                      <ArrowUpIcon v-if="overview.income_growth >= 0" class="h-4 w-4 flex-shrink-0 self-center" />
                      <ArrowDownIcon v-else class="h-4 w-4 flex-shrink-0 self-center" />
                      {{ Math.abs(overview.income_growth).toFixed(1) }}%
                    </div>
                  </dd>
                </dl>
              </div>
            </div>
          </div>
        </div>

        <!-- Maintenance Issues -->
        <div class="bg-white overflow-hidden shadow rounded-lg">
          <div class="p-5">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <ExclamationTriangleIcon class="h-6 w-6 text-gray-400" />
              </div>
              <div class="ml-5 w-0 flex-1">
                <dl>
                  <dt class="text-sm font-medium text-gray-500 truncate">
                    Maintenance Issues
                  </dt>
                  <dd class="flex items-baseline">
                    <div :class="[
                      'text-2xl font-semibold',
                      overview.maintenance_issues > 0 ? 'text-red-600' : 'text-green-600'
                    ]">
                      {{ overview.maintenance_issues }}
                    </div>
                    <div class="ml-2 text-sm text-gray-500">
                      {{ overview.maintenance_compliance.toFixed(1) }}% compliance
                    </div>
                  </dd>
                </dl>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Asset Categories -->
      <div class="bg-white shadow rounded-lg">
        <div class="px-4 py-5 sm:p-6">
          <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">
            Asset Categories
          </h3>
          
          <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div 
              v-for="category in assetCategories" 
              :key="category.name"
              class="border rounded-lg p-4"
            >
              <div class="flex items-center justify-between mb-3">
                <h4 class="text-sm font-medium text-gray-900">{{ category.name }}</h4>
                <span :class="[
                  'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium',
                  category.status === 'active' ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800'
                ]">
                  {{ category.status }}
                </span>
              </div>
              
              <div class="space-y-2">
                <div class="flex justify-between text-sm">
                  <span class="text-gray-600">Total Assets</span>
                  <span class="font-medium">{{ category.total_assets }}</span>
                </div>
                <div class="flex justify-between text-sm">
                  <span class="text-gray-600">Allocated</span>
                  <span class="font-medium">{{ category.allocated_assets }}</span>
                </div>
                <div class="flex justify-between text-sm">
                  <span class="text-gray-600">Available</span>
                  <span class="font-medium">{{ category.available_assets }}</span>
                </div>
                <div class="flex justify-between text-sm">
                  <span class="text-gray-600">Total Value</span>
                  <span class="font-medium">K{{ formatNumber(category.total_value) }}</span>
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
              :href="route('admin.assets.inventory')"
              class="inline-flex items-center justify-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
            >
              <BuildingOffice2Icon class="-ml-1 mr-2 h-4 w-4" />
              Manage Inventory
            </Link>
            
            <Link 
              :href="route('admin.assets.allocations')"
              class="inline-flex items-center justify-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
            >
              <UserGroupIcon class="-ml-1 mr-2 h-4 w-4" />
              View Allocations
            </Link>
            
            <Link 
              :href="route('admin.assets.maintenance')"
              class="inline-flex items-center justify-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
            >
              <WrenchScrewdriverIcon class="-ml-1 mr-2 h-4 w-4" />
              Maintenance
            </Link>
            
            <button 
              @click="generateReport"
              :disabled="generatingReport"
              class="inline-flex items-center justify-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50"
            >
              <DocumentArrowDownIcon v-if="generatingReport" class="animate-spin -ml-1 mr-2 h-4 w-4" />
              <DocumentArrowDownIcon v-else class="-ml-1 mr-2 h-4 w-4" />
              Generate Report
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
  BuildingOffice2Icon,
  CurrencyDollarIcon,
  DocumentArrowDownIcon,
  ExclamationTriangleIcon,
  InformationCircleIcon,
  UserGroupIcon,
  WrenchScrewdriverIcon,
  XCircleIcon,
} from '@heroicons/vue/24/outline'

interface Props {
  overview: any
  assetCategories: any[]
  alerts: any[]
  period: string
}

const props = defineProps<Props>()

const selectedPeriod = ref(props.period)
const loading = ref(false)
const generatingReport = ref(false)

const formatNumber = (value: number): string => {
  return new Intl.NumberFormat('en-US', {
    minimumFractionDigits: 0,
    maximumFractionDigits: 2,
  }).format(value)
}

const updatePeriod = () => {
  loading.value = true
  router.get(route('admin.assets.dashboard'), 
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

const generateReport = async () => {
  if (generatingReport.value) return
  
  generatingReport.value = true
  
  try {
    const response = await fetch(route('admin.assets.generate-performance-report'), {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
      },
      body: JSON.stringify({
        period: selectedPeriod.value
      })
    })
    
    const data = await response.json()
    
    if (data.success) {
      // Download the report
      window.open(data.download_url, '_blank')
    } else {
      alert('Failed to generate report: ' + data.message)
    }
  } catch (error) {
    console.error('Error generating report:', error)
    alert('An error occurred while generating the report')
  } finally {
    generatingReport.value = false
  }
}
</script>