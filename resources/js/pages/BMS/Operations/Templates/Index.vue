<template>
  <CMSLayout page-title="Task Templates">
    <div class="max-w-7xl mx-auto space-y-6">
      <!-- Header -->
      <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-gray-900">Task Templates</h1>
        <button
          @click="showCreateModal = true"
          class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition"
        >
          Create Template
        </button>
      </div>

      <!-- Templates List -->
      <div class="bg-white rounded-lg shadow overflow-hidden">
        <div v-if="templates.length === 0" class="text-center py-12 text-gray-500">
          No templates yet. Create your first template to get started.
        </div>
        <div v-else class="divide-y divide-gray-200">
          <div v-for="template in templates" :key="template.id" class="p-6 hover:bg-gray-50">
            <div class="flex items-start justify-between">
              <div class="flex-1">
                <h3 class="text-lg font-semibold text-gray-900">{{ template.name }}</h3>
                <p v-if="template.description" class="mt-1 text-sm text-gray-600">{{ template.description }}</p>
                <div class="mt-3 flex items-center gap-4 text-sm text-gray-500">
                  <span class="px-2 py-1 bg-gray-100 rounded">{{ template.type }}</span>
                  <span class="px-2 py-1 bg-gray-100 rounded">{{ template.priority }}</span>
                  <span v-if="template.estimated_hours">{{ template.estimated_hours }}h estimated</span>
                  <span v-if="template.workflow">{{ template.workflow.name }}</span>
                </div>
              </div>
              <button
                @click="createFromTemplate(template)"
                class="ml-4 px-4 py-2 text-sm font-medium text-blue-600 border border-blue-600 rounded-lg hover:bg-blue-50 transition"
              >
                Use Template
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Create Template Modal -->
    <div v-if="showCreateModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg p-6 max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Create Task Template</h3>
        <form @submit.prevent="createTemplate">
          <div class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Template Name</label>
              <input
                v-model="form.name"
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
                  <option value="order">Order</option>
                  <option value="job">Job</option>
                  <option value="project_task">Project Task</option>
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
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Estimated Hours</label>
              <input
                v-model.number="form.estimated_hours"
                type="number"
                step="0.5"
                min="0"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              />
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
              Create Template
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Use Template Modal -->
    <div v-if="showUseModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Create Task from Template</h3>
        <form @submit.prevent="submitCreateFromTemplate">
          <div class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Task Title</label>
              <input
                v-model="useForm.title"
                type="text"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                required
              />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Due Date</label>
              <input
                v-model="useForm.due_date"
                type="date"
                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
              />
            </div>
          </div>
          <div class="mt-6 flex justify-end gap-2">
            <button
              type="button"
              @click="showUseModal = false"
              class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 rounded-lg hover:bg-gray-200 transition"
            >
              Cancel
            </button>
            <button
              type="submit"
              class="px-4 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition"
            >
              Create Task
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
  templates: any[]
}

const props = defineProps<Props>()

const showCreateModal = ref(false)
const showUseModal = ref(false)
const selectedTemplate = ref<any>(null)

const form = ref({
  name: '',
  description: '',
  type: 'task',
  priority: 'medium',
  estimated_hours: null,
})

const useForm = ref({
  title: '',
  due_date: '',
})

const createTemplate = () => {
  router.post(route('cms.operations.templates.store'), form.value, {
    onSuccess: () => {
      showCreateModal.value = false
      form.value = {
        name: '',
        description: '',
        type: 'task',
        priority: 'medium',
        estimated_hours: null,
      }
    },
  })
}

const createFromTemplate = (template: any) => {
  selectedTemplate.value = template
  useForm.value.title = template.name
  showUseModal.value = true
}

const submitCreateFromTemplate = () => {
  router.post(route('cms.operations.templates.create-task', selectedTemplate.value.id), useForm.value, {
    onSuccess: () => {
      showUseModal.value = false
      useForm.value = { title: '', due_date: '' }
    },
  })
}
</script>
