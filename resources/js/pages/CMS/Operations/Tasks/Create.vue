<template>
  <CMSLayout page-title="Create Task">
    <div class="max-w-3xl mx-auto">
      <div class="bg-white rounded-lg shadow">
        <div class="px-6 py-4 border-b border-gray-200">
          <h2 class="text-lg font-semibold text-gray-900">Create New Task</h2>
        </div>

        <form @submit.prevent="submit" class="p-6 space-y-6">
          <!-- Title -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
              Title <span class="text-red-500">*</span>
            </label>
            <input
              v-model="form.title"
              type="text"
              required
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              :class="{ 'border-red-500': form.errors.title }"
            />
            <p v-if="form.errors.title" class="mt-1 text-sm text-red-600">{{ form.errors.title }}</p>
          </div>

          <!-- Description -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
            <textarea
              v-model="form.description"
              rows="4"
              class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
            ></textarea>
          </div>

          <!-- Type & Priority -->
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">
                Type <span class="text-red-500">*</span>
              </label>
              <select
                v-model="form.type"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              >
                <option value="task">Task</option>
                <option value="order">Order</option>
                <option value="job">Job</option>
                <option value="project_task">Project Task</option>
                <option value="maintenance">Maintenance</option>
                <option value="inspection">Inspection</option>
              </select>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">
                Priority <span class="text-red-500">*</span>
              </label>
              <select
                v-model="form.priority"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              >
                <option value="low">Low</option>
                <option value="medium">Medium</option>
                <option value="high">High</option>
                <option value="urgent">Urgent</option>
              </select>
            </div>
          </div>

          <!-- Workflow & Assigned To -->
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Workflow</label>
              <select
                v-model="form.workflow_id"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              >
                <option value="">No Workflow</option>
                <option v-for="workflow in workflows" :key="workflow.id" :value="workflow.id">
                  {{ workflow.name }}
                </option>
              </select>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Assigned To</label>
              <select
                v-model="form.assigned_to"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              >
                <option value="">Unassigned</option>
                <option v-for="user in users" :key="user.id" :value="user.id">
                  {{ user.name }}
                </option>
              </select>
            </div>
          </div>

          <!-- Due Date & Estimated Hours -->
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Due Date</label>
              <input
                v-model="form.due_date"
                type="date"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Estimated Hours</label>
              <input
                v-model="form.estimated_hours"
                type="number"
                step="0.5"
                min="0"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              />
            </div>
          </div>

          <!-- Actions -->
          <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-200">
            <Link
              :href="route('cms.operations.tasks.index')"
              class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition"
            >
              Cancel
            </Link>
            <button
              type="submit"
              :disabled="form.processing"
              class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition disabled:opacity-50"
            >
              {{ form.processing ? 'Creating...' : 'Create Task' }}
            </button>
          </div>
        </form>
      </div>
    </div>
  </CMSLayout>
</template>

<script setup lang="ts">
import { useForm, Link } from '@inertiajs/vue3'
import CMSLayout from '@/Layouts/CMSLayout.vue'

interface Props {
  workflows: any[]
  users: any[]
}

const props = defineProps<Props>()

const form = useForm({
  title: '',
  description: '',
  type: 'task',
  priority: 'medium',
  workflow_id: '',
  assigned_to: '',
  due_date: '',
  estimated_hours: '',
})

const submit = () => {
  form.post(route('cms.operations.tasks.store'))
}
</script>
