<template>
  <BMSLayout page-title="Operations Dashboard">
    <div class="space-y-6">
      <!-- Page Header -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold text-gray-900">Operations Dashboard</h1>
          <p class="mt-1 text-sm text-gray-500">Monitor tasks, workload, and bottlenecks</p>
        </div>
        <Link
          :href="route('bms.operations.tasks.create')"
          class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition"
        >
          <PlusIcon class="h-5 w-5" aria-hidden="true" />
          New Task
        </Link>
      </div>

      <!-- Statistics Cards -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="bg-white rounded-lg shadow p-6">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm font-medium text-gray-500">Total Tasks</p>
              <p class="mt-2 text-3xl font-bold text-gray-900">{{ statistics.total }}</p>
            </div>
            <div class="p-3 bg-blue-100 rounded-lg">
              <ClipboardDocumentListIcon class="h-8 w-8 text-blue-600" aria-hidden="true" />
            </div>
          </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm font-medium text-gray-500">Active Tasks</p>
              <p class="mt-2 text-3xl font-bold text-blue-600">{{ statistics.active }}</p>
            </div>
            <div class="p-3 bg-blue-100 rounded-lg">
              <PlayIcon class="h-8 w-8 text-blue-600" aria-hidden="true" />
            </div>
          </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm font-medium text-gray-500">Overdue</p>
              <p class="mt-2 text-3xl font-bold text-red-600">{{ statistics.overdue }}</p>
            </div>
            <div class="p-3 bg-red-100 rounded-lg">
              <ExclamationTriangleIcon class="h-8 w-8 text-red-600" aria-hidden="true" />
            </div>
          </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm font-medium text-gray-500">Completion Rate</p>
              <p class="mt-2 text-3xl font-bold text-green-600">{{ statistics.completion_rate }}%</p>
            </div>
            <div class="p-3 bg-green-100 rounded-lg">
              <CheckCircleIcon class="h-8 w-8 text-green-600" aria-hidden="true" />
            </div>
          </div>
        </div>
      </div>

      <!-- Workload & Bottlenecks -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Team Workload -->
        <div class="bg-white rounded-lg shadow">
          <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900">Team Workload</h2>
          </div>
          <div class="p-6">
            <div v-if="workload.length === 0" class="text-center py-8 text-gray-500">
              No workload data available
            </div>
            <div v-else class="space-y-4">
              <div v-for="item in workload" :key="item.user_id" class="flex items-center justify-between">
                <div class="flex-1">
                  <p class="text-sm font-medium text-gray-900">{{ item.user_name }}</p>
                  <div class="mt-1 flex items-center gap-4 text-xs text-gray-500">
                    <span>{{ item.task_count }} tasks</span>
                    <span>{{ item.total_hours }}h</span>
                    <span v-if="item.overdue_count > 0" class="text-red-600">{{ item.overdue_count }} overdue</span>
                  </div>
                </div>
                <div class="w-24">
                  <div class="h-2 bg-gray-200 rounded-full overflow-hidden">
                    <div 
                      class="h-full bg-blue-600 rounded-full"
                      :style="{ width: `${Math.min((item.total_hours / 40) * 100, 100)}%` }"
                    ></div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Bottlenecks -->
        <div class="bg-white rounded-lg shadow">
          <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900">Workflow Bottlenecks</h2>
          </div>
          <div class="p-6">
            <div v-if="bottlenecks.length === 0" class="text-center py-8 text-gray-500">
              No bottlenecks detected
            </div>
            <div v-else class="space-y-4">
              <div v-for="(bottleneck, index) in bottlenecks" :key="index" class="border-l-4 pl-4"
                :class="{
                  'border-red-500': bottleneck.severity === 'critical',
                  'border-orange-500': bottleneck.severity === 'high',
                  'border-yellow-500': bottleneck.severity === 'medium',
                  'border-gray-300': bottleneck.severity === 'low',
                }"
              >
                <p class="text-sm font-medium text-gray-900">{{ bottleneck.workflow_name }}</p>
                <p class="text-xs text-gray-600">{{ bottleneck.stage_name }}</p>
                <div class="mt-1 flex items-center gap-3 text-xs text-gray-500">
                  <span>{{ bottleneck.tasks_count }} tasks</span>
                  <span>{{ bottleneck.avg_duration_days }} days avg</span>
                  <span class="px-2 py-0.5 rounded-full text-xs font-medium"
                    :class="{
                      'bg-red-100 text-red-700': bottleneck.severity === 'critical',
                      'bg-orange-100 text-orange-700': bottleneck.severity === 'high',
                      'bg-yellow-100 text-yellow-700': bottleneck.severity === 'medium',
                      'bg-gray-100 text-gray-700': bottleneck.severity === 'low',
                    }"
                  >
                    {{ bottleneck.severity }}
                  </span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Recent Tasks -->
      <div class="bg-white rounded-lg shadow">
        <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
          <h2 class="text-lg font-semibold text-gray-900">Recent Tasks</h2>
          <Link
            :href="route('bms.operations.tasks.index')"
            class="text-sm text-blue-600 hover:text-blue-700 font-medium"
          >
            View all
          </Link>
        </div>
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Task</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Priority</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Assigned To</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Due Date</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="task in recentTasks" :key="task.id" class="hover:bg-gray-50">
                <td class="px-6 py-4">
                  <Link
                    :href="route('bms.operations.tasks.show', task.id)"
                    class="text-sm font-medium text-blue-600 hover:text-blue-700"
                  >
                    {{ task.title }}
                  </Link>
                  <p class="text-xs text-gray-500">{{ task.task_number }}</p>
                </td>
                <td class="px-6 py-4">
                  <span class="px-2 py-1 text-xs font-medium rounded-full"
                    :class="{
                      'bg-gray-100 text-gray-700': task.status === 'pending',
                      'bg-blue-100 text-blue-700': task.status === 'in_progress',
                      'bg-green-100 text-green-700': task.status === 'completed',
                      'bg-red-100 text-red-700': task.status === 'blocked',
                    }"
                  >
                    {{ task.status.replace('_', ' ') }}
                  </span>
                </td>
                <td class="px-6 py-4">
                  <span class="px-2 py-1 text-xs font-medium rounded-full"
                    :class="{
                      'bg-gray-100 text-gray-700': task.priority === 'low',
                      'bg-yellow-100 text-yellow-700': task.priority === 'medium',
                      'bg-orange-100 text-orange-700': task.priority === 'high',
                      'bg-red-100 text-red-700': task.priority === 'urgent',
                    }"
                  >
                    {{ task.priority }}
                  </span>
                </td>
                <td class="px-6 py-4 text-sm text-gray-900">
                  {{ task.assigned_user?.name || 'Unassigned' }}
                </td>
                <td class="px-6 py-4 text-sm text-gray-500">
                  {{ task.due_date ? new Date(task.due_date).toLocaleDateString() : '-' }}
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </BMSLayout>
</template>

<script setup lang="ts">
import { Link } from '@inertiajs/vue3'
import BMSLayout from '@/Layouts/BMSLayout.vue'
import {
  PlusIcon,
  ClipboardDocumentListIcon,
  PlayIcon,
  ExclamationTriangleIcon,
  CheckCircleIcon,
} from '@heroicons/vue/24/outline'

interface Props {
  statistics: {
    total: number
    active: number
    completed: number
    overdue: number
    due_today: number
    blocked: number
    by_priority: {
      urgent: number
      high: number
      medium: number
      low: number
    }
    completion_rate: number
  }
  workload: Array<{
    user_id: number
    user_name: string
    task_count: number
    total_hours: number
    overdue_count: number
  }>
  bottlenecks: Array<{
    workflow_name: string
    stage_name: string
    tasks_count: number
    avg_duration_days: number
    severity: string
  }>
  recentTasks: any[]
}

const props = defineProps<Props>()
</script>
