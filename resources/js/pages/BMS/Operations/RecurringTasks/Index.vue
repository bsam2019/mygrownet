<template>
  <CMSLayout page-title="Recurring Tasks">
    <div class="max-w-7xl mx-auto space-y-6">
      <!-- Header -->
      <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-900">Recurring Tasks</h1>
        <button
          @click="showCreateModal = true"
          class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition"
        >
          Create Recurring Task
        </button>
      </div>

      <!-- Recurring Tasks List -->
      <div class="bg-white rounded-lg shadow overflow-hidden">
        <div v-if="recurringTasks.length === 0" class="text-center py-12 text-gray-500">
          No recurring tasks yet. Create one to automate task generation.
        </div>
        <div v-else class="divide-y divide-gray-200">
          <div v-for="task in recurringTasks" :key="task.id" class="p-6 hover:bg-gray-50">
            <div class="flex items-start justify-between">
              <div class="flex-1">
                <div class="flex items-center gap-3">
                  <h3 class="text-lg font-semibold text-gray-900">{{ task.title }}</h3>
                  <span
                    class="px-2 py-1 text-xs font-medium rounded-full"
                    :class="task.is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700'"
                  >
                    {{ task.is_active ? 'Active' : 'Inactive' }}
                  </span>
                </div>
                <p v-if="task.description" class="mt-1 text-sm text-gray-600">{{ task.description }}</p>
                <div class="mt-3 flex items-center gap-4 text-sm text-gray-500">
                  <span class="px-2 py-1 bg-gray-100 rounded">{{ task.recurrence_pattern }}</span>
                  <span class="px-2 py-1 bg-gray-100 rounded">{{ task.priority }}</span>
                  <span v-if="task.assigned_user">Assigned to: {{ task.assigned_user.name }}</span>
                  <span v-if="task.next_generation_at">
                    Next: {{ new Date(task.next_generation_at).toLocaleDateString() }}
                  </span>
                </div>
              </div>
              <button
                @click="toggleTask(task.id)"
                class="ml-4 px-4 py-2 text-sm font-medium rounded-lg transition"
                :class="task.is_active 
                  ? 'text-red-600 border border-red-600 hover:bg-red-50' 
                  : 'text-green-600 border border-green-600 hover:bg-green-50'"
              >
                {{ task.is_active ? 'Deactivate' : 'Activate' }}
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Create Recurring Task Modal -->
    <div v-if="showCreateModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg p-6 max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Create Recurring Task</h3>
        <form @submit.prevent="createRecurringTask">
          <div class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Task Title</label>
              <input
                v-model="form.title"
                type="text"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                required
              />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
              <textarea
                v-model="form.description"
                rows="3"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              ></textarea>
            </div>
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Type</label>
                <select
                  v-model="form.type"
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  required
                >
                  <option value="task">Task</option>
                  <option value="maintenance">Maintenance</option>
                  <option value="inspection">Inspection</option>
                </select>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Priority</label>
                <select
                  v-model="form.priority"
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  required
                >
                  <option value="low">Low</option>
                  <option value="medium">Medium</option>
                  <option value="high">High</option>
                  <option value="urgent">Urgent</option>
                </select>
              </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Recurrence Pattern</label>
                <select
                  v-model="form.recurrence_pattern"
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  required
                >
                  <option value="daily">Daily</option>
                  <option value="weekly">Weekly</option>
                  <option value="monthly">Monthly</option>
                  <option value="quarterly">Quarterly</option>
                  <option value="yearly">Yearly</option>
                </select>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Interval</label>
                <input
                  v-model.number="form.recurrence_interval"
                  type="number"
                  min="1"
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  required
                />
              </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Start Date</label>
                <input
                  v-model="form.start_date"
                  type="date"
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                  required
                />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">End Date (Optional)</label>
                <input
                  v-model="form.end_date"
                  type="date"
                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                />
              </div>
            </div>
          </div>
          <div class="mt-6 flex justify-end gap-2">
            <button
              type="button"
              @click="showCreateModal = false"
              class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition"
            >
              Cancel
            </button>
            <button
              type="submit"
              class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition"
            >
              Create Recurring Task
            </button>
          </div>
        </form>
      </div>
    </div>
  </CMSLayout>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'
import CMSLayout from '@/Layouts/CMSLayout.vue'

interface Props {
  recurringTasks: any[]
}

const props = defineProps<Props>()

const showCreateModal = ref(false)

const form = ref({
  title: '',
  description: '',
  type: 'task',
  priority: 'medium',
  recurrence_pattern: 'weekly',
  recurrence_interval: 1,
  start_date: '',
  end_date: '',
})

const createRecurringTask = () => {
  router.post(route('cms.operations.recurring-tasks.store'), form.value, {
    onSuccess: () => {
      showCreateModal.value = false
      form.value = {
        title: '',
        description: '',
        type: 'task',
        priority: 'medium',
        recurrence_pattern: 'weekly',
        recurrence_interval: 1,
        start_date: '',
        end_date: '',
      }
    },
  })
}

const toggleTask = (id: number) => {
  router.post(route('cms.operations.recurring-tasks.toggle', id))
}
</script>
