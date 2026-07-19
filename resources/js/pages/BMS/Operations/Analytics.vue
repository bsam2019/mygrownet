<template>
  <BMSLayout page-title="Analytics Dashboard">
    <div class="space-y-6">
      <!-- Page Header -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold text-gray-900">Analytics Dashboard</h1>
          <p class="mt-1 text-sm text-gray-500">Team performance and productivity insights</p>
        </div>
        <div class="flex items-center gap-3">
          <select
            v-model="dateRange"
            @change="updateDateRange"
            class="px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
          >
            <option value="7">Last 7 Days</option>
            <option value="30">Last 30 Days</option>
            <option value="90">Last 90 Days</option>
          </select>
          <Link
            :href="route('bms.operations.gantt')"
            class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition"
          >
            <ChartBarIcon class="h-5 w-5" aria-hidden="true" />
            Gantt Chart
          </Link>
        </div>
      </div>

      <!-- Key Metrics -->
      <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="bg-white rounded-lg shadow p-6">
          <div class="flex items-center gap-3">
            <div class="p-3 bg-green-100 rounded-lg">
              <CheckCircleIcon class="h-6 w-6 text-green-600" aria-hidden="true" />
            </div>
            <div>
              <p class="text-sm font-medium text-gray-500">Completion Rate</p>
              <p class="mt-1 text-2xl font-bold text-green-600">{{ analytics.completion_rate }}%</p>
            </div>
          </div>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
          <div class="flex items-center gap-3">
            <div class="p-3 bg-blue-100 rounded-lg">
              <ClockIcon class="h-6 w-6 text-blue-600" aria-hidden="true" />
            </div>
            <div>
              <p class="text-sm font-medium text-gray-500">Avg Completion Time</p>
              <p class="mt-1 text-2xl font-bold text-blue-600">{{ analytics.average_completion_time }}h</p>
            </div>
          </div>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
          <div class="flex items-center gap-3">
            <div class="p-3 bg-purple-100 rounded-lg">
              <ChartPieIcon class="h-6 w-6 text-purple-600" aria-hidden="true" />
            </div>
            <div>
              <p class="text-sm font-medium text-gray-500">Estimation Accuracy</p>
              <p class="mt-1 text-2xl font-bold text-purple-600">{{ analytics.estimation_accuracy }}%</p>
            </div>
          </div>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
          <div class="flex items-center gap-3">
            <div class="p-3 bg-amber-100 rounded-lg">
              <ExclamationTriangleIcon class="h-6 w-6 text-amber-600" aria-hidden="true" />
            </div>
            <div>
              <p class="text-sm font-medium text-gray-500">Overdue Tasks</p>
              <p class="mt-1 text-2xl font-bold text-amber-600">{{ analytics.overdue_tasks }}</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Completion Trends Chart -->
      <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-6">Task Completion Trends</h2>
        <div class="space-y-4">
          <div
            v-for="trend in analytics.completion_trends"
            :key="trend.period"
            class="border border-gray-200 rounded-lg p-4"
          >
            <div class="flex items-center justify-between mb-2">
              <span class="text-sm font-medium text-gray-700">{{ formatPeriod(trend.period) }}</span>
              <span class="text-sm font-bold text-gray-900">{{ trend.completed_count }} tasks</span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-2">
              <div
                class="bg-green-500 h-full rounded-full transition-all duration-500"
                :style="{ width: (trend.completed_count / maxCompletedCount * 100) + '%' }"
              ></div>
            </div>
          </div>
        </div>
      </div>

      <!-- Team Performance Comparison -->
      <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-6">Team Performance</h2>
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead>
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Team Member
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Tasks Completed
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Hours Logged
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  On-Time Rate
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Avg Duration
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Actions
                </th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr
                v-for="member in analytics.team_performance"
                :key="member.user_id"
                class="hover:bg-gray-50"
              >
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex items-center">
                    <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-blue-600 rounded-full flex items-center justify-center">
                      <span class="text-white font-bold text-sm">{{ member.user_name.charAt(0) }}</span>
                    </div>
                    <div class="ml-3">
                      <p class="text-sm font-medium text-gray-900">{{ member.user_name }}</p>
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span class="text-sm font-bold text-gray-900">{{ member.tasks_completed }}</span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span class="text-sm text-gray-900">{{ member.hours_logged }}h</span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span
                    :class="[
                      'px-2 py-1 rounded-full text-xs font-semibold',
                      member.on_time_rate >= 80 ? 'bg-green-100 text-green-700' :
                      member.on_time_rate >= 60 ? 'bg-amber-100 text-amber-700' :
                      'bg-red-100 text-red-700'
                    ]"
                  >
                    {{ member.on_time_rate }}%
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span class="text-sm text-gray-900">{{ member.average_duration }}h</span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <Link
                    :href="route('bms.operations.analytics.user', member.user_id)"
                    class="text-blue-600 hover:text-blue-800 text-sm font-medium"
                  >
                    View Details
                  </Link>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Empty State -->
      <div v-if="analytics.team_performance.length === 0" class="bg-white rounded-lg shadow p-12 text-center">
        <ChartBarIcon class="mx-auto h-12 w-12 text-gray-400" aria-hidden="true" />
        <h3 class="mt-2 text-sm font-medium text-gray-900">No analytics data</h3>
        <p class="mt-1 text-sm text-gray-500">Complete some tasks to see performance analytics.</p>
      </div>
    </div>
  </BMSLayout>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'
import { router, Link } from '@inertiajs/vue3'
import BMSLayout from '@/Layouts/BMSLayout.vue'
import {
  ChartBarIcon,
  CheckCircleIcon,
  ClockIcon,
  ChartPieIcon,
  ExclamationTriangleIcon
} from '@heroicons/vue/24/outline'

interface CompletionTrend {
  period: string
  completed_count: number
}

interface TeamMember {
  user_id: number
  user_name: string
  tasks_completed: number
  hours_logged: number
  on_time_rate: number
  average_duration: number
}

interface Props {
  analytics: {
    completion_rate: number
    average_completion_time: number
    estimation_accuracy: number
    overdue_tasks: number
    completion_trends: CompletionTrend[]
    team_performance: TeamMember[]
  }
}

const props = defineProps<Props>()

const dateRange = ref(30)

const maxCompletedCount = computed(() => {
  if (props.analytics.completion_trends.length === 0) return 1
  return Math.max(...props.analytics.completion_trends.map(t => t.completed_count))
})

const formatPeriod = (period: string) => {
  const date = new Date(period)
  return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })
}

const updateDateRange = () => {
  router.get(route('bms.operations.analytics'), { days: dateRange.value }, {
    preserveState: true,
  })
}
</script>
