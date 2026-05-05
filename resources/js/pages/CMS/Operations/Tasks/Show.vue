<template>
  <CMSLayout :page-title="`Task: ${task.title}`">
    <div class="max-w-5xl mx-auto space-y-6">
      <!-- Task Header -->
      <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-start justify-between">
          <div class="flex-1">
            <div class="flex items-center gap-3">
              <h1 class="text-2xl font-bold text-gray-900">{{ task.title }}</h1>
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
            </div>
            <p class="mt-1 text-sm text-gray-500">{{ task.task_number }}</p>
          </div>

          <div class="flex gap-2">
            <button
              v-if="task.status === 'pending'"
              @click="startTask"
              class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition"
            >
              Start Task
            </button>
            <button
              v-if="task.status === 'in_progress'"
              @click="completeTask"
              class="px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-lg hover:bg-green-700 transition"
            >
              Complete Task
            </button>
          </div>
        </div>

        <div class="mt-6 grid grid-cols-2 md:grid-cols-4 gap-4">
          <div>
            <p class="text-sm font-medium text-gray-500">Assigned To</p>
            <p class="mt-1 text-sm text-gray-900">{{ task.assigned_user?.name || 'Unassigned' }}</p>
          </div>
          <div>
            <p class="text-sm font-medium text-gray-500">Due Date</p>
            <p class="mt-1 text-sm text-gray-900">
              {{ task.due_date ? new Date(task.due_date).toLocaleDateString() : 'Not set' }}
            </p>
          </div>
          <div>
            <p class="text-sm font-medium text-gray-500">Estimated Hours</p>
            <p class="mt-1 text-sm text-gray-900">{{ task.estimated_hours || '-' }}</p>
          </div>
          <div>
            <p class="text-sm font-medium text-gray-500">Workflow Stage</p>
            <p class="mt-1 text-sm text-gray-900">{{ task.workflow_stage?.name || 'No workflow' }}</p>
          </div>
        </div>

        <div v-if="task.description" class="mt-6">
          <p class="text-sm font-medium text-gray-500">Description</p>
          <p class="mt-2 text-sm text-gray-700 whitespace-pre-wrap">{{ task.description }}</p>
        </div>
      </div>

      <!-- Activity Log -->
      <div class="bg-white rounded-lg shadow">
        <div class="px-6 py-4 border-b border-gray-200">
          <h2 class="text-lg font-semibold text-gray-900">Activity Log</h2>
        </div>
        <div class="p-6">
          <div v-if="task.logs.length === 0" class="text-center py-8 text-gray-500">
            No activity yet
          </div>
          <div v-else class="space-y-4">
            <div v-for="log in task.logs" :key="log.id" class="flex gap-4">
              <div class="flex-shrink-0">
                <div class="w-8 h-8 bg-gray-200 rounded-full flex items-center justify-center">
                  <span class="text-xs font-medium text-gray-600">{{ log.user?.name?.charAt(0) }}</span>
                </div>
              </div>
              <div class="flex-1">
                <p class="text-sm text-gray-900">
                  <span class="font-medium">{{ log.user?.name }}</span>
                  <span class="text-gray-600"> {{ log.action }} </span>
                </p>
                <p v-if="log.note" class="mt-1 text-sm text-gray-600">{{ log.note }}</p>
                <p class="mt-1 text-xs text-gray-500">{{ new Date(log.created_at).toLocaleString() }}</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </CMSLayout>
</template>

<script setup lang="ts">
import { router } from '@inertiajs/vue3'
import CMSLayout from '@/Layouts/CMSLayout.vue'

interface Props {
  task: any
}

const props = defineProps<Props>()

const startTask = () => {
  router.post(route('cms.operations.tasks.start', props.task.id))
}

const completeTask = () => {
  router.post(route('cms.operations.tasks.complete', props.task.id))
}
</script>
