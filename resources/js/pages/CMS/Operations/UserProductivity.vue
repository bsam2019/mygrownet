<template>
  <CMSLayout :page-title="`${user.name} - Productivity`">
    <div class="space-y-6">
      <!-- Page Header -->
      <div class="flex items-center justify-between">
        <div class="flex items-center gap-4">
          <Link
            :href="route('cms.operations.analytics')"
            class="p-2 hover:bg-gray-100 rounded-lg transition"
            aria-label="Back to analytics"
          >
            <ArrowLeftIcon class="h-5 w-5 text-gray-600" aria-hidden="true" />
          </Link>
          <div class="flex items-center gap-3">
            <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-blue-600 rounded-full flex items-center justify-center">
              <span class="text-white font-bold text-lg">{{ user.name.charAt(0) }}</span>
            </div>
            <div>
              <h1 class="text-2xl font-bold text-gray-900">{{ user.name }}</h1>
              <p class="text-sm text-gray-500">Productivity Report</p>
            </div>
          </div>
        </div>
        <select
          v-model="dateRange"
          @change="updateDateRange"
          class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
        >
          <option value="7">Last 7 Days</option>
          <option value="30">Last 30 Days</option>
          <option value="90">Last 90 Days</option>
        </select>
      </div>

      <!-- Key Metrics -->
      <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="bg-white rounded-lg shadow p-6">
          <div class="flex items-center gap-3">
            <div class="p-3 bg-green-100 rounded-lg">
              <CheckCircleIcon class="h-6 w-6 text-green-600" aria-hidden="true" />
            </div>
            <div>
              <p class="text-sm font-medium text-gray-500">Tasks Completed</p>
              <p class="mt-1 text-2xl font-bold text-green-600">{{ metrics.total_completed }}</p>
            </div>
          </div>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
          <div class="flex items-center gap-3">
            <div class="p-3 bg-blue-100 rounded-lg">
              <ClockIcon class="h-6 w-6 text-blue-600" aria-hidden="true" />
            </div>
            <div>
              <p class="text-sm font-medium text-gray-500">Hours Logged</p>
              <p class="mt-1 text-2xl font-bold text-blue-600">{{ metrics.total_hours }}h</p>
            </div>
          </div>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
          <div class="flex items-center gap-3">
            <div class="p-3 bg-purple-100 rounded-lg">
              <BoltIcon class="h-6 w-6 text-purple-600" aria-hidden="true" />
            </div>
            <div>
              <p class="text-sm font-medium text-gray-500">Efficiency</p>
              <p class="mt-1 text-2xl font-bold text-purple-600">{{ metrics.efficiency }}%</p>
            </div>
          </div>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
          <div class="flex items-center gap-3">
            <div class="p-3 bg-amber-100 rounded-lg">
              <CalendarIcon class="h-6 w-6 text-amber-600" aria-hidden="true" />
            </div>
            <div>
              <p class="text-sm font-medium text-gray-500">On-Time Rate</p>
              <p class="mt-1 text-2xl font-bold text-amber-600">{{ metrics.on_time_rate }}%</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Daily Productivity Chart -->
      <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-6">Daily Productivity</h2>
        <div class="space-y-4">
          <div
            v-for="day in metrics.daily_metrics"
            :key="day.date"
            class="border border-gray-200 rounded-lg p-4"
          >
            <div class="flex items-center justify-between mb-3">
              <div>
                <h3 class="text-sm font-semibold text-gray-900">{{ formatDate(day.date) }}</h3>
                <p class="text-xs text-gray-500 mt-1">
                  {{ day.tasks_completed }} completed, {{ day.tasks_started }} started
                </p>
              </div>
              <span class="text-sm font-bold text-gray-900">{{ day.hours_logged }}h</span>
            </div>

            <!-- Progress Bar -->
            <div class="mb-2">
              <div class="flex items-center justify-between mb-1">
                <span class="text-xs font-medium text-gray-600">Efficiency</span>
                <span class="text-xs font-bold text-purple-600">{{ day.efficiency }}%</span>
              </div>
              <div class="w-full bg-gray-200 rounded-full h-2">
                <div
                  class="bg-purple-500 h-full rounded-full transition-all duration-500"
                  :style="{ width: day.efficiency + '%' }"
                ></div>
              </div>
            </div>

            <!-- On-Time vs Late -->
            <div class="grid grid-cols-2 gap-4 pt-2 border-t border-gray-100">
              <div>
                <p class="text-xs text-gray-500">On-Time</p>
                <p class="text-sm font-bold text-green-600">{{ day.on_time_completions }}</p>
              </div>
              <div>
                <p class="text-xs text-gray-500">Late</p>
                <p class="text-sm font-bold text-red-600">{{ day.late_completions }}</p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Task Breakdown -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- By Priority -->
        <div class="bg-white rounded-lg shadow p-6">
          <h2 class="text-lg font-semibold text-gray-900 mb-4">Tasks by Priority</h2>
          <div class="space-y-3">
            <div
              v-for="priority in metrics.by_priority"
              :key="priority.priority"
              class="flex items-center justify-between"
            >
              <div class="flex items-center gap-2">
                <span
                  :class="[
                    'w-3 h-3 rounded-full',
                    priority.priority === 'urgent' ? 'bg-red-500' :
                    priority.priority === 'high' ? 'bg-orange-500' :
                    priority.priority === 'medium' ? 'bg-yellow-500' :
                    'bg-blue-500'
                  ]"
                ></span>
                <span class="text-sm font-medium text-gray-700 capitalize">{{ priority.priority }}</span>
              </div>
              <span class="text-sm font-bold text-gray-900">{{ priority.count }}</span>
            </div>
          </div>
        </div>

        <!-- By Status -->
        <div class="bg-white rounded-lg shadow p-6">
          <h2 class="text-lg font-semibold text-gray-900 mb-4">Current Workload</h2>
          <div class="space-y-3">
            <div
              v-for="status in metrics.by_status"
              :key="status.status"
              class="flex items-center justify-between"
            >
              <div class="flex items-center gap-2">
                <span
                  :class="[
                    'w-3 h-3 rounded-full',
                    status.status === 'completed' ? 'bg-green-500' :
                    status.status === 'in_progress' ? 'bg-blue-500' :
                    status.status === 'blocked' ? 'bg-red-500' :
                    'bg-gray-500'
                  ]"
                ></span>
                <span class="text-sm font-medium text-gray-700 capitalize">{{ status.status.replace('_', ' ') }}</span>
              </div>
              <span class="text-sm font-bold text-gray-900">{{ status.count }}</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Estimation Accuracy -->
      <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">Time Estimation Accuracy</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
          <div class="text-center">
            <p class="text-sm text-gray-500 mb-2">Estimated Hours</p>
            <p class="text-3xl font-bold text-gray-900">{{ metrics.estimated_hours }}h</p>
          </div>
          <div class="text-center">
            <p class="text-sm text-gray-500 mb-2">Actual Hours</p>
            <p class="text-3xl font-bold text-gray-900">{{ metrics.actual_hours }}h</p>
          </div>
          <div class="text-center">
            <p class="text-sm text-gray-500 mb-2">Accuracy</p>
            <p
              :class="[
                'text-3xl font-bold',
                metrics.estimation_accuracy >= 80 ? 'text-green-600' :
                metrics.estimation_accuracy >= 60 ? 'text-amber-600' :
                'text-red-600'
              ]"
            >
              {{ metrics.estimation_accuracy }}%
            </p>
          </div>
        </div>
      </div>
    </div>
  </CMSLayout>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { router, Link } from '@inertiajs/vue3'
import CMSLayout from '@/Layouts/CMSLayout.vue'
import {
  CheckCircleIcon,
  ClockIcon,
  BoltIcon,
  CalendarIcon,
  ArrowLeftIcon
} from '@heroicons/vue/24/outline'

interface DailyMetric {
  date: string
  tasks_completed: number
  tasks_started: number
  hours_logged: number
  efficiency: number
  on_time_completions: number
  late_completions: number
}

interface Props {
  user: {
    id: number
    name: string
  }
  metrics: {
    total_completed: number
    total_hours: number
    efficiency: number
    on_time_rate: number
    estimated_hours: number
    actual_hours: number
    estimation_accuracy: number
    daily_metrics: DailyMetric[]
    by_priority: Array<{ priority: string; count: number }>
    by_status: Array<{ status: string; count: number }>
  }
}

defineProps<Props>()

const dateRange = ref(30)

const formatDate = (dateString: string) => {
  const date = new Date(dateString)
  return date.toLocaleDateString('en-US', { weekday: 'short', month: 'short', day: 'numeric' })
}

const updateDateRange = () => {
  router.get(window.location.pathname, { days: dateRange.value }, {
    preserveState: true,
  })
}
</script>
