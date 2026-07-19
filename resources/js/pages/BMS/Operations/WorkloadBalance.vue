<template>
  <BMSLayout page-title="Workload Balance">
    <div class="space-y-6">
      <!-- Page Header -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold text-gray-900">Workload Balance</h1>
          <p class="mt-1 text-sm text-gray-500">Monitor team workload distribution and utilization</p>
        </div>
        <Link
          :href="route('bms.operations.scenarios.index')"
          class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition"
        >
          <BeakerIcon class="h-5 w-5" aria-hidden="true" />
          What-If Scenarios
        </Link>
      </div>

      <!-- Summary Statistics -->
      <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
        <div class="bg-white rounded-lg shadow p-6">
          <div class="flex items-center gap-3">
            <div class="p-3 bg-red-100 rounded-lg">
              <ExclamationTriangleIcon class="h-6 w-6 text-red-600" aria-hidden="true" />
            </div>
            <div>
              <p class="text-sm font-medium text-gray-500">Overloaded</p>
              <p class="mt-1 text-2xl font-bold text-red-600">{{ workloadData.overloaded_users }}</p>
            </div>
          </div>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
          <div class="flex items-center gap-3">
            <div class="p-3 bg-green-100 rounded-lg">
              <CheckCircleIcon class="h-6 w-6 text-green-600" aria-hidden="true" />
            </div>
            <div>
              <p class="text-sm font-medium text-gray-500">Balanced</p>
              <p class="mt-1 text-2xl font-bold text-green-600">{{ workloadData.balanced_users }}</p>
            </div>
          </div>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
          <div class="flex items-center gap-3">
            <div class="p-3 bg-blue-100 rounded-lg">
              <UserGroupIcon class="h-6 w-6 text-blue-600" aria-hidden="true" />
            </div>
            <div>
              <p class="text-sm font-medium text-gray-500">Underutilized</p>
              <p class="mt-1 text-2xl font-bold text-blue-600">{{ workloadData.underutilized_users }}</p>
            </div>
          </div>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
          <div class="flex items-center gap-3">
            <div class="p-3 bg-purple-100 rounded-lg">
              <ChartBarIcon class="h-6 w-6 text-purple-600" aria-hidden="true" />
            </div>
            <div>
              <p class="text-sm font-medium text-gray-500">Avg Utilization</p>
              <p class="mt-1 text-2xl font-bold text-purple-600">{{ workloadData.average_utilization }}%</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Workload Cards -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div
          v-for="user in workloadData.users"
          :key="user.user_id"
          class="bg-white rounded-lg shadow p-6"
        >
          <div class="flex items-start justify-between mb-4">
            <div class="flex items-center gap-3">
              <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-blue-600 rounded-full flex items-center justify-center">
                <span class="text-white font-bold text-lg">{{ user.user_name.charAt(0) }}</span>
              </div>
              <div>
                <h3 class="text-lg font-semibold text-gray-900">{{ user.user_name }}</h3>
                <p class="text-sm text-gray-500">{{ user.task_count }} tasks</p>
              </div>
            </div>
            <span
              :class="[
                'px-3 py-1 rounded-full text-xs font-semibold',
                user.status === 'overloaded' ? 'bg-red-100 text-red-700' :
                user.status === 'balanced' ? 'bg-green-100 text-green-700' :
                'bg-blue-100 text-blue-700'
              ]"
            >
              {{ user.status.charAt(0).toUpperCase() + user.status.slice(1) }}
            </span>
          </div>

          <!-- Utilization Bar -->
          <div class="mb-4">
            <div class="flex items-center justify-between mb-2">
              <span class="text-sm font-medium text-gray-700">Utilization</span>
              <span class="text-sm font-bold text-gray-900">{{ user.utilization_percentage }}%</span>
            </div>
            <div class="w-full bg-gray-200 rounded-full h-3 overflow-hidden">
              <div
                :class="[
                  'h-full rounded-full transition-all duration-500',
                  user.status === 'overloaded' ? 'bg-red-500' :
                  user.status === 'balanced' ? 'bg-green-500' :
                  'bg-blue-500'
                ]"
                :style="{ width: Math.min(user.utilization_percentage, 100) + '%' }"
              ></div>
            </div>
          </div>

          <!-- Hours Info -->
          <div class="grid grid-cols-2 gap-4 mb-4">
            <div>
              <p class="text-xs text-gray-500">Total Hours</p>
              <p class="text-lg font-bold text-gray-900">{{ user.total_hours }}h</p>
            </div>
            <div>
              <p class="text-xs text-gray-500">Available</p>
              <p class="text-lg font-bold text-gray-900">40h</p>
            </div>
          </div>

          <!-- Task List -->
          <div class="border-t border-gray-200 pt-4">
            <p class="text-sm font-semibold text-gray-700 mb-2">Active Tasks</p>
            <div class="space-y-2 max-h-40 overflow-y-auto">
              <div
                v-for="task in user.tasks"
                :key="task.id"
                class="flex items-center justify-between text-sm"
              >
                <Link
                  :href="route('bms.operations.tasks.show', task.id)"
                  class="text-blue-600 hover:text-blue-800 truncate flex-1"
                >
                  {{ task.title }}
                </Link>
                <span class="text-gray-500 ml-2">{{ task.estimated_hours }}h</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Empty State -->
      <div v-if="workloadData.users.length === 0" class="bg-white rounded-lg shadow p-12 text-center">
        <UserGroupIcon class="mx-auto h-12 w-12 text-gray-400" aria-hidden="true" />
        <h3 class="mt-2 text-sm font-medium text-gray-900">No workload data</h3>
        <p class="mt-1 text-sm text-gray-500">Assign tasks to team members to see workload distribution.</p>
      </div>
    </div>
  </BMSLayout>
</template>

<script setup lang="ts">
import { Link } from '@inertiajs/vue3'
import BMSLayout from '@/Layouts/BMSLayout.vue'
import {
  UserGroupIcon,
  ChartBarIcon,
  ExclamationTriangleIcon,
  CheckCircleIcon,
  BeakerIcon
} from '@heroicons/vue/24/outline'

interface Task {
  id: number
  title: string
  estimated_hours: number
}

interface UserWorkload {
  user_id: number
  user_name: string
  task_count: number
  total_hours: number
  utilization_percentage: number
  status: 'overloaded' | 'balanced' | 'underutilized'
  tasks: Task[]
}

interface Props {
  workloadData: {
    users: UserWorkload[]
    overloaded_users: number
    balanced_users: number
    underutilized_users: number
    average_utilization: number
  }
}

defineProps<Props>()
</script>
