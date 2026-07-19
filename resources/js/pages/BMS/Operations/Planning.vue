<template>
  <CMSLayout page-title="Planning">
    <div class="space-y-6">
      <!-- Page Header -->
      <div>
        <h1 class="text-2xl font-bold text-gray-900">Planning & Workload Management</h1>
        <p class="mt-1 text-sm text-gray-500">Balance workload and plan capacity</p>
      </div>

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
            <div v-for="item in workload" :key="item.user_id" class="border border-gray-200 rounded-lg p-4">
              <div class="flex items-center justify-between mb-3">
                <div>
                  <p class="text-sm font-semibold text-gray-900">{{ item.user_name }}</p>
                  <div class="mt-1 flex items-center gap-4 text-xs text-gray-500">
                    <span>{{ item.task_count }} tasks</span>
                    <span>{{ item.total_hours }}h allocated</span>
                    <span v-if="item.overdue_count > 0" class="text-red-600">
                      {{ item.overdue_count }} overdue
                    </span>
                  </div>
                </div>
                <div class="text-right">
                  <p class="text-sm font-medium text-gray-900">
                    {{ Math.round((item.total_hours / 40) * 100) }}%
                  </p>
                  <p class="text-xs text-gray-500">Capacity</p>
                </div>
              </div>
              <div class="h-2 bg-gray-200 rounded-full overflow-hidden">
                <div
                  class="h-full rounded-full transition-all"
                  :class="{
                    'bg-green-500': (item.total_hours / 40) * 100 < 80,
                    'bg-yellow-500': (item.total_hours / 40) * 100 >= 80 && (item.total_hours / 40) * 100 < 100,
                    'bg-red-500': (item.total_hours / 40) * 100 >= 100,
                  }"
                  :style="{ width: `${Math.min((item.total_hours / 40) * 100, 100)}%` }"
                ></div>
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
            <div
              v-for="(bottleneck, index) in bottlenecks"
              :key="index"
              class="border-l-4 pl-4 py-3"
              :class="{
                'border-red-500 bg-red-50': bottleneck.severity === 'critical',
                'border-orange-500 bg-orange-50': bottleneck.severity === 'high',
                'border-yellow-500 bg-yellow-50': bottleneck.severity === 'medium',
                'border-gray-300 bg-gray-50': bottleneck.severity === 'low',
              }"
            >
              <div class="flex items-start justify-between">
                <div>
                  <p class="text-sm font-semibold text-gray-900">{{ bottleneck.workflow_name }}</p>
                  <p class="text-sm text-gray-700">Stage: {{ bottleneck.stage_name }}</p>
                  <div class="mt-2 flex items-center gap-4 text-xs text-gray-600">
                    <span>{{ bottleneck.tasks_count }} tasks stuck</span>
                    <span>{{ bottleneck.avg_duration_days }} days average duration</span>
                  </div>
                </div>
                <span
                  class="px-3 py-1 text-xs font-medium rounded-full"
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

      <!-- Upcoming Tasks Calendar View -->
      <div class="bg-white rounded-lg shadow">
        <div class="px-6 py-4 border-b border-gray-200">
          <h2 class="text-lg font-semibold text-gray-900">Upcoming Tasks</h2>
        </div>
        <div class="p-6">
          <div v-if="tasks.length === 0" class="text-center py-8 text-gray-500">
            No upcoming tasks
          </div>
          <div v-else class="space-y-3">
            <div v-for="task in tasks" :key="task.id" class="flex items-center gap-4 p-3 border border-gray-200 rounded-lg hover:border-blue-300 transition">
              <div class="flex-1">
                <p class="text-sm font-medium text-gray-900">{{ task.title }}</p>
                <div class="mt-1 flex items-center gap-3 text-xs text-gray-500">
                  <span>{{ task.assigned_user?.name }}</span>
                  <span>{{ task.workflow_stage?.name }}</span>
                </div>
              </div>
              <div class="text-right">
                <p class="text-sm font-medium text-gray-900">
                  {{ new Date(task.due_date).toLocaleDateString() }}
                </p>
                <p class="text-xs text-gray-500">Due date</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </CMSLayout>
</template>

<script setup lang="ts">
import CMSLayout from '@/Layouts/CMSLayout.vue'

interface Props {
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
  tasks: any[]
}

const props = defineProps<Props>()
</script>
