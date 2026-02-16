<template>
  <CMSLayout title="Onboarding Templates">
    <div class="space-y-6">
      <!-- Header -->
      <div class="flex justify-between items-center">
        <div>
          <h1 class="text-2xl font-bold text-gray-900">Onboarding Templates</h1>
          <p class="text-sm text-gray-600 mt-1">Manage employee onboarding workflows</p>
        </div>
        <button
          @click="showCreateModal = true"
          class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
        >
          Create Template
        </button>
      </div>

      <!-- Templates List -->
      <div class="grid gap-6">
        <div
          v-for="template in templates"
          :key="template.id"
          class="bg-white rounded-lg shadow p-6"
        >
          <div class="flex justify-between items-start mb-4">
            <div>
              <h3 class="text-lg font-semibold text-gray-900">{{ template.template_name }}</h3>
              <p class="text-sm text-gray-600 mt-1">
                {{ template.department?.name || 'All Departments' }}
              </p>
              <span
                v-if="template.is_default"
                class="inline-block mt-2 px-2 py-1 text-xs font-medium bg-blue-100 text-blue-800 rounded-full"
              >
                Default Template
              </span>
            </div>
            <button
              @click="selectedTemplate = template"
              class="text-blue-600 hover:text-blue-800 text-sm font-medium"
            >
              Add Task
            </button>
          </div>

          <!-- Tasks List -->
          <div class="space-y-2">
            <div
              v-for="task in template.tasks"
              :key="task.id"
              class="flex items-center justify-between p-3 bg-gray-50 rounded-lg"
            >
              <div class="flex-1">
                <div class="flex items-center gap-2">
                  <span class="text-sm font-medium text-gray-900">{{ task.task_name }}</span>
                  <span
                    v-if="task.is_mandatory"
                    class="px-2 py-0.5 text-xs bg-red-100 text-red-800 rounded"
                  >
                    Required
                  </span>
                </div>
                <p class="text-xs text-gray-600 mt-1">
                  {{ task.task_category }} • Due {{ task.due_days_after_start }} days after start • Assigned to {{ task.assigned_to_role }}
                </p>
              </div>
            </div>
            <div v-if="!template.tasks?.length" class="text-sm text-gray-500 text-center py-4">
              No tasks added yet
            </div>
          </div>
        </div>
      </div>

      <!-- Create Template Modal -->
      <div
        v-if="showCreateModal"
        class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
      >
        <div class="bg-white rounded-lg p-6 max-w-md w-full">
          <h2 class="text-xl font-bold text-gray-900 mb-4">Create Template</h2>
          <form @submit.prevent="createTemplate" class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Template Name</label>
              <input
                v-model="templateForm.template_name"
                type="text"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-lg"
              />
            </div>
            <div class="flex items-center">
              <input
                v-model="templateForm.is_default"
                type="checkbox"
                class="h-4 w-4 text-blue-600 rounded"
              />
              <label class="ml-2 text-sm text-gray-700">Set as default template</label>
            </div>
            <div class="flex justify-end gap-4">
              <button
                type="button"
                @click="showCreateModal = false"
                class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50"
              >
                Cancel
              </button>
              <button
                type="submit"
                class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
              >
                Create
              </button>
            </div>
          </form>
        </div>
      </div>

      <!-- Add Task Modal -->
      <div
        v-if="selectedTemplate"
        class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
      >
        <div class="bg-white rounded-lg p-6 max-w-md w-full">
          <h2 class="text-xl font-bold text-gray-900 mb-4">Add Task</h2>
          <form @submit.prevent="addTask" class="space-y-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Task Name</label>
              <input
                v-model="taskForm.task_name"
                type="text"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-lg"
              />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Category</label>
              <select
                v-model="taskForm.task_category"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-lg"
              >
                <option value="documentation">Documentation</option>
                <option value="system_access">System Access</option>
                <option value="training">Training</option>
                <option value="equipment">Equipment</option>
                <option value="introduction">Introduction</option>
                <option value="other">Other</option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Assigned To</label>
              <select
                v-model="taskForm.assigned_to_role"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-lg"
              >
                <option value="hr">HR</option>
                <option value="it">IT</option>
                <option value="manager">Manager</option>
                <option value="employee">Employee</option>
              </select>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-2">Due Days After Start</label>
              <input
                v-model.number="taskForm.due_days_after_start"
                type="number"
                min="0"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-lg"
              />
            </div>
            <div class="flex items-center">
              <input
                v-model="taskForm.is_mandatory"
                type="checkbox"
                class="h-4 w-4 text-blue-600 rounded"
              />
              <label class="ml-2 text-sm text-gray-700">Mandatory task</label>
            </div>
            <div class="flex justify-end gap-4">
              <button
                type="button"
                @click="selectedTemplate = null"
                class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50"
              >
                Cancel
              </button>
              <button
                type="submit"
                class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700"
              >
                Add Task
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </CMSLayout>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import CMSLayout from '@/Layouts/CMSLayout.vue';

const props = defineProps<{
  templates: any[];
}>();

const showCreateModal = ref(false);
const selectedTemplate = ref<any>(null);

const templateForm = ref({
  template_name: '',
  is_default: false,
});

const taskForm = ref({
  task_name: '',
  task_category: 'documentation',
  assigned_to_role: 'hr',
  due_days_after_start: 0,
  is_mandatory: true,
});

const createTemplate = () => {
  router.post(route('cms.hrms-onboarding.templates.store'), templateForm.value, {
    onSuccess: () => {
      showCreateModal.value = false;
      templateForm.value = { template_name: '', is_default: false };
    },
  });
};

const addTask = () => {
  router.post(
    route('cms.hrms-onboarding.tasks.store', selectedTemplate.value.id),
    taskForm.value,
    {
      onSuccess: () => {
        selectedTemplate.value = null;
        taskForm.value = {
          task_name: '',
          task_category: 'documentation',
          assigned_to_role: 'hr',
          due_days_after_start: 0,
          is_mandatory: true,
        };
      },
    }
  );
};
</script>
