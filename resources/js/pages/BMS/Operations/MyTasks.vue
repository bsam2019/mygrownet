<template>
  <BMSLayout page-title="My Tasks">
    <div class="space-y-6">
      <!-- Page Header -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold text-gray-900">My Tasks</h1>
          <p class="mt-1 text-sm text-gray-500">Tasks assigned to you</p>
        </div>
        <Link
          :href="route('bms.operations.tasks.create')"
          class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition"
        >
          <PlusIcon class="h-5 w-5" aria-hidden="true" />
          New Task
        </Link>
      </div>

      <!-- Statistics -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white rounded-lg shadow p-6">
          <p class="text-sm font-medium text-gray-500">Active Tasks</p>
          <p class="mt-2 text-3xl font-bold text-blue-600">{{ statistics.total }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
          <p class="text-sm font-medium text-gray-500">Due Today</p>
          <p class="mt-2 text-3xl font-bold text-orange-600">{{ statistics.due_today }}</p>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
          <p class="text-sm font-medium text-gray-500">Overdue</p>
          <p class="mt-2 text-3xl font-bold text-red-600">{{ statistics.overdue }}</p>
        </div>
      </div>

      <!-- Filter Tabs -->
      <div class="bg-white rounded-lg shadow">
        <div class="border-b border-gray-200">
          <nav class="flex -mb-px">
            <button
              @click="changeFilter(null)"
              :class="[
                !filter ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                'px-6 py-3 border-b-2 font-medium text-sm transition'
              ]"
            >
              All Tasks
            </button>
            <button
              @click="changeFilter('today')"
              :class="[
                filter === 'today' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                'px-6 py-3 border-b-2 font-medium text-sm transition'
              ]"
            >
              Due Today
            </button>
            <button
              @click="changeFilter('overdue')"
              :class="[
                filter === 'overdue' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                'px-6 py-3 border-b-2 font-medium text-sm transition'
              ]"
            >
              Overdue
            </button>
            <button
              @click="changeFilter('active')"
              :class="[
                filter === 'active' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                'px-6 py-3 border-b-2 font-medium text-sm transition'
              ]"
            >
              In Progress
            </button>
          </nav>
        </div>

        <!-- Tasks List -->
        <div class="p-6">
          <div v-if="tasks.data.length === 0" class="text-center py-12">
            <ClipboardDocumentCheckIcon class="mx-auto h-12 w-12 text-gray-400" aria-hidden="true" />
            <h3 class="mt-2 text-sm font-medium text-gray-900">No tasks</h3>
            <p class="mt-1 text-sm text-gray-500">You don't have any tasks in this category.</p>
          </div>

          <div v-else class="space-y-4">
            <div
              v-for="task in tasks.data"
              :key="task.id"
              class="border border-gray-200 rounded-lg p-4 hover:border-blue-300 hover:shadow-md transition"
            >
              <div class="flex items-start justify-between">
                <div class="flex-1">
                  <Link
                    :href="route('bms.operations.tasks.show', task.id)"
                    class="text-base font-semibold text-gray-900 hover:text-blue-600"
                  >
                    {{ task.title }}
                  </Link>
                  <p class="mt-1 text-sm text-gray-500">{{ task.task_number }}</p>
                  
                  <div class="mt-3 flex items-center gap-4">
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

                    <span v-if="task.workflow_stage" class="text-xs text-gray-500">
                      {{ task.workflow_stage.name }}
                    </span>

                    <span v-if="task.due_date" class="text-xs text-gray-500">
                      Due: {{ new Date(task.due_date).toLocaleDateString() }}
                    </span>
                  </div>
                </div>

                <div class="ml-4 flex gap-2">
                  <button
                    v-if="task.status === 'pending'"
                    @click="startTask(task.id)"
                    class="px-3 py-1.5 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition"
                  >
                    Start
                  </button>
                  <button
                    v-if="task.status === 'in_progress'"
                    @click="completeTask(task.id)"
                    class="px-3 py-1.5 text-sm font-medium text-white bg-green-600 rounded-lg hover:bg-green-700 transition"
                  >
                    Complete
                  </button>
                </div>
              </div>
            </div>
          </div>

          <!-- Pagination -->
          <div v-if="tasks.data.length > 0" class="mt-6 flex items-center justify-between border-t border-gray-200 pt-4">
            <div class="text-sm text-gray-700">
              Showing {{ tasks.from }} to {{ tasks.to }} of {{ tasks.total }} tasks
            </div>
            <div class="flex gap-2">
              <template v-for="link in tasks.links" :key="link.label">
                <Link
                  v-if="link.url"
                  :href="link.url"
                  :class="[
                    link.active ? 'bg-blue-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-50',
                    'px-3 py-2 text-sm font-medium border border-gray-300 rounded-lg transition'
                  ]"
                  v-html="link.label"
                />
                <span
                  v-else
                  :class="[
                    'bg-white text-gray-700 opacity-50 cursor-not-allowed',
                    'px-3 py-2 text-sm font-medium border border-gray-300 rounded-lg'
                  ]"
                  v-html="link.label"
                />
              </template>
            </div>
          </div>
        </div>
      </div>
    </div>
  </BMSLayout>
</template>

<script setup lang="ts">
import { router, Link } from '@inertiajs/vue3'
import BMSLayout from '@/Layouts/BMSLayout.vue'
import { ClipboardDocumentCheckIcon, PlusIcon } from '@heroicons/vue/24/outline'

interface Props {
  tasks: {
    data: any[]
    from: number
    to: number
    total: number
    links: any[]
  }
  statistics: {
    total: number
    due_today: number
    overdue: number
  }
  filter: string | null
}

const props = defineProps<Props>()

const changeFilter = (newFilter: string | null) => {
  router.get(route('bms.operations.my-tasks'), { filter: newFilter }, {
    preserveState: true,
    preserveScroll: true,
  })
}

const startTask = (taskId: number) => {
  router.post(route('bms.operations.tasks.start', taskId), {}, {
    preserveScroll: true,
  })
}

const completeTask = (taskId: number) => {
  router.post(route('bms.operations.tasks.complete', taskId), {}, {
    preserveScroll: true,
  })
}
</script>
