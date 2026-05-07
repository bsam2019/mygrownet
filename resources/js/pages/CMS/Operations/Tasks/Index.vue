<template>
  <CMSLayout page-title="All Tasks">
    <div class="space-y-6">
      <!-- Page Header -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold text-gray-900">All Tasks</h1>
          <p class="mt-1 text-sm text-gray-500">Manage all tasks across workflows</p>
        </div>
        <Link
          :href="route('cms.operations.tasks.create')"
          class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition"
        >
          <PlusIcon class="h-5 w-5" aria-hidden="true" />
          New Task
        </Link>
      </div>

      <!-- Filters -->
      <div class="bg-white rounded-lg shadow p-4">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
            <input
              v-model="filterForm.search"
              type="text"
              placeholder="Search tasks..."
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              @input="applyFilters"
            />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
            <select
              v-model="filterForm.status"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              @change="applyFilters"
            >
              <option value="">All Statuses</option>
              <option value="pending">Pending</option>
              <option value="in_progress">In Progress</option>
              <option value="completed">Completed</option>
              <option value="blocked">Blocked</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Priority</label>
            <select
              v-model="filterForm.priority"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              @change="applyFilters"
            >
              <option value="">All Priorities</option>
              <option value="low">Low</option>
              <option value="medium">Medium</option>
              <option value="high">High</option>
              <option value="urgent">Urgent</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Assigned To</label>
            <select
              v-model="filterForm.assigned_to"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              @change="applyFilters"
            >
              <option value="">All Users</option>
              <option v-for="user in users" :key="user.id" :value="user.id">
                {{ user.name }}
              </option>
            </select>
          </div>
        </div>
      </div>

      <!-- Tasks Table -->
      <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Task</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Priority</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Assigned To</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Due Date</th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="task in tasks.data" :key="task.id" class="hover:bg-gray-50">
              <td class="px-6 py-4">
                <Link
                  :href="route('cms.operations.tasks.show', task.id)"
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
              <td class="px-6 py-4">
                <Link
                  :href="route('cms.operations.tasks.show', task.id)"
                  class="text-sm text-blue-600 hover:text-blue-700 font-medium"
                >
                  View
                </Link>
              </td>
            </tr>
          </tbody>
        </table>

        <!-- Pagination -->
        <div class="px-6 py-4 border-t border-gray-200 flex items-center justify-between">
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
  </CMSLayout>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { router, Link } from '@inertiajs/vue3'
import CMSLayout from '@/Layouts/CMSLayout.vue'
import { PlusIcon } from '@heroicons/vue/24/outline'

interface Props {
  tasks: {
    data: any[]
    from: number
    to: number
    total: number
    links: any[]
  }
  workflows: any[]
  users: any[]
  filters: {
    status?: string
    priority?: string
    assigned_to?: number
    workflow_id?: number
    search?: string
  }
}

const props = defineProps<Props>()

const filterForm = ref({
  search: props.filters.search || '',
  status: props.filters.status || '',
  priority: props.filters.priority || '',
  assigned_to: props.filters.assigned_to || '',
  workflow_id: props.filters.workflow_id || '',
})

let filterTimeout: ReturnType<typeof setTimeout>

const applyFilters = () => {
  clearTimeout(filterTimeout)
  filterTimeout = setTimeout(() => {
    router.get(route('cms.operations.tasks.index'), filterForm.value, {
      preserveState: true,
      preserveScroll: true,
    })
  }, 300)
}
</script>
