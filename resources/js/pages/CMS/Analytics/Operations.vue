<script setup lang="ts">
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'
import { ChartBarIcon, ClockIcon, CheckCircleIcon, TruckIcon } from '@heroicons/vue/24/outline'
import CMSLayoutNew from '@/Layouts/CMSLayoutNew.vue'

defineOptions({
  layout: CMSLayoutNew
})

interface Props {
  metrics: {
    job_completion_rate: number
    average_job_duration: number
    jobs_by_status: Record<string, number>
    jobs_by_type: Record<string, number>
    worker_productivity: Array<{
      worker_name: string
      total_hours: number
      total_days: number
      total_earned: number
    }>
    inventory_turnover: number
    jobs_timeline: Record<string, number>
  }
  period: string
}

const props = defineProps<Props>()

const selectedPeriod = ref(props.period)

const changePeriod = (period: string) => {
  selectedPeriod.value = period
  router.get(route('cms.analytics.operations'), { period }, {
    preserveState: true,
    preserveScroll: true,
  })
}

const formatNumber = (value: number) => {
  return new Intl.NumberFormat('en-US', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  }).format(value)
}

const getStatusColor = (status: string) => {
  const colors = {
    pending: 'bg-gray-500',
    in_progress: 'bg-blue-500',
    completed: 'bg-green-500',
    cancelled: 'bg-red-500',
  }
  return colors[status as keyof typeof colors] || 'bg-gray-500'
}
</script>

<template>
  <div class="px-4 sm:px-6 lg:px-8 py-8">
    <!-- Header -->
    <div class="sm:flex sm:items-center sm:justify-between mb-6">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">Operations Dashboard</h1>
        <p class="mt-1 text-sm text-gray-500">Monitor operational performance and efficiency</p>
      </div>
      <div class="mt-4 sm:mt-0">
        <select
          v-model="selectedPeriod"
          @change="changePeriod(selectedPeriod)"
          class="block w-full rounded-lg border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
        >
          <option value="week">Last Week</option>
          <option value="month">Last Month</option>
          <option value="quarter">Last Quarter</option>
          <option value="year">Last Year</option>
        </select>
      </div>
    </div>

    <!-- Key Metrics -->
    <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4 mb-8">
      <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200">
        <div class="p-5">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                <CheckCircleIcon class="h-6 w-6 text-green-600" aria-hidden="true" />
              </div>
            </div>
            <div class="ml-5 w-0 flex-1">
              <dl>
                <dt class="text-sm font-medium text-gray-500 truncate">Completion Rate</dt>
                <dd class="flex items-baseline">
                  <div class="text-2xl font-semibold text-gray-900">{{ metrics.job_completion_rate }}%</div>
                </dd>
              </dl>
            </div>
          </div>
        </div>
      </div>

      <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200">
        <div class="p-5">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                <ClockIcon class="h-6 w-6 text-blue-600" aria-hidden="true" />
              </div>
            </div>
            <div class="ml-5 w-0 flex-1">
              <dl>
                <dt class="text-sm font-medium text-gray-500 truncate">Avg Job Duration</dt>
                <dd class="flex items-baseline">
                  <div class="text-2xl font-semibold text-gray-900">{{ metrics.average_job_duration }}</div>
                  <span class="ml-2 text-sm text-gray-500">days</span>
                </dd>
              </dl>
            </div>
          </div>
        </div>
      </div>

      <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200">
        <div class="p-5">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                <ChartBarIcon class="h-6 w-6 text-purple-600" aria-hidden="true" />
              </div>
            </div>
            <div class="ml-5 w-0 flex-1">
              <dl>
                <dt class="text-sm font-medium text-gray-500 truncate">Active Jobs</dt>
                <dd class="flex items-baseline">
                  <div class="text-2xl font-semibold text-gray-900">
                    {{ (metrics.jobs_by_status.pending || 0) + (metrics.jobs_by_status.in_progress || 0) }}
                  </div>
                </dd>
              </dl>
            </div>
          </div>
        </div>
      </div>

      <div class="bg-white overflow-hidden shadow-sm rounded-lg border border-gray-200">
        <div class="p-5">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <div class="w-12 h-12 bg-amber-100 rounded-lg flex items-center justify-center">
                <TruckIcon class="h-6 w-6 text-amber-600" aria-hidden="true" />
              </div>
            </div>
            <div class="ml-5 w-0 flex-1">
              <dl>
                <dt class="text-sm font-medium text-gray-500 truncate">Inventory Turnover</dt>
                <dd class="flex items-baseline">
                  <div class="text-2xl font-semibold text-gray-900">{{ formatNumber(metrics.inventory_turnover) }}</div>
                </dd>
              </dl>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
      <!-- Jobs by Status -->
      <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Jobs by Status</h3>
        <div class="space-y-3">
          <div v-for="(count, status) in metrics.jobs_by_status" :key="status" class="flex items-center justify-between">
            <div class="flex items-center gap-3">
              <div :class="['w-3 h-3 rounded-full', getStatusColor(status)]"></div>
              <span class="text-sm font-medium text-gray-700 capitalize">{{ status.replace('_', ' ') }}</span>
            </div>
            <span class="text-sm font-semibold text-gray-900">{{ count }}</span>
          </div>
        </div>
      </div>

      <!-- Jobs by Type -->
      <div class="bg-white rounded-lg shadow p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Jobs by Type</h3>
        <div class="space-y-3">
          <div v-for="(count, type) in metrics.jobs_by_type" :key="type" class="flex items-center justify-between">
            <span class="text-sm font-medium text-gray-700">{{ type }}</span>
            <span class="text-sm font-semibold text-gray-900">{{ count }}</span>
          </div>
          <div v-if="Object.keys(metrics.jobs_by_type).length === 0" class="text-center py-4 text-sm text-gray-500">
            No data available
          </div>
        </div>
      </div>
    </div>

    <!-- Worker Productivity -->
    <div class="bg-white rounded-lg shadow mb-6">
      <div class="px-6 py-4 border-b border-gray-200">
        <h3 class="text-lg font-semibold text-gray-900">Worker Productivity</h3>
      </div>
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Worker</th>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Hours</th>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Days</th>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Earned</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="worker in metrics.worker_productivity" :key="worker.worker_name">
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                {{ worker.worker_name }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">
                {{ worker.total_hours }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">
                {{ worker.total_days }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 text-right">
                K{{ formatNumber(worker.total_earned) }}
              </td>
            </tr>
            <tr v-if="metrics.worker_productivity.length === 0">
              <td colspan="4" class="px-6 py-8 text-center text-sm text-gray-500">
                No worker data available
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Jobs Timeline -->
    <div class="bg-white rounded-lg shadow p-6">
      <h3 class="text-lg font-semibold text-gray-900 mb-4">Jobs Timeline</h3>
      <div class="space-y-2">
        <div v-for="(count, date) in metrics.jobs_timeline" :key="date" class="flex items-center justify-between">
          <span class="text-sm text-gray-600">{{ date }}</span>
          <div class="flex items-center gap-2">
            <div class="w-32 bg-gray-200 rounded-full h-2">
              <div 
                class="bg-blue-600 h-2 rounded-full" 
                :style="{ width: `${Math.min((count / Math.max(...Object.values(metrics.jobs_timeline))) * 100, 100)}%` }"
              ></div>
            </div>
            <span class="text-sm font-medium text-gray-900 w-8 text-right">{{ count }}</span>
          </div>
        </div>
        <div v-if="Object.keys(metrics.jobs_timeline).length === 0" class="text-center py-4 text-sm text-gray-500">
          No timeline data available
        </div>
      </div>
    </div>
  </div>
</template>
